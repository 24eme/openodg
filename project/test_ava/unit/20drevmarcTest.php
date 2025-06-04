<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(21);

$viti = EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$vitiCompte = $viti->getCompte();
$campagne = '2023';
$dateDrev = '2023-12-01';
$dateFacturation = '2024-01-15';

foreach(DRevMarcClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drevMarc = DRevMarcClient::getInstance()->find($k);
    $drevMarc->delete(false);
}

$t->comment("Création d'une drev marc");

$dateDebutDistillation = "2024-03-01";
$dateFinDistillation = "2024-03-31";

$drevMarc = DRevMarcClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drevMarc->debut_distillation = $dateDebutDistillation;
$drevMarc->fin_distillation = $dateFinDistillation;
$drevMarc->fin_distillation = $dateFinDistillation;
$drevMarc->qte_marc = 4575;
$drevMarc->volume_obtenu = 	2.46;
$drevMarc->titre_alcool_vol = 62.7;
$drevMarc->validate($dateDrev);
$drevMarc->validateOdg($dateDrev);
$drevMarc->save();

$t->ok($drevMarc->_rev, "La drev marc ".$drevMarc->_id." a une révision ");
$t->is($drevMarc->validation, $dateDrev, "La date de validation est renseigné");
$t->is($drevMarc->validation_odg, $dateDrev, "La date de validation ODG est renseigné");
$t->is(count($drevMarc->mouvements), 0, "Les mouvements n'ont pas été générés");

$drevMarc->generateMouvementsFactures();
$drevMarc->save();

$t->ok($drevMarc->mouvements->exist($vitiCompte->identifiant), "Le noeud mouvements existe");
$t->is(count($drevMarc->mouvements->get($vitiCompte->identifiant)), 2, "2 mouvements ont été générés");

$template = TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-AOC-".$campagne);

$templateCotisationCollection = $template->cotisations['07_marc_alsace_gewurztraminer'];
$templateCotisation = $templateCotisationCollection->details['00_marc_fixe'];


$mouvement = $drevMarc->mouvements->get($vitiCompte->identifiant)->getFirst();
$t->is($mouvement->categorie, $templateCotisationCollection->getKey(), "La categorie du mouvement est ".$templateCotisationCollection->getKey());
$t->is($mouvement->type_hash, $templateCotisation->getKey(), "Le type hash du mouvement est ".$templateCotisation->getKey());
$t->is($mouvement->quantite, 1, "Le mouvement à une quantité de 1");
$t->is($mouvement->taux, $templateCotisation->prix, "Le taux est ".$templateCotisation->prix);
$t->is($mouvement->type_libelle, $templateCotisation->libelle, "Le libelle est ".$templateCotisation->libelle);

$t->comment("Génération de la facture");

$generation = new Generation();
$generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
$generation->arguments->add('date_facturation', $dateFacturation);
$generation->arguments->add('date_mouvement', $dateFacturation);
$model = TemplateFactureClient::getInstance()->getTemplateIdFromCampagne($campagne);
$generation->arguments->add('modele', $model);
$g = FactureClient::getInstance()->createFacturesBySoc(array($viti->getCompte()->identifiant => $mouvements), $dateFacturation, null, $generation);
$g->save();
$t->ok($g->_rev, "Generation générée ".$g->_id);
$f = FactureClient::getInstance()->find($g->documents[0]);
$t->ok($f->_rev, "Facture existe ".$f->_id);

$t->ok($f->_rev, "La facture ".$f->_id." a une révision ");
$t->ok(count($f->lignes) == 1 && count($f->lignes->getFirst()->details) == 2, "La facture à une seule ligne");
$lignes = $f->lignes->get($templateCotisationCollection->getKey());
$ligne = $lignes->details->getFirst();
$t->is($lignes->libelle, $templateCotisationCollection->libelle, "Le libellé du groupe de ligne de facture est : ".$templateCotisationCollection->libelle);
$t->is($lignes->produit_identifiant_analytique, $templateCotisationCollection->code_comptable, "Le code douane du groupe de ligne de facture est : ".$templateCotisationCollection->code_comptable);
$t->is($ligne->libelle, $mouvement->type_libelle, "Le libellé de la ligne de facture est : ".$mouvement->type_libelle);
$t->is($ligne->quantite, $mouvement->quantite, "La quantité de la ligne de facture est de ".$mouvement->quantite);
$t->is($ligne->prix_unitaire, $mouvement->taux, "La prix unitaire de la ligne de facture est de ".$mouvement->taux);
$t->is($ligne->taux_tva, $templateCotisation->tva, "Le taux de tva de la ligne de facture est nul");

$t->comment("Génération d'une facture sans aucun mouvment à facturer");

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $vitiCompte, $dateFacturation);

$t->ok(!$f, "La facture n'a pas été créé");
