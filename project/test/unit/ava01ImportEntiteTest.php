<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(9);
$t->comment("Lancement d'un import light");

$import_entite_task = new importEntiteFromXmlTask($this->dispatcher, $this->formatter);

$t->comment('création des différentes sociétés');

ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_14199.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();

$societe14199 = SocieteClient::getInstance()->find("SOCIETE-014199");
$t->isnt($societe14199, null, "La societe 014199 existe");
$t->is($societe14199->raison_sociale,"CAVE LES VIGNERONS DE TAVEL", "La societe 014199 a la bonne raison sociale");
$t->is($societe14199->telephone, "", "La societe 014199 n'a pas de numéro de téléphone");
$t->is($societe14199->email, "admin@cavedetavel.com", "La societe 014199 a le bon email");
$t->is($societe14199->siege->adresse, "RTE DE LA COMMANDERIE", "La societe 014199 a la bonne adresse");
$t->is($societe14199->siege->commune, "TAVEL ", "La societe 014199 a la bonne commune");
$t->is($societe14199->siege->code_postal, "30126", "La societe 014199 a le bon cp");
$t->is($societe14199->siret,"77594472100015", "La societe 014199 a le bon siret");


$etablissement14199 = EtablissementClient::getInstance()->find("ETABLISSEMENT-01419901");
$t->isnt($etablissement14199, null, "L'etablissement 01419901 existe");
$t->is($etablissement14199->cvi, "3032604760", "L'etablissement 01419901 a le bon cvi");

$compte14199 = CompteClient::getInstance()->find("COMPTE-01419901");
$t->isnt($compte14199, null, "Le compte 01419901 existe");
$t->is($compte14199->statut,"ACTIF", "Le compte 01419901 est actif");
$t->is($compte14199->id_societe,"SOCIETE-014199", "Le compte 01419901 a pour id societe SOCIETE-014199");
$t->is($compte14199->nom,"CAVE LES VIGNERONS DE TAVEL", "Le compte 01419901 a le bon nom");
$t->is($compte14199->nom_a_afficher,"CAVE LES VIGNERONS DE TAVEL", "Le compte 01419901 a le bon nom a afficher");

ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_16952.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();

$societe16952 = SocieteClient::getInstance()->find("SOCIETE-016952");
$t->isnt($societe16952, null, "La societe 016952 existe");
$t->is($societe16952->telephone, "04.66.50.03.93", "La societe 016952 a le bon numéro de téléphone");

ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_9967.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();
$societe9967 = SocieteClient::getInstance()->find("SOCIETE-009967");
$compte996701 = CompteClient::getInstance()->find("COMPTE-00996701");
$t->is($societe9967->statut, "SUSPENDU", "La societe 009967 est suspendu");
$t->is($compte996701->statut, "SUSPENDU", "Le compte 009967 est suspendu");

$etablissement16952 = EtablissementClient::getInstance()->find("ETABLISSEMENT-01695201");
$t->isnt($etablissement16952, null, "L'etablissement 01695201 existe");

$compte16952 = CompteClient::getInstance()->find("COMPTE-01695201");
$t->isnt($compte16952, null, "Le compte 01695201 existe");


ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/autres_groupes_15103.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();

$societe15103 = SocieteClient::getInstance()->find("SOCIETE-015103");
$t->isnt($societe15103, null, "La societe 015103 existe");

$etablissement15103 = EtablissementClient::getInstance()->find("ETABLISSEMENT-01510301");
$t->is($etablissement15103, null, "L'etablissement 01510301 n'existe pas : C'est une société autre");

$compte15103 = CompteClient::getInstance()->find("COMPTE-01510301");
$t->isnt($compte15103, null, "Le compte 01510301 existe");

ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/autres_nogroupes_12046.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();

$societe12046 = SocieteClient::getInstance()->find("SOCIETE-012046");
$t->is($societe12046, null, "La societe 12046 n'existe pas :  c'est un interlocuteur");

$etablissement12046 = EtablissementClient::getInstance()->find("ETABLISSEMENT-01204601");
$t->is($etablissement12046, null, "L'etablissement 01510301 n'existe pas : c'est un interlocuteur");

$compte12046 = CompteClient::getInstance()->find("COMPTE-01204601");
$t->is($compte12046, null, "Le compte 01204601 n'existe pas : c'est un interlocuteur");

// Compte interlocuteur
$compte01419902 = CompteClient::getInstance()->find("COMPTE-01419902");
$t->isnt($compte01419902, null, "Le compte 01419902 existe en tant qu'interlocuteur");

$t->is($compte01419902->nom, "PALY", "Son nom est bon.");
