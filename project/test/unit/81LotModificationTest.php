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

$t = new lime_test(58);

$annee = (date('Y')-1)."";
$campagne = $annee.'-'.($annee + 1);
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
//Suppression des templates de facturation
foreach(TemplateFactureClient::getInstance()->findAll() as $k => $doc) {
    TemplateFactureClient::getInstance()->delete($doc);
}

acCouchdbManager::getClient()->storeDoc(json_decode(file_get_contents(sfConfig::get('sf_data_dir')."/configuration/".$application."/TEMPLATE-FACTURE-".$annee.".json")));

$template = TemplateFactureClient::getInstance()->findByCampagne($campagne);
foreach($template->cotisations as $kg => $gcotis) {
    foreach($gcotis->details as $kc => $cotis) {
        if (!preg_match('/detail_identifiant/', $cotis->libelle)) {
            $cotis->libelle .= ' %detail_identifiant%';
        }
    }
}
$template->save();

$config = ConfigurationClient::getCurrent();
$produitconfig1 = null;
$produitconfig2 = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(!$produitconfig1) {
        $produitconfig1 = $produitconfig->getCepage();
        continue;
    }
    if(!$produitconfig2) {
        $produitconfig2 = $produitconfig->getCepage();
        break;
    }
}

$commissions = DegustationConfiguration::getInstance()->getLieux();

$t->comment("Préparation de la DRev");

$dateValidation = new DateTime();
$dateValidation = $dateValidation->modify('-1 month')->format('Y-m-d');
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $annee);
$drev->save();
$produit1 = $drev->addProduit($produitconfig1->getHash());
$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$drev->addLot();
$drev->lots[0]->numero_logement_operateur = '1';
$drev->lots[0]->volume = 1;
$drev->validate($dateValidation);
$drev->validateOdg($dateValidation);
$drev->save();

$lotDrev = $drev->lots[0];
$dateDegustation = new DateTime();
$dateDegustation = $dateDegustation->modify('+5 days')->format('Y-m-d');
$degustation = DegustationClient::getInstance()->createDoc($dateDegustation.' 09:24:00');
$degustation->setLots(array($drev->lots[0]));
$degustation->save();

$t->comment('Récupération du lot');

$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id, "01");

$t->ok($lot->getMouvement(Lot::STATUT_REVENDIQUE), "Mouvement de lot revendiqué");
$t->is($lot->unique_id, $drev->lots[0]->unique_id, "Le lot récupéré à le même unique id");
$t->is($lot->id_document, $drev->_id, "Le lot récupéré provient de la drev");
$t->is($lot->document_ordre, "01", "Le lot récupéré a le numéro d'ordre 01");
$t->is($lot->id_document_provenance, null, "Le lot de la drev n'a pas de document de provenance");

$t->comment('Modification du lot');

$form = new LotModificationForm($lot);

$values = array();
$values['volume'] = 11;
$values['produit_hash'] = $produitconfig2->getHash();
$values['numero_logement_operateur'] = "A";
$values['millesime'] = "2021";
$values['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$values['destination_date'] = date('d/m/Y');
$values['specificite'] = 'bio';
$values['cepage_0'] = 'CHENIN B';
$values['repartition_hl_0'] = 11;
$values['_revision'] = $lot->getDocument()->_rev;

$form->bind($values);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$drev = DRevClient::getInstance()->find($drev->_id);
$lotDrev = $drev->getLot($lot->unique_id);
$drevM01 = $drev->findMaster();
$lotDrevM01 = $drevM01->getLot($lot->unique_id);
$degustation = DegustationClient::getInstance()->find($degustation->_id);
$lotDegustation = $degustation->getLot($lot->unique_id);

$t->is($lotDrev->volume, 1,"Le volume de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->produit_hash, $produitconfig1->getHash(),"Le produit de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->cepages->toArray(true, false), array(),"Le cépage de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->numero_logement_operateur, '1',"Le numéro de logement de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->millesime, null,"Le millésime de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->destination_type, null,"La destination de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->destination_date, null,"La date de destination de la drev d'origine n'a pas été modifiée");
$t->is($lotDrev->specificite, null,"La spécificité de la drev d'origine n'a pas été modifiée");
$t->is(count($lotDrev->getMouvements()), 0, "Le lot de la drev d'origine n'a aucun mouvement");

$t->is($drevM01->_id, $drev->_id.'-M01',"La modification du lot a créé une modificatrice");
$t->is($drevM01->numero_archive, $drev->numero_archive, "La modificatrice a le même numero d'archive que la drev d'origine");
$t->is($lotDrevM01->id_document, $drevM01->_id, "L'id document du lot de la modificatrice est celui de la modificatrice");
$t->ok($lotDrevM01->getMouvement(Lot::STATUT_REVENDIQUE), "Mouvement de lot revendiqué");
$t->is($lotDrevM01->volume, $values['volume'], "Le lot de la modificatrice a le nouveau volume");
$t->is($lotDrevM01->produit_hash, $values['produit_hash'],"Le produit de la drev modificatrice a évolué");
$t->is($lotDrevM01->cepages->toArray(true, false), array('CHENIN B' => 11),"Le cépage de la drev modificatrice a évolué");
$t->is($lotDrevM01->numero_logement_operateur, $values['numero_logement_operateur'],"Le numéro de logement de la drev modificatrice a évolué");
$t->is($lotDrevM01->millesime, $values['millesime'],"Le millésime de la drev modificatrice a évolué");
$t->is($lotDrevM01->destination_type, $values['destination_type'],"La destination type de la drev modificatrice a évolué");
$t->is($lotDrevM01->destination_date, date('Y-m-d'),"La date de destination de la drev modificatrice a évolué");
$t->is($lotDrevM01->specificite, $values['specificite'],"La specificité de la drev modificatrice a évolué");
$t->is($lotDrevM01->date, $lotDrev->date, "La date du lot est la même");
$t->is($lotDrevM01->numero_dossier, $lotDrev->numero_dossier, "Le dossier du lot de la drev M01 est le même que celui de la M00");

$t->is($lotDegustation->volume, $values['volume'], "Le lot de la dégusation a le nouveau volume");
$t->is($lotDegustation->produit_hash, $values['produit_hash'],"Le produit de la dégustation a évolué");
$t->is($lotDegustation->cepages->toArray(true, false), array('CHENIN B' => 11),"Les cépage de la dégustation a évolué");
$t->is($lotDegustation->numero_logement_operateur, $values['numero_logement_operateur'],"Le numéro de logement de la dégustation a évolué");
$t->is($lotDegustation->millesime, $values['millesime'],"Le millésime de la dégustation a évolué");
$t->is($lotDegustation->destination_type, $values['destination_type'],"La destination type de la dégustation a évolué");
$t->is($lotDegustation->destination_date, date('Y-m-d'),"La date de destination de la dégustation a évolué");
$t->is($lotDegustation->specificite, $values['specificite'],"La specificité de la dégustation a évolué");
$t->is($lotDegustation->date, preg_replace("/ .*$/", "", $degustation->date), "La date du lot est la même");
$t->is($lotDegustation->id_document_provenance, $drevM01->_id, "Le document de provenance du lot de dégustation est la drev modificatrice");

$lotFinal = LotsClient::getInstance()->find($lot->declarant_identifiant, $lot->campagne, $lot->numero_dossier, $lot->numero_archive);

$t->is($lotFinal->numero_dossier, $lot->numero_dossier, "Le lot récupéré à le même numéro de dossier");
$t->is($lotFinal->numero_archive, $lot->numero_archive, "Le lot récupéré à le même numéro d'archive");
$t->is($lotFinal->unique_id, $lot->unique_id, "Le lot récupéré à le même unique id");
$t->is($lotFinal->id_document, $drevM01->_id, "Le lot récupéré provient de la drev modificatrice");
$t->is($lotFinal->document_ordre, "01", "Le lot récupéré a le numéro d'ordre 01");

$t->comment('Facturation du lot');

$mouvementINAOM00 = null;
foreach($drev->mouvements->get($viti->identifiant) as $mouvement) {
    if($mouvement->categorie == "08_inao") {
        $mouvementINAOM00 = $mouvement;
    }
}
$mouvementINAOM01 = null;
foreach($drevM01->mouvements->get($viti->identifiant) as $mouvement) {
    if($mouvement->categorie == "08_inao") {
        $mouvementINAOM01 = $mouvement;
    }
}

$t->is($mouvementINAOM00->quantite, 1, "La quantité du mouvement de facturation de la M00 est de 1");
$t->is($mouvementINAOM00->detail_identifiant, $lot->numero_dossier, "Le numéro de dossier facturé de la M00 est le même que celui du lot");
$t->is($mouvementINAOM01->quantite, 10, "La quantité du mouvement de facturation de la M01 est de 10");
$t->is($mouvementINAOM01->detail_identifiant, $lot->numero_dossier, "Le numéro de dossier facturé de la M01 est le même que celui du lot");
$t->ok(strpos($mouvementINAOM01->detail_libelle, $lot->numero_dossier) !== false, "le détail identifiant est bien dans le libellé");


$t->comment('Nouvelle modification du lot');

$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id, "01");
$form = new LotModificationForm($lot);

$values = array();
$values['volume'] = 11;
$values['produit_hash'] = $produitconfig1->getHash();
$values['numero_logement_operateur'] = "A";
$values['millesime'] = ($annee+2)."";
$values['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$values['destination_date'] = date('d/m/Y');
$values['specificite'] = 'bio';
$values['cepage_0'] = 'CHENIN B';
$values['repartition_hl_0'] = 11;
$values['_revision'] = $lot->getDocument()->_rev;

$form->bind($values);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();
$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id, "01");
$drevM02 = $lot->getDocument();
$t->ok(preg_match('/M02$/', $drevM02->_id), "c'est bien la M02 qui représente maintenant le lot");
$t->is($lot->produit_hash, $produitconfig1->getHash(), "c'est bien le premier produit qui est remis dans le lot");
$t->is(count($drevM02->mouvements), 0, "la M02 n'a pas de mouvement facturable");


$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id, "01");
$form = new LotModificationForm($lot);

$t->is($form->getDefault("millesime"), ($annee+2)."", "Le millésime par défaut du form est ".($annee+2));

$values = array();
$values['volume'] = 11;
$values['produit_hash'] = $produitconfig1->getHash();
$values['numero_logement_operateur'] = "A";
$values['millesime'] = ($annee+2)."";
$values['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$values['destination_date'] = date('d/m/Y');
$values['specificite'] = 'bio';
$values['cepage_0'] = 'CHENIN B';
$values['repartition_hl_0'] = 11;
$values['_revision'] = $lot->getDocument()->_rev;

$form->bind($values);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();
$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id, "01");
$drevresteM02 = $lot->getDocument();
$t->ok(preg_match('/M02$/', $drevresteM02->_id), "Une non modification du lot, ne change pas la version de la DREV qui reste M02");

$t->comment("Suppression d'un lot");

$lastDrev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($drev->identifiant, $drev->periode);
$drevM03 = $lastDrev->generateModificative();
$lot = $drevM03->addLot();
$lot->produit_hash = $produitconfig1->getHash();
$lot->volume = 100;
$drevM03->validate();
$drevM03->validateOdg();
$t->ok($drevM03->lots[1]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot créé est en attente de prélevement");

$drevM04 = $drevM03->generateModificative();
$drevM04->validate();
$drevM04->validateOdg();

$drevM05 = $drevM04->generateModificative();
$drevM05->lots->remove(1);
$drevM05->validate();
$drevM05->validateOdg();

$drevM03 = DRevClient::getInstance()->find($drevM03->_id);
$t->ok(!$drevM03->lots[1]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot supprimé n'est plus en attente de prélevement");


