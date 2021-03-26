<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(25);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$centilisations = ConditionnementConfiguration::getInstance()->getContenances();
$centilisations_bib_key = key($centilisations["bib"]);

//Suppression des Conditioinnement précédents
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$mydate = $year.'-11-01';

//Début des tests
$t->comment("Création d'un Conditionnement");

$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $mydate);
$conditionnement->save();

$t->comment($conditionnement->_id);
$t->is($conditionnement->date, $mydate, "La date est bien la date fournie ($mydate)");
$t->is($conditionnement->campagne, $campagne, "La campagne $campagne est bonne");
$t->is($conditionnement->_id, "CONDITIONNEMENT-".$viti->identifiant."-".$year.'1101', "L'identifiant est bien constituté de la date");
$t->is($conditionnement->_id, ConditionnementClient::getInstance()->findByIdentifiantAndDate($viti->identifiant, $mydate)->_id, "On retrouve bien le conditionnement à partir de l'identifiant and la date");
$t->is($conditionnement->type_archive, "Revendication", "Type d'archive Revendication");
$t->is($conditionnement->numero_archive, null, "Numéro d'archive nul");

$produits = $conditionnement->getConfigProduits();

foreach ($produits as $key => $produit) {
  break;
}
$t->comment("création du lot 1");
$lot1 = $conditionnement->addLot();
$t->is($lot1->millesime, $year, "Le millésime est intialisé à $year d'après la campagne");
$t->is($lot1->specificite, Lot::SPECIFICITE_UNDEFINED, "La spécificité est nul à la création du lot");
$t->ok($lot1->isEmpty(), "Le lot est vide sans numéro et sans produit");
$lot1->add('numero_logement_operateur', "1");
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
$lot3->numero_logement_operateur = 'C12';
$lot3->specificite =  null;
$lot3->centilisation = $centilisations_bib_key;
$conditionnement->save();

$validation = new ConditionnementValidation($conditionnement);
$t->is(count($validation->getPointsByCode(ConditionnementValidation::TYPE_WARNING, "lot_a_completer")), 1, "Point vigilance: la date de destinantion n'a pas été saisie");

$t->is($conditionnement->lots[0]->specificite, "UNDEFINED", "L'absence de spécificité crée une spécificité UNDEFINED temporaire");
$t->ok(!$conditionnement->lots[2]->specificite, "Une spécificité vide ne crée pas de valeur de spécificité");
$t->is($lot3->getCentilisation(), $centilisations_bib_key, "la centilisation est accessible via le getCentilisation");

$t->comment("création du lot 4");
$lot4 = $conditionnement->addLot();
$conditionnement->save();

$t->is(count($conditionnement->lots), 4, "4 lots avant le clean");
$conditionnement->cleanLots();
$t->is(count($conditionnement->lots), 3, "3 lots après le clean");
$conditionnement->save();

$conditionnement->validate();
$conditionnement->save();
$t->ok($conditionnement->numero_archive, "Numéro d'archive défini");

$conditionnement->validateOdg();
$conditionnement->save();

$t->comment("Historique de mouvements");
$lot = $conditionnement->lots[0];

$t->ok($lot->numero_dossier, "Numéro de dossier");
$t->ok($lot->numero_archive, "Numéro d'archive");
$t->is(count($lot->getMouvements()), 2, "2 mouvements pour le lot");
$t->ok($lot->getMouvement(Lot::STATUT_CONDITIONNE), 'Le lot est conditionné');
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTABLE), 'Le lot est affectable');
$t->is($lot->getTypeProvenance(), null, "pas de provenance");