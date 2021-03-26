<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(50);

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
$lot->numero_logement_operateur = $i;
$lot->volume = 50;
$lot->affectable = true;
$lot->destination_type = null;
$lot->destination_date = ($campagne+1).'-'.sprintf("%02d", 1).'-'.sprintf("%02d", 1);
$lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
$i++;
}
$drev->validate();
$drev->validateOdg();
$drev->save();

$t->is(count($drev->lots), 3, "3 lots ont automatiquement été créés");

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 3, "3 mouvements de lot prelevables ont été générés");

$degustation = new Degustation();
$degustation->lieu = "Test — Test";
$degustation->date = date('Y-m-d')." 14:00";
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 3, "3 lots en attentes de dégustation");
$degustation->save();
$degustation->setLots($lotsPrelevables);
$degustation->save();

$t->is(count(MouvementLotView::getInstance()->getByStatut(Lot::STATUT_AFFECTABLE)->rows), 0, "0 lots prelevables");

$year = date('Y') - 1;
$date = $year.'-10-15 12:15:42';
$campagne = $year.'-'.($year + 1);

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date);
$chgtDenom->constructId();
$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant);

$t->is($chgtDenom->_id, "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date), "id du document");
$t->is($chgtDenom->campagne, $campagne, "le chgt de denom a la bonne campagne à $campagne");
$t->is($chgtDenom->periode, $year, "le chgt de denom a la bonne periode à $year");
$t->is(count($lots), 0, "0 lot disponible au changement de denomination");


$degustation->lots[0]->statut = Lot::STATUT_NONCONFORME;
$degustation->lots[1]->statut = Lot::STATUT_CONFORME;
$degustation->lots[2]->statut = Lot::STATUT_CONFORME;
$degustation->save();

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant);
$t->is(count($lots), 3, "3 mouvements disponibles au changement de dénomination");

$lot = current($lots);
$t->is($lot->statut, Lot::STATUT_NONCONFORME, 'le lot sélectionné de la dégust est bien NON CONFORME');
$volume = $lot->volume;
$autreLot = next($lots);

$t->comment("Création d'un Changement de Denom Total");

$chgtDenom->setLotOrigine($lot);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenom->changement_produit_hash = $autreLot->produit_hash;
$chgtDenom->changement_volume = $volume;
$chgtDenom->changement_cepages = array('CABERNET' => $volume);
$chgtDenom->save();

$t->is(count($chgtDenom->lots), 1, "1 seul lot généré");
$chgtDenom->generateMouvementsLots(1);
$chgtDenom->save();

$t->is($chgtDenom->lots[0]->numero_archive, $lot->numero_archive, "Le numéro d'archive n'a pas changé");
$t->is($chgtDenom->lots[0]->numero_dossier, $lot->numero_dossier, "Le numéro de dossier n'a pas changé");

$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->ok($chgtDenom->isChgtTotal(), "Le changement est bien indiqué comme total");
$lot = $chgtDenom->lots->get(0);
$t->is($lot->produit_hash, $chgtDenom->changement_produit_hash, "Le produit est bien le nouveau dans le lot");
$t->is($lot->volume, $chgtDenom->changement_volume, "Le volume est bien le nouveau dans le lot");
$t->is($lot->produit_libelle, $chgtDenom->changement_produit_libelle, "Le libellé du produit est bien le nouveau dans le lot");
$t->is($lot->cepages->toArray(), array('CABERNET' => $volume), "Le 100% cepage est bien appliqué dans dans le lot");

$t->is(count($chgtDenom->lots->get(0)->getMouvements()), 3, "3 mouvements de lots");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "statut du lot change dest");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_REVENDIQUE), "statut du lot revendique");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_AFFECTABLE), "statut du lot affectable");
$degustProvenance = $chgtDenom->lots->get(0)->getLotProvenance();
$t->is($degustProvenance->getDocument()->_id, $degustation->_id, 'la provenance du chgt est bien la dégustation '.$degustation->_id);
$t->ok($degustProvenance->getMouvement(Lot::STATUT_REVENDIQUE_CHANGE), "statut du lot de la degust est à 'revendiqué changé'");
$t->ok(!$degustProvenance->getMouvement(Lot::STATUT_REVENDIQUE), "plus de statut 'revendiqué' dans le lot de la degust");
$t->ok($degustProvenance->getMouvement(Lot::STATUT_CHANGE_SRC), "statut du lot de la degust à 'revendiqué changé'");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Chgt de Denom Partiel");
$chgtDenom->setLotOrigine($lot);
$chgtDenom->changement_produit_hash = $autreLot->produit_hash;
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$chgtDenom->generateMouvementsLots(1);
$t->is($chgtDenom->lots[0]->numero_archive, $lot->numero_archive.'a', "numeros d'archive correctement postfixés : ".$lot->numero_archive.'a');
$t->is($chgtDenom->lots[1]->numero_archive, $lot->numero_archive.'b', "numeros d'archive correctement postfixés : ".$lot->numero_archive.'b');
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_NONCONFORME, "statut du lot orginel est bien non conforme");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE), "le lot originel a bien un mouvement au statut changé");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Total");
$chgtDenom->setLotOrigine($lot);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = $volume;
$chgtDenom->generateLots();
$t->ok($chgtDenom->isChgtTotal(), "Le changement qui a un volume identique est bien un changement total");
$t->is(count($chgtDenom->lots), 1, "Ce changement total ne génère plus que 1 lot");
$chgtDenom->generateMouvementsLots(1);
$t->is($chgtDenom->lots[0]->numero_archive, $lot->numero_archive, "Un chgm total ne change pas le numero d'archive");
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is(count($chgtDenom->lots), 1, "Dans un declassement total, on a bien un seul lot");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Partiel");
$chgtDenom->setLotOrigine($lot);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->generateLots();
$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$chgtDenom->generateMouvementsLots();
$t->is($chgtDenom->lots[0]->numero_archive, $lot->numero_archive.'a', "Pour le déclassement, le 1er lot est postfixé par a");
$t->is($chgtDenom->lots[1]->numero_archive, $lot->numero_archive.'b', "Pour le déclassement, le 2d lot est postfixé par b");
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_NONCONFORME), "le mouvement du lot d'origine a bien toujours un statut non conforme");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "statut des mvt nouveau lot a bien un affectable");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE), "statut des mvt du lot origine a bien un changé");
