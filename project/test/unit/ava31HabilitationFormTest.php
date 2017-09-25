<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(10);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
  $drev = HabilitationClient::getInstance()->find($k);
  $drev->delete(false);
}

$date = date('Y-m-d');

$t->comment("Création du doc");

$habilitation = HabilitationClient::getInstance()->createDoc($viti->identifiant, $date);
$habilitation->save();

$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id est bien construit");

$produitConfig = null;
foreach($habilitation->getConfiguration()->getProduits() as $p) {
    $produitConfig = $p;
    break;
}

$t->comment("Form d'ajout de produit");

$form = new HabilitationCepageAjoutProduitForm($habilitation);

$form->bind(array('hashref' => $produitConfig->getHash(), '_revision' => $habilitation->_rev));

$t->ok($form->isValid(), "Le formulaire d'ajout est valide");
$form->save();

$t->ok(count($habilitation->getProduits()) == 1, "Le produit a été ajouté au document");
$t->ok($habilitation->exist($produitConfig->getHash()), "Le produit ajouté est correct");

$produit = $habilitation->get($produitConfig->getHash());
$activiteKey = null;
foreach(HabilitationClient::$activites_libelles as $key => $activiteLiebelle) {
    $activiteKey = key(HabilitationClient::$activites_libelles);
    break;
}
$activite = $produit->activites->get($activiteKey);

$t->is($produit->getLibelle(), $produitConfig->getLibelleComplet(), "Le libellé du produit a été enregistré dans le doc");
$t->ok(count($produit->activites) > 0, "La liste d'activité a été initialisé");

$t->comment("Form d'edition des produits");

$statutKey = null;
foreach(HabilitationClient::$statuts_libelles as $key => $statutLibelle) {
    $statutKey = $key;
    break;
}
$commentaire = "Test commentaire unitaire";

$form = new HabilitationEditionForm($habilitation);
$hashForKey = $activite->getHashForKey();
$form->bind(array('date_'.$hashForKey => date('d/m/Y'), 'statut_'.$hashForKey => $statutKey, 'commentaire_'.$hashForKey => $commentaire, '_revision' => $habilitation->_rev));

$t->ok($form->isValid(), "Le formulaire d'edition est valide");
$form->save();

$t->is($activite->date, date('Y-m-d'), "La date enregistré est ".date('Y-m-d'));
$t->is($activite->statut, $statutKey, "La statut enregistré est ".$statutKey);
$t->is($activite->commentaire, $commentaire, "La commentaire enregistré est ".$commentaire);
