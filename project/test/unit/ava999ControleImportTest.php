<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(8);

$t->comment("Contrôle des DRev");

$t->is(count(DRevClient::getInstance()->getIds("2016")), 3303, "Nombre de drevs importés");

$drev2755 = DRevClient::getInstance()->find('DREV-00275501-2016');
$t->ok($drev2755, "La DRev 00275501 existe");



$t->comment("Contôle présence de commentaire pour 11408");
$t->isnt(EtablissementClient::getInstance()->find("ETABLISSEMENT-01140801")->commentaire, "", "Le commentaire de 11408 n'est pas vide");

$t->comment("Il existe 032874 avec pour raison_sociale ARSIC BILIANA");
$societe32874 = SocieteClient::getInstance()->find("SOCIETE-032874");
$t->isnt($societe32874, null, "La société 032874 existe");
$t->is($societe32874->raison_sociale, "ARSIC BILIANA", "La société 032874 s'appelle avec la bonne raison sociale");

$societe45013 = SocieteClient::getInstance()->find("SOCIETE-045013");
$t->isnt($societe32874, null, "La société 045013 existe");
$t->is($societe32874->raison_sociale, "ARSIC BILIANA", "La société 045013 s'appelle avec la bonne raison sociale");

$etablissement04501301 = EtablissementClient::getInstance()->find("ETABLISSEMENT-04501301");
$t->isnt($etablissement04501301, null, "L'etablissement 04501301 existe");
$t->is($etablissement04501301->nom, "ARSIC BILIANA", "L'etablissement 04501301 s'appelle avec la bon nom");

$t->comment("Contrôle des habilitations");

$habilitation04546601 = HabilitationClient::getInstance()->getLastHabilitation('04546601');
$t->ok($habilitation04546601, "L'habilitation 04546601 existe");
$t->is($habilitation04546601->get("/declaration/certifications/AOP/genres/TRANQ/appellations/CDR/activites/PRODUCTEUR/statut"), HabilitationClient::STATUT_HABILITE, "04546601 Habilité CDR en producteur");
$t->is($habilitation04546601->get("/declaration/certifications/AOP/genres/TRANQ/appellations/CVG/activites/PRODUCTEUR/statut"), HabilitationClient::STATUT_HABILITE, "04546601 Habilité CVG en producteur");
