<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}

function countMouvements($degustation) {
    $nb_mvmts = 0;

    foreach ($degustation->mouvements_lots as $ope) {
        foreach ($ope as $m) {
            $nb_mvmts++;
        }
    }

    return $nb_mvmts;
}

$t = new lime_test(2);

//Début des tests
$t->comment("Création d'un second passage");

foreach (DegustationClient::getInstance()->getHistory(1) as $d) {
    $degustation = $d;
}

$lot = $degustation->lots[0];
$new_mvmt = $lot->redegustation($degustation);

$t->is($new_mvmt->statut, Lot::STATUT_PRELEVABLE, 'Le status est changé');
$t->is($new_mvmt->id_document, $degustation->_id, "L'id du doc est la même degustation");
$t->is($new_mvmt->numero_archive, $lot->numero_archive, "Le numero archive n'a pas changé");
$t->is($new_mvmt->numero_dossier, $lot->numero_dossier, "Le numero dossier n'a pas changé");

$t->is($lot->statut, Lot::STATUT_NONCONFORME, "Le statut n'as pas bougé pour le lot originel");
$t->is(countMouvements($degustation), 3, "Le nombre de mouvement de la dégustation originale n'a pas bougé");

$t->comment("Nouvelle dégustation avec le nouveau mouvement");
$new_Degustation = new Degustation();
$new_Degustation->addLot($new_mvmt);

$t->is(countMouvements($new_Degustation), 0, "L'ajout de lot n'a pas généré de mouvements de lots");

$new_Degustation->generateMouvementsLots();

$lots = $new_Degustation->getLots();
$t->is(count($lots), 1, "Il y a un lot dans la nouvelle dégustation");

$new_lot = $lots[0];
$t->is($new_lot->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le statut est en attente de prélèvement");
