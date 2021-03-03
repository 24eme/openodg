<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(2);

//Début des tests
$t->comment("Création d'un second passage");

foreach (DegustationClient::getInstance()->getHistory(1) as $d) {
    $degustation = $d;
}

$lot = $degustation->lots[0];
$new_mvmt = $lot->redegustation($degustation);

$nb_mvmts = 0;
foreach ($degustation->mouvements_lots as $ope) {
    foreach ($ope as $m) {
        $nb_mvmts++;
    }
}

$t->is($nb_mvmts, 3, "Le nombre de mouvement n'a pas bougé");

$t->is($new_mvmt->statut, Lot::STATUT_PRELEVABLE, 'Le status est changé');
$t->is($new_mvmt->id_document, $degustation->_id, "L'id du doc est la même degustation");
$t->is($new_mvmt->numero_archive, $lot->numero_archive, "Le numero archive n'a pas changé");
$t->is($new_mvmt->numero_dossier, $lot->numero_dossier, "Le numero dossier n'a pas changé");

$t->is($lot->statut, Lot::STATUT_NONCONFORME, "Le statut n'as pas bougé pour le lot originel");

$new_Degustation = new Degustation();
$new_Degustation->addLot($new_mvmt);
$new_Degustation->generateMouvementsLots();

$lots = $new_Degustation->getLots();
$t->is(count($lots), 1, "Il y a un lot dans la nouvelle dégustation");

$new_lot = $lots[0];
$t->is($new_lot->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le statut est en attente de prélèvement");
