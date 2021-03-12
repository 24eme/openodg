<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(20);

$vitiCompte =  CompteClient::getInstance()->find('COMPTE-E7523700100');
$dateFacturation = date('Y-m-d');
$dateDebut = (date("Y")-1)."-01-01";
$dateFin = (date("Y")-1)."-12-31";
$periode = str_replace("-", "", $dateDebut)."-".str_replace("-", "", $dateFin);
$templateFacture = TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-ABONNEMENT-".$periode);
$templateCotisationCollection = $templateFacture->cotisations->getFirst();
$templateCotisation = $templateCotisationCollection->details->getFirst();

foreach(AbonnementClient::getInstance()->getAbonnementsByCompte($vitiCompte->identifiant, acCouchdbClient::HYDRATE_JSON) as $k => $v) {
    $abonnement = AbonnementClient::getInstance()->find($k);
    $abonnement->delete(false);
}

$t->comment("Création d'un abonnement");

$abonnement = AbonnementClient::getInstance()->findOrCreateDoc($vitiCompte->identifiant, $dateDebut, $dateFin);
$abonnement->tarif = AbonnementClient::TARIF_PLEIN;
$abonnement->generateMouvementsFactures();
$abonnement->save();

$t->ok($abonnement->_rev, "L'abonnement ".$abonnement->_id." a une révision ");
$t->is($abonnement->identifiant, $vitiCompte->identifiant, "L'abonnement à l'identifiant ".$vitiCompte->identifiant);
$t->is($abonnement->tarif, "PLEIN", "L'abonnement à un tarif plein");
$t->is($abonnement->periode, $periode, "L'abonnement à pour période ".$periode);
$t->ok($abonnement->mouvements->exist($vitiCompte->identifiant), "Les mouvements ont été générés");
$t->is(count($abonnement->mouvements->get($vitiCompte->identifiant)), 1, "Un mouvement a été généré");

$mouvement = $abonnement->mouvements->get($vitiCompte->identifiant)->getFirst();
$t->is($mouvement->categorie, $templateCotisationCollection->getKey(), "La categorie du mouvement est ".$templateCotisationCollection->getKey());
$t->is($mouvement->type_hash, $templateCotisation->getKey(), "Le type hash du mouvement est ".$templateCotisation->getKey());
$t->is($mouvement->quantite, 1, "Le mouvement à une quantité de 1");
$t->is($mouvement->taux, $templateCotisation->prix, "Le taux est ".$templateCotisation->prix);
$t->is($mouvement->type_libelle, $templateCotisation->libelle, "Le libelle est ".$templateCotisation->libelle);

$t->comment("Génération de la facture");

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $vitiCompte, $dateFacturation);
$f->save();

$t->ok($f->_rev, "La facture ".$f->_id." a une révision ");
$t->ok(count($f->lignes) == 1 && count($f->lignes->getFirst()->details) == 1, "La facture à une seule ligne");
$lignes = $f->lignes->get($templateCotisationCollection->getKey());
$ligne = $lignes->details->getFirst();
$t->is($lignes->libelle, $templateCotisationCollection->libelle, "Le libellé du groupe de ligne de facture est : ".$templateCotisationCollection->libelle);
$t->is($lignes->produit_identifiant_analytique, $templateCotisationCollection->code_comptable, "Le code douane du groupe de ligne de facture est : ".$templateCotisationCollection->code_comptable);
$t->is($ligne->libelle, $mouvement->type_libelle, "Le libellé de la ligne de facture est : ".$mouvement->type_libelle);
$t->is($ligne->quantite, $mouvement->quantite, "La quantité de la ligne de facture est de ".$mouvement->quantite);
$t->is($ligne->prix_unitaire, $mouvement->taux, "La prix unitaire de la ligne de facture est de ".$mouvement->taux);
$t->is($ligne->taux_tva, $templateCotisation->tva, "Le taux de tva de la ligne de facture est de ".$templateCotisation->tva);

$t->comment("Génération d'une facture sans aucun mouvment à facturer");

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $vitiCompte, $dateFacturation);

$t->ok(!$f, "La facture n'a pas été créé");
