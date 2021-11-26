<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

sfConfig::set('app_facture_emetteur' , $emetteurs);

$t = new lime_test(26);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$societe = $viti->getSociete();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}

//Suppression des ChgtDenom précédents
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

//Suppression des factures précédentes
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}

//Suppression des generation précédentes
foreach(GenerationClient::getInstance()->findHistory() as $k => $g) {
    $generation = GenerationClient::getInstance()->find($g->id);
    $generation->delete(false);
}

//Suppression des dégustation précédentes
foreach(DegustationClient::getInstance()->getHistory(9999, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

//Suppression MandatSepa
$oldmandat = MandatSepaClient::getInstance()->findLastBySociete($societe->identifiant);
if ($oldmandat) {
    MandatSepaClient::getInstance()->delete($oldmandat);
}

//Suppression des factures
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}

//Suppression des templates de facturation
foreach(TemplateFactureClient::getInstance()->findAll() as $k => $doc) {
    TemplateFactureClient::getInstance()->delete($doc);
}

$periode = (date('Y')-1)."";

acCouchdbManager::getClient()->storeDoc(json_decode(str_replace("%periode%", $periode, file_get_contents(dirname(__FILE__).'/../data/template_facture_igp.json'))));

$t->comment("Création d'une drev");

$config = ConfigurationClient::getCurrent();
$produit_hash = null;
foreach($config->getProduits() as $hash => $produit) {
    $produit_hash = $produit->getHash();
    break;
}

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();

$lot = $drev->addLot();
$lot->numero_logement_operateur = 'CUVE A';
$lot->produit_hash = $produit_hash;
$lot->volume = 100;
$lot = $drev->addLot();
$lot->numero_logement_operateur = 'CUVE Abis';
$lot->produit_hash = $produit_hash;
$lot->volume = 10;
$drev->save();

$drev->validate();
$drev->validateOdg();
$drev->save();

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drev->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 110, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" est 100");
$t->is($getVolumeLotsFacturables_quantite, 110, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" est 100");


$t->comment("Création d'une drev modificatrice avec un nouveau lot");

$drevM01 = DRevClient::getInstance()->find($drev->_id)->generateModificative();

$lot = $drevM01->addLot();
$lot->numero_logement_operateur = 'CUVE B';
$lot->produit_hash = $produit_hash;
$lot->volume = 50;
$drevM01->save();
$drevM01->validate();
$drevM01->validateOdg();
$drevM01->save();

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM01->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M01 : 50");
$t->is($getVolumeLotsFacturables_quantite, 50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M01 : 50");

$t->comment("Réduction du volume de la drev");

$drevM02 = DRevClient::getInstance()->find($drevM01->_id);
$lot = $drevM02->lots[0];
$lot->volume = 90;
LotsClient::getInstance()->modifyAndSave($lot);
$drevM02 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);

$t->is($drevM02->_id, $drev->_id."-M02", "La modification du lot a engendré une modificatrice");

$diff = $drevM02->getDiffLotVolume();
$t->is(count($diff), 1, "la modification a bien généré trois différences (2 de volumes, un unique_id)");
$t->is($diff['/lots/0/volume'], 100, "la première diff de volume donnne bien l'ancien volume du lot 1");
$t->is($drevM02->lots[0]->getOriginalVolumeIfModifying(), 100, "on repère bien que c'est une modification");


$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM02->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, -10, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M01 : -10");
$t->is($getVolumeLotsFacturables_quantite, -10, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M01 : -10");

$t->comment("suppression d'un lot de la drev");
$drevM03 = $drevM02->generateModificative();
unset($drevM03->lots[2]);
$drevM03->save();
$drevM03->validate();
$drevM03->validateOdg();
$drevM03->save();

$diff = $drevM03->getDiffLotVolume();
$t->is(count($diff), 2, "la modification a bien généré trois différences (2 de volumes, un unique_id)");
$t->is($diff['/lots/2/volume'], 50, "la diff de volume donnne bien l'ancien volume du lot 1");
$t->ok(isset($diff['/lots/2/unique_id']), "la diff de volume a bien un unique_id car il est supprimé");

$deletedlots = $drevM03->getDeletedLots();
$t->is(count($deletedlots), 1, "on repère bien le supprimé");
$t->is($deletedlots[0]->unique_id, $diff['/lots/2/unique_id'], "c'est le bon unique_id supprimé");
$t->is($deletedlots[0]->volume, 50, "c'est le bon volume supprimé");

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM03->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, -50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M01 : -50");
$t->is($getVolumeLotsFacturables_quantite, -50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M01 : -50");

$t->comment("suppression d'un lot de la drev + ajout d'un autre");
$drevM04 = $drevM03->generateModificative();
unset($drevM04->lots[0]);
$drevM04->save();
$lot = $drevM04->addLot();
$lot->numero_logement_operateur = 'CUVE B';
$lot->produit_hash = $produit_hash;
$lot->volume = 168;
$drevM04->save();
$drevM04->validate();
$drevM04->validateOdg();
$drevM04->save();

$diff = $drevM04->getDiffLotVolume();
$t->is(count($diff), 4, "la modification a bien généré 4 différences (2 de volumes, 2 unique_id car décallage d'index)");
$t->is($diff['/lots/0/volume'], 90, "la diff de volume donnne bien l'ancien volume du lot 1");
$t->ok(isset($diff['/lots/0/unique_id']), "la diff de volume a bien un unique_id car il est supprimé");

$deletedlots = $drevM04->getDeletedLots();
$t->is(count($deletedlots), 1, "on repère bien le supprimé");
$t->is($deletedlots[0]->unique_id, $diff['/lots/0/unique_id'], "c'est le bon unique_id supprimé");
$t->is($deletedlots[0]->volume, 90, "c'est le bon volume supprimé");

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM04->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 78, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M01 : -50");
$t->is($getVolumeLotsFacturables_quantite, 78, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M01 : -50");

