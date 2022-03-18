<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(15);

$campagne = (date('Y')-1)."";
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des docs précédents
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation1 = DegustationClient::getInstance()->find($k);
    $degustation1->delete(false);
}


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
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
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
$degustation->etape = DegustationEtapes::ETAPE_TABLES;
$degustation->numero_archive = 2021102;
$degustation->lieu = "Syndicat des Vignerons";
$degustation->save();
$degustationTerminee = DegustationClient::getInstance()->createDoc($dateDegustation.' 10:48:00');
$degustationTerminee->etape = DegustationEtapes::ETAPE_RESULTATS;
$degustationTerminee->save();

$t->comment('Récupération du lot');

$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id);

$degustations = DegustationClient::getInstance()->getHistoryEncours();

$t->comment('Récupération de la dégustation');

$t->is(count($degustations),1,"Il y a une dégustation en cours");
$degustationTeste = current($degustations);
$t->is($degustationTeste->_id,$degustation->_id,"La dégustation récupérée correspond à celle crée");

$t->comment('Création du formulaire');

$form = new DegustationAffectionLotForm($lot);

$nom = "Degustation du ".$degustationTeste->date." au ".$degustationTeste->lieu;
$t->is($form->getDegustationChoices(),array($degustationTeste->_id => $nom),"Exemple : Degustation n°2021102 du 21/05/2021 à 10h30 au Syndicat des Vignerons");

$t->comment('On ajoute un leurre');
$produitLeurreHash = $produitconfig2->getHash();
$leurre = $degustationTeste->addLeurre($produitLeurreHash, 'Cepage leurre', date('Y'), 1);

$t->is($leurre->leurre, true, 'C\'est un leurre');
$t->is($leurre->getProduitHash(), $produitLeurreHash, 'Le hash est le même');
$t->is($leurre->numero_table, 1, 'Le numéro de table est le 1');
$t->is($leurre->details, 'Cepage leurre', 'Le cepage du leurre est "Cepage leurre"');

$degustationTeste->save();

$values = array();
$values['degustation'] = $degustationTeste->_id;
//Checkbox
$values['preleve'] = 1;
$values['numero_table'] = 1;

$form->bind($values);
$t->ok($form->isValid(),"Le formulaire est valide");

$t->comment('Sauvegarde du formulaire');
$form->save();

$degustation = DegustationClient::getInstance()->find($degustationTeste->_id);
$t->is(count($degustation->getLots()),2,"La dégustation a un lot");
$t->is($lot->unique_id,$degustation->lots->get(1)->unique_id,"Le lot de la dégustation correspond au lot crée");
$lotAjoutee = $degustation->lots->get(1);
$t->is($lotAjoutee->preleve,date('Y-m-d'),"Le lot a une date de prelevement");
$t->is($lotAjoutee->numero_table,1,"Le lot est bien assigné à une table seulement si la dégustation est à l'étape ETAPE_TABLES");
$t->is($lotAjoutee->statut, Lot::STATUT_ATTABLE, "Le 1er lot est attablé");

$degustationAnonyme = DegustationClient::getInstance()->find($degustation->_id);
$degustationAnonyme->anonymize();
$degustationAnonyme->save();
$t->ok($degustationAnonyme->isAnonymized(),"La dégustation a été anonymisé");

$t->comment('Récupération du lot');

$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id);

$t->is($lot->getDocument()->_id, $degustation->_id, "Le lot récupéré est le dernier de l'historique (plus grand document d'ordre)");
