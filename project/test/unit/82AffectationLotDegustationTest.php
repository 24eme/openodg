<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test();

$campagne = (date('Y')-1)."";
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des docs précédents
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
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

$commissions = DegustationClient::getInstance()->getHistoryLieux();

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
$degustation->save();
$degustationTerminee = DegustationClient::getInstance()->createDoc($dateDegustation.' 10:48:00');
$degustationTerminee->etape = DegustationEtapes::ETAPE_RESULTATS;
$degustationTerminee->save();

$t->comment('Récupération du lot');

$lot = LotsClient::getInstance()->findByUniqueId($drev->lots[0]->declarant_identifiant, $drev->lots[0]->unique_id);

$degustations = DegustationClient::getInstance()->getHistoryEncours();

$t->comment('Récupération de la dégustation');

$t->is(count($degustations),1,"Il y a une dégustation en cours");
$degustationTeste = $degustations[0];
$t->is($degustationTeste->_id,$degustation->_id,"La dégustation récupérée correspond à celle crée");

$t->comment('Création du formulaire');

$form = new DegustationAffectionLotForm($lot,$degustationTeste);

$values = array();
$values['degustation'] = $degustationTeste->_id;
$values['prelevement'] = false;
$values['table'] = 2;

$form->bind($values);
$form->save();

$t->ok($form->isValid(),"Le formulaire est valide");

$degustations = DegustationClient::getInstance()->find($degustationTeste->_id);

$t->is(count($degustationTeste->lots),1,"La dégustation à un lot");
$t->is($lots[0]->unique_id,$degustationTeste->lots->getFirst()->unique_id,"Le lot de la dégustation correspond au lot crée");
$lotAjoutee = $degustationTeste->lots->getFirst();
$t->is($lotAjoutee->prelevee,null,"Le lot n'a pas été prelevé");
$t->is($lotAjoutee->numero_table,2,"Le lot est bien assigné à la table 2");
