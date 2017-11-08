<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(2);

$t->comment("Contrôle des DRev");

$t->is(count(DRevClient::getInstance()->getIds("2016")), 3303, "Nombre de drevs importés");

$drev2755 = DRevClient::getInstance()->find('DREV-00275501-2016');
$t->ok($drev2755, "La DRev 00275501 existe");
