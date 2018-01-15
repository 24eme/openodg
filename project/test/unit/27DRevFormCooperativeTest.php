<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(13);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}

$campagne = (date('Y')-1)."";

$dr = SV11Client::getInstance()->createDoc($viti->identifiant, $campagne);
$dr->setLibelle("SV11 $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier(dirname(__FILE__).'/../data/sv11_douane.csv');
$dr->save();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->is($drev->getDocumentDouanierType(), "SV11", "Le document douanier de la DRev est de type SV11");

$drev->importFromDocumentDouanier();
$drev->save();

$t->is(count($drev->getProduits()), 2, "La DRev a repris 2 produits du csv de la SV11");

$produits = $drev->getProduits();

$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();
next($produits);
$produit2 = current($produits);
$produit_hash2 = $produit2->getHash();

$t->is($produit1->getLibelleComplet(), "Côtes du Rhône Rouge", "Le libelle du produit est Condrieu Blanc");
$t->is($produit1->recolte->superficie_total, 32.2855, "La superficie de récolte totale est récupéré sur csv");
$t->is($produit1->recolte->volume_total, 1536.16, "Le volume total est récupéré du csv");
$t->is($produit1->recolte->recolte_nette, 1536.16, "Le volume de récolte net est récupéré du csv");
$t->is($produit1->recolte->volume_sur_place, 1536.16, "Le volume sur place est récupéré du csv");
$t->is($produit1->superficie_revendique, $produit1->recolte->superficie_total, "La superificie totale est mise automatiquement");

$t->is($produit2->getLibelleComplet(), "Côtes du Rhône Rosé", "Le libelle du produit est Condrieu Blanc");
$t->is($produit2->recolte->superficie_total, 4.078, "La superficie de récolte totale est récupéré sur csv");
$t->is($produit2->recolte->volume_total, 194.31, "Le volume total est récupéré du csv");
$t->is($produit2->recolte->recolte_nette, 194.31, "Le volume de récolte net est récupéré du csv");
$t->is($produit2->superficie_revendique, $produit2->recolte->superficie_total, "La superificie totale est mise automatiquement");
