<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(38);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
  $drev = DRevClient::getInstance()->find($k);
  $drev->delete(false);
}

$campagne = (date('Y')-1)."";


$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération des données à partir de la DR");

$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane.csv');
$csvContent = $csv->convert();
file_put_contents("/tmp/dr.csv", $csvContent);
$csv = new DRCsvFile("/tmp/dr.csv");
$drev->importCSVDouane($csv->getCsvAcheteur("7523700100"));
$drev->save();

$t->is(count($drev->getProduits()), 2, "La DRev a repris 2 produits du csv de la DR");

$produit1 = current($drev->getProduits());
$produit_hash1 = $produit1->getHash();
$produit1->vci_stock_initial = 3;
$drev->save();

$t->is($produit1->getLibelleComplet(), "Saint Joseph Rouge", "Le libelle du produit est Saint Joseph");
$t->is($produit1->detail->superficie_total, 247.86, "La superficie total de la DR pour le produit est de 333.87");
$t->is($produit1->detail->volume_total, 105.18, "Le volume total de la DR pour ce produit est de 169.25");
$t->is($produit1->detail->vci, 2, "Le vci de la DR pour ce produit est de 2");
$t->is($produit1->detail->volume_sur_place, 105.18, "Le volume sur place pour ce produit est de 108.94");
$t->is($produit1->detail->usages_industriels_total, 3.03, "Les usages industriels la DR pour ce produit sont de 4.32");

$t->comment("Formulaire de revendication");

$form = new DRevRevendicationForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), 2, "La form a 2 produits");
$t->is($form['produits'][$produit_hash1]['detail']['superficie_total']->getValue(), $produit1->detail->superficie_total, "La superficie totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['detail']['volume_total']->getValue(), $produit1->detail->volume_total, "La volume totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['detail']['volume_sur_place']->getValue(), $produit1->detail->volume_sur_place, "Le volume sur place est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['detail']['usages_industriels_total']->getValue(), $produit1->detail->usages_industriels_total, "Les usages industriels la DR sont initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), $produit1->superficie_revendique, "La superficie revendique est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['volume_revendique_sans_vci']->getValue(), $produit1->volume_revendique_sans_vci, "Le volume revendique avec vci est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['volume_revendique_avec_vci']->getValue(), $produit1->volume_revendique_avec_vci, "Le volume revendique sans vci est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['vci_stock_initial']->getValue(), 3, "Le stock initial VCI de la DR est initialisé dans le form");

$values = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$values['produits'][$produit_hash1]['superficie_revendique'] = 200;
$values['produits'][$produit_hash1]['volume_revendique_sans_vci'] = 100;
$values['produits'][$produit_hash1]['volume_revendique_avec_vci'] = 100;
$values['produits'][$produit_hash1]['detail']['superficie_total'] = 257.86;

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$t->is($produit1->superficie_revendique, $values['produits'][$produit_hash1]['superficie_revendique'], "La superficie revendique est enregistré");
$t->is($produit1->vci_stock_initial, $values['produits'][$produit_hash1]['vci_stock_initial'], "Le stock initial de la DR est enregistré");
$t->is($produit1->detail->superficie_total, $values['produits'][$produit_hash1]['detail']['superficie_total'], "La superficie total de la DR est enregistré");
$t->is($produit1->detail->volume_total, $values['produits'][$produit_hash1]['detail']['volume_total'], "Le volume total de la DR est enregistré");
$t->is($produit1->detail->usages_industriels_total, $values['produits'][$produit_hash1]['detail']['usages_industriels_total'], "Les usages industriels de la DR ont été enregistrés");

$t->comment("Formulaire du VCI");

$form = new DRevVciForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), 1, "La form a 1 seul produit");
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
