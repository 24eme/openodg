<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(15);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}

$t->comment("Création des docs");
$date = '2012-01-01';
$habilitation = HabilitationClient::getInstance()->createDoc($viti->identifiant, $date);
$habilitation->save();

$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id d'un doc dans le passé est bien construit");

$date = date('Y-m-d');
$habilitation = HabilitationClient::getInstance()->createOrGetDocFromHistory($habilitation);
$habilitation->save();
$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id d'un doc actuel est bien construit");


$produitConfig_0 = null;
$produitConfig_1 = null;
foreach($habilitation->getConfiguration()->getProduitsCahierDesCharges() as $p) {
    if(!$produitConfig_0){
      $produitConfig_0 = $p;
      continue;
    }
    if(!$produitConfig_1 && ($p->getHash() != $produitConfig_0->getHash())){
      $produitConfig_1 = $p;
      break;
    }
}

$t->comment("Form d'ajout de produit");

$form = new HabilitationAjoutProduitForm($habilitation);

$form->bind(array('hashref' => $produitConfig_0->getHash(), '_revision' => $habilitation->_rev));

$t->ok($form->isValid(), "Le formulaire d'ajout est valide");
$form->save();

$t->ok(count($habilitation->getProduits()) == 1, "Le produit a été ajouté au document");
$t->ok($habilitation->exist($produitConfig_0->getHash()), "Le produit ajouté est correct");

$t->comment("Form d'ajout de produit avec activité");
$activites = array(HabilitationClient::ACTIVITE_PRODUCTEUR,HabilitationClient::ACTIVITE_VINIFICATEUR,HabilitationClient::ACTIVITE_VRAC);
$form = new HabilitationAjoutProduitForm($habilitation);
$statut = HabilitationClient::STATUT_DEMANDE;
$form->bind(array('hashref' => $produitConfig_1->getHash(), 'statut' => $statut, 'activites' => $activites, '_revision' => $habilitation->_rev));

$t->ok($form->isValid(), "Le formulaire d'ajout est valide");
$form->save();

$t->ok(count($habilitation->getProduits()) == 2, "Le produit a été ajouté au document");
$t->ok($habilitation->exist($produitConfig_1->getHash()), "Le produit ajouté est correct");

$HabilitationActivites = $habilitation->get($produitConfig_1->getHash())->activites;
$activite_tmp = array();
foreach ($HabilitationActivites as $key => $activite) {
  if($activite->statut == $statut){
    $activite_tmp[] = $key;
  }
}
$t->ok(count($activite_tmp) == 3, "Le produit a 3 activites en demande");

$produit = $habilitation->get($produitConfig_0->getHash());
$activiteKey = null;
foreach(HabilitationClient::$activites_libelles as $key => $activiteLiebelle) {
    $activiteKey = key(HabilitationClient::$activites_libelles);
    break;
}
$activite = $produit->activites->get($activiteKey);

$t->is($produit->getLibelle(), $produitConfig_0->getLibelleComplet(), "Le libellé du produit a été enregistré dans le doc");
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
