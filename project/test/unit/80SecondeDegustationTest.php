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

$t = new lime_test(16);

//Début des tests
$t->comment("Création d'un second passage");

foreach (DegustationClient::getInstance()->getHistory(1) as $d) {
    $degustation = $d;
}

$t->is(countMouvements($degustation), 24, "Il y a 24 (8 × 3) mouvements originaux dans la dégustation");
$t->is(count($degustation->getMvtLotsPrelevables()), 1, "Il y a un seul mouvement prélevable");

$lot = $degustation->lots[0];
$t->is(MouvementLotHistoryView::getInstance()->getNombrePassage($lot), 1, "C'est le premier passage du lot");
$lot->redegustation();
$degustation->save();

$t->is(MouvementLotHistoryView::getInstance()->getNombrePassage($lot), 2, "C'est le deuxième passage du lot");
$t->is($lot->statut, Lot::STATUT_NONCONFORME, "Le lot n'a pas bougé");

$t->is(countMouvements($degustation), 25, "Il y a un mouvement de plus dans la dégustation");
$t->is(count($degustation->getMvtLotsPrelevables()), 2, "Un deuxième mouvement a été créé");

$mvts_prelevables = $degustation->getMvtLotsPrelevables();
foreach ($mvts_prelevables as $key => $m) {
    if (strpos($key, 'DEGUST') === 0) {
        $mvt = $m;
        continue;
    }
}

$t->is($mvt->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le mouvement est prélevable");
//$t->ok($mvt->nombre_degustation, "Le mouvement est taggué en redégustation");
//$t->is($mvt->nombre_degustation, 2, "C'est le deuxième passage du mouvement");
$t->is($mvt->id_document, $degustation->_id, "L'id du doc du mouvement est la même degustation");
$t->is($mvt->numero_archive, $lot->numero_archive, "Le numero archive n'a pas changé");
$t->is($mvt->numero_dossier, $lot->numero_dossier, "Le numero dossier n'a pas changé");

$degustation->generateMouvementsLots();
$degustation->save();

$t->is(countMouvements($degustation), 4, "Regénérer les mouvements n'en rajoute pas");

$t->comment("Nouvelle dégustation");
$nouvelle_degustation = new Degustation();
$lot_2passage = $nouvelle_degustation->addLot($mvt, Lot::STATUT_ATTENTE_PRELEVEMENT);

$t->is($lot_2passage->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le nouveau lot est en attente de prélèvement");
//$t->is($lot_2passage->nombre_degustation, 2, "Il s'agit de la deuxième dégustation");

if (getenv('NODELETE')) {
    exit;
}

$mvmts_prelevables = $degustation->getMvtLotsPrelevables();
foreach($mvmts_prelevables as $m) {
    if ($m->nombre_degustation) {
        $degustation->remove($m->origine_mouvement);
    }
}
$degustation->save();
