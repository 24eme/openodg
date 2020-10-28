<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(16);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
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
$lot->numero = $i;
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
    if ($mvtLot->prelevable && !$mvtLot->preleve) {
      $nbLotPrelevable++;
    }
  }
}
$t->is($nbLotPrelevable, 3, "3 mouvements de lot prelevables ont été générés");

$newDegutation = new Degustation();
$t->is(count($newDegutation->getLotsPrelevables()), 3, "3 lots en attentes de dégustation");

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant);
$mvtLots = $chgtDenom->getMvtLots();
$t->is(count($mvtLots), 3, "3 mvtlots disponibles au chgt de denom");

$mvtLot = current($mvtLots);
$mvtLotKey = Lot::generateMvtKey($mvtLot);
$lot = $drev->get($mvtLot->origine_hash);
$autreLot = $drev->get(next($mvtLots)->origine_hash);

$t->comment("Création d'un Chgt de Denom Total");
$chgtDenom->changement_origine_mvtkey = $mvtLotKey;
$chgtDenom->changement_produit = $autreLot->produit_hash;
$chgtDenom->changement_produit_libelle = $autreLot->produit_libelle;
$chgtDenom->changement_volume = null;
$chgtDenom->changement_numero = null;
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 1, "1 lot généré");
$chgtDenom->generateMouvementsLots(0);
$nbLotNonPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if (!$mvtLot->prelevable && !$mvtLot->preleve && $mvtLot->numero == $lot->numero && $mvtLot->volume == $lot->volume) {
      $nbLotNonPrelevable++;
    }
  }
}
$t->is($nbLotNonPrelevable, 1, "1 mouvement de lot non prelevable généré");
$chgtDenom->clearMouvementsLots();
$chgtDenom->generateMouvementsLots(1);
$nbLotPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if ($mvtLot->prelevable && !$mvtLot->preleve && $mvtLot->numero == $lot->numero && $mvtLot->volume == $lot->volume) {
      $nbLotPrelevable++;
    }
  }
}
$t->is($nbLotPrelevable, 1, "1 mouvement de lot prelevable généré");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Chgt de Denom Partiel");
$chgtDenom->changement_origine_mvtkey = $mvtLotKey;
$chgtDenom->changement_produit = $autreLot->produit_hash;
$chgtDenom->changement_produit_libelle = $autreLot->produit_libelle;
$chgtDenom->changement_volume = round($lot->volume / 2, 2);
$chgtDenom->changement_numero = 4;
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$chgtDenom->generateMouvementsLots(0);
$nbLotNonPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if (!$mvtLot->prelevable && !$mvtLot->preleve && in_array($mvtLot->numero, array($lot->numero, 4)) && $mvtLot->volume == $chgtDenom->changement_volume) {
      $nbLotNonPrelevable++;
    }
  }
}
$t->is($nbLotNonPrelevable, 2, "2 mouvements de lot non prelevables générés");
$chgtDenom->clearMouvementsLots();
$chgtDenom->generateMouvementsLots(1);
$nbLotPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if ($mvtLot->prelevable && !$mvtLot->preleve && in_array($mvtLot->numero, array($lot->numero, 4)) && $mvtLot->volume == $chgtDenom->changement_volume) {
      $nbLotPrelevable++;
    }
  }
}
$t->is($nbLotPrelevable, 2, "2 mouvements de lot prelevables générés");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Total");
$chgtDenom->changement_origine_mvtkey = $mvtLotKey;
$chgtDenom->changement_produit = null;
$chgtDenom->changement_produit_libelle = null;
$chgtDenom->changement_volume = null;
$chgtDenom->changement_numero = null;
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 1, "1 lot généré");
$chgtDenom->generateMouvementsLots(0);
$nbLotNonPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if (!$mvtLot->prelevable && !$mvtLot->preleve && $mvtLot->numero == $lot->numero && $mvtLot->volume == $lot->volume) {
      $nbLotNonPrelevable++;
    }
  }
}
$t->is($nbLotNonPrelevable, 1, "1 mouvement de lot non prelevable généré");
$chgtDenom->clearMouvementsLots();
$chgtDenom->generateMouvementsLots(1);
$nbLotPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if ($mvtLot->prelevable && !$mvtLot->preleve && $mvtLot->numero == $lot->numero && $mvtLot->volume == $lot->volume) {
      $nbLotPrelevable++;
    }
  }
}
$t->is($nbLotPrelevable, 0, "0 mouvement de lot prelevable généré");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Partiel");
$chgtDenom->changement_origine_mvtkey = $mvtLotKey;
$chgtDenom->changement_produit = null;
$chgtDenom->changement_produit_libelle = null;
$chgtDenom->changement_volume = round($lot->volume / 2, 2);
$chgtDenom->changement_numero = 4;
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$chgtDenom->generateMouvementsLots(0);
$nbLotNonPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if (!$mvtLot->prelevable && !$mvtLot->preleve && in_array($mvtLot->numero, array($lot->numero, 4)) && $mvtLot->volume == $chgtDenom->changement_volume) {
      $nbLotNonPrelevable++;
    }
  }
}
$t->is($nbLotNonPrelevable, 2, "2 mouvements de lot non prelevables générés");
$chgtDenom->clearMouvementsLots();
$chgtDenom->generateMouvementsLots(1);
$nbLotPrelevable=0;
if ($chgtDenom->mouvements_lots->exist($viti->identifiant)) {
  foreach($chgtDenom->mouvements_lots->get($viti->identifiant) as $k => $mvtLot) {
    if ($mvtLot->prelevable && !$mvtLot->preleve && in_array($mvtLot->numero, array($lot->numero, 4)) && $mvtLot->volume == $chgtDenom->changement_volume) {
      $nbLotPrelevable++;
    }
  }
}
$t->is($nbLotPrelevable, 1, "1 mouvement de lot prelevable généré");
