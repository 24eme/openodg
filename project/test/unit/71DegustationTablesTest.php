<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$campagne = (date('Y')-1)."";
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";

$doc = acCouchdbManager::getClient()->find($docid);

$t->is($doc->hasFreeLots(), true, 'Il y a des lots non assignés');

$t->comment('On attribue le lot à la première table');
$doc->getLots()[0]->numero_table = 1;
$t->is(count($doc->getLotsTableOrFreeLots(1)), 1, 'La table 1 à un lot');
$t->is($doc->hasFreeLots(), false, "Il n'y a plus de lot non assigné");
