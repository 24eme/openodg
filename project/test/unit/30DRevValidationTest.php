<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$nb_test = 29;
$has_lot = false;
if ($application == 'loire' || $application == 'igp13') {
    $has_lot = true;
    $nb_test += 3;
}
$t = new lime_test($nb_test);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$campagne = (date('Y')-1)."";

//Début des tests
//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
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

if ($has_lot) {
    $lot = $drev->addLot();
    $lot->millesime = null;
    $lot->numero_logement_operateur = "1";
    $lot->specificite = Lot::SPECIFICITE_PRIMEUR;
    $lot->volume = 30.4;
    $lot->destination_type = null;
    $lot->destination_date = $campagne.'-11-15';
    $lot->produit_hash = $produit1->getConfig()->getHash();
    $lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
    $lot->addCepage("Chenin", 60);
    $lot->addCepage("Sauvignon", 40);
}
$drev->save();

$t->comment("Validation des Drev");
$date_validation_1 = $campagne."-10-30";
$date_validation_odg_1 = $campagne."-11-05";

$t->comment("Point de vigilance DRev");
$validation = new DRevValidation($drev);
$vigilance = $validation->getVigilances();
$t->is(count($vigilance), 2, "Il ya deux points de vigilance dont un dû au millésime absent.");

$drev->validate($date_validation_1);
$drev->save();
$t->is($drev->isValidee(),true,"La Drev est validée");
$t->is($drev->getValidation(),$date_validation_1,"La date de validation est ".$date_validation_1);
$t->is($drev->isValideeOdg(),false,"La Drev n'est pas encore validée par l'odg");

$t->is($drev->getStatutOdg(), DRevClient::STATUT_SIGNE, "La DREV est bien mise au statut signé");

$drev->setStatutOdgByRegion(DRevClient::STATUT_EN_ATTENTE);
$t->is($drev->getStatutOdg(), DRevClient::STATUT_EN_ATTENTE, "La DREV est bien mise au statut mise en attente");

if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(DRevConfiguration::getInstance()->getOdgRegions() as $region) {
        $drev->validateOdg($date_validation_odg_1, $region);
    }
} else {
    $drev->validateOdg($date_validation_odg_1);
}
$drev->save();

$t->is($drev->isValidee(),true,"La Drev est validée");
$t->is($drev->isValideeOdg(),true,"La Drev est validée par l'odg");
$t->is($drev->getValidationOdg(),$date_validation_odg_1,"La date de validation de l'odg est ".$date_validation_odg_1);
$t->is($drev->getStatutOdg(), DRevClient::STATUT_VALIDATION_ODG, "La validation ODG fait sauter le statut mise en attente");

if ($application == 'loire') {
    $t->is($drev->lots[0]->date,$date_validation_1,"La date de version du lot est celle de la validation ODG");
}

$t->comment("Création d'une modificatrice  Drev");

$date_validation_2 = $campagne."-11-15";
$date_validation_odg_2 = $campagne."-11-30";

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
    $lot->destination_date = ($campagne+1).'-06-15';
    $lot->produit_hash = $produit1->getConfig()->getHash();
    $lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
    $lot->addCepage("Chenin", 30);
    $lot->addCepage("Sauvignon", 70);
}

$t->comment("Point de vigilance DRev modificatrice");
$validation = new DRevValidation($drev_modificative);
$vigilance = $validation->getVigilances();
$t->is(count($vigilance), 3, "Il ya trois points de vigilance, un repris de la DRev et un autre dans la DRev modificatrice");

$drev_modificative->validate($date_validation_2);
if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(DRevConfiguration::getInstance()->getOdgRegions() as $region) {
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
    $dateDegustVoulue = $campagne.'-12-25';
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

foreach (DrevConfiguration::getInstance()->getOdgRegions() as $region) {
    $configDRev = sfConfig::get('drev_configuration_drev');
    $configDRev['odg'][$region]['email_notification'] = 'email@email.email';
    sfConfig::set('drev_configuration_drev', $configDRev);
}
DrevConfiguration::getInstance()->load();

$t->ok(Email::getInstance()->getMessageDRevValidationDeclarant($drev), "Mail de validation à envoyer au déclarant");
if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    $t->is(count(Email::getInstance()->getMessagesDRevValidationNotificationSyndicats($drev)), 1, "Mails de notification de validation à envoyer aux syndicats");
} else {
    $t->is(count(Email::getInstance()->getMessagesDRevValidationNotificationSyndicats($drev)), 0, "Aucun mail de notification de validation à envoyer aux syndicats");
}
$t->ok(Email::getInstance()->getMessageDRevConfirmee($drev), "Mail de confirmation à envoyer au déclarant");
$t->ok(Email::getInstance()->getMessageDrevPapierConfirmee($drev), "Mail de confirmation papier à envoyer au déclarant");
$t->is(count(Email::getInstance()->getMessagesDRevValidation($drev)), 0, "Aucun mail envoyé");

$drev->validate();
$messages = Email::getInstance()->getMessagesDRevValidation($drev);
$t->is(count($messages), 1, "Mail de validation à envoyer au déclarant");
if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    $t->like($messages[0]->getSubject(), "/Validation de la Déclaration de Revendication/", "Sujet du mail de validation");
} else {
    $t->is($messages[0]->getSubject(), "Validation de votre Déclaration de Revendication", "Sujet du mail de validation");
}
if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(DRevConfiguration::getInstance()->getOdgRegions() as $region) {
        $drev->validateOdg(null, $region);
    }
} else {
    $drev->validateOdg();
}
$messages = Email::getInstance()->getMessagesDRevValidation($drev);
$t->is(count($messages), 1, "Mail de validation definitive envoyé pour de faux au déclarant");
$t->is($messages[0]->getSubject(), "Validation définitive de votre Déclaration de Revendication", "Sujet du mail de validation définitive");
$drev->add('papier', 1);
$messages = Email::getInstance()->getMessagesDRevValidation($drev);
$t->is(count($messages), 1, "Mail de validation définitive papier envoyé pour de faux au déclarant");
$t->is($messages[0]->getSubject(), "Réception de votre Déclaration de Revendication", "Sujet du mail de confirmation papier");
