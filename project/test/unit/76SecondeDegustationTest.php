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

$t = new lime_test(13);

//Début des tests
$t->comment("Création d'un second passage");

foreach (DegustationClient::getInstance()->getHistory(1) as $d) {
    $degustation = $d;
}

$t->is(countMouvements($degustation), 3, "Il y a 3 mouvements originaux dans la dégustation");
$t->is(count($degustation->getMvtLotsPrelevables()), 1, "Il y a un seul mouvement prélevable");

$lot = $degustation->lots[0];
$lot->remove('nombre_degustation');
$lot->redegustation();
$degustation->save();

$t->is($lot->statut, Lot::STATUT_NONCONFORME, "Le lot n'a pas bougé");
$t->ok($lot->nombre_degustation, "Le lot est taggué en redegustation");
$t->is($lot->nombre_degustation, 2, "C'est le deuxième passage du lot");

$t->is(countMouvements($degustation), 4, "Il y a un mouvement de plus dans la dégustation");
$t->is(count($degustation->getMvtLotsPrelevables()), 2, "Un deuxième mouvement a été créé");

$mvts_prelevables = $degustation->getMvtLotsPrelevables();
$mvt = array_shift($mvts_prelevables);

$t->is($mvt->statut, Lot::STATUT_PRELEVABLE, "Le mouvement est prélevable");
$t->ok($mvt->nombre_degustation, "Le mouvement est taggué en redégustation");
$t->is($mvt->nombre_degustation, 2, "C'est le deuxième passage du mouvement");
$t->is($mvt->id_document, $degustation->_id, "L'id du doc du mouvement est la même degustation");
$t->is($mvt->numero_archive, $lot->numero_archive, "Le numero archive n'a pas changé");
$t->is($mvt->numero_dossier, $lot->numero_dossier, "Le numero dossier n'a pas changé");

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
