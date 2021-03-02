<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(27);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

$campagne = (date('Y')-1)."";

//Début des tests
$t->comment("Création d'une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->constructId();
$drev->storeDeclarant();

$produits = $drev->getConfigProduits();
$nbProduit = 0;
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    $drev->addProduit($produit->getHash());
    $nbProduit++;
    if ($nbProduit == 3) {
      break;
    }
}
$i=1;
foreach($drev->lots as $lot) {
$lot->id_document = $drev->_id;
$lot->millesime = $campagne;
$lot->numero_cuve = $i;
$lot->volume = rand(10,50);
$lot->destination_type = null;
$lot->destination_date = ($campagne+1).'-'.sprintf("%02d", rand(1,12)).'-'.sprintf("%02d", rand(1,28));
$lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
$i++;
}
$drev->validate();
$drev->save();

$t->is(count($drev->lots), 3, "3 lots ont automatiquement été créés");

$nbLotPrelevable=0;
if ($drev->mouvements_lots->exist($viti->identifiant)) {
  foreach($drev->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if ($mvtLot->statut == Lot::STATUT_PRELEVABLE) {
      $nbLotPrelevable++;
    }
  }
}
$t->is($nbLotPrelevable, 3, "3 mouvements de lot prelevables ont été générés");

$newDegutation = new Degustation();
$newDegutation->lieu = "Test — Test";
$newDegutation->date = date('Y-m-d')." 14:00";
$t->is(count($newDegutation->getLotsPrelevables()), 3, "3 lots en attentes de dégustation");
$mvtkeys = array();
foreach ($newDegutation->getLotsPrelevables() as $key => $value) {
  $mvtkeys[$key] = 1;
}
$newDegutation->setLotsFromMvtKeys($mvtkeys, Lot::STATUT_ATTENTE_PRELEVEMENT);
$newDegutation->validate();
$newDegutation->save();

$t->is(count(MouvementLotView::getInstance()->getByStatut($campagne, Lot::STATUT_PRELEVABLE)->rows), 0, "0 lots prelevables");

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant);
$mvtLots = $chgtDenom->getMvtLots();

$t->is(count($mvtLots), 0, "0 mvtlots disponibles au chgt de denom");

$first = true;
foreach($newDegutation->getLots() as $lot) {
  $lot->setStatut(($first)? Lot::STATUT_NONCONFORME : Lot::STATUT_CONFORME);
  $first = false;
}
$newDegutation->generateMouvementsLots();
$newDegutation->save();

$t->is(count(MouvementLotView::getInstance()->getByStatut($campagne, Lot::STATUT_NONCONFORME)->rows), 1, "1 lot non conforme");
$t->is(count(MouvementLotView::getInstance()->getByStatut($campagne, Lot::STATUT_CONFORME)->rows), 2, "2 lots conformes");

$mvtLots = $chgtDenom->getMvtLots();
$t->is(count($mvtLots), 3, "3 mvtlots disponibles au chgt de denom");

$mvtLot = current($mvtLots);
$mvtLotKey = Lot::generateMvtKey($mvtLot);
$volume = $mvtLot->volume;
$autreLot = $drev->get(next($mvtLots)->origine_hash);

$t->comment("Création d'un Chgt de Denom Total");

$chgtDenom->setMouvementLotOrigine($mvtLot);
$chgtDenom->changement_produit = $autreLot->produit_hash;
$chgtDenom->changement_volume = $volume;
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 1, "1 lot généré");
$chgtDenom->generateMouvementsLots(1);
$chgtDenom->save();
$postfix = 'a';
$okPostfix = true;
foreach ($chgtDenom->lots as $lot) {
  if ($lot->numero_archive != $mvtLot->numero_archive.$postfix) {
    $okPostfix = false;
    break;
  }
  $postfix++;
}

$t->is($okPostfix, true, "numeros d'archive correctement postfixés");
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_CONFORME, "statut du lot conforme");
$t->ok($chgtDenom->getMvtLot(), "récupération du mouvement de lot ");
$t->is($chgtDenom->getMouvementLotOrigine()->statut, Lot::STATUT_CHANGE, "statut origine changé");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Chgt de Denom Partiel");
$chgtDenom->setMouvementLotOrigine($mvtLot);
$chgtDenom->changement_produit = $autreLot->produit_hash;
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$chgtDenom->generateMouvementsLots(1);
$postfix = 'a';
$okPostfix = true;
foreach ($chgtDenom->lots as $lot) {
  if ($lot->numero_archive != $mvtLot->numero_archive.$postfix) {
    $okPostfix = false;
    break;
  }
  $postfix++;
}
$t->is($okPostfix, true, "numeros d'archive correctement postfixés");
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_CONFORME, "statut du lot conforme");
$t->is($chgtDenom->lots->get(1)->statut, Lot::STATUT_PRELEVABLE, "statut du nouveau lot prelevable");
$t->is($chgtDenom->getMouvementLotOrigine()->statut, Lot::STATUT_CHANGE, "statut origine changé");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Total");
$chgtDenom->setMouvementLotOrigine($mvtLot);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = $volume;
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 1, "1 lot généré");
$chgtDenom->generateMouvementsLots(1);
$postfix = 'a';
$okPostfix = true;
foreach ($chgtDenom->lots as $lot) {
  if ($lot->numero_archive != $mvtLot->numero_archive.$postfix) {
    $okPostfix = false;
    break;
  }
  $postfix++;
}
$t->is($okPostfix, true, "numeros d'archive correctement postfixés");
$t->is($chgtDenom->changement_produit, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_CONFORME, "statut du lot conforme");
$t->is($chgtDenom->getMouvementLotOrigine()->statut, Lot::STATUT_DECLASSE, "statut origine déclassé");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Partiel");
$chgtDenom->setMouvementLotOrigine($mvtLot);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$chgtDenom->generateMouvementsLots(1);
$postfix = 'a';
$okPostfix = true;
foreach ($chgtDenom->lots as $lot) {
  if ($lot->numero_archive != $mvtLot->numero_archive.$postfix) {
    $okPostfix = false;
    break;
  }
  $postfix++;
}
$t->is($okPostfix, true, "numeros d'archive correctement postfixés");
$t->is($chgtDenom->changement_produit, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_CONFORME, "statut du lot conforme");
$t->is($chgtDenom->lots->get(1)->statut, Lot::STATUT_CONFORME, "statut du nouveau lot prelevable");
$t->is($chgtDenom->getMouvementLotOrigine()->statut, Lot::STATUT_CHANGE, "statut origine changé");
