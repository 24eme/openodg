<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(4);

$t->comment("Contrôle des DRev");

$t->is(count(DRevClient::getInstance()->getIds("2016")), 3303, "Nombre de drevs importés");

$drev2755 = DRevClient::getInstance()->find('DREV-00275501-2016');
$t->ok($drev2755, "La DRev 00275501 existe");

$t->comment("Contrôle des habilitations");

$habilitation04546601 = HabilitationClient::getInstance()->getLastHabilitation('04546601');
$t->ok($habilitation04546601, "L'habilitation 04546601 existe");
$t->is($habilitation04546601->get("/declaration/certifications/AOP/genres/TRANQ/appellations/CDR/activites/PRODUCTEUR/statut"), HabilitationClient::STATUT_HABILITE, "04546601 Habilité CDR en producteur");
$t->is($habilitation04546601->get("/declaration/certifications/AOP/genres/TRANQ/appellations/CVG/activites/PRODUCTEUR/statut"), HabilitationClient::STATUT_HABILITE, "04546601 Habilité CVG en producteur");
