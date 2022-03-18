<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}

function countMouvements($degustation) {
    $nb_mvmts = 0;

    foreach ($degustation->mouvements_lots as $ope) {
        foreach ($ope as $m) {
            $nb_mvmts++;
        }
    }

    return $nb_mvmts;
}

$t = new lime_test(65);

$annee = (date('Y')-1)."";
$campagne = $annee.'-'.($annee + 1);
$degust1_date_fr = '09/09/'.$annee;
$degust2_date_fr = '11/11/'.$annee;
$degust1_time_fr = '09:09';
$degust2_time_fr = '11:11';
$degust1_date = $annee.'-09-01 '.$degust1_time_fr;
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$degust =  CompteTagsView::getInstance()->findOneCompteByTag('automatique', 'degustateur_porteur_de_memoire');

//Suppression des docs précédents
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = TransactionClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation1 = DegustationClient::getInstance()->find($k);
    $degustation1->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

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
$commissions = array('lieu 1', 'lieu 2');

$t->comment("prépartion avec une DRev");
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $annee);
$drev->save();
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
$t->comment($drev->_id);

//Début des tests
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$lotsEnManquement = DegustationClient::getInstance()->getManquements();

$t->is(count($lotsPrelevables), 2, "Il y a 2 mouvements prélevables");
$t->is(count($lotsEnManquement), 0, "Il y a 0 mouvement en manquement");
$t->is($drev->lots[1]->document_ordre, '01', "le numéro d'ordre du lot de la DREV est bien 01");
$t->is($drev->lots[1]->getNombrePassage(), 0, 'Le numero de passage est bien 0');

$t->comment("Création d'une première degustation");
$degustation1 = new Degustation();
$form = new DegustationCreationForm($degustation1);
$values = array('date' => $degust1_date_fr, 'time' => $degust1_time_fr, 'lieu' => $commissions[0]);
$form->bind($values);
$degustation1 = $form->save();
$iddegust1 = $degustation1->_id;
$t->comment($iddegust1);

$t->comment("Sélection des lots");
$form = new DegustationSelectionLotsForm($degustation1);
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation1->_rev,
);
$valuesRev['lots'][$drev->lots[0]->getUnicityKey()]['preleve'] = 1;
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;
$form->bind($valuesRev);
$form->save();

$degustation1->lots[0]->preleve = date('Y-m-d');
$degustation1->lots[0]->numero_table = 1;
$degustation1->lots[1]->preleve = date('Y-m-d');
$degustation1->lots[1]->numero_table = 2;

$degustation1->anonymize();
$degustation1->save();


$t->is($degustation1->lots[0]->numero_table, 1, 'Le lot 1 est atablé sur la table 1');
$t->is($degustation1->lots[1]->numero_table, 2, 'Le lot 2 est atablé sur la table 2');
$t->is($degustation1->lots[0]->numero_anonymat, "A01", 'Le lot 1 est bien anonymisé');
$t->is($degustation1->lots[1]->numero_anonymat, "B01", 'Le lot 2 est bien anonymisé');
$t->is($degustation1->lots[0]->_get('date_commission'), $degustation1->getDateFormat('Y-m-d'), 'Le lot 1 est à une date de commission');
$t->is($degustation1->lots[1]->_get('date_commission'), $degustation1->getDateFormat('Y-m-d'), 'Le lot 2 est à une date de commission');

$drev = DRevClient::getInstance()->find($drev->_id);
$t->is($drev->lots[0]->_get('date_commission'), $degustation1->lots[0]->date_commission, 'La date de commission du lot dans de la drev est celui de la degustation');
$t->is($drev->lots[1]->_get('date_commission'), $degustation1->lots[1]->date_commission, 'La date de commission du lot dans de la drev est celui de la degustation');

$t->comment("Conformité des lots");
$degustation1->lots[0]->statut = Lot::STATUT_CONFORME;
$degustation1->lots[0]->email_envoye = date('Y-m-d');
$degustation1->lots[0]->motif = "très bon";

$degustation1->lots[1]->statut = Lot::STATUT_NONCONFORME;
$degustation1->lots[1]->email_envoye = date('Y-m-d');
$degustation1->lots[1]->conformite = Lot::CONFORMITE_NONCONFORME_MAJEUR;
$degustation1->lots[1]->motif = "oeuf pourri";

$degustation1->save();

$degustation1 = DegustationClient::getInstance()->find($iddegust1);
$t->is($degustation1->lots[0]->getNombrePassage(), 1, 'Le numero de passage du lot 1 de la degustation 1 est bien 1');
$t->is($degustation1->lots[1]->getNombrePassage(), 1, 'Le numero de passage du lot 2 de la degustation 1 est bien 1');
$t->is($degustation1->lots[0]->email_envoye, date('Y-m-d'), 'La notification du lot 1 est à la date du jour');
$t->is($degustation1->lots[1]->email_envoye, date('Y-m-d'), 'La notification du lot 2 est à la date du jour');

$lot_degust1 = $degustation1->lots[1];
$t->ok($lot_degust1->getMouvement(Lot::STATUT_NONCONFORME), "Le lot est non conforme");
$t->ok($lot_degust1->getMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE), 'Le lot à un manquement en attente');

$t->comment("On enresgistre une redégustation");
$lot_degust1->redegustation();
$degustation1->save();

$t->ok($lot_degust1->getMouvement(Lot::STATUT_NONCONFORME), "Le lot est toujours non conforme");
$t->ok(! $lot_degust1->getMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE), "Le lot n'est plus en manquement en attente");
$t->ok($lot_degust1->affectable, "Le lot est affectable");
$t->ok($lot_degust1->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot a un mouvement affectable");
$t->ok(!$lot_degust1->getMouvement(Lot::STATUT_AFFECTE_SRC), "Le lot n'a pas encore de mouvement affecte source");

$t->is($lot_degust1->getMouvement(Lot::STATUT_AFFECTABLE)->detail, '2me passage', "Le mouvement en attente de redégustation provenant de la 1ère dégustation est indiqué comme en attente de 2ème dégustation");

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$lotsEnManquement = DegustationClient::getInstance()->getManquements();

$t->is(count($lotsPrelevables), 1, "Il y a 1 mouvement prélevable");
$t->is(count($lotsEnManquement), 0, "Il y a 0 mouvement en manquement");

$lotPrelevable = current($lotsPrelevables);
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($lotPrelevable), 1, "Le lot prélevable sait qu'il a une dégust précédente");
$t->is($lotPrelevable->specificite, "2ème dégustation", "La spécificité du lot prélevable est : 2ème dégustation");
$t->is($lotPrelevable->id_document, $degustation1->_id, "Le lot prélevable de la degust 1 a été généré par ".$degustation1->_id);
$t->is($lotPrelevable->id_document_provenance, $drev->_id, "Le lot prélevable de la degust 1 a pour origine ".$drev->_id);
$t->is($lotPrelevable->id_document_affectation, null, "Le lot prélevable de la degust 1 n'est pas encore affecté");

$t->comment('Deuxième degustation');
$degustation2 = new Degustation();
$form = new DegustationCreationForm($degustation2);
$values = array('date' => $degust2_date_fr, 'time' => $degust2_time_fr, 'lieu' => $commissions[1]);
$form->bind($values);
$degustation2 = $form->save();
$t->comment($degustation2->_id);

$t->comment("Sélection du lot dans la 2de dégustation");
$form = new DegustationSelectionLotsForm($degustation2);
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation2->_rev,
);
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;
$form->bind($valuesRev);
$form->save();
$degustation1 = DegustationClient::getInstance()->find($degustation1->_id);
$degustation2 = DegustationClient::getInstance()->find($degustation2->_id);

$t->is(count($degustation2->lots), 1, "La nouvelle degust à 1 lot");
$lot_degust1 = $degustation1->lots[1];
$lot_degust2 = $degustation2->lots[0];
$drev = DRevClient::getInstance()->find($drev->_id);

$t->is(array_keys($lot_degust2->getMouvements()), array($campagne.'-00001-00002-02-ATTENTE-PRELEVEMENT', $campagne.'-00001-00002-01-AFFECTE-DEST'), "Le lot a deux mouvements (effecté et attente de prlvmt) vu qu'il n'a pas été encore prélevé");
$t->is($lot_degust2->numero_archive, $lot_degust1->numero_archive, "Le numero archive de la dégustation 2 n'a pas changé par rapport à la dégustation 1");
$t->is($lot_degust2->numero_dossier, $lot_degust1->numero_dossier, "Le numero dossier de la dégustation 2 n'a pas changé à la dégustation 1");
$t->is($lot_degust2->email_envoye, null, "La date d'envoi de mail a été réinitialisée");
$t->is($lot_degust2->preleve, null, "La date de prélèvement a été réinitialisée");
$t->is($lot_degust2->conformite, null, "La Conformité a été réinitialisée");
$t->is($lot_degust2->motif, null, "Le motif a été réinitialisé");
$t->is($lot_degust2->numero_table, null, "La table a bien été réinitialisée");
$t->is($lot_degust2->numero_anonymat, null, "L'anonyma a bien été réinitialisé");

$t->is($lot_degust2->id_document, $degustation2->_id, "L'id du doc du mouvement est la même degustation");
$t->ok($lot_degust2->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Le lot a le mouvement ".Lot::STATUT_ATTENTE_PRELEVEMENT);
$t->ok($lot_degust2->getMouvement(Lot::STATUT_AFFECTE_DEST), "Le lot a le mouvement ".Lot::STATUT_AFFECTE_DEST);
$t->ok(!$lot_degust2->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot n'a pas de mouvement ".Lot::STATUT_AFFECTABLE);
$t->is($lot_degust2->id_document_provenance, $degustation1->_id, "Le lot affecté a bien un document de provenance indiqué comme ".$degustation1->_id);
$t->is($lot_degust2->document_ordre, '03', "Le numéro d'ordre du lot de la 2d dégustation est bien 03");
$t->is($lot_degust2->getNombrePassage(), 2, "Le lot de la deuxième degust a comme numero de passage 2");
$t->ok($lot_degust2->isSecondPassage(), "Le lot de la deuxième degust est bien en isSecondPassage()");
$t->is($lot_degust2->affectable, false, "Le lot de la 2d dégustation n'est plus affectable");
$t->is(MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($lot_degust2), 2, "Il y a deux affecte source avant le lot de la 2de dégustation");
$t->is($lot_degust2->specificite, "2ème dégustation", "La spécificité du lot attribué à la 2de dégustation est : 2ème dégustation");
$t->is($lot_degust2->_get('date_commission'), $degustation2->getDateFormat('Y-m-d'), 'Le lot de la dégustation à la même date de commission que la date de la dégustation');
$t->is($lot_degust2->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT)->date_commission, $lot_degust2->date_commission, "La date de commission du mouvement");

$t->is($lot_degust1->document_ordre, '02', "Le numéro d'ordre du lot de la degustation 1 est bien 02");
$t->is($lot_degust1->id_document_affectation, $degustation2->_id, "Le lot de la degust 1 est bien affecté à la degustation 2 ".$degustation2->_id);
$t->is($lot_degust1->id_document_provenance, $drev->_id, "Le lot de la degust 1 vient bien toujours de la drev ".$drev->_id);
$t->ok($lot_degust1->getMouvement(Lot::STATUT_AFFECTE_SRC), "Le lot de la degust 1 est bien en affecte_src");
$t->ok(!$lot_degust1->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot dans la 1ère dégustation n'est plus affectable / ".Lot::STATUT_AFFECTABLE);
$t->is($lot_degust1->getNombrePassage(), 1, "Le lot de la première degust a toujours comme numero de passage 1");
$t->is($lot_degust1->_get('date_commission'), $degustation1->getDateFormat('Y-m-d'), 'Le lot de la dégustation à la même date de commission que la date de la dégustation');

$t->is($drev->lots[0]->id_document_affectation, $degustation1->_id, "Le lot de la drev est resté affecté à la degustation 1 ".$degustation1->_id);
$t->is($drev->lots[0]->_get('date_commission'), $degustation1->getDateFormat('Y-m-d'), 'Le lot de la drev à la même date de commission que la 1ère dégustation');

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 0, "Il y a 0 mouvement prélevable");
$t->is(count(DegustationClient::getInstance()->getManquements()), 0, "Il y a 0 mouvement en manquement");
