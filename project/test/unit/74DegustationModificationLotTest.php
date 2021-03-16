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

$first_lot_degust = $degustation->lots->get(0);
$volume = $first_lot_degust->volume;
$first_document_origine_id = $first_lot_degust->id_document;
$drev = DRevClient::getInstance()->find($first_document_origine_id);

if($drevModificatrice = DRevClient::getInstance()->find($first_document_origine_id."-M01")){
  $drevModificatrice->delete(false);
}

$first_mvtlot_drev = $first_lot_degust->getLotPere();

$t->comment("La condition préalable est que le lot soit prélevable");
$t->is($first_mvtlot_drev->statut,Lot::STATUT_PRELEVABLE, "Le lot de la DRev est bien prélevable");

$newVolume = $volume+100;

$form = new DegustationLotForm($first_lot_degust);
$valuesRev = array(
  '_revision' => $degustation->_rev,
    'volume' => $newVolume,
);
$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire de changement de volume est valide");

$form->save();

$degustation = acCouchdbManager::getClient()->find($docid);
$lot = $degustation->lots->get(0);
$new_document_origine_id = $lot->id_document;

$t->is($lot->volume, $newVolume, 'Le mouvement dans la degustation a le nouveau volume');

$t->isnt($first_document_origine_id,$new_document_origine_id, 'Le document d\'origine a changé il s\'agit maintenant d\'une modificatrice de Drev');

$first_document_origine = DRevClient::getInstance()->find($first_document_origine_id);


$oldMvtLot = $first_document_origine->exist($first_lot_degust->origine_mouvement);

$t->is(false, $oldMvtLot, 'Le mouvement lot d\'origine de la Degustation de clef '.$first_lot_degust->origine_mouvement.' n\'existe plus dans la DREV 0');

$t->isnt($first_lot_degust->origine_mouvement, $first_mvtlot_drev->getHash(), 'Les mouvements lot d\'origine dans la DREV 0 et la DREV M01 sont différents');

$lot_origine_drev = $first_document_origine->get($first_lot_degust->getHash());

$t->is($lot_origine_drev->volume, $volume, 'Le volume dans la Drev 0 n\'a pas changé');
$t->is($lot_origine_drev->statut, Lot::STATUT_NONPRELEVABLE, 'Le statut dans la Drev 0 est devenu "NON PRELEVABLE"');

$drevModificatrice = DRevClient::getInstance()->find($new_document_origine_id);
$lot_drev_modif = $drevModificatrice->get($lot->getHash());

$t->is($lot_drev_modif->volume, $newVolume, 'Le mouvement dans la Drev Modif a le nouveau volume');
$t->is($lot_drev_modif->statut, Lot::STATUT_PRELEVABLE, 'Le statut dans la Drev Modif est "PRELEVABLE"');
