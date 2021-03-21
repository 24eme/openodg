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

$t = new lime_test(1);

//Début des tests
$t->comment("Récupération des historiques des mouvements");

foreach (DegustationClient::getInstance()->getHistory(1) as $d) {
    $degustation = $d;
}

$lot = $d->lots->get(0);
$mouvements = MouvementLotHistoryView::getInstance()->getMouvements($lot->declarant_identifiant, $lot->campagne, $lot->numero_dossier, $lot->numero_archive);

$t->is(count($mouvements->rows), 8, "Il y a 8 mouvements pour ce lot");
