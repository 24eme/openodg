<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(3);

//Début des tests
$t->comment("Création d'un second passage");

$lots_a_redeguster = DegustationClient::getInstance()->getManquements();
$lot_a_redeguster = array_pop($lots_a_redeguster);
$specificite_originale = $lot_a_redeguster->specificite;

$lot_a_redeguster->prelevable = Lot::STATUT_PRELEVABLE;
$t->is($lot_a_redeguster->prelevable, Lot::STATUT_PRELEVABLE, 'Le statut est à prélevable');

DegustationClient::updatedSpecificite($lot_a_redeguster);
$t->is($lot_a_redeguster->specificite, $specificite_originale.', 2ème passage', 'Il s\'agit d\'un second passage');

DegustationClient::updatedSpecificite($lot_a_redeguster);
$t->is($lot_a_redeguster->specificite, $specificite_originale.', 3ème passage', "Il s'agit d'un troisième passage");
