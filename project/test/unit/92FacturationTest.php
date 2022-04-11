<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

sfConfig::set('app_facture_emetteur' , $emetteurs);

$t = new lime_test(86);

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
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
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

$t->is($drev->mouvements->get($drev->identifiant)->getFirst()->date, $drev->validation, "En l'absence de date de commission la date du mouvement est celle de la validation de la drev");

$dateCommission = (new DateTime())->modify('+20 days')->format('Y-m-d');

$drev->add('date_commission', $dateCommission);
$drev->generateMouvementsFactures();
$drev->save();

$t->is($drev->mouvements->get($drev->identifiant)->getFirst()->date, $dateCommission, "Avec une date de commission la date du mouvement est celle de la date de commission");
$t->is($drev->mouvements->get($drev->identifiant)->getFirst()->date_version, $drev->validation, "La date de version du mouvement est la date de validation de la drev");

$t->comment("Changement de cuve");

$drevM01 = DRevClient::getInstance()->find($drev->_id);
$lot = $drevM01->lots[0];
$lot->numero_logement_operateur = 'CuveCexCuveA';
LotsClient::getInstance()->modifyAndSave($lot);
$drevM01 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);

$t->is($drevM01->_id, $drev->_id."-M01", "La modification du lot a engendré une modificatrice");
$t->is($drevM01->lots[0]->numero_logement_operateur, 'CuveCexCuveA', "Le logement a été changé");

$t->ok(!$drevM01->mouvements->exist($drev->identifiant), "La modification n'a pas engendré de mouvement");

$t->comment("Réduction du volume de la drev après une drev ayant le même numéro de dossier");

$drevM01 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$lot = $drevM01->lots[0];
$lot->volume = 90;
LotsClient::getInstance()->modifyAndSave($lot);
$drevM02 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);

$t->is($drevM02->_id, $drev->_id."-M02", "La modification du lot a engendré une modificatrice");

$diff = $drevM02->getDiffLotVolume();
$t->is(count($diff), 1, "la modification a bien généré une différence de volume");
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
$t->is($getVolumeRevendiqueNumeroDossier_quantite, -10, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M02 : -10");
$t->is($getVolumeLotsFacturables_quantite, -10, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M02 : -10");

$t->comment("Création d'une drev modificatrice avec un nouveau lot");

$drevM03 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode)->generateModificative();

$lot = $drevM03->addLot();
$lot->numero_logement_operateur = 'CUVE B';
$lot->produit_hash = $produit_hash;
$lot->volume = 50;
$drevM03->save();
$drevM03 = DRevClient::getInstance()->find($drevM03->_id);
$drevM03->validate();
$drevM03->save();
$drevM03 = DRevClient::getInstance()->find($drevM03->_id);
$diff = $drevM03->getDiffLotVolume();

$drevM03->validateOdg();
$drevM03->save();

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM03->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M03 : 50");
$t->is($getVolumeLotsFacturables_quantite, 50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M03 : 50");

$t->comment("Augmentation du volume de la drev après une drev ayant un autre numéro de dossier");

$drevM03 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$lot = $drevM03->lots[0];
$lot->volume = 150;
LotsClient::getInstance()->modifyAndSave($lot);
$drevM04 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);

$t->is($drevM04->_id, $drev->_id."-M04", "La modification du lot a engendré une modificatrice");

$diff = $drevM04->getDiffLotVolume();
$t->is(count($diff), 1, "la modification a bien généré une différence de volume");
$t->is($diff['/lots/0/volume'], 90, "la première diff de volume donnne bien l'ancien volume du lot 1");
$t->is($drevM04->lots[0]->getOriginalVolumeIfModifying(), 90, "on repère bien que c'est une modification");

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM04->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 60, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M04 : 60");
$t->is($getVolumeLotsFacturables_quantite, 60, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M04 : -10");

$t->comment("suppression d'un lot de la drev");
$drevM05 = $drevM04->generateModificative();
$lot2 = $drevM05->lots[2];
unset($drevM05->lots[2]);
$drevM05->save();
$drevM05 = DRevClient::getInstance()->find($drevM05->_id);
$drevM05->validate();
$drevM05->save();
$drevM05 = DRevClient::getInstance()->find($drevM05->_id);
$drevM05->validateOdg();
$drevM05->save();

$diff = $drevM05->getDiffLotVolume();
$t->is(count($diff), 2, "la modification a bien généré trois différences (2 de volumes, un unique_id)");
$t->is($diff['/lots/2/volume'], 50, "la diff de volume donnne bien l'ancien volume du lot 1");
$t->ok(isset($diff['/lots/2/unique_id']), "la diff de volume a bien un unique_id car il est supprimé");
$t->is(strlen($diff['/lots/2/unique_id']), 21, "la diff de volume a bien un unique_id (".$diff['/lots/2/unique_id'].") valide");

$deletedlots = $drevM05->getDeletedLots();
$t->is(count($deletedlots), 1, "on repère bien le supprimé");
$t->is($deletedlots[0]->unique_id, $diff['/lots/2/unique_id'], "c'est le bon unique_id supprimé");
$t->is($deletedlots[0]->volume, 50, "c'est le bon volume supprimé");

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM05->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, -50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M05 : -50");
$t->is($getVolumeLotsFacturables_quantite, -50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M05 : -50");

$t->comment("rajout du lot");
$drevM06 = $drevM05->generateModificative();
$drevM06->save();
$lot = $drevM06->addLot();
$lot->volume = $lot2->volume;
$lot->numero_logement_operateur = $lot2->numero_logement_operateur;
$lot->produit_hash = $lot2->produit_hash;
$drevM06->save();
$drevM06 = DRevClient::getInstance()->find($drevM06->_id);
$drevM06->validate();
$drevM06->save();
$t->is(strlen($drevM06->lots[2]->unique_id), 21, "lot 2 unique_id (".$drevM06->lots[2]->unique_id.") valide");
$drevM06->validateOdg();
$drevM06->save();
$diff = $drevM06->getDiffLotVolume();
$t->is(count($diff), 2, "la modification a bien généré 2 différences (1 de volumes, un unique_id)");
$t->is($diff['/lots/2/volume'], 50, "la diff de volume donnne bien l'ancien volume du lot 1");
$t->is(strlen($diff['/lots/2/unique_id']), 21, "la diff de volume a bien un unique_id (".$diff['/lots/2/unique_id'].") valide");
$deletedlots = $drevM06->getDeletedLots();
$t->is(count($deletedlots), 0, "on ne repère pasde suppression");
$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM06->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M06 : 50");
$t->is($getVolumeLotsFacturables_quantite, 50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M06 : 50");
$t->comment("mise à 0 du volume de lot 2");
$lot = $drevM06->lots[2];
$lot->volume = 0;
LotsClient::getInstance()->modifyAndSave($lot);
$drevM07 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$t->is(strlen($drevM07->lots[2]->unique_id), 21, "lot 2 unique_id (".$drevM07->lots[2]->unique_id.") valide");
$drevM07->validateOdg();
$drevM07->save();
$diff = $drevM07->getDiffLotVolume();
$t->is(count($diff), 1, "la modification a bien généré 1 différence (1 de volumes)");
$t->is($diff['/lots/2/volume'], 50, "la diff de volume donnne bien l'ancien volume du lot 1");
$deletedlots = $drevM07->getDeletedLots();
$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM07->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($drevM07->lots[2]->volume, 0, "le volume est bien de 0");
$t->is($getVolumeRevendiqueNumeroDossier_quantite, -50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M07 : -50");
$t->is($getVolumeLotsFacturables_quantite, -50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M07 : -50");

$t->comment("remise à 50 du volume de lot 2");
$lot = clone $drevM07->lots[2];
$lot->volume = 50;
LotsClient::getInstance()->modifyAndSave($lot);
$drevM08 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$t->is($drevM08->lots[2]->volume, 50, "le nouveau volume de 50 est bien enregistré");
$t->is($drevM08->lots[2]->getOriginalVolumeIfModifying(), 0, "l'ancien volume de 0 est trouvé");
$t->is($drevM08->lots[2]->numero_dossier, $drevM07->lots[2]->numero_dossier, "les deux derniers enregistrement ont les même numéros de dossier");
$t->is($drevM08->lots[2]->numero_dossier, $drevM08->numero_archive, "le lot a bien le numero de dossier de sa drev");

$diff = $drevM08->getDiffLotVolume();
$t->is(count($diff), 1, "la modification a bien généré 1 différences (volumes)");
$t->is($diff['/lots/2/volume'], 0, "la diff de volume donnne bien l'ancien volume du lot 2");
$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM08->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 50, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M08 : 50");
$t->is($getVolumeLotsFacturables_quantite, 50, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M08 : 50");

$t->comment("suppression d'un lot de la drev + ajout d'un autre");
$drevM09 = $drevM08->generateModificative();
unset($drevM09->lots[0]);
$drevM09->save();
$lot = $drevM09->addLot();
$lot->numero_logement_operateur = 'CUVE B';
$lot->produit_hash = $produit_hash;
$lot->volume = 168;
$drevM09->save();
$drevM09->validate();
$drevM09->save();
$drevM09->validateOdg();
$drevM09->save();
$diff = $drevM09->getDiffLotVolume();
$t->is(count($diff), 6, "la modification a bien généré 4 différences (2 de volumes, 2 unique_id car décallage d'index)");
$t->is($diff['/lots/0/volume'], 150, "la diff de volume donnne bien l'ancien volume du lot 1");
$t->ok(isset($diff['/lots/0/unique_id']), "la diff de volume a bien un unique_id car il est supprimé");

$deletedlots = $drevM09->getDeletedLots();
$t->is(count($deletedlots), 1, "on repère bien le supprimé");
$t->is($deletedlots[0]->unique_id, $diff['/lots/0/unique_id'], "c'est le bon unique_id supprimé");
$t->is($deletedlots[0]->volume, 150, "c'est le bon volume supprimé");

$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM09->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 18, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M01 : -18");
$t->is($getVolumeLotsFacturables_quantite, 18, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M01 : -18");

$t->comment("Modification d'autre chose que du volume");
$lot = clone $drevM09->lots[0];
$lot->volume = 20;
LotsClient::getInstance()->modifyAndSave($lot);
$drevM10 = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($viti->identifiant, $periode);
$drevM10->lots[1]->numero_logement_operateur = 'XX12';
$drevM10->lots[1]->id_document = $drevM10->_id;
$drevM10->save();
$drevM10->devalidate();
$drevM10->save();
$drevM10->validate();
$drevM10->validateOdg();
$drevM10->save();
$t->is($drevM10->lots[0]->volume, 20, "le nouveau volume de 10 est bien enregistré");
$t->is($drevM10->lots[0]->getOriginalVolumeIfModifying(), 10, "l'ancien volume de 0 est trouvé");
$t->is($drevM10->lots[1]->volume, 50, "le volume du lot modifié de 50 est bon");
$t->is($drevM10->lots[1]->getOriginalVolumeIfModifying(), 50, "l'ancien volume de 50 est trouvé");

$diff = $drevM10->getDiffLotVolume();
$t->is(count($diff), 1, "la modification a bien généré 1 différence");
$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM10->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, 10, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M08 : 10");
$t->is($getVolumeLotsFacturables_quantite, 10, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M08 : 10");

$t->comment("suppression du dernier lot avec bidouillage de numero d'archive (cas Vaucluse)");

$drevM11 = $drevM10->generateModificative();
$drevM11->numero_archive = $drevM10->numero_archive;
unset($drevM11->lots[2]);
$drevM11->save();
$drevM11 = DRevClient::getInstance()->find($drevM11->_id);
$drevM11->validate();
$drevM11->validateOdg();
$drevM11->save();

$diff = $drevM11->getDiffLotVolume();
$t->is(count($diff), 2, "la modification a bien généré trois différences (2 de volumes, un unique_id)");
$t->is($diff['/lots/2/volume'], 168, "la diff de volume donnne bien l'ancien volume du lot 1");

$deletedlots = $drevM11->getDeletedLots();
$t->is(count($deletedlots), 1, "on repère bien le supprimé");
$t->is($deletedlots[0]->unique_id, $diff['/lots/2/unique_id'], "c'est le bon unique_id supprimé");
$t->is($deletedlots[0]->volume, 168, "c'est le bon volume supprimé");
$getVolumeRevendiqueNumeroDossier_quantite = null;
$getVolumeLotsFacturables_quantite = null;
foreach($drevM11->mouvements->get($drev->identifiant) as $m) {
    if($m->type_hash == '01_getVolumeRevendiqueNumeroDossier') {
        $getVolumeRevendiqueNumeroDossier_quantite = $m->quantite;
    }elseif ($m->type_hash == '02_getVolumeLotsFacturables') {
        $getVolumeLotsFacturables_quantite = $m->quantite;
    }
}
$t->is($getVolumeRevendiqueNumeroDossier_quantite, -168, "La quantité du mouvement a facturer \"getVolumeRevendiqueNumeroDossier\" du seul modifié M11 : -168");
$t->is($getVolumeLotsFacturables_quantite, -168, "La quantité du mouvement a facturer \"getVolumeLotsFacturables\" du seul modifié M11 : -168");



$t->comment("Création d'un changement de dénomination");

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $drevM11->lots[0], $drevM11->getDate(), null);
$chgtDenom->changement_volume = 4;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
$chgtDenom->validate();
$chgtDenom->save();

$t->ok(!$chgtDenom->exist($chgtDenom->identifiant), "Aucun mouvement de facture avant la validation par l'ODG");

$chgtDenom->validateODG();
$chgtDenom->save();

$t->is(count($chgtDenom->mouvements->get($chgtDenom->identifiant)->toArray()), 1, "Le mouvement de facturation a été généré après la validation par l'ODG");
$mouvChgtDenom = $chgtDenom->mouvements->get($chgtDenom->identifiant)->getFirst();
$t->is($mouvChgtDenom->taux, 15, "Le taux de facturation du mouvement \"03_getFirstChgtDenomFacturable\" du 1er changement de dénomination est de 15 €");
$t->is($mouvChgtDenom->detail_identifiant, $chgtDenom->numero_archive, "Le numéro d'archive du changement de dénomination est repris dans le mouvement \"03_getFirstChgtDenomFacturable\"");
$t->is($mouvChgtDenom->detail_libelle, "N° ".$chgtDenom->numero_archive, "Le libellé du mouvement \"03_getFirstChgtDenomFacturable\" contient le numéro d'archive");

$t->comment("Création d'un second changement de dénomination le même jour");

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $chgtDenom->lots[0], $drevM04->getDate()." 00:00:01", null);
$chgtDenom->changement_volume = 2;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
$chgtDenom->validate();
$chgtDenom->validateODG();
$chgtDenom->save();

$mouvChgtDenom = $chgtDenom->mouvements->get($chgtDenom->identifiant)->getFirst();
$t->is($mouvChgtDenom->taux, 10, "Le taux de facturation du mouvement \"03_getFirstChgtDenomFacturable\" du 2ème changement de dénomination est de 10 €");
$t->is($mouvChgtDenom->detail_identifiant, $chgtDenom->numero_archive, "Le numéro d'archive du changement de dénomination est repris dans le mouvement \"03_getFirstChgtDenomFacturable\"");
$t->is($mouvChgtDenom->detail_libelle, "N° ".$chgtDenom->numero_archive, "Le libellé du mouvement \"03_getFirstChgtDenomFacturable\" contient le numéro d'archive");

$t->comment("Création d'un troisième changement de dénomination le lendemain");

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $chgtDenom->lots[0], preg_replace("/.{2}$/", "01", $drevM04->getDate()), null);
$chgtDenom->changement_volume = 1;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
$chgtDenom->validate();
$chgtDenom->validateODG();
$chgtDenom->save();

$mouvChgtDenom = $chgtDenom->mouvements->get($chgtDenom->identifiant)->getFirst();
$t->is($mouvChgtDenom->taux, 15, "Le taux de facturation du mouvement \"03_getFirstChgtDenomFacturable\" du 2ème changement de dénomination est de 15 €");

$t->fail('CotisationFixe getNbLieuxPrelevements ne doit pas renvoyé 1 sur une lot modifié');

$t->comment("Facturation d'une déclaration de conditionnement");

$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, ConfigurationClient::getInstance()->getCurrentCampagne(), date('Y-m-d'));
$conditionnement->save();

$lot = $conditionnement->addLot();
$lot->numero_logement_operateur = 'CUVE A';
$lot->produit_hash = $produit_hash;
$lot->volume = 100;

$conditionnement->validate();
$conditionnement->validateOdg();
$conditionnement->save();

$mouvConditionnement = $conditionnement->mouvements->get($conditionnement->identifiant)->getFirst();
$t->is($mouvConditionnement->taux, 200, "Le taux de facturation du mouvement \"03_conditionnement/05_aFacturer\" est de 200 €");
$t->is($mouvConditionnement->quantite, 1, "Le quantite de facturation du mouvement \"03_conditionnement/05_aFacturer\" est de 1");
