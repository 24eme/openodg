<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(10);

$cvi_test = "0000000000";

$certipaq = CertipaqService::getInstance();
$deroulant = CertipaqDeroulant::getInstance();
$drev = CertipaqDRev::getInstance();
$operateur = CertipaqOperateur::getInstance();

$token = $certipaq->getToken();

$t->ok($token, "Il y a un token");
$t->ok($certipaq->getProfil(), "On récupère l'info du profil");
$t->cmp_ok(count($deroulant->getListeActivitesOperateurs()), '>=', 1, "On récupère la liste d'activité");
$t->cmp_ok(count($deroulant->getListeTypeControle()), '>=', 1, "On récupère la liste des types controle");

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

$operateur = $operateur->recherche(['cvi' => $cvi_test]);
$t->is(count($operateur), 1, "On récupère les infos du viti");
$t->is($operateur[0]->cvi, $cvi_test, "C'est le cvi qu'on a demandé");
