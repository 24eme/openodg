<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(2);

$compte = CompteClient::getInstance()->find("COMPTE-E7523700100");
$etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-7523700100");

if($compte) {
    $compte->delete();
}
if($etablissement) {
    $etablissement->delete();
}

$compte = new Compte(CompteClient::TYPE_COMPTE_ETABLISSEMENT);
$compte->adresse = "1 rue Garnier";
$compte->commune = "NEUILLY-SUR-SEINE";
$compte->code_postal = "92200";
$compte->email = "email@email.fr";
$compte->cvi = "7523700100";
$compte->identifiant = "E7523700100";
$compte->numero_archive = "00001";
$compte->campagne_archive = "UNIQUE";
$compte->addInfo("attributs", CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS);
$compte->addInfo("attributs", CompteClient::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR);
$compte->addInfo("attributs", CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR);
$compte->addInfo("attributs", CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR);
$compte->addInfo("attributs", CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR);
$compte->save();

$etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-7523700100");

$t->ok($compte->_rev, "Le compte a été créé");
$t->ok($etablissement->_rev, "L'établissement a été créé");
