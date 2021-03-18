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

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des docs précédents
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = TransactionClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

//Début des tests
$t->comment("Création d'un second passage");

foreach (DegustationClient::getInstance()->getHistory(1) as $d) {
    $degustation = $d;
}

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();

$t->is(countMouvements($degustation), 24, "Il y a 24 (8 × 3) mouvements originaux dans la dégustation");
$t->is(count($lotsPrelevables), 1, "Il y a un seul mouvement prélevable");

$lot = $degustation->lots[0];
$t->is(MouvementLotHistoryView::getInstance()->getNombrePassage($lot), 1, "C'est le premier passage du lot");
$lot->redegustation();
$degustation->save();

$t->is(MouvementLotHistoryView::getInstance()->getNombrePassage($lot), 2, "C'est le deuxième passage du lot");
$t->is($lot->statut, Lot::STATUT_NONCONFORME, "Le lot n'a pas bougé");

$t->is(countMouvements($degustation), 25, "Il y a un mouvement de plus dans la dégustation");

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();

$t->is(count($lotsPrelevables), 2, "Un deuxième mouvement a été créé");

foreach ($lotsPrelevables as $key => $m) {
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
$lot_2passage = $nouvelle_degustation->lots->add(null, $mvt);

$t->is($lot_2passage->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le nouveau lot est en attente de prélèvement");
//$t->is($lot_2passage->nombre_degustation, 2, "Il s'agit de la deuxième dégustation");

if (getenv('NODELETE')) {
    exit;
}

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
foreach($lotsPrelevables as $m) {
    if ($m->nombre_degustation) {
        $degustation->remove($m->origine_mouvement);
    }
}
$degustation->save();
