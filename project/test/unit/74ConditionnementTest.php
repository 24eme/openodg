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
//Début des tests
$t->comment("Création d'un Conditionnement");

$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $campagne);

$conditionnement->storeDeclarant();
$conditionnement->save();

$produits = $conditionnement->getConfigProduits();

foreach ($produits as $key => $produit) {
  break;
}
$lot = $conditionnement->addLot();

$t->is($lot->millesime, $conditionnement->campagne, "Le millésime est intialisé");
$t->is($lot->specificite, Lot::SPECIFITE_UNDEFINED, "La spécificité est nul à la création du lot");

$t->ok($lot->isEmpty(), "Le lot est vide");
$lot->add('numero', "1");
$t->ok(!$lot->isEmpty(), "Le lot n'est plus vide");

$lot->volume = 12;

$lot = $conditionnement->addLot();
$lot->produit_hash = $produit->getHash();
$conditionnement->save();

$validation = new ConditionnementValidation($conditionnement);

$t->is(count($validation->getPointsByCode(ConditionnementValidation::TYPE_ERROR, "lot_incomplet")), 2, "Point bloquant:Aucun produit saisi lors de l'etape Lot");
$lot = $conditionnement->addLot();
$lot->produit_hash = $produit->getHash();
$lot->volume = 15;
$lot->add('numero', 'C12');
$lot->add('specificite', "");
$lot->add('centilisation', $centilisations_bib_key);

$validation = new ConditionnementValidation($conditionnement);
$t->is(count($validation->getPointsByCode(ConditionnementValidation::TYPE_WARNING, "lot_a_completer")), 1, "Point vigilance: la date de destinantion n'a pas été saisie");


$lot = $conditionnement->addLot();

$t->is(count($conditionnement->lots), 4, "4 lots avant le clean");
$conditionnement->cleanLots();
$t->is(count($conditionnement->lots), 3, "3 lots après le clean");

$conditionnement->validate();
