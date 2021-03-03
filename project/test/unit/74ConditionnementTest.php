<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(7);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$centilisations = ConditionnementConfiguration::getInstance()->getContenances();
$centilisations_bib_key = key($centilisations["bib"]);

//Suppression des Conditioinnement précédents
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

$campagne = (date('Y')-1)."";
$mydate = $campagne.'-'.date('m-d');
//Début des tests
$t->comment("Création d'un Conditionnement");

$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $campagne, $mydate);

$conditionnement->storeDeclarant();
$conditionnement->save();

$t->comment($conditionnement->_id);
$t->is($conditionnement->date, $mydate, "La date est bien la date fournie ($mydate)");
$t->is($conditionnement->_id, "CONDITIONNEMENT-".$viti->identifiant."-".preg_replace('/-/', '', $mydate), "L'identifiant est bien constituté de la date");

$produits = $conditionnement->getConfigProduits();

foreach ($produits as $key => $produit) {
  break;
}
$t->comment("création du lot 1");
$lot1 = $conditionnement->addLot();
$t->is($lot1->millesime, $conditionnement->campagne, "Le millésime est intialisé avec la campagne");
$t->is($lot1->specificite, Lot::SPECIFICITE_UNDEFINED, "La spécificité est nul à la création du lot");
$t->ok($lot1->isEmpty(), "Le lot est vide sans numéro et sans produit");
$lot1->add('numero', "1");
$t->ok(!$lot1->isEmpty(), "Le lot n'est plus vide avec un numéro");
$lot1->volume = 12;

$t->comment("création du lot 2");
$lot2 = $conditionnement->addLot();
$lot2->produit_hash = $produit->getHash();
$t->ok(!$lot2->isEmpty(), "Le lot n'est plus vide avec juste un produit");
$conditionnement->save();
$validation = new ConditionnementValidation($conditionnement);
$t->is(count($validation->getPointsByCode(ConditionnementValidation::TYPE_ERROR, "lot_incomplet")), 2, "Point bloquant: Aucun produit saisi lors de l'etape Lot");

$t->comment("création du lot 3");
$lot3 = $conditionnement->addLot();
$lot3->produit_hash = $produit->getHash();
$lot3->volume = 15;
$lot3->add('numero', 'C12');
$lot3->add('specificite', "");
$lot3->add('centilisation', $centilisations_bib_key);
$conditionnement->save();

$validation = new ConditionnementValidation($conditionnement);
$t->is(count($validation->getPointsByCode(ConditionnementValidation::TYPE_WARNING, "lot_a_completer")), 1, "Point vigilance: la date de destinantion n'a pas été saisie");


$t->comment("création du lot 4");
$lot4 = $conditionnement->addLot();
$conditionnement->save();

$t->is(count($conditionnement->lots), 4, "4 lots avant le clean");
$conditionnement->cleanLots();
$t->is(count($conditionnement->lots), 3, "3 lots après le clean");
