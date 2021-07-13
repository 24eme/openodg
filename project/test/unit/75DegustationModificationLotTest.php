<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$campagne = (string) date('Y') - 1;
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$degustation = acCouchdbManager::getClient()->find($docid);
if ($degustation == null) {
    exit('Doc null');
}

$lotDegust = $degustation->lots->get(0);
$lotDegust->volume = $lotDegust->getLotPere()->volume;

$drev = DRevClient::getInstance()->find($lotDegust->id_document);

if($drevModificatrice = DRevClient::getInstance()->find($drev->_id."-M01")){
    $drevModificatrice->delete();
}

$newVolume = $lotDegust->volume + 100;


$form = new DegustationLotForm($lotDegust);
$valuesRev = array(
  '_revision' => $degustation->_rev,
    'volume' => $newVolume,
);
$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire de changement de volume est valide");

$form->save();

$degustation = acCouchdbManager::getClient()->find($docid);
$new_document_origine_id = $lot->id_document;

$t->is($degustation->lots->get(0)->volume, $newVolume, 'Le mouvement dans la degustation a le nouveau volume');

$t->is($degustation->lots->get(0)->id_document, $drev->_id."-M01", 'Le document d\'origine a changé il s\'agit maintenant d\'une modificatrice de Drev');

// Tester la mécanique d'ajout d'un nouveau lot avec une le nouveau volume et la supression du lot d'origine
