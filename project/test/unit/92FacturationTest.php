<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

sfConfig::set('app_facture_emetteur' , $emetteurs);

$t = new lime_test(4);

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
$drev->save();

$drev->validate();
$drev->validateOdg();
$drev->save();

foreach($drev->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == 'volume_revendique_numero_dossier') {
        $t->is($m->quantite, 100, "La quantité du mouvement a facturer \"volume_revendique_numero_dossier\" est 100");
    }
}

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

foreach($drevM01->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == 'volume_revendique_numero_dossier') {
        $t->is($m->quantite, 50, "La quantité du mouvement a facturer \"volume_revendique_numero_dossier\" est 50");
    }
}

$t->comment("Réduction du volume de la drev");

$drevM02 = DRevClient::getInstance()->find($drevM01->_id);
$lot = $drevM01->lots[0];
$lot->volume = 90;

LotsClient::getInstance()->modifyAndSave($lot);
$drevM02 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);

$t->is($drevM02->_id, $drev->_id."-M02", "La modification du lot a engendré une modificatrice");

foreach($drevM02->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == 'volume_revendique_numero_dossier') {
        $t->is($m->quantite, -10, "La quantité du mouvement à facturer \"volume_revendique_numero_dossier\" est -10");
    }
}
