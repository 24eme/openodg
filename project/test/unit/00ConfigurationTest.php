<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');


if ($application != 'igp13') {
    $t = new lime_test(1);
}else{
  $t = new lime_test(2);
}

$this->campagne = date("Y");
$t->comment("test de la configuration d'année $this->campagne");

$config = ConfigurationClient::getInstance()->getConfiguration($this->campagne.'-10-01');

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

if ($application == 'igp13') {
  $t->comment("On active les vins de base pour les EFF");
  $config->setEffervescentVindebaseActivate();
  $produitsEff = array();
  foreach($config->getProduits() as $keyProd => $produit) {

      if(strpos($produit->getHash(), "EFF/appellations") === false) {
          continue;
      }
      $produitsEff[$keyProd] = $produit;
  }


  foreach ($produitsEff as $p) {
    $t->ok(strpos($p->getLibelleFormat(), "Vin de base") === 0, "Les produits effeverscents ont vin de base dans leur nom : ".$p->getLibelleFormat());
    break;
  }
}
