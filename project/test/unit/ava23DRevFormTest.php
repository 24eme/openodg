<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(33);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
  $drev = DRevClient::getInstance()->find($k);
  $drev->delete(false);
}

$campagne = (date('Y')-1)."";

$t->comment("Création de la DR");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération de la DR");
$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane.csv');
$csvContent = $csv->convert();
file_put_contents("/tmp/dr.csv", $csvContent);
$csv = new DRCsvFile("/tmp/dr.csv");
$drev->importCSVDouane($csv->getCsvAcheteur("7523700100"));
$drev->save();

$produit1 = current($drev->getProduits());
$produit_hash1 = $produit1->getHash();
$produit1->vci_stock_initial = 3;
$drev->save();

$t->is(count($drev->getProduits()), 1, "La DRev à repris 1 produit du csv de la DR");
$t->is($produit1->detail->superficie_total, 333.87, "La superficie total de la DR pour le produit est de 333.87");
$t->is($produit1->detail->volume_total, 169.25, "Le volume total de la DR pour ce produit est de 169.25");
$t->is($produit1->detail->vci, 2, "Le vci de la DR pour ce produit est de 2");
$t->is($produit1->detail->volume_sur_place, 108.94, "Le volume sur place pour ce produit est de 108.94");
$t->is($produit1->detail->usages_industriels_total, 4.32, "Les usages industriels la DR pour ce produit sont de 4.32");

$t->comment("Formulaire de revendication");

$form = new DRevRevendicationForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), 1, "La form a 1 produit");
$t->is($form['produits'][$produit_hash1]['detail']['superficie_total']->getValue(), 333.87, "La superficie totale de la DR est 337.87");
$t->is($form['produits'][$produit_hash1]['detail']['volume_total']->getValue(), 169.25, "La volume totale de la DR est 169.25");
$t->is($form['produits'][$produit_hash1]['detail']['volume_sur_place']->getValue(), 108.94, "Le volume sur place pour ce produit est de 108.94");
$t->is($form['produits'][$produit_hash1]['detail']['vci']->getValue(), 2, "Le vci la DR sont de 4.32");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), null, "La superficie revendique est null");
$t->is($form['produits'][$produit_hash1]['volume_revendique_sans_vci']->getValue(), null, "Le volume revendique avec vci est null");
$t->is($form['produits'][$produit_hash1]['volume_revendique_avec_vci']->getValue(), null, "Le volume revendique sans vci est null");
$t->is($form['produits'][$produit_hash1]['vci_stock_initial']->getValue(), 3, "Le stock initial de la DR pour ce produit est de 3");

$values = array(
    'produits' => array(
        $produit_hash1 => array("superficie_revendique" => 200, "volume_revendique_sans_vci" => 100, "volume_revendique_avec_vci" => 100, 'vci_stock_initial' => 3, 'detail' => array('superficie_total' => 400)),
    ),
    '_revision' => $drev->_rev,
);

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$t->is($produit1->superficie_revendique, 200, "La superficie revendique est de 200");
$t->is($produit1->vci_stock_initial, 3, "Le stock initial de la DR pour ce produit est de 3");
$t->is($produit1->detail->superficie_total, 400, "La superficie total de la DR est de 400");

$t->comment("Formulaire du VCI");

$form = new DRevVciForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), 1, "La form a 1 produit");
$t->is($form['produits'][$produit_hash1]['vci_stock_initial']->getValue(), 3, "Le stock VCI avant récolte du formulaire est de 3");
$t->is($form['produits'][$produit_hash1]['vci']->getValue(), 0, "Le VCI du formulaire est de 0");
$t->is($form['produits'][$produit_hash1]['vci_destruction']->getValue(), null, "Le VCI desctruction est nul");
$t->is($form['produits'][$produit_hash1]['vci_complement_dr']->getValue(), null, "Le VCI en complément de la DR est nul");
$t->is($form['produits'][$produit_hash1]['vci_substitution']->getValue(), null, "Le VCI en substitution est nul");
$t->is($form['produits'][$produit_hash1]['vci_rafraichi']->getValue(), null, "Le VCI rafraichi est nul");
$t->is($form['produits'][$produit_hash1]['vci_stock_final']->getValue(), null, "Le VCI stock après récolte est nul");

$values = array(
    'produits' => array(
        $produit_hash1 => array("vci_stock_initial" => 3, "vci" => 12, "vci_destruction" => 1, "vci_complement_dr" => "2", "vci_substitution" => 0, "vci_rafraichi" => null, "vci_stock_final" => 10)
    ),
    '_revision' => $drev->_rev,
);

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$produit1 = $drev->get($produit_hash1);
$t->is($produit1->vci_stock_initial, 3, "Le stock VCI avant récolte du produit du doc est de 3");
$t->is($produit1->vci, 12, "Le VCI du produit du doc est de 12");
$t->is($produit1->vci_destruction, 1, "Le VCI en destruction du produit du doc est de 1");
$t->is($produit1->vci_complement_dr, 2, "Le VCI en complément de la DR du produit du doc est de 2");
$t->is($produit1->vci_substitution, 0, "Le VCI en substitution de la DR du produit du doc est de 0");
$t->is($produit1->vci_rafraichi, null, "Le VCI rafraichi du produit du doc est nul");
$t->is($produit1->vci_stock_final, 10, "Le VCI stock après récolte du produit du doc est 10");
