<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(46);
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
echo $import_entite_task_ret;

$societe9967 = SocieteClient::getInstance()->find("SOCIETE-009967");
$etablissement9967 = EtablissementClient::getInstance()->find("ETABLISSEMENT-00996701");
$compte996701 = CompteClient::getInstance()->find("COMPTE-00996701");
$t->is($societe9967->statut, "SUSPENDU", "La societe 009967 est suspendu");
$t->is($etablissement9967->statut, "SUSPENDU", "L'etablissement 00996701 est suspendu");
$t->is($compte996701->statut, "SUSPENDU", "Le compte 00996701 est suspendu");

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



$t->comment("Import de la societe 19499 pour tester les groupes");
ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_19499.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();

ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/evvSiret_45453.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();
$societe45453 = SocieteClient::getInstance()->find("SOCIETE-045453");
$t->isnt($societe45453, null, "La societe 045453 existe");
$t->is($societe45453->siege->adresse, "VINIFICATEUR", "La societe 045453 a pour adresse VINIFICATEUR");
$t->is($societe45453->siege->adresse_complementaire, "100 COTEAUX DE BASSENON − DOMAINE RHONE 2 VALLEES", "La societe 045453 a pour adresse complementaire 100 COTEAUX DE BASSENON DOMAINE RHONE 2 VALLEES");




$societe19499 = SocieteClient::getInstance()->find("SOCIETE-019499");
$t->isnt($societe19499, null, "La societe 014199 existe");
$t->is($societe19499->raison_sociale,"LES COTEAUX DU MORTIER (EARL)", "La societe 019499 a la bonne raison sociale");

$t->comment("Import de l'interlocuteur 20104 de la societe 19499 pour tester les groupes");
ob_start();
$import_entite_task->run(array('file_path' => '/tmp/ODGRHONE_IDENTITES_DATA/IDENTITES_DATA/autres_nogroupes_20104.xml'),array());
$import_entite_task_ret = ob_get_contents();
ob_end_clean();

// Compte interlocuteur
$compte01949902 = CompteClient::getInstance()->find("COMPTE-01949902");
$t->isnt($compte01949902, null, "Le compte 01949902 existe en tant qu'interlocuteur");
$t->is($compte01949902->nom, "AGIER", "Son nom est bon.");

$typeg = "groupes";
$tag_0 = "Assemblée Générale 2014";
$tag_1 = "Conseil d'Administration 2014";
$tag_s0 = "assemblee_generale_2014";
$tag_s1 = "conseil_d_administration_2014";

$t->ok($compte01949902->exist('groupes'),"Le compte 01949902 possède la clé groupe ");

$t->ok(count($compte01949902->groupes),"Le compte 01949902 a des groupes ");

$t->ok($compte01949902->groupes->exist($tag_0),"Le compte 01949902 possède le groupe : ".$tag_0."");
$t->ok($compte01949902->groupes->exist($tag_1),"Le compte 01949902 possède le groupe : ".$tag_1."");

$fct0 = "Délégué";
$fct1 = "Suppléant";

if($compte01949902->groupes->exist($tag_0) && $compte01949902->groupes->exist($tag_1)){
  $t->is($compte01949902->groupes->$tag_0,$fct0,"Le compte 01949902 a la fct $fct0 dans le groupe $tag_0");
  $t->is($compte01949902->groupes->$tag_1,$fct1,"Le compte 01949902 a la fct $fct1 dans le groupe $tag_1");
}

$t->ok(array_key_exists($typeg,$compte01949902->getTags()->toArray(1,0)),"Le compte 01949902 a un type de tag : ".$typeg."");

foreach ($compte01949902->getTags() as $typet => $t_t){
  if($typet == $typeg){
    $t->ok(in_array($tag_s0,$t_t->toArray(1,0)),"Le compte 01949902 possède le tag : ".$tag_0."");
    $t->ok(in_array($tag_s1,$t_t->toArray(1,0)),"Le compte 01949902 possède le tag : ".$tag_1."");
  }
}
