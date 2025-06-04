<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test();
if (!DRevConfiguration::getInstance()->isModuleEnabled()) {
    $t->pass("no DREV for ".$application);
    return;
}

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$periode = (date('Y')-1)."";

//Début des tests
//Suppression des DRev et DR précédentes
$dr = DRClient::getInstance()->findByArgs($viti->identifiant, $periode, acCouchdbClient::HYDRATE_JSON);
if ($dr) {
  DRClient::getInstance()->deleteDoc($dr);
}
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) {
      DRClient::getInstance()->deleteDoc($dr);
    }
    $drev->delete(false);
}

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();
$t->comment($drev->_id);

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    if($has_lot && !$produit->isRevendicationParLots()) {
        continue;
    }
    $produit_hash1 = $produit->getHash();
    break;
}
$produit1 = $drev->addProduit($produit_hash1);
foreach($produits as $produit) {
    if($has_lot && !$produit->isRevendicationParLots()) {
        continue;
    }
    if($produit_hash1 == $produit->getHash()) {
        continue;
    }
    if ($produit1->getConfig()->getCouleur()->getKey() == $produit->getCouleur()->getKey()) {
        continue;
    }
    $produit_hash2 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    if($produit->isRevendicationParLots()) {
        continue;
    }
    $produit_hash_aoc = $produit->getHash();
    break;
}

$produit2 = $drev->addProduit($produit_hash2);
$produit_aoc = null;
if ($produit_hash_aoc) {
    $produit_aoc = $drev->addProduit($produit_hash_aoc);
}

$produit_hash1 = $produit1->getHash();
$produit_hash2 = $produit2->getHash();

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);

$produit1->superficie_revendique = 200;
$produit2->superficie_revendique = 150;


if (RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash())) {
    if (!sfConfig::get('app_region') && RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash())) {
        sfConfig::set('app_region', RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash()));
    }
    //$infos = sfConfig::get('region_configuration_region');
    $infos = sfConfig::get('region_configuration_region');
    $region = RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash());
    if (!isset($infos['odg'])) {
        $infos = array('odg' => array());
    }
    if (!isset($infos['odg'][$region])) {
        $infos['odg'] = array($region => array('email_notification' => 'notification@example.org', 'email' => 'email@example.org'));
    }
    foreach(array_keys($infos['odg']) as $k) {
        if (!isset($infos['odg'][$k]['email_notification'])) {
            $infos['odg'][$k]['email_notification'] = 'notification@example.org';
            $infos['odg'][$k]['email'] = 'email@example.org';
        }
    }
    sfConfig::set('region_configuration_region', $infos);
    sfConfig::set('drev_configuration_drev', $infos);

    $infos = sfConfig::get('app_facture_emetteur');
    if (!isset($infos[$region])) {
        $infos = array($region => array('email' => 'email@example.org'));
    }
    foreach(array_keys($infos) as $k) {
        if (!isset($infos[$k]['email'])) {
            $infos[$k]['email'] = 'email@example.org';
        }
    }
    sfConfig::set('app_facture_emetteur', $infos);

}

if ($has_lot) {
    $lot = $drev->addLot();
    $lot->millesime = null;
    $lot->numero_logement_operateur = "1";
    $lot->specificite = Lot::SPECIFICITE_PRIMEUR;
    $lot->volume = 30.4;
    $lot->destination_type = null;
    $lot->destination_date = $periode.'-11-15';
    $lot->produit_hash = $produit1->getConfig()->getHash();
    $lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
    $lot->addCepage("Chenin", 60);
    $lot->addCepage("Sauvignon", 40);
}else{
    $produit1->volume_revendique_total = 100;
    $produit1->volume_revendique_issu_recolte = 100;
}
$drev->save();

$t->comment("Validation des Drev");
$date_validation_1 = $periode."-10-30";
$date_validation_odg_1 = $periode."-11-05";

$t->comment("Point de vigilance DRev");
$validation = new DRevValidation($drev);
$vigilance = $validation->getVigilances();
if ($has_lot) {
    $t->ok(count($vigilance) && $vigilance[0] && preg_match('/Millésime/', $vigilance[0]->getInfo()), "Il y a une vigilance dû au millésime absent.");
}

$drev->validate($date_validation_1);
$drev->save();
$t->is($drev->isValidee(),true,"La Drev est validée");
$t->is($drev->getValidation(),$date_validation_1,"La date de validation est ".$date_validation_1);
$t->is($drev->isValideeOdg(),false,"La Drev n'est pas encore validée par l'odg");

$t->is($drev->getStatutOdg(), DRevClient::STATUT_SIGNE, "La DREV est bien mise au statut signé");

$drev->setStatutOdgByRegion(DRevClient::STATUT_EN_ATTENTE);
$t->is($drev->getStatutOdg(), DRevClient::STATUT_EN_ATTENTE, "La DREV est bien mise au statut mise en attente");

if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(RegionConfiguration::getInstance()->getOdgRegions() as $region) {
        $drev->validateOdg($date_validation_odg_1, $region);
    }
} else {
    $drev->validateOdg($date_validation_odg_1);
}
$drev->add('papier', 0);
$drev->save();

$t->is($drev->isValidee(),true,"La Drev est validée");
$t->is($drev->isValideeOdg(),true,"La Drev est validée par l'odg");
$t->is($drev->getValidationOdg(),$date_validation_odg_1,"La date de validation de l'odg est ".$date_validation_odg_1);
$t->is($drev->getStatutOdg(), DRevClient::STATUT_VALIDATION_ODG, "La validation ODG fait sauter le statut mise en attente");

if ($application == 'loire') {
    $t->is($drev->lots[0]->date,$date_validation_1,"La date de version du lot est celle de la validation ODG");
}

$t->comment("DRev envoi de mail de la validation");
$drev->devalidate();

$is_syndicat_devalide = true;
foreach ($drev->declaration->getSyndicats() as $syndicat) {
    $infos = RegionConfiguration::getInstance()->getOdgRegionInfos($syndicat);
    if($drev->isValidateOdgByRegion($syndicat)) {
        $is_syndicat_devalide = false;
    }
}
$t->ok($is_syndicat_devalide, 'Tous les produits sont dévalidés par chacun des syndicats');

foreach (RegionConfiguration::getInstance()->getOdgRegions() as $region) {
    $configDRev = sfConfig::get('drev_configuration_drev');
    $configDRev['odg'][$region]['email_notification'] = 'email@email.email';
    sfConfig::set('drev_configuration_drev', $configDRev);
}
DrevConfiguration::getInstance()->load();
if (!DrevConfiguration::getInstance()->isSendMailToOperateur()) {
    return;
}
$t->ok(Email::getInstance()->getMessageDRevValidationDeclarant($drev), "Mail de validation à envoyer au déclarant");
if(DrevConfiguration::getInstance()->hasValidationOdgRegion() && !DrevConfiguration::getInstance()->hasEmailDisabled()) {
    $t->is(count(Email::getInstance()->getMessagesDRevValidationNotificationSyndicats($drev)), 1, "Mails de notification de validation à envoyer aux syndicats");
} else {
    $t->is(count(Email::getInstance()->getMessagesDRevValidationNotificationSyndicats($drev)), 0, "Aucun mail de notification de validation à envoyer aux syndicats");
}
$t->ok(Email::getInstance()->getMessageDRevConfirmee($drev), "Mail de confirmation à envoyer au déclarant");
$t->is(count(Email::getInstance()->getMessageDrevPapierConfirmee($drev)), DrevConfiguration::getInstance()->isSendMailToOperateur(), "Mail de confirmation papier à envoyer au déclarant");
$t->is(count(Email::getInstance()->getMessagesDRevValidation($drev)), 0, "Aucun mail envoyé");

$drev->validate();
if (DrevConfiguration::getInstance()->isSendMailToOperateur() && !DRevConfiguration::getInstance()->hasEmailDisabled() && !DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    $messages = Email::getInstance()->getMessagesDRevValidation($drev);
    $t->is(count($messages), 1, "Mail de validation à envoyer au déclarant");
    if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
        $t->like($messages[0]->getSubject(), "/Validation de la Déclaration de Revendication/", "Sujet du mail de validation");
    } else {
        $t->is($messages[0]->getSubject(), "Validation de votre Déclaration de Revendication", "Sujet du mail de validation");
    }
}
if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(RegionConfiguration::getInstance()->getOdgRegions() as $region) {
        $drev->validateOdg(null, $region);
    }
} else {
    $drev->validateOdg();
}
$messages = Email::getInstance()->getMessagesDRevValidation($drev);
$t->is(count($messages), 1, "Mail de validation definitive envoyé pour de faux au déclarant");
$t->is($messages[0]->getSubject(), "Validation de votre Déclaration de Revendication", "Sujet du mail de validation définitive");
$drev->add('papier', 1);
$messages = Email::getInstance()->getMessagesDRevValidation($drev);
$t->is(count($messages), 0, "Pas de mail de validation définitive papier envoyé pour de faux au déclarant");

if (!DRevConfiguration::getInstance()->isModificativeEnabled()) {
    return;
}

$t->comment("Création d'une modificatrice  Drev");

$date_validation_2 = $periode."-11-15";
$date_validation_odg_2 = $periode."-11-30";

$drev_modificative = $drev->generateModificative();
$drev_modificative->save();



// Ajout d'un lot

$lot = null;
if ($has_lot) {
    $lot = $drev_modificative->addLot();

    $lot->millesime = null;
    $lot->numero_logement_operateur = "14";
    $lot->volume = 3.5;
    $lot->specificite = Lot::SPECIFICITE_PRIMEUR;
    $lot->destination_type = null;
    $lot->destination_date = ($periode+1).'-06-15';
    $lot->produit_hash = $produit1->getConfig()->getHash();
    $lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
    $lot->addCepage("Chenin", 30);
    $lot->addCepage("Sauvignon", 70);
}

$t->comment("Point de vigilance DRev modificatrice");
$validation = new DRevValidation($drev_modificative);
$vigilance = $validation->getVigilances();
$t->is(count($vigilance), 4, "Les points de vigilance la DRev modificatrice");

$drev_modificative->validate($date_validation_2);
if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(RegionConfiguration::getInstance()->getOdgRegions() as $region) {
        $drev_modificative->validateOdg($date_validation_odg_2, $region);
    }
} else {
    $drev_modificative->validateOdg($date_validation_odg_2);
}

$drev_modificative->save();

if ($lot) {
$lot = $drev_modificative->lots->getLast();
}

$t->is($drev_modificative->getVersion(),"M01","La Drev modificatrice est de rang 01");
$t->is($drev_modificative->isValidee(),true,"La Drev modificatrice est validée");
$t->is($drev_modificative->isValideeOdg(),true,"La Drev modificatrice est validée par l'odg");
$t->is($drev_modificative->getValidation(),$date_validation_2,"La date de validation est ".$date_validation_2);
$t->is($drev_modificative->getValidationOdg(),$date_validation_odg_2,"La date de validation de l'odg est ".$date_validation_odg_2);

if ($lot) {
    $t->is($drev_modificative->lots[0]->date,$date_validation_1,"La date de version du lot de départ est celle de la validation de la M00 (date_validation_1)");
    $t->is($lot->date,$date_validation_2,"La date de version du dernier lot est celle de la validation ODG de la M01 ($date_validation_2)");
}

if ($application == 'igp13') {
    $dateDegustVoulue = $periode.'-12-25';
    $drev->setDateDegustationSouhaitee($dateDegustVoulue);
    $t->is($drev->date_degustation_voulue, $dateDegustVoulue, 'La date de dégustation voulue par l\'opérateur est '.$dateDegustVoulue);
}

$t->comment("DRev envoi de mail de la validation");

$drev = $drev_modificative->generateModificative();
$drev->save();

$t->is(count($drev->getDocumentsAEnvoyer()), 0, "Aucun document à envoyer");
$drev->documents->add('test_en_attente')->statut = DRevDocuments::STATUT_EN_ATTENTE;
$drev->documents->add('test_recu')->statut = DRevDocuments::STATUT_RECU;
$t->is(count($drev->getDocumentsAEnvoyer()), 1, "1 document à envoyer");
