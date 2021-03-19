<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(16);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$periode = (date('Y')-3)."";
$campagne = $periode."-".($periode + 1);

//Début des tests
$t->comment("Création d'une DRev n-1");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->is($drev->identifiant, $viti->identifiant, "L'identifiant est celui du viti : ".$viti->identifiant);
$t->is($drev->campagne, $campagne, "La campagne est ".$campagne);
$t->is($drev->periode, $periode, "La periode est ".$periode);
$drev->storeDeclarant();
$drev->save();

$t->comment("Saisie des volumes VCI en n -1 : ".$drev->_id);

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    $produit_hash1 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    $produit_hash2 = $produit->getHash();
}

$madenomination = "Denomination de test";
$produit1 = $drev->addProduit($produit_hash1, $madenomination);

$produit2 = $drev->addProduit($produit_hash2);

$produit_hash1 = $produit1->getHash();
$produit_hash2 = $produit2->getHash();
$drev->save();

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);

$produit1->superficie_revendique = 200;
$produit1->volume_revendique_issu_recolte = 80;
$produit1->vci->constitue = 20;

$produit2->superficie_revendique = 150;
$produit2->volume_revendique_issu_recolte = 110;

$drev->save();

$t->is(count($drev->getProduits()), 2, "La drev a 2 produits");
$t->is($drev->declaration->getTotalTotalSuperficie(), 350, "La supeficie revendiqué totale est 350");
$t->is($drev->declaration->getTotalVolumeRevendique(), 190, "Le volume revendiqué totale est 190");
$t->is($drev->get($produit_hash1)->hasVCI(), true, "le produit VCI est repéré comme tel");
$t->is($drev->get($produit_hash2)->hasVCI(), false, "le produit sans VCI est repéré comme tel");
$t->is(count($drev->declaration->getProduitsVci()), 1, "A donc un seul produit VCI");
$t->is($drev->get($produit_hash1)->vci->stock_final, 20, "stock final du produit VCI correct");
$t->is($drev->get($produit_hash2)->vci->stock_final, 0, "stock final du produit sans VCI null");

$periode_n = $periode + 1;
$drev_n = DRevClient::getInstance()->createDoc($viti->identifiant, $periode_n);
$drev_n->save();
$t->comment("Récupération du stock VCI en n : ".$drev_n->_id);
$t->is(count($drev_n->getProduitsVci()), 1, "même nombre de produit VCI");
$t->is(count($drev_n->getProduits()), 1, "même nombre de produit que de VCI");
$t->is($drev_n->get($produit_hash1)->vci->stock_precedent, 20, "reprise du stock du produit VCI correct");
$t->is($drev_n->get($produit_hash1)->denomination_complementaire, $madenomination, "la dénomination est conservée");
$t->is($drev_n->exist($produit2->getParent()->getHash()), false, "reprise du stock du produit sans VCI null");
