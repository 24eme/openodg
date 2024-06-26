<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (in_array($application, array('nantes', 'loire'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas de parcellaire activé");
    return;
}

$t = new lime_test(20);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$year = date('Y') - 1;
$date = $year.'-12-01';
$campagne = $year.'-'.($year + 1);
foreach(ParcellaireClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $date, "INAO");
$parcellaire->save();

$t->is($parcellaire->_id, 'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id du doc est ".'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date));
$t->is($parcellaire->campagne, $campagne, 'La campagne du parcellaire est bien indiquée');
$t->is($parcellaire->source, "INAO", "La source des données est l'INAO");

$configProduit = null;
foreach($parcellaire->getConfigProduits() as $produit) {
    $configProduit = $produit;
    break;
}

$communes = CommunesConfiguration::getInstance()->getByCodeCommune();
$t->ok($communes, "config/communes.yml contient des communes");
$commune = current($communes);
$code_commune = key($communes);
$commune2 = next($communes);
$numero_ordre_key = "00";
$parcelle = $parcellaire->addParcelleWithProduit($configProduit->getHash(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), "Grenache", "2010", $commune2, "", "AK", "47", null);
$parcellaire->addParcelleWithProduit($configProduit->getHash(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT",25);
$parcellaire->addParcelleWithProduit($configProduit->getHash(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT",26);
$parcellaire->save();

$t->is(count($parcellaire->declaration), 1, "Le parcellaire a un produit");
$t->is(count($parcellaire->getParcelles()), 4, "Le parcellaire 4 parcelles");
$t->is(count($parcellaire->declaration->getParcelles()), 4, "Le parcellaire a des parcelles dans le produit");
$parcelle = array_values($parcellaire->declaration->getParcelles())[0];
$t->is($parcelle->produit_hash, '/declaration/certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT', "La première parcelles du produit as bien un produit_hash");
$t->is($parcelle->getProduit()->getLibelleComplet(), $configProduit->getLibelleComplet(), "Le libellé du produit est ". $configProduit->getLibelleComplet());
$t->is($parcelle->getKey(), $code_commune."000AB0052-00", "La clé de la parcelle est bien construite");
$t->is($parcelle->code_commune, $code_commune, "Le code commune est : $code_commune");
$t->is($parcelle->campagne_plantation, "2005", "La campagne de plantation a été enregistré");
$t->is($parcelle->cepage, "Sirah N", "Le cépage a été enregistré");
$t->is($parcelle->numero_ordre, 0, "Le numéro d'ordre a été enregistré");
$t->is($parcelle->commune, $commune, "La commune est : " . $commune);
$t->is($parcelle->lieu, "LA HAUT", "La lieu est : LA HAUT");
$t->is($parcelle->idu, $code_commune."000AB0052" , "Le code IDU est ".$code_commune."000AB0052");

$parcelles = $parcellaire->getParcelles()->toArray();
array_shift($parcelles);
array_shift($parcelles);
$parcelle3 = array_shift($parcelles);
$t->is($parcelle3->getKey(), $code_commune."000AB0052-01", "La clé de la parcelle 3 est bien construite");

$parcelle4 = array_shift($parcelles);
$t->is($parcelle4->getKey(), $code_commune."000AB0052-02", "La clé de la parcelle 4 est bien construite : elle a pour numéro d'ordre '26'");

$t->is($parcellaire->pieces[0]->libelle, "Parcellaire au ".$parcellaire->getDateFr(), "La déclaration a bien généré un document (une pièce)");
