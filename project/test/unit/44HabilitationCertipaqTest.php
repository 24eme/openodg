<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test();

$certipaq = CertipaqService::getInstance();
$token = $certipaq->getToken();

$t->ok($token, "Il y a un token: $token");
