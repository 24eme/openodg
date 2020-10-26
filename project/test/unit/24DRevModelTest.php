<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(40);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$campagne = (date('Y'))."";

//Début des tests
$t->comment("Création d'une DRev (DREV-".$viti->identifiant."-".$campagne.")");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->is($drev->identifiant, $viti->identifiant, "L'identifiant est celui du viti : ".$viti->identifiant);
$t->is($drev->campagne, $campagne, "La campagne est ".$campagne);

$drev->storeDeclarant();
$drev->save();

$t->is($drev->declarant->raison_sociale, $viti->raison_sociale, "La raison sociale est celle du viti : ".$viti->raison_sociale);

$t->comment("Saisie des volumes");

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    if($produit->getRendement() > 0) {
        continue;
    }
    $produit_hash1 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    if($produit->getRendement() > 0) {
        continue;
    }
    $produit_hash2 = $produit->getHash();
}
foreach($produits as $produit) {
    if($produit->getRendement() <= 0 || !$produit->hasMutageAlcoolique()) {
        continue;
    }
    $produit_hash_mutage = $produit->getHash();
}

$produit1 = $drev->addProduit($produit_hash1);
$produit2 = $drev->addProduit($produit_hash2);
$produit3 = $drev->addProduit($produit_hash2, "BIO");

$produit_hash1 = $produit1->getHash();
$produit_hash2 = $produit2->getHash();
$produit_hash3 = $produit3->getHash();

if(isset($produit_hash_mutage)) {
    $produit_mutage = $drev->addProduit($produit_hash_mutage);
    $produit_hash_mutage = $produit_mutage->getHash();
}

$drev->save();

$t->is($drev->exist($produit_hash1), true, "La produit ajouté existe");
$t->is($drev->get($produit_hash1)->getHash(), $produit_hash1, "La produit ajouté est récupérable");

$t->is($drev->get($produit_hash2)->getKey(), "DEFAUT", "La clé du produit est DEFAUT");
$t->is($drev->get($produit_hash2)->denomination_complementaire, null, "La dénomination complémentaire est null");
$t->is($drev->get($produit_hash2)->libelle, $drev->get($produit_hash2)->getConfig()->getLibelleComplet(), "Le libellé est enregistré");

$t->ok($drev->get($produit_hash3)->getKey() != "DEFAUT", "La clé du produit bio n'est pas DEFAUT");
$t->is($drev->get($produit_hash3)->denomination_complementaire, "BIO", "La dénomination complémentaire est BIO");
$t->is($drev->get($produit_hash3)->libelle, $drev->get($produit_hash3)->getConfig()->getLibelleComplet()." BIO", "La dénomination complémentaire est dans le libellé");

if(isset($produit_hash_mutage)) {
    $t->is($drev->get($produit_hash_mutage)->libelle, $drev->get($produit_hash_mutage)->getConfig()->getLibelleComplet(), "Libellé du produit en mutage ".$drev->get($produit_hash_mutage)->libelle);
    $t->ok($drev->hasProduitWithMutageAlcoolique(), "Détection de produit en mutage alcoolique");
} else {
    $t->pass("Test non nécessaire car pas de mutage");
    $t->ok(!$drev->hasProduitWithMutageAlcoolique(), "Aucun produit en mutage alcoolique");
}

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);
$produit3 = $drev->get($produit_hash3);

$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$produit1->vci->rafraichi = 10;

$produit2->superficie_revendique = 150;
$produit2->recolte->superficie_total = 150;
$produit2->volume_revendique_issu_recolte = 110;

$totalSuperficie = 350;
$totalVolume = 200;
$nbProduits = 3;

if(isset($produit_hash_mutage)) {
    $produit_mutage->superficie_revendique = 100;
    $produit_mutage->recolte->superficie_total = 100;
    $produit_mutage->volume_revendique_issu_recolte = 65;
    $produit_mutage->volume_revendique_issu_mutage = 1;
    $totalSuperficie += 100;
    $totalVolume += 66;
    $nbProduits += 1;
}

$drev->save();

if(isset($produit_hash_mutage)) {
    $t->is($drev->get($produit_hash_mutage)->volume_revendique_total, 66, "Le volume en mutage est pris en compte dans le volume total");
} else {
    $t->pass("Test non nécessaire car pas de mutage");
}
$t->is(count($drev->getProduits()), $nbProduits, "La drev a ".$nbProduits." produits");
$t->is($drev->declaration->getTotalTotalSuperficie(), $totalSuperficie, "La supeficie revendiqué totale est ".$totalSuperficie);
$t->is($drev->declaration->getTotalVolumeRevendique(), $totalVolume, "Le volume revendiqué totale est ".$totalVolume);

$t->comment("Validation");

$drev->validate();
$drev->validateOdg();
$drev->save();

$t->is($drev->declaration->getTotalTotalSuperficie(), $totalSuperficie, "La supeficie revendiqué totale est toujours de 350");
$t->is($drev->declaration->getTotalVolumeRevendique(), $totalVolume, "Le volume revendiqué totale est toujours de 200");

$t->is($drev->validation, date('Y-m-d'), "La DRev a la date du jour comme date de validation");
if(DRevConfiguration::getInstance()->hasValidationOdgAuto()) {
    $t->is($drev->validation_odg, null, "La DRev a la date du jour comme date de validation odg");
} else {
    $t->is($drev->validation_odg, date('Y-m-d'), "La date de validation ODG n'est pas mise automatiquement");
}

if(FactureConfiguration::getInstance()->isActive()) {
    $t->is(count($drev->mouvements->toArray(0,1)), 0, "La DRev n'a pas encore de mouvements");
} else {
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
}

$drev->devalidate();
$t->is($drev->validation, NULL, "La DRev n'est plus validée");

$drev->validate();
$drev->save();


if(FactureConfiguration::getInstance()->isActive()) {
    $t->isnt($drev->getTemplateFacture(), null, "getTemplateFacture de la DRev doit retourner un template de facture pour la campagne ".$drev->campagne." (pour pouvoir avoir des mouvements)");
    $mouvements = $drev->mouvements->get($viti->identifiant);
    $t->is(count($mouvements), 4, "La DRev a 4 mouvements");
    $mouvement = $mouvements->getFirst();
    $t->ok($mouvement->facture === 0 && $mouvement->facturable === 1, "Le mouvement est non facturé et facturable");
    $t->ok($mouvement->date === $campagne."-12-10" && $mouvement->date_version === $drev->validation, "Les dates du mouvement sont égale à la date de validation de la DRev");
} else {
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
}

$t->comment("Génération d'une modificatrice");

$drevM1 = $drev->generateModificative();
$drevM1->save();

$t->is($drevM1->_id, $drev->_id."-M01", "L'id de la drev est ".$drev->_id."-M01");
$t->ok($drevM1->validation === null && $drevM1->validation_odg === null, "La drev modificatrice est dévalidée");

$drevMaster = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($viti->identifiant, $campagne);
$t->is($drevM1->_id, $drevMaster->_id, "La récupération de la drev master renvoi la drev ".$drevM1->_id);

$produit1M1 = $drevM1->get($produit1->getHash());
$produit1M1->superficie_revendique = 120;
$produit1M1->recolte->superficie_total = 120;
$produit1M1->volume_revendique_issu_recolte = 90;
$produit1M1->volume_revendique_total = 90;
$produit1M1->vci->rafraichi = 20;

$t->ok($drevM1->isModifiedMother($produit1->getHash(), 'superficie_revendique'), "La superficie vinifiee est marquée comme modifié par rapport à la précedente");

$t->comment("Validation de la modificatrice");

$drevM1->save();
$drevM1->validate();
$drevM1->save();

if(FactureConfiguration::getInstance()->isActive()) {
    $t->is(count($drevM1->mouvements->get($viti->identifiant)), 4, "La DRev modificatrice a 4 mouvements");
} else {
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
}

$t->comment("Test de la réserve interpro");

$rdm_orig = $produit2->getConfig()->getRendementCepage();
$rdm_ri = $produit2->getConfig()->getRendementReserveInterpro();
$produit2->getConfig()->add('attributs')->add('rendement', 60);
$produit2->getConfig()->add('attributs')->add('rendement_reserve_interpro', 50);
$produit2->getConfig()->clearStorage();

$drevM2 = $drevM1->generateModificative();
$drevM2->save();
$produit2M2 = $drevM2->get($produit2->getHash());
$produit2M2->volume_revendique_total = $produit2M2->superficie_revendique * 50 + 5;
$produit2M2->volume_revendique_issu_recolte = $produit2M2->volume_revendique_total;

$drevM2->save();
$drevM2->validate();
$drevM2->save();

$t->is($produit2M2->getConfig()->getRendementReserveInterpro(), 50, "le rendement interpro est bien celui attendu en configuration 2");
$t->ok($drevM2->get($produit2->getHash())->exist('dont_volume_revendique_reserve_interpro'), "Le volume dédié à la réserve interpro est bien présent");
$t->is($drevM2->get($produit2->getHash())->get('dont_volume_revendique_reserve_interpro'), 5, "Le volume dédié à la réserve interpro est le bon");
$t->ok(!$drevM2->get($produit1->getHash())->exist('dont_volume_revendique_reserve_interpro'), "Le volume dédié à la réserve interpro n'est pas présent pour le 1er produit");
$t->ok($drevM2->hasProduitsReserveInterpro(), "La Drev indique bien qu'il existe des produits en reserve interpro");
$t->ok(!$drevM1->hasProduitsReserveInterpro(), "La Drev précédente n'a pas de produit en réserve interpro");

$produits = $drevM2->getProduitsWithReserveInterpro();
$t->is(count($produits), 1, "Il existe bien un produit avec des la réserve interpro");
$t->is($produits[0]->getHash(), $produit2M2->getHash(), "Le produit avec des la réserve interpro est le bon");

$produit2->getConfig()->add('attributs')->add('rendement', $rdm_orig);
$produit2->getConfig()->add('attributs')->add('rendement_reserve_interpro', $rdm_ri);
$produit2->getConfig()->clearStorage();
