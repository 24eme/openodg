<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(13);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$date = (date('Y') - 1).date('-m-d');

foreach(ParcellaireClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $date, "INAO");
$parcellaire->save();

$t->is($parcellaire->_id, 'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id du doc est ".'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date));
$t->is($parcellaire->source, "INAO", "La source des données est l'INAO");

$configProduit = null;
foreach($parcellaire->getConfigProduits() as $produit) {
    $configProduit = $produit;
    break;
}

$communes = CommunesConfiguration::getInstance()->getByCodeCommune();
$commune = current($communes);
$code_commune = key($communes);

$parcelle = $parcellaire->addParcelle($configProduit->getHash(), "Sirah N", "2005", $commune, "10", "52", "LA HAUT");
$parcellaire->addParcelle($configProduit->getHash(), "Grenache", "2010", "PEYNIER", "18", "42", null);
$parcellaire->save();

$t->is(count($parcellaire->declaration), 1, "Le parcellaire a un produit");
$t->is(count($parcellaire->getParcelles()), 2, "Le parcellaire  une parcelle");
$t->is($parcelle->getProduit()->getLibelle(), $configProduit->getLibelleComplet(), "Le libellé du produit est ". $configProduit->getLibelleComplet());
$t->is($parcelle->getKey(), "SIRAH-N-2005-".$commune."-10-52-LA-HAUT", "La clé de la parcelle est bien construite");
$t->is($parcelle->code_commune, $code_commune, "Le code commune est : $code_commune");
$t->is($parcelle->campagne_plantation, "2005", "La campagne de plantation a été enregistré");
$t->is($parcelle->cepage, "Sirah N", "Le cépage a été enregistré");
$t->is($parcelle->commune, $commune, "La commune est : " . $commune);
$t->is($parcelle->lieu, "LA HAUT", "La lieu est : LA HAUT");
$t->is($parcelle->idu, $code_commune."000100052" , "Le code IDU est ".$code_commune."000100052");

$t->is($parcellaire->pieces[0]->libelle, "Parcellaire au ".$parcellaire->getDateFr(), "La déclaration a bien généré un document (une pièce)");
