<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if(!sfConfig::get('app_certipaq_oauth')) {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled (not configured)");
    return;
}

$t = new lime_test(14);

$t->ok(CertipaqService::getInstance()->getToken(), "CertipaqService arrive à récupérer un token");
$profil = (array) CertipaqService::getInstance()->getProfil();
$t->is(array_keys($profil), array('nom', 'prenom'), "On récupère l'info du profil");

$operateurs = CertipaqOperateur::getInstance()->getAll();
$t->ok(count($operateurs), "On est capable de récupérer tous les opérateurs");

$operateur_test = (array) array_shift($operateurs);
$t->is(array_keys($operateur_test), array('id','dr_type_entreprise_id','raison_sociale','nom_entreprise','siret','cvi','adresse','complement_adresse','cp','ville','pays','canton','localisation','latitude','longitude','telephone','portable','fax','email','observations'), "L'operateur de test récupéré a bien les bons attributs");

$resultats = CertipaqOperateur::getInstance()->findByCviOrSiret($operateur_test['cvi']);
$t->is(count($resultats), 1, "On récupère les infos du viti sur la base du cvi");
$t->is($resultats[0]->cvi, $operateur_test['cvi'], "C'est le cvi qu'on a demandé");

$resultats = CertipaqOperateur::getInstance()->findByCviOrSiret($operateur_test['siret']);
$t->is(count($resultats), 1, "On récupère les infos du viti sur la base du siret");
$t->is($resultats[0]->siret, $operateur_test['siret'], "C'est le siret qu'on a demandé");

$t->comment("Identifiant opérateur: ".$operateur_test['id']);
$infos_operateur = CertipaqOperateur::getInstance()->recuperation($operateur_test['id']);
$t->is(array_keys((array) $infos_operateur), array('id','dr_type_entreprise_id','raison_sociale','nom_entreprise','siret','cvi','adresse','complement_adresse','cp','ville','pays','canton','localisation','latitude','longitude','telephone','portable','fax','email','observations','sites','organismes_rattachement'), "On a bien les infos attentues pour la requete opérateur par id");
$t->is($infos_operateur['id'], $operateur_test['id'], "On récupère les infos opérateurs");
$t->is($infos_operateur['cvi'], $operateur_test['cvi'], "C'est le bon cvi");
$t->ok(count($infos_operateur['sites']), "Il a des sites");
$t->ok(count($infos_operateur['sites'][0]->habilitations), "Il a des habilitations");

$t->ok($infos_operateur['sites'][0]->habilitations[0]->dr_statut_habilitation->cle, "On récupère le statut de l'habilitation ".$infos_operateur['sites'][0]->dr_statut_habilitation->libelle);
