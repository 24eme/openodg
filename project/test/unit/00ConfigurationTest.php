<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(1);

$campagne = date("Y");
$t->comment("test de la configuration d'année $campagne");

$config = ConfigurationClient::getInstance()->getConfiguration($campagne.'-10-01');

$produitsEff = array();
foreach($config->getProduits() as $keyProd => $produit) {

    if(strpos($produit->getHash(), "EFF/appellations") === false) {
        continue;
    }
    $produitsEff[$keyProd] = $produit;
}

if(!count($produitsEff)){
  $t->ok(true, "La configuration n'a pas de produits effeverscents");
}
foreach ($produitsEff as $p) {
  $t->ok(!strpos($p->getLibelleFormat(), "Vin de base"), "Les produits effeverscents ne sont pas libellés vins de base");
  break;
}
