<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(8);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$campagne = date('Y');

foreach(ParcellaireClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $campagne);
$parcellaire->save();

$t->is($parcellaire->_id, 'PARCELLAIRE-'.$viti->identifiant.'-'.$campagne, "L'id du doc est ".'PARCELLAIRE-'.$viti->identifiant.'-'.$campagne);

$configProduit = null;
foreach($parcellaire->getConfigProduits() as $produit) {
    $configProduit = $produit;
    break;
}

$detail = $parcellaire->addParcelle($configProduit->getHash(), "Sirah N", "2005", "Avignon", "10", "52", null, null);
$parcellaire->addParcelle($configProduit->getHash(), "Grenache", "2010", "Les Arcs", "18", "42", null, null);
$parcellaire->save();

$t->is(count($parcellaire->getProduits()), 1, "Le parcellaire a un produit");
$t->is(count($parcellaire->getParcelles()), 2, "Le parcellaire  une parcelle");
$t->is($detail->getProduit()->getLibelle(), $configProduit->getLibelleComplet(), "Le libellé du produit est ". $configProduit->getLibelleComplet());
$t->is($detail->getKey(), "SIRAH-N-2005-AVIGNON-10-52", "La clé de la parcelle est bien construite");
$t->is($detail->campagne_plantation, "2005", "La campagne de plantation a été enregistré");
$t->is($detail->cepage, "Sirah N", "Le cépage a été enregistré");
$t->is($detail->commune, "Avignon", "La commune a été enregistré");
