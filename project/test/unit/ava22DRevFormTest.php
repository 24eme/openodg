<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(5);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
  $drev = DRevClient::getInstance()->find($k);
  $drev->delete(false);
}

$campagne = (date('Y')-1)."";

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    $produit_hash1 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    $produit_hash2 = $produit->getHash();
}
$produit1 = $drev->addProduit($produit_hash1);
$produit1->vci = 10;
$produit2 = $drev->addProduit($produit_hash2);
$drev->save();

$t->comment("Formulaire du VCI");

$form = new DRevVciForm($drev);

$defaults = $form->getDefaults();

$t->ok(count($form['produits']), "La form a 2 produits");
$t->is($form['produits'][$produit_hash1]['vci']->getValue(), 10, "Le VCI du formulaire est de 10");
$t->is($form['produits'][$produit_hash1]['vci_destruction']->getValue(), null, "Le VCI desctruction est nul");
$t->is($form['produits'][$produit_hash1]['vci_complement_dr']->getValue(), null, "Le VCI en complément de la DR est nul");


$values = array(
    'produits' => array(
        $produit_hash1 => array("vci" => 12, "vci_destruction" => 1, "vci_complement_dr" => "2"),
        $produit_hash2 => array(),
    ),
    '_revision' => $drev->_rev,
);

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");
