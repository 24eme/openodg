<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(8);

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

$t->comment("Les enfants de Cecilius (04546601) en demande d'habilitation alors que habilité depuis 20/09");

$habilitation04546601 = HabilitationClient::getInstance()->getLastHabilitation('04546601');
$t->ok($habilitation04546601, "L'habilitation 04546601 existe");
$t->is($habilitation04546601->get("/declaration/certifications/AOP/genres/TRANQ/appellations/CDR/activites/PRODUCTEUR/statut"), HabilitationClient::STATUT_HABILITE, "04546601 Habilité CDR en producteur");
$t->is($habilitation04546601->get("/declaration/certifications/AOP/genres/TRANQ/appellations/CVG/activites/PRODUCTEUR/statut"), HabilitationClient::STATUT_HABILITE, "04546601 Habilité CVG en producteur");

$t->comment("L'établissement avec le CVI 0707000160 doit être \"Négociant Vinificateur\" et l'établissement avec le CVI 0707000150 doit être \"Producteur\"");

$etablisement0707000160 = EtablissementClient::getInstance()->findByCvi("0707000160");
$t->is($etablisement0707000160->famille, "NEGOCIANT_VINIFICATEUR", "La famille du CVI 0707000160 négociant vinificateur");

$etablisement0707000150 = EtablissementClient::getInstance()->findByCvi("0707000150");
$t->is($etablisement0707000150->famille, "PRODUCTEUR", "La famille du CVI 0707000150 négociant vinificateur");

$t->comment("La drev du 035292 doit être sur l'établissement (Négociant vinificateur) avec le CVI 0707000160");

$drev035292NV = DRevClient::getInstance()->find('DREV-'.$etablisement0707000160->identifiant.'-2016');
$t->ok($drev035292NV, "La DRev du négociant vinificateur 0707000160 existe");

$drev035292P = DRevClient::getInstance()->find('DREV-'.$etablisement0707000150->_id.'-2016');
$t->ok($drev035292P, "La DRev du producteur 0707000150 n'existe pas");

$t->comment("Existance de Denis A");

$t->ok(CompteClient::getInstance()->find('COMPTE-00144202'), "L'intelocuteur de son EARL existe");
$t->ok(CompteClient::getInstance()->find('COMPTE-01449002'), "L'intelocuteur du syndicat de CAIRANNE");
