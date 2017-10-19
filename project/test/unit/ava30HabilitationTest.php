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

$t->comment("Création d'une doc dans le passé");
$date = '2000-10-01';
$habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($viti->identifiant, $date);
$habilitation->save();

$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id est bien construit ".$habilitation->_id );
$t->is(count($habilitation->declaration), 0, "l'habilitation est vierge de produit");
$t->is(count($habilitation->historique), 0, "l'habilitation est vierge d'historique");


$produitConfig = null;
foreach($habilitation->getConfiguration()->getProduitsCahierDesCharges() as $p) {
    $produitConfig = $p;
    break;
}

$t->comment("Ajout de produit");

$habProduit = $habilitation->addProduit($produitConfig->getHash());
$habilitation->save();

$t->is(count($habilitation->declaration), 1, "l'ajout de produit créer bien un produit dans le noeud déclaration");
$produit = $habilitation->get($produitConfig->getHash());
$t->ok($produit, "le produit ajouté a bien la hash choisie");
$t->is($produit->getLibelle(), $produitConfig->getLibelleComplet(), "Le libellé du produit a été enregistré dans le doc");
$t->is(count($produit->activites), 5, "La liste d'activité a été initialisé à 5");
$t->is(count($habilitation->historique), 1, "l'ajout du produit a créé un historique");

$activiteKey = null;
foreach(HabilitationClient::$activites_libelles as $key => $activiteLiebelle) {
  $activiteKey = key(HabilitationClient::$activites_libelles);
  break;
}
$activite = $produit->activites->get($activiteKey);

$t->comment("Ajout d'activité");

$habProduit->activites[$activiteKey]->updateHabilitation(HabilitationClient::STATUT_DEMANDE, "dossier reçu");
$habilitation->save();
$t->is($habProduit->activites[$activiteKey]->date, $date, "l'activité a été changée");
$t->is(count($habilitation->historique), 2, "la modification de l'activité a été enregistrée dans l'historique");

$t->comment("Changement d'activité");

$habProduit->activites[$activiteKey]->updateHabilitation(HabilitationClient::STATUT_HABILITE , "INAO OK");
$habilitation->save();
$t->is($habProduit->activites[$activiteKey]->statut, HabilitationClient::STATUT_HABILITE, "le statut de l'activité a été changée");
$t->is(count($habilitation->historique), 3, "la modification de l'activité a été enregistrée dans l'historique");

$t->comment('Habilitation à une autre date');
$date = '2010-10-01';
$habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($viti->identifiant, $date);
$habilitation->save();
$t->is($habilitation->_id, 'HABILITATION-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id est bien construit ".$habilitation->_id );
$t->is(count($habilitation->declaration), 1, "l'habilitation n'est pas vierge de produit");
$t->is(count($habilitation->historique), 0, "l'habilitation est vierge d'historique");

$habProduit = $habilitation->get($produitConfig->getHash());
$habProduit->activites[$activiteKey]->updateHabilitation(HabilitationClient::STATUT_SUSPENDU , "ne respecte pas le cahier des charges");
$habilitation->save();
$t->is($habProduit->activites[$activiteKey]->date, $date, "la date de l'activité été changée");
$t->is($habProduit->activites[$activiteKey]->statut, HabilitationClient::STATUT_SUSPENDU, "le statut de l'activité été changée");
$t->is(count($habilitation->historique), 1, "la modification de l'activité a été enregistrée dans l'historique");
$full = $habilitation->getFullHistorique();
$t->is(count($full), 4, "l'historique complet contient toutes les modifications");
$t->is($full[count($full)-1]->date, $habilitation->historique[0]->date, "l'historique complet est dans le bon ordre");
$full = $habilitation->getFullHistoriqueReverse();
$t->is($full[0]->date, $habilitation->historique[0]->date, "l'historique complet invsersé est dans le bon ordre");
