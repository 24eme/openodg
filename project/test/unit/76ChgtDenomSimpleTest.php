<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}

$t = new lime_test();

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
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
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

$year = (date('Y'))."";
$periode = $year;
$doc_date = date('Y-m-d');

//Début des tests
$t->comment("Création d'une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->constructId();
$drev->storeDeclarant();

$produits = $drev->getConfigProduits();
$nbProduit = 0;
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    $drev->addProduit($produit->getHash());
    $lot = $drev->addLot();
    $lot->produit_hash = $produit->getHash();
    $nbProduit++;
    if ($nbProduit == 3) {
      break;
    }
}
$i=1;
foreach($drev->lots as $lot) {
    $lot->id_document = $drev->_id;
    $lot->millesime = $periode;
    $lot->numero_logement_operateur = $i;
    $lot->volume = 50;
    $lot->affectable = true;
    $lot->cepages = array('CABERNET' => 25, 'PINOT' => 25);
    $lot->destination_type = null;
    $lot->destination_date = ($periode+1).'-'.sprintf("%02d", 1).'-'.sprintf("%02d", 1);
    $lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
    $i++;
}
$drev->validate($doc_date);
$drev->add('date_commission', $drev->getDateValidation('Y-m-d'));
$drev->validateOdg($doc_date);
$drev->save();

$lots = array();
foreach(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null) as $key => $lot) {
    if($lot->affectable) {
        continue;
    }
    $lots[] = $lot;
}

$t->comment("Test via une dégustation");

$lotsPrelevables = DegustationClient::getInstance()->getLotsEnAttente(null);

$degustation = new Degustation();
$degustation->lieu = "Test — Test";
$degustation->date = date('Y-m-d')." 14:00";
$degustation->save();
$degustation->setLots($lotsPrelevables);
$degustation->save();

$degustation->lots[0]->statut = Lot::STATUT_NONCONFORME;
$degustation->lots[1]->statut = Lot::STATUT_CONFORME;
$degustation->lots[2]->statut = Lot::STATUT_CONFORME;
$degustation->save();

$t->comment("Création d'un Changement de Denom Total depuis une dégustation");

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);

$lotFromDegust = current($lots);
$volume = $lotFromDegust->volume;
$lotFromDegustConforme = next($lots); // pour hash produit suivante

$date = date('Y-m-d').' 14:00:00';
$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lotFromDegust, $date, null);
$chgtDenom->save();
$t->comment($chgtDenom->_id);

$t->is($chgtDenom->changement_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, 'changement_numero_logement_operateur par defaut est le numero logement du lot origine');
$t->is($chgtDenom->origine_affectable, $lotFromDegust->affectable, "L'affectation origine reprends l'affectation du lot d'origine de la dégustation");
$t->is($chgtDenom->changement_affectable, $lotFromDegust->affectable, "L'affectation changé reprends l'affectation du lot d'origine de la dégustation comme le changement est total");
$t->is($chgtDenom->origine_statut, Lot::STATUT_NONCONFORME, "Le statut d'origine du lot est non conforme");

$chgtDenom->remove('changement_cepages');
$chgtDenom->add('changement_cepages');
$chgtDenom->changement_cepages = array('CABERNET' => $volume);
$chgtDenom->changement_produit_hash = $lotFromDegustConforme->produit_hash;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->changement_numero_logement_operateur = "2(ex1)";
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$t->is(count($chgtDenom->lots), 2, "1 seul lot généré");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->changement_volume, $volume, "Le changement est bien sur $volume hl");
$t->is($chgtDenom->changement_produit_libelle, $lotFromDegustConforme->produit_libelle, "Libellé produit est bien ".$lotFromDegustConforme->produit_libelle);
$t->is($chgtDenom->origine_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, "Le logement origine est celui du lot origine");
$t->ok($chgtDenom->isTotal(), "Le changement est bien indiqué comme total");

$lotFromChgmtOrig = $chgtDenom->lots->get(0);
$lotFromChgmtRes = $chgtDenom->lots->get(1);
$t->is($lotFromChgmtOrig->numero_archive, $lotFromDegust->numero_archive, "Le numéro d'archive n'a pas changé");
$t->is($lotFromChgmtOrig->numero_dossier, $lotFromDegust->numero_dossier, "Le numéro de dossier n'a pas changé");
$t->is($lotFromChgmtOrig->document_ordre, '03', "Le numéro d'ordre est bien 03");
$t->is($lotFromChgmtOrig->id_document_provenance, $degustation->_id, "Le lot origine provient bien de la dégustation ".$degustation->_id);
$t->is($lotFromChgmtOrig->produit_hash, $chgtDenom->origine_produit_hash, "Le produit est bien l'original dans le lot origine");
$t->is($lotFromChgmtOrig->volume, 0, "Le volume est bien à 0 dans le lot origine");
$t->is($lotFromChgmtOrig->produit_libelle, $chgtDenom->origine_produit_libelle, "Le libellé du produit est bien l'origine dans le lot");
$t->is($lotFromChgmtOrig->numero_logement_operateur, $chgtDenom->origine_numero_logement_operateur, "Le lot origine a bien logement origine");
$t->is($lotFromChgmtOrig->statut, Lot::STATUT_NONCONFORME, "Le statut du lot origine est non conforme");
$t->is($lotFromChgmtOrig->affectable, false, "le lot origine avec un volume à 0 n'est pas affectable");
$t->is($lotFromChgmtRes->produit_hash, $chgtDenom->changement_produit_hash, "Le produit est bien le nouveau dans le lot");
$t->is($lotFromChgmtRes->volume, $chgtDenom->changement_volume, "Le volume est bien le nouveau dans le lot");
$t->is($lotFromChgmtRes->produit_libelle, $chgtDenom->changement_produit_libelle, "Le libellé du produit est bien le nouveau dans le lot");
$t->is($lotFromChgmtRes->volume, $volume, "Le volume de $volume hl est bien appliqué dans le nouveau dans le lot");
$t->is($lotFromChgmtRes->cepages->toArray(), array('CABERNET' => $volume), "Le 100% cepage est bien appliqué dans dans le lot");
$t->is($lotFromChgmtRes->numero_logement_operateur, $chgtDenom->changement_numero_logement_operateur, "Le lot changé a changé de logement");
$t->is($lotFromChgmtRes->statut, Lot::STATUT_NONAFFECTABLE, "Le statut du lot changé est réputé conforme");

$t->ok($lotFromChgmtOrig->getMouvement(Lot::STATUT_CHANGE_DEST), "statut du lot change dest");
$t->ok(!$lotFromChgmtOrig->getMouvement(Lot::STATUT_NONAFFECTABLE), "statut du lot en volume à 0  n'est pas affectable");
$t->ok($lotFromChgmtOrig->getMouvement(Lot::STATUT_NONCONFORME), "mouvement du lot non conforme");
$t->ok($lotFromChgmtRes->getMouvement(Lot::STATUT_CHANGEABLE), "statut du lot changeable");
$t->ok($lotFromChgmtRes->getMouvement(Lot::STATUT_NONAFFECTABLE), "statut du lot affectable (provenant d'une non conformité)");
$t->ok(!$lotFromChgmtRes->getMouvement(Lot::STATUT_NONCONFORME), "pas de mouvement du lot non conforme");

$degustProvenance = $lotFromChgmtOrig->getLotProvenance();
$t->ok($degustProvenance && ($degustProvenance->getDocument()->_id == $degustation->_id), 'la provenance du chgt est bien la dégustation '.$degustation->_id);
$t->ok($degustProvenance && $degustProvenance->getMouvement(Lot::STATUT_CHANGE_SRC), "statut du lot de la degust à 'revendiqué changé'");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
