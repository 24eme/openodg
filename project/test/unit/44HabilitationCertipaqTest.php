<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(6);

$certipaq = CertipaqService::getInstance();
$deroulant = CertipaqDeroulant::getInstance();
$drev = CertipaqDRev::getInstance();

$token = $certipaq->getToken();

$t->ok($token, "Il y a un token");
$t->ok($certipaq->getProfil(), "On récupère l'info du profil");
$t->cmp_ok(count($deroulant->getListeActivitesOperateurs()), '>=', 1, "On récupère la liste d'activité");
$t->cmp_ok(count($deroulant->getListeTypeControle()), '>=', 1, "On récupère la liste des types controle");

$t->comment("DRev");
$t->ok($drev->list(['date' => ['2019-01-01', '2022-01-01']]));
try {
    var_dump($drev->find($i));
    $t->fail();
} catch (Exception $e) {
    $t->pass($e->getMessage());
}
