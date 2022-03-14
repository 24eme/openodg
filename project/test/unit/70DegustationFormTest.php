<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "pass AOC");
    return;
}

$t = new lime_test(137);

$annee = (date('Y')-1)."";
if ($annee < 8){
    $annee = $annee - 1;
}
$campagne = $annee.'-'.($annee + 1);
$date = $annee.'-09-01';
$degust_date = $date.' 12:45';
$degust_date_fr = '01/09/'.$annee;
$degust_time_fr = '12:45';
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$degustPorteurMemoire =  CompteTagsView::getInstance()->findOneCompteByTag('automatique', 'degustateur_porteur_de_memoire');


$degustTechnicien = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($degustPorteurMemoire->getSociete());
$degustTechnicien->nom = 'Actualys';
$degustTechnicien->prenom = 'Degustateur technicien';
$degustTechnicien->add('droits');
$degustTechnicien->droits->add(null, 'degustateur:technicien');
$degustTechnicien->save();

$degustUsager = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($degustPorteurMemoire->getSociete());
$degustUsager->nom = 'Actualys';
$degustUsager->prenom = 'Degustateur usager';
$degustUsager->add('droits');
$degustUsager->droits->add(null, 'degustateur:usager_du_produit');
$degustUsager->save();

foreach(ArchivageAllView::getInstance()->getDocsByTypeAndCampagne('Revendication', $campagne, 0, 99999, "%05d") as $r) {
    $doc = acCouchdbManager::getClient()->find($r->id);
    $doc->delete();
}
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}
foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $cd = ChgtDenomClient::getInstance()->find($k);
    $cd->delete(false);
}
foreach(DegustationClient::getInstance()->getHistory(100, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DegustationClient::getInstance()->deleteDoc(DegustationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}

$docid = "DEGUSTATION-".preg_replace("/[:\ -]+/", "", $degust_date);

$config = ConfigurationClient::getCurrent();
$produitconfig1 = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(!$produitconfig1) {
        $produitconfig1 = $produitconfig->getCepage();
        continue;
    }
    break;
}
$produitconfig_hash1 = $produitconfig1->getHash();
$lieu = "Lieu test — adresse lieu test";

$t->comment("prépartion avec une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $annee);
$chais = $drev->add('chais');
$chais->adresse = 'adresse Chai Drev';
$chais->code_postal = 'cp Chai Drev';
$chais->commune = 'commune Chai Drev';
$addrCompleteLgtDrev = $drev->constructAdresseLogement();

$drev->save();

$iddrev = $drev->_id;
$produit1 = $drev->addProduit($produitconfig_hash1);
$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$drev->addLot();
$drev->lots[0]->numero_logement_operateur = '1';
$drev->lots[0]->volume = 1;
$drev->lots[1] = clone $drev->lots[0];
$drev->lots[1]->numero_logement_operateur = '2';
$drev->lots[1]->volume = 2;
$drev->validate();
$drev->validateOdg();
$drev->save();

$t->ok($drev->lots[0]->numero_archive, "Numéro d'archive du lot 1");
$t->ok($drev->lots[0]->numero_dossier, "Numéro de dossier du lot 1");
$t->ok($drev->lots[1]->numero_archive, "Numéro d'archive du lot 2");
$t->ok($drev->lots[1]->numero_dossier, "Numéro de dossier du lot 2");
$t->is($drev->lots[1]->document_ordre, '01', "Document ordre du lot 1 est bien 01");
$t->is($drev->lots[1]->document_ordre, '01', "Document ordre du lot 2 est bien 01");
$t->is($drev->lots[1]->adresse_logement, $addrCompleteLgtDrev, "Lot drev a l'adresse de chai");
$t->ok(!$drev->hasLotsUtilises(), "La drev n'a pas de lot utilisée");

$t->comment($drev->_id);
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$lotPrelevable = current($lotsPrelevables);
$t->is(count($lotsPrelevables), 2, 'on a deux mouvements de lot prélevable');
$t->is($lotPrelevable->id_document_provenance, null, "L'id du document de provenance est null vu que le lot est un lot DRev");
$t->is($lotPrelevable->type_document, "DREV", "Le type de document du lot est DREV");

$t->comment("Création d'un lot de transaction");
$transaction = TransactionClient::getInstance()->createDoc($viti->identifiant, $campagne, $date);

$chais = $transaction->add('chais');
$chais->adresse = $adresseLogement;
$chais->code_postal = $cpLogement;
$chais->commune = $communeLogement;
$addrCompleteLgtTrans = $transaction->constructAdresseLogement();


$lot_transaction = $transaction->addLot();
$lot_transaction->produit_hash = $produitconfig_hash1;
$lot_transaction->volume = 15;
$lot_transaction->numero_logement_operateur = 'C12';

$transaction->validate();
$transaction->validateOdg();
$transaction->save();
$t->comment($transaction->_id);
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 3, 'on a un 3ème lot prélevable');

$t->is($transaction->lots[0]->adresse_logement,$addrCompleteLgtTrans, "Dans la transaction on a l'addresse de logement (depuis le chai)");

$t->comment("Test de la dégustation : $docid");
$t->comment("Création de la dégustation");

$degustation = DegustationClient::getInstance()->createDoc($degust_date);
$t->is($degustation->_id, $docid, "doc id");
$t->is($degustation->campagne, $campagne, "campagne à partir de la date");

$form = new DegustationCreationForm();
$values = array('date' => $degust_date_fr, 'time' => $degust_time_fr, 'lieu' => "Lieu test — adresse lieu test");

$form->bind($values);
$t->ok($form->isValid(), "Le formulaire de création est valide");
$degustation = $form->save();
$t->ok($degustation->_id, "la création donne un id à la degustation");
$t->is($degustation->_id, $docid, "doc id");

$degustation = DegustationClient::getInstance()->find($degustation->_id);
$t->is($degustation->date, $degust_date.":00", "La date de la degustation est la bonne");
$t->is($degustation->getDateFormat(), $date, "La méthode getDateFormat renvoi la date");
$t->is($degustation->lieu, $lieu, "Lieu de la dégustation");
$t->is($degustation->getLieuNom(), "Lieu test", "Nom du lieu de la dégustation");

$t->comment("Prélèvement");
$form = new DegustationSelectionLotsForm($degustation);

$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation->_rev,
);

$valuesRev['lots'][$drev->lots[0]->getUnicityKey()]['preleve'] = 1;
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;
$valuesRev['lots'][$transaction->lots[0]->getUnicityKey()]['preleve'] = 1;

$t->ok(isset($valuesRev['lots'][$drev->lots[0]->getUnicityKey()]), 'On retrouve le lot 1 de la DREV dans le formulaire sur la base de la vue');
$t->ok(isset($valuesRev['lots'][$drev->lots[1]->getUnicityKey()]), 'On retrouve le lot 2 de la DREV dans le formulaire sur la base de la vue');
$t->ok(isset($valuesRev['lots'][$transaction->lots[0]->getUnicityKey()]), 'On retrouve le lot de la transaction dans le formulaire sur la base de la vue');

$form->bind($valuesRev);
$form->save();

$degustation = DegustationClient::getInstance()->find($degustation->_id);

$t->is(count($degustation->lots), 3, 'Il y a 3 lots dans la dégustation');

$t->is($degustation->lots[0]->date, (new DateTime($degustation->date))->format('Y-m-d'), "Le lot à pour date celle de la degustation");
$t->is($degustation->lots[0]->date_commission, $degustation->getDateFormat('Y-m-d'), "Le lot de la commission à pour date celle de la degustation");
$t->is($degustation->lots[0]->initial_type, DRevClient::TYPE_MODEL, "L'initial type par défaut est ".DRevClient::TYPE_MODEL);
$degustation->lots[0]->initial_type = null;
$t->is($degustation->lots[0]->initial_type, DRevClient::TYPE_MODEL, "L'initial type calculé est ".DRevClient::TYPE_MODEL);
$t->is($degustation->lots[0]->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->initial_type, $degustation->lots[0]->initial_type, "L'initial type du mouvement");
$t->is($degustation->lots[0]->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->date_commission, $degustation->lots[0]->date_commission, "La date de commission du mouvement");

$t->is($degustation->lots[2]->initial_type, TransactionClient::TYPE_MODEL, "L'initial type par défaut est ".TransactionClient::TYPE_MODEL);
$degustation->lots[2]->initial_type = null;
$t->is($degustation->lots[2]->initial_type, TransactionClient::TYPE_MODEL, "L'initial type calculé est ".TransactionClient::TYPE_MODEL);
$t->is($degustation->lots[2]->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->initial_type, $degustation->lots[2]->initial_type, "L'initial type du mouvement");


$t->is($degustation->lots[0]->getUniqueId(), $campagne."-00001-00001", "Le lot 1 de la dégustation a bien la clé unique 2020-2021-00001-00001");
$t->is($degustation->lots[1]->getUniqueId(), $campagne."-00001-00002", "Le lot 2 de la dégustation a bien la clé unique 2020-2021-00001-00002");
$t->is($degustation->lots[2]->getUniqueId(), $campagne."-00002-00003", "Le lot 3 de la dégustation a bien la clé unique 2020-2021-00002-00003");
$t->is($degustation->lots[1]->adresse_logement,$addrCompleteLgtDrev, "Dans la dégustation on a l'addresse de logement depuis la DRev");

$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($degustation->lots[0]), 1, "Il y a une affectation source avant celle-ci pour le lot 1 (de transaction) de la dégustation");
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($degustation->lots[1]), 1, "Il y a une affectation source avant celle-ci pour le lot 2 (de la drev) de la dégustation");
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($degustation->lots[2]), 1, "Il y a une affectation source avant celle-ci pour le lot 3 (de la drev) de la dégustation");
$t->is($degustation->lots[0]->getNombrePassage(), 1, "Le lot 1 de la dégustation a est bien à son premier passage");
$t->is($degustation->lots[1]->getNombrePassage(), 1, "Le lot 2 de la dégustation a est bien à son premier passage");
$t->is($degustation->lots[2]->getNombrePassage(), 1, "Le lot 3 de la dégustation a est bien à son premier passage");
$t->is($degustation->lots[0]->document_ordre, '02', "Le lot 1 de la dégustation a bien 02 comme document d'ordre");
$t->is($degustation->lots[1]->document_ordre, '02', "Le lot 2 de la dégustation a bien 02 comme document d'ordre");
$t->is($degustation->lots[2]->document_ordre, '02', "Le lot 3 de la dégustation a bien 02 comme document d'ordre");
$t->is($degustation->lots[0]->id_document_provenance, $drev->_id, "La provenance du lot 1 de la dégustation est bien la DREV ".$drev->_id);
$t->is($degustation->lots[1]->id_document_provenance, $drev->_id, "La provenance du lot 2 de la dégustation est bien la DREV ".$drev->_id);
$t->is($degustation->lots[2]->id_document_provenance, $transaction->_id, "La provenance du lot 3 de la dégustation est bien la transaction ".$transaction->_id);

$drev = DRevClient::getInstance()->find($iddrev);
$t->ok($drev->hasLotsUtilises(), "La drev a des lots utilisée");
$t->is($drev->lots[0]->id_document_affectation, $degustation->_id, "L'affectation du lot 1 dans la DREV est bien ".$degustation->_id);
$t->is($drev->lots[0]->date, $drev->getDateValidation('Y-m-d'), "La date drev du lot 1 est toujours celle de la DREV");
$t->is($drev->lots[0]->date_commission, $degustation->getDateFormat('Y-m-d'), "La date de la commission est dans le lot 1 de la DREV");
$t->is($drev->lots[1]->date, $drev->getDateValidation('Y-m-d'), "La date drev du lot 2 est toujours celle de la DREV");
$t->is($drev->lots[1]->id_document_affectation, $degustation->_id, "L'affectation du lot 2 dans la DREV est bien ".$degustation->_id);
$t->is($drev->lots[1]->date_commission, $degustation->getDateFormat('Y-m-d'), "La date de la commission est dans le lot 2 de la DREV");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_AFFECTE_SRC), "Le lot de la drev a un mouvement affecté");
$t->ok(!$drev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot de la drev a un mouvement affectable");
$t->ok(!$drev->lots[0]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le lot de la drev ");
$t->ok(!$drev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot de la drev n'est pas cheagable");
$t->ok(!$drev->lots[0]->isChange(), "Le lot n'est pas changé");
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($drev->lots[0]), 0, "Il n'y pas a d'affectation source avant celle-ci pour le lot 1 dans la DREV");
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($drev->lots[1]), 0, "Il n'y pas a d'affectation source avant celle-ci pour le lot 2 dans la DREV");

$transaction = TransactionClient::getInstance()->find($transaction->_id);
$t->is($transaction->lots[0]->date, $transaction->getDateValidation('Y-m-d'), "La date drev du lot 1 est toujours celle de la transaction");
$t->is($transaction->lots[0]->id_document_affectation, $degustation->_id, "L'affectation du lot 1 dans la transaction est bien ".$degustation->_id);
$t->is($transaction->lots[0]->date_commission, $degustation->getDateFormat('Y-m-d'), "La date de la commission est dans le lot 1 de la transaction");

$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($transaction->lots[0]), 0, "Il n'y pas a d'affectation source avant celle-ci pour le lot 1 dans la DREV");

$t->is(count($degustation->mouvements_lots->get($drev->identifiant)), 6, "Il y a 4 mouvements");
$t->is(count($degustation->lots[0]->getMouvements()), 2, "Le lot 1 à deux mouvements");
$t->is(count($degustation->lots[1]->getMouvements()), 2, "Le lot 2 à deux mouvements");
$t->is(count($degustation->lots[2]->getMouvements()), 2, "Le lot 3 à deux mouvements");
$t->ok($degustation->lots[0]->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Il a un mouvement attente prelevement");
$t->ok($degustation->lots[0]->getMouvement(Lot::STATUT_AFFECTE_DEST), "Il a un mouvement affecté destination");
$t->ok(!$degustation->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Il a un mouvement n'est plus affectable");
$t->ok($degustation->lots[1]->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Il a un mouvement attente prelevement");
$t->ok($degustation->lots[1]->getMouvement(Lot::STATUT_AFFECTE_DEST), "Il a un mouvement affecté destination");
$t->ok(!$degustation->lots[1]->getMouvement(Lot::STATUT_AFFECTABLE), "Il a un mouvement n'est plus affectable");
$t->ok($degustation->lots[2]->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Il a un mouvement attente prelevement");
$t->ok($degustation->lots[2]->getMouvement(Lot::STATUT_AFFECTE_DEST), "Il a un mouvement affecté destination");
$t->ok(!$degustation->lots[2]->getMouvement(Lot::STATUT_AFFECTABLE), "Il a un mouvement n'est plus affectable");

$t->is(count(DegustationClient::getInstance()->getLotsPrelevables()), 0, "Il n'y a plus de mouvement prélevable");

$t->comment('on ajoute un leurre, on revient pour décocher un lot, le leurre ne doit pas avoir disparu');
$degustation->addLeurre($degustation->lots[0]->produit_hash, null, date('Y'), 1);
$degustation->save();
$degustation = DegustationClient::getInstance()->find($degustation->_id);

$t->is(count($degustation->lots), 4, "3 lots + 1 leurre");

$t->comment('On décoche les lots et on en sélectionne qu\'un');
$form = new DegustationSelectionLotsForm($degustation);

$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation->_rev,
);

foreach ($valuesRev['lots'] as &$lot) {
    unset($lot['preleve']);
}
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;

$form->bind($valuesRev);
$form->save();

$degustation = DegustationClient::getInstance()->find($degustation->_id);
$t->is(count($degustation->lots), 2, 'Il y a un lot et un leurre dans la dégustation');
$t->is($degustation->lots[0]->getNombrePassage(), 1, "Le numero de passage du lot restant est bien toujours 1");
$t->is($degustation->lots[0]->id_document, $degustation->_id, "Le doc id du seul lot restant est bien ".$degustation->_id);
$t->is($degustation->lots[0]->id_document_provenance, $drev->_id, "La provenance du seul lot restant est bien toujours ".$drev->_id);
$t->is($degustation->lots[0]->document_ordre, '02', "Le document ordre du seul lot restant est bien toujours 02");
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($degustation->lots[0]), 1, "Le lot qui reste dans la dégut a bien une affectation source");
$t->is($degustation->lots[1]->isLeurre(), true, "Le lot 2 est un leurre");

$t->is(count(DegustationClient::getInstance()->getLotsPrelevables()), 2, "Il y a 2 mouvements prélevables (1 de la transaction, l'autre de la drev)");

$drev = DRevClient::getInstance()->find($iddrev);
$transaction = TransactionClient::getInstance()->find($transaction->_id);
$t->is($transaction->lots[0]->id_document_affectation, null, "L'affectation du lot de la transaction n'est plus ".$degustation->_id);
$t->is($drev->lots[0]->id_document_affectation, null, "L'affectation du lot 1 dans la DREV n'est plus ".$degustation->_id);
$t->is($drev->lots[1]->id_document_affectation, $degustation->_id, "L'affectation du lot 2 dans la DREV est bien ".$degustation->_id);

$lotDegustation = $degustation->lots[0];
$lotDrev = $drev->lots[1];

$t->ok($lotDegustation, 'Lot à dégusté depuis la drev enregistré');
$t->ok($lotDrev, "Le lot de la drev a pu être récupéré depuis la dégustation");

$t->is($lotDegustation->id_document, $degustation->_id, "Id du document qui a ajouté le lot");
$t->is($lotDegustation->getUniqueId(), $drev->lots[1]->getUniqueId(), "Le lot à la même clé unique");
$t->is($lotDegustation->volume, $lotDrev->volume, 'Le lot a le bon volume');
$t->is($lotDegustation->numero_logement_operateur, $lotDrev->numero_logement_operateur, 'Le lot a le bon numero de cuve');
$t->is($lotDegustation->numero_dossier, $lotDrev->numero_dossier, 'Le lot a le bon numero de cuve');
$t->is($lotDegustation->declarant_identifiant, $drev->identifiant, 'Le lot a le bon declarant');
$t->is($lotDegustation->declarant_nom, $drev->declarant->raison_sociale, 'Le lot a le bon nom de declarant');
$t->is($lotDegustation->produit_hash, $lotDrev->produit_hash, 'Le lot a la bonne hash produit');
$t->is($lotDegustation->produit_libelle, $lotDrev->produit_libelle, 'Le lot a le bon libellé produit');
$t->is($lotDegustation->specificite, $lotDrev->specificite, 'Le lot a le bonne spécificité');

$t->is($lotDegustation->millesime, $lotDrev->millesime, 'Le lot a le bon millésoùe');
$t->is($lotDegustation->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, 'Le lot a le bon statut');
$t->is($lotDegustation->id_document_provenance, $drev->_id, 'La provenance du lot est la drev');
$t->is($lotDegustation->getTypeProvenance(), "DREV", 'La provenance est DRev');

$t->is(count($degustation->mouvements_lots->get($drev->identifiant)->toArray(true, false)), 2, 'La génération de mouvement a généré 2 mouvements');
$t->ok($lotDegustation->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Mouvement de lot en attente de prelevement présent");
$t->is($lotDegustation->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->document_ordre, "02", "Document d'ordre du mouvement");
$t->is($lotDegustation->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->document_type, "Degustation", "Document type du mouvement");
$t->is($lotDegustation->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->document_id, $degustation->_id, "Document id du mouvement");
$t->ok($lotDegustation->getMouvement(Lot::STATUT_AFFECTE_DEST), "Mouvement de lot affecté destination présent");

$lotProvenance = $lotDegustation->getLotProvenance();

$t->ok($lotProvenance->getDocument()->_id, $drev->_id, "Récupération du document du lot père");
$t->ok($lotProvenance->getUniqueId(), $lotDrev->getUniqueId(), "Récupération du lot père");
$t->is($lotProvenance->id_document_affectation, $degustation->_id, "Dans la drev le lot est relié à la dégustation");
$t->ok(!$lotProvenance->getMouvement(Lot::STATUT_AFFECTABLE), "Pas de mouvement affectable dans la drev");
$t->ok($lotProvenance->getMouvement(Lot::STATUT_AFFECTE_SRC), "Mouvement affecte dans la drev");

$t->comment("Prélévé");

$form = new DegustationPreleveLotsForm($degustation);
$defaults = $form->getDefaults();
$t->is($defaults['lots'][0]['preleve'], false, "Le lot est marqué comme non prélevé dans le form");
$valuesRev = array(
    '_revision' => $degustation->_rev,
    'lots' => array()
);

$valuesRev['lots'][0]['preleve'] = true;

$form->bind($valuesRev);
$form->save();
$degustation = DegustationClient::getInstance()->find($degustation->_id);
$t->is($degustation->lots[0]->statut, Lot::STATUT_PRELEVE, 'Le lot est marqué comme prélevé');
$t->is($degustation->lots[0]->preleve, date('Y-m-d'), 'Le lot a bien une date de prélèvement à aujourd\'hui');

$degustation->generateMouvementsLots();
$t->is(count($degustation->mouvements_lots->{$drev->identifiant}), 3, 'La génération a généré 3 mouvements');

$form = new DegustationPreleveLotsForm($degustation);
$defaults = $form->getDefaults();

$t->is($defaults['lots'][0]['preleve'], true, "Le lot est marqué comme prélevé dans le form");

$t->comment('Changement de logement');
$degustation->updateLotLogement($degustation->lots[0], $degustation->lots[0]->numero_logement_operateur + 1);
$t->is($degustation->lots[0]->numero_logement_operateur, $lotDrev->numero_logement_operateur + 1, "Le changement de logement est effectif dans la dégustation");

$t->comment("Dégustateurs");
$formPorteurDeMemoire = new DegustationSelectionDegustateursForm($degustation, array(), array('college' => 'degustateur_porteur_de_memoire'));
$defaultsPorteurDeMemoire = $formPorteurDeMemoire->getDefaults();

$t->ok(isset($defaultsPorteurDeMemoire['degustateurs']['degustateur_porteur_de_memoire'][$degustPorteurMemoire->_id]), 'Notre dégustateur est dans le formulaire comme porteur de mémoire');
$valuesRev = array(
    '_revision' => $degustation->_rev,
);

$valuesRev['degustateurs']['degustateur_porteur_de_memoire'][$degustPorteurMemoire->_id]['selectionne'] = 1;
$formPorteurDeMemoire->bind($valuesRev);
$formPorteurDeMemoire->save();
$t->is(count($degustation->degustateurs->degustateur_porteur_de_memoire), 1, 'On a bien notre dégustateur porteur de mémoire');

$degustation = DegustationClient::getInstance()->find($degustation->_id);

$formTechnicien = new DegustationSelectionDegustateursForm($degustation, array(), array('college' => 'degustateur_technicien'));
$defaultsTechnicien = $formTechnicien->getDefaults();
$t->ok(isset($defaultsTechnicien['degustateurs']['degustateur_technicien'][$degustTechnicien->_id]), 'Notre dégustateur est dans le formulaire comme technicien');
$valuesRev = array(
    '_revision' => $degustation->_rev,
);

$valuesRev['degustateurs']['degustateur_technicien'][$degustTechnicien->_id]['selectionne'] = 1;
$formTechnicien->bind($valuesRev);
$formTechnicien->save();
$t->is(count($degustation->degustateurs->degustateur_technicien), 1, 'On a bien le dégustateur technicien');

$degustation = DegustationClient::getInstance()->find($degustation->_id);

$formUsager = new DegustationSelectionDegustateursForm($degustation, array(), array('college' => 'degustateur_usager_du_produit'));
$defaultsUsager = $formUsager->getDefaults();
$t->ok(isset($defaultsUsager['degustateurs']['degustateur_usager_du_produit'][$degustPorteurMemoire->_id]), 'Notre dégustateur est dans le formulaire comme usager du produit');

$valuesRev = array(
    '_revision' => $degustation->_rev,
);

$valuesRev['degustateurs']['degustateur_usager_du_produit'][$degustUsager->_id]['selectionne'] = 1;

$formUsager->bind($valuesRev);
$formUsager->save();

$t->is(count($degustation->degustateurs->degustateur_usager_du_produit), 1, 'On a bien le dégustateur usager du produit');

$degustation = DegustationClient::getInstance()->find($degustation->_id);
$t->is(count($degustation->degustateurs), 3, 'On a bien les trois collèges');


$t->comment('Présence dégustateur');
$t->comment('On confirme les deux premiers degustateurs');
$degustation->degustateurs->degustateur_usager_du_produit->get($degustUsager->_id)->add('confirmation', 1);
$degustation->degustateurs->degustateur_technicien->get($degustTechnicien->_id)->add('confirmation', 1);

$t->is($degustation->hasAllDegustateursConfirmation(), false, "Les dégustateurs n'ont pas tous signalé leurs présence");

$t->comment('On confirme le dernier degustateur');
$degustation->degustateurs->degustateur_porteur_de_memoire->get($degustPorteurMemoire->_id)->add('confirmation', 1);

$t->is($degustation->hasAllDegustateursConfirmation(), true, "Les dégustateurs ont tous signalé leurs présence");
