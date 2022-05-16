<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(15);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant, '9999-99-99', acCouchdbClient::HYDRATE_DOCUMENT, null, 'ALL') as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}

$t->comment("Création des docs");
$date = '2012-01-01';
$habilitation = HabilitationClient::getInstance()->createDoc($viti->identifiant, null, $date);
$habilitation->save();

$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id d'un doc dans le passé est bien construit");

$date = date('Y-m-d');
$habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($habilitation->identifiant, $date);
$habilitation->save();
$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id d'un doc actuel est bien construit");


$produitConfig_0 = null;
$produitConfig_1 = null;
foreach($habilitation->getProduitsConfig($configuration) as $p) {
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

$t->ok(!$form->isValid(), "Le formulaire d'ajout est valide");

$t->comment("Form d'ajout de produit avec activité");
$activites = array(HabilitationClient::ACTIVITE_PRODUCTEUR,HabilitationClient::ACTIVITE_VINIFICATEUR,HabilitationClient::ACTIVITE_VRAC);
$form = new HabilitationAjoutProduitForm($habilitation);
$statut = HabilitationClient::STATUT_DEMANDE_HABILITATION;
$date = "07/09/".(date('Y')+1);
$dateIso = (date('Y')+1)."-09-07";

$form->bind(array('hashref' => $produitConfig_0->getHash(), 'statut' => $statut, 'date' => $date, 'activites' => $activites, '_revision' => $habilitation->_rev));

$t->ok($form->isValid(), "Le formulaire d'ajout est valide");
$form->save();

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($habilitation->identifiant);

$t->is(count($habilitation->getProduits()), 1, "Le produit a été ajouté au document");
$t->ok($habilitation->exist($produitConfig_0->getHash()), "Le produit ajouté est correct");

$HabilitationActivites = $habilitation->get($produitConfig_0->getHash())->activites;
$activite_tmp = array();
$dates = array();
foreach ($HabilitationActivites as $key => $activite) {
  if($activite->statut == $statut){
    $activite_tmp[] = $key;
    $dates[$activite->date] = $activite->date;
  }
}
$t->ok(count($activite_tmp) == 3, "Le produit a 3 activites en demande");

$t->ok(count($dates) == 1, "Le produit a ses activites avec une seule date");
$f_date = array_pop($dates);
$t->is($f_date,$dateIso, "Le produit a ses activites avec la date $date");

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($habilitation->identifiant);

$produit = $habilitation->get($produitConfig_0->getHash());
$activiteKey = null;
foreach(HabilitationClient::getInstance()->getActivites() as $key => $activiteLiebelle) {
    $activiteKey = $key;
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

$date = "07/09/".(date('Y')+2);
$dateIso = (date('Y')+2)."-09-07";

$form = new HabilitationEditionForm($habilitation);
$hashForKey = $activite->getHashForKey();
$form->bind(array('date_'.$hashForKey => $date, 'statut_'.$hashForKey => $statutKey, 'commentaire_'.$hashForKey => $commentaire, '_revision' => $habilitation->_rev));

$t->ok($form->isValid(), "Le formulaire d'edition est valide");
$form->save();

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($habilitation->identifiant);

$produit = $habilitation->get($produitConfig_0->getHash());
$activiteKey = null;
foreach(HabilitationClient::getInstance()->getActivites() as $key => $activiteLibelle) {
    $activiteKey = $key;
    break;
}
$activite = $produit->activites->get($activiteKey);

$t->is($activite->date, $dateIso, "La date enregistré est ".$dateIso);
$t->is($activite->statut, $statutKey, "La statut enregistré est ".$statutKey);
$t->is($activite->commentaire, $commentaire, "La commentaire enregistré est ".$commentaire);
