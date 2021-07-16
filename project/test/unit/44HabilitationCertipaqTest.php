<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(10);

$cvi_test = "0708900950";

$certipaq = CertipaqService::getInstance();
$deroulant = CertipaqDeroulant::getInstance();
$drev = CertipaqDRev::getInstance();
$operateur = CertipaqOperateur::getInstance();

$statuts_habilitations = $deroulant->getListeStatutHabilitation();
$token = $certipaq->getToken();

$t->ok($token, "Il y a un token");
$t->ok($certipaq->getProfil(), "On récupère l'info du profil");
$t->cmp_ok(count($deroulant->getListeActivitesOperateurs()), '>=', 1, "On récupère la liste d'activité");
$t->cmp_ok(count($deroulant->getListeTypeControle()), '>=', 1, "On récupère la liste des types controle");
$t->cmp_ok(count($deroulant->getListeStatutHabilitation()), '>=', 1, "On récupère la liste des statuts d'habilitation");

$t->comment("DRev");
$t->ok($drev->list(['date' => ['2019-01-01', '2022-01-01']]));
try {
    $drev->find(1);
    $t->fail();
} catch (Exception $e) {
    $t->pass($e->getMessage());
}

try {
    $operateur->recherche();
    $t->fail();
} catch (Exception $e) {
    $t->pass($e->getMessage());
}

try {
    $operateur->recherche(['raison_sociale' => 'test']);
    $t->fail();
} catch (Exception $e) {
    $t->pass($e->getMessage());
}

$resultats = $operateur->recherche(['cvi' => $cvi_test]);
$t->is(count($resultats), 1, "On récupère les infos du viti");
$t->is($resultats[0]->cvi, $cvi_test, "C'est le cvi qu'on a demandé");

$t->comment("Identifiant opérateur: ".$resultats[0]->id);
$infos_operateur = $operateur->recuperation($resultats[0]->id);
$t->ok($infos_operateur->id, "On récupère les infos opérateurs");
$t->is($infos_operateur->cvi, $cvi_test, "C'est le bon operateur");
$t->cmp_ok(count($infos_operateur->sites), ">", 0, "Il a des sites");
$t->cmp_ok(count($infos_operateur->sites[0]->habilitations), ">", 0, "Il a des habilitations");
