<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$routing = clone ProjectConfiguration::getAppRouting();
$context->set('routing', $routing);

$t = new lime_test(70);

$viti =  EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$compte = $viti->getCompte();

foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(FactureClient::getInstance()->getFacturesByCompte($compte->identifiant, acCouchdbClient::HYDRATE_JSON) as $k => $v) {
    $facture = FactureClient::getInstance()->find($k);
    $facture->delete(false);
}

$campagne = (date('Y')-2)."";
$templateFacture = TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-AOC-".$campagne);
$societe = $compte;

$compte->infos->syndicats->add('COMPTE-S006234', 'SYND VITI. MITTELBERGHEIM');
$compte->infos->attributs->add('ADHERENT_SYNDICAT', 'Adhérent au syndicat');
$compte->save();

$t->comment("Création d'une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->is($drev->identifiant, $viti->identifiant, "L'identifiant est celui du viti : ".$viti->identifiant);
$t->is($drev->campagne, $campagne, "La campagne est ".$campagne);

$drev->storeDeclarant();
$drev->save();

$t->is($drev->declarant->raison_sociale, $viti->raison_sociale, "La raison sociale est celle du viti : ".$viti->raison_sociale);

$t->comment("Stockage de la DR en attachments");

$drev->storeAsAttachment("csv", "DR.csv", "text/csv");
$drev->storeAsAttachment("pdf", "DR.pdf", "application/pdf");
$t->is(file_get_contents($drev->getAttachmentUri('DR.csv')), "csv", "Le csv de la DR est récupérable");
$t->is(file_get_contents($drev->getAttachmentUri('DR.pdf')), "pdf", "Le pdf de la DR est récupérable");

$t->comment("Saisie des volumes");

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    $produit_hash1 = $produit->getHash();
    foreach($produit->getProduits() as $cepage) {
        if(!isset($produit_hash1_cepageA)) {
        $produit_hash1_cepageA = $cepage->getHash();
          continue;
        }
        $produit_hash1_cepageB = $cepage->getHash();
        break;
    }
    break;
}
foreach($produits as $produit) {
    $produit_hash2 = $produit->getHash();
}

$produit1 = $drev->addProduit($produit_hash1);
$produit2 = $drev->addProduit($produit_hash2);

$produit1CepageA = $drev->addProduitCepage($produit_hash1_cepageA);
$produit1CepageB = $drev->addProduitCepage($produit_hash1_cepageB);

$drev->save();

$drev->declaration->reorderByConf();

$drev->save();

$t->is($drev->exist($produit_hash1), true, "La produit ajouté existe");
$t->is($drev->get($produit_hash1)->getHash(), $produit_hash1, "La produit ajouté est récupérable");
$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);
$produit1CepageA = $drev->get($produit1CepageA->getHash());
$produit1CepageB = $drev->get($produit1CepageB->getHash());

$produit1->superficie_vinifiee = 100;
$produit1->superficie_revendique = 200;
$produit1->volume_revendique = 190;
$produit1CepageA->volume_revendique = 100;
$produit1CepageA->superficie_revendique = 100;
$produit1CepageA->updateTotal();
$produit1CepageB->volume_revendique = 90;
$produit1CepageB->superficie_revendique = 100;
$produit1CepageB->updateTotal();

$produit2->superficie_vinifiee = 150;
$produit2->superficie_revendique = 150;
$produit2->volume_revendique = 0;

$drev->save();

$t->is(count($drev->getProduits()), 2, "La drev a 2 produits");
$t->is($drev->declaration->getTotalTotalSuperficie(), 350, "La supeficie revendiqué totale est 350");
$t->is($drev->declaration->getTotalSuperficieVinifiee(), 2.5, "La superficie vinifié totale est 250");
$t->is($drev->declaration->getTotalVolumeRevendique(), 190, "Le volume revendiqué totale est 190");
$t->is($produit1CepageA->volume_revendique_total, 100, "Le volume revendiqué cépage est 100");
$t->is($produit1CepageB->volume_revendique_total, 90, "Le volume revendiqué cépage est 90");

$t->comment("Lots");

$drev->updatePrelevementsFromRevendication();
$drev->updateLotsFromCepage();
$drev->prelevements->cuve_ALSACE->updateTotal();

$drev->save();

$t->is(count($drev->prelevements->cuve_ALSACE->lots), 2, "2 cépages intialisés dans le les lots");
$t->is($drev->prelevements->cuve_ALSACE->total_lots, 0, "Aucun lot déclaré dans les cépages");
$t->is($drev->prelevements->cuve_ALSACE->getNbLotsMinimum(), 2, "Au moins un lot est requis");

$drev->prelevements->cuve_ALSACE->lots->getFirst()->nb_hors_vtsgn = 3;
$drev->prelevements->cuve_ALSACE->updateTotal();

$t->is($drev->prelevements->cuve_ALSACE->total_lots, 3, "3 lots déclarés dans les cépages");

$drev->save();
$drev->prelevements->cuve_ALSACE->lots->getFirst()->nb_hors_vtsgn = 1;
$drev->prelevements->cuve_ALSACE->updateTotal();
$drev->save();
$t->is($drev->prelevements->cuve_ALSACE->getNbLotsMinimum(), 2, "Au moins 2 lot est requis");

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');
$t->ok(isset($erreurs['declaration_lots_inferieur']), "Point bloquant : Le nb de lots est inferieur au nb des cepages");


$t->comment("Validation");

$drev->add('etape', 'validation');
$drev->validate();
$drev->save();

$compteIdentifiant = $societe->identifiant;

$t->is($drev->validation, date('Y-m-d'), "La DRev a la date du jour comme date de validation");
$t->is($drev->validation_odg, null, "La DRev n'est pas encore validé par l'odg");
$t->is(count($drev->mouvements), 0, "La DRev n'a pas de mouvements");
$t->is($drev->pieces[0]->libelle, "Revendication des appellations viticoles ".$drev->campagne." (Télédéclaration)", "Contrôle sur le libellé du document (pièces)");

$drev->validateOdg();
$drev->save();

$t->is($drev->validation_odg, date('Y-m-d'), "La DRev est validé par l'odg");
$t->is(count($drev->mouvements), 0, "La DRev n'a pas de mouvements");

$t->comment("Génération des mouvements");

$drev->generateMouvementsFactures();
$drev->save();

$t->is(count($drev->mouvements->get($compteIdentifiant)), 9, "La DRev a 9 mouvements");

$mouvement = $drev->mouvements->get($compteIdentifiant)->getFirst();

$t->is($mouvement->categorie, "odg_ava", "La catégorie du mouvement est odg_ava");
$t->ok($mouvement->facture === 0, "Le mouvement est non facture");
$t->ok($mouvement->facturable === 1, "Le mouvement est facturable");

foreach($drev->mouvements->get($compteIdentifiant) as $mouvement) {
    if($mouvement->categorie == "syndicat_viticole") {
        $t->ok($mouvement->taux > 0, "La cotisation du syndicat viticole est supérieur à 0");
    }
}

$t->comment("Dévalidation et Revalidation");

$drev->devalidate();
$drev->save();

$t->is($drev->validation, null, "La DRev n'a plus de date validation");
$t->is($drev->validation_odg, null, "La DRev n'a plus de date de validation par l'odg");
$t->is($drev->etape, null, "L'étape est nul");
$t->is(count($drev->mouvements), 0, "Les mouvements ont été supprimés");

$drev->generateMouvementsFactures();
$drev->save();

$t->is(count($drev->mouvements), 0, "Aucun mouvment n'a été généré car la drev n'est pas validé");

$drev->validate();
$drev->validateOdg();
$drev->generateMouvementsFactures();
$drev->save();

$t->is(count($drev->mouvements->get($compteIdentifiant)), 9, "La DRev a 9 mouvements");

$t->comment("Facturation de la DRev");

$dateFacturation = date('Y-m-d');

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $compte, $dateFacturation);
$f->save();

$t->ok($f->_rev, "Facture généré ".$f->_id);

$t->is(count($f->lignes), count($templateFacture->cotisations), "La facture a le même nombre de lignes que dans le template");

$superficieHaVinifie = 0;
$superficieAresRevendique = 0;
$volumeHlRevendique = 0;

foreach($f->lignes->get('odg_ava')->details as $ligne) {
    if(preg_match("/hectares vinifiés/", $ligne->libelle)) {
        $superficieHaVinifie += $ligne->quantite;
    }

    if(preg_match("/Tranche de [0-9]+\.*[0-9]* ares \(([0-9]+\.*[0-9]*) ares\)/", $ligne->libelle, $matches)) {
        $superficieAresRevendique += $matches[1]*1.0;
    }
};

foreach($f->lignes->get('inao')->details as $ligne) {
    if(preg_match("/hl de vin revendiqué/", $ligne->libelle)) {
        $volumeHlRevendique += $ligne->quantite;
    }
};


$t->is($f->lignes->get('odg_ava')->libelle, "Cotisation ODG-AVA", "Le libellé du groupe de ligne odg_ava est Cotisation ODG-AVA");
$t->is($f->lignes->get('odg_ava')->produit_identifiant_analytique, "706300", "Le code comptable du groupe de ligne odg_ava est 706300");
$t->ok($f->lignes->get('odg_ava')->origine_mouvements->exist($drev->_id), "Les origines du mouvements sont stockés");

$t->is($superficieHaVinifie, $drev->declaration->getTotalSuperficieVinifiee(), "La superifcie vinifiée prise en compte dans la facture est de ".$drev->declaration->getTotalSuperficieVinifiee()." ha");
$t->is($superficieAresRevendique, $drev->declaration->getTotalTotalSuperficie(), "La superifcie revendiqué prise en compte dans la facture est de ".$drev->declaration->getTotalTotalSuperficie()." ares");
$t->is($volumeHlRevendique, $drev->declaration->getTotalVolumeRevendique(), "La volume revendiqué prise en compte dans la facture est de ".$drev->declaration->getTotalVolumeRevendique()." hl");

$t->comment("Export csv de la facture");

$export = new ExportFactureCSV_ava($f, false);
$t->is(count(explode("\n", $export->exportFacture())), 10, "L'export fait 10 lignes");

$t->comment("Envoi de la facture par mail");

$message = FactureEmailManager::getInstance($instance)->compose($compte);

@mkdir(sfConfig::get('sf_test_dir')."/output");
file_put_contents(sfConfig::get('sf_test_dir')."/output/email_facture.eml", $message);

$t->ok($message, "Mail généré : ".sfConfig::get('sf_test_dir')."/output/email_facture.eml");

$t->comment("Regénération de la facture");

$newF = FactureClient::getInstance()->regenerate($f);
$newF->save();

$fData = $f->getData();
$newFData = $f->getData();
unset($fData->_rev);
unset($newFData->_rev);

$native_json = new acCouchdbJsonNative($fData);
$final_json = new acCouchdbJsonNative($newFData);

$t->ok($native_json->equal($final_json), "La facture a été regénérée, et elle est identique à l'original");

$t->comment("Génération d'une modificatrice");

$drev = DRevClient::getInstance()->find($drev->_id);

$drevM1 = $drev->generateModificative();
$drevM1->save();

$t->is(file_get_contents($drevM1->getAttachmentUri('DR.csv')), "csv", "Le csv de la DR est récupérable");
$t->is(file_get_contents($drevM1->getAttachmentUri('DR.pdf')), "pdf", "Le pdf de la DR est récupérable");

$t->is($drevM1->_id, $drev->_id."-M01", "L'id de la drev est ".$drev->_id."-M01");
$t->ok($drevM1->validation === null && $drevM1->validation_odg === null, "La drev modificatrice est dévalidée");
$t->is(count($drevM1->mouvements), 0, "Les mouvements ont été supprimés");

$drevMaster = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($viti->identifiant, $campagne);
$t->is($drevM1->_id, $drevMaster->_id, "La récupération de la drev master renvoi la drev ".$drevM1->_id);

$produit1M1 = $drevM1->get($produit1->getHash());
$produit1M1->superficie_vinifiee = 180;

$t->ok($drevM1->isModifiedMother($produit1->getHash(), 'superficie_vinifiee'), "La superficie vinifiee est marqué comme modifié par rapport à la précedente");

$drevM1->validate();
$drevM1->validateOdg();
$drevM1->generateMouvementsFactures();
$drevM1->save();

$t->is($drevM1->pieces[0]->libelle, "Revendication des appellations viticoles ".$drev->campagne." Version 1 (Télédéclaration)", "Contrôle sur le libellé du document (pièces) de la modificatrice");

$drevM1 = DRevClient::getInstance()->find($drevM1->_id);

$t->is(count($drevM1->mouvements->get($compteIdentifiant)), 2, "La DRev modificatrice a 2 mouvements");

$mouvementM1 = $drevM1->mouvements->get($compteIdentifiant)->getFirst();

$t->comment("Facturation de la modificatrice");

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $compte, $dateFacturation);
$f->save();

$superficieHaVinifie = 0;
$superficieAresRevendique = 0;
$volumeHlRevendique = 0;

foreach($f->lignes->get('odg_ava')->details as $ligne) {
    if(preg_match("/hectares vinifiés/", $ligne->libelle)) {
        $superficieHaVinifie += $ligne->quantite;
    }

    if(preg_match("/Tranche de [0-9]+\.*[0-9]* ares \(([0-9]+\.*[0-9]*) ares\)/", $ligne->libelle, $matches)) {
        $superficieAresRevendique += $matches[1]*1.0;
    }
};

$surfaceVinifieeFacturableAttendu = $drevM1->getSurfaceVinifieeFacturable() - $drev->getSurfaceVinifieeFacturable();
$surfaceFacturableAttendu = $drevM1->getSurfaceFacturable() - $drev->getSurfaceFacturable();
$volumeFacturableAttendu = $drevM1->getVolumeFacturable() - $drev->getVolumeFacturable();

$t->is($superficieHaVinifie, $surfaceVinifieeFacturableAttendu, "La superficie vinifiée prise en compte dans la facture est de ".$surfaceVinifieeFacturableAttendu." ha");
$t->is($volumeHlRevendique, $volumeFacturableAttendu, "La volume revendiqué prise en compte dans la facture est de ".$volumeFacturableAttendu." hl");

$t->comment("Génération d'une modificatrice sans modification");

$drevM2 = $drevM1->generateModificative();
$drevM2->save();
$drevM2->validate();
$drevM2->validateOdg();
$drevM2->generateMouvementsFactures();
$drevM2->save();

$t->is(count($drevM2->mouvements->get($compteIdentifiant)), 0, "La DRev modificatrice a aucun mouvement");

$t->comment("Génération de 2 modificatrices");

$drevM3 = $drevM2->generateModificative();
$drevM3->save();

$produit1M3 = $drevM3->get($produit1->getHash());
$produit1M3->volume_revendique = 120;

$drevM3->validate();
$drevM3->validateOdg();
$drevM3->save();

$drevM4 = $drevM3->generateModificative();
$drevM4->save();

$produit1M4 = $drevM4->get($produit1->getHash());
$produit1M4->superficie_revendique = 210;
$produit1M4->volume_revendique = 140;

$drevM4->validate();
$drevM4->validateOdg();
$drevM4->save();

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $compte, $dateFacturation);
$f->save();

$t->ok($f->_rev, "La facture ".$f->_id." a une révision");
$t->is(count($f->lignes->inao->details), 1, "Une seul ligne de facture pour la facturation de l'inao basé sur le volume");
$t->is($f->lignes->inao->details[0]->quantite, $produit1M4->volume_revendique - $produit1M1->volume_revendique, "La quantité est sommée");
$t->is(count($f->lignes->odg_ava->details), 1, "La cotisation odg ava à 1 ligne");
$t->is($f->lignes->odg_ava->details[0]->libelle, "Tranche de 50 ares (".$drevM4->getSurfaceFacturable()." ares) à partir du 51ème are", "Le libellé provient de la dernière modificatrice");

$t->comment("Génération d'une facture sans aucun mouvement à facturer");

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $compte, $dateFacturation);

$t->ok(!$f, "La facture n'a pas été créé");

$t->comment("DRev modificatrice entrainant un avoir");

$drevM5 = $drevM4->generateModificative();
$drevM5->save();

$produit1M5 = $drevM5->get($produit1->getHash());
$produit1M5->superficie_revendique = 60;
$produit1M5->superficie_vinifiee = 120;
$produit1M5->volume_revendique = 90;

$drevM5->validate();
$drevM5->validateOdg();
$drevM5->save();

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $compte, $dateFacturation);
$f->save();

$t->ok($f->_rev, "La facture ".$f->_id." a une révision");
$t->ok($f->isAvoir(), "La facture est un avoir");
$t->ok($f->getTaxe() < 0, "La facture a de la TVA a payé");
$t->is(count($f->lignes->inao->details), 1, "Une seul ligne de facture pour la facturation de l'inao basé sur le volume");
$t->is($f->lignes->inao->details[0]->quantite, $produit1M5->volume_revendique - $produit1M4->volume_revendique, "La quantité est sommée");

$compte->infos->syndicats->remove('COMPTE-S006234');
$compte->infos->attributs->remove('ADHERENT_SYNDICAT');

$compte->save();
