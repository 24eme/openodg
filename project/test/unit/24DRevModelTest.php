<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(55);

$igp13 = ($application == 'igp13');

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$periode = date('Y');
if (date('m') < 8) {
    $periode = $periode - 1;
}
$campagne = $periode.'-'.($periode + 1);

//Début des tests
$t->comment("Création d'une DRev (DREV-".$viti->identifiant."-".$periode.")");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();

$t->is($drev->identifiant, $viti->identifiant, "L'identifiant est celui du viti : ".$viti->identifiant);
$t->is($drev->periode, $periode, "La période est ".$periode);
$t->is($drev->campagne, $campagne, "La campagne est ".$campagne);
$t->is($drev->type_archive, "Revendication", "Le type d'archive est Revendication");
$t->is($drev->numero_archive, null, "Le numéro d'archive est vide");
$t->is($drev->_id,  'DREV-'.$viti->identifiant.'-'.$periode, "L'id est DREV-".$viti->identifiant.'-'.$periode);

$drev->storeDeclarant();
$drev->save();

$t->is($drev->declarant->raison_sociale, $viti->raison_sociale, "La raison sociale est celle du viti : ".$viti->raison_sociale);

$t->comment("Saisie des volumes");

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    if($produit->getRendement() > 0) {
        if($igp13 && preg_match("/(APL|MED)/",$produit->getHash())){
          continue;
        }
        $produit_hash1 = $produit->getHash();
        break;
    }
}
foreach($produits as $produit) {
    if($produit->getRendement() > 0 && $produit_hash1!=$produit->getHash()) {
      if($igp13 && preg_match("/(APL|MED)/",$produit->getHash())){
        continue;
      }
      $produit_hash2 = $produit->getHash();
      break;
    }
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

if (DRevConfiguration::getInstance()->isRevendicationParLots()) {
    $drev->addLot();
    $drev->lots[0]->numero_logement_operateur = '1';
    $drev->lots[0]->produit_hash = $produit->getHash();
    $drev->lots[0]->volume = 100;
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

$date = date('c');
$drev->validate($date);
if (DRevConfiguration::getInstance()->hasValidationOdgRegion()) {
    foreach(DrevConfiguration::getInstance()->getOdgRegions() as $region) {
        $drev->validateOdg($date, $region);
    }
}else {
    $drev->validateOdg($date);
}
$drev->save();

$t->is($drev->declaration->getTotalTotalSuperficie(), $totalSuperficie, "La supeficie revendiqué totale est toujours de 350");
$t->is($drev->declaration->getTotalVolumeRevendique(), $totalVolume, "Le volume revendiqué totale est toujours de 200");

$t->ok($drev->numero_archive, "Le numéro d'archive a été défini");
$t->is($drev->validation, $date , "La DRev a la date du jour comme date de validation");
$t->is($drev->validation_odg, $date, "La DRev a la date du jour comme date de validation odg");

if(FactureConfiguration::getInstance()->isActive()) {
    $t->is(count($drev->mouvements->toArray(0,1)), 0, "La DRev n'a pas encore de mouvements");
} else {
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
}

$drev->devalidate();
$t->is($drev->validation, NULL, "La DRev n'est plus validée");

$drev->validate();
$drev->save();


$nbMvtsAttendu = 0;
if(FactureConfiguration::getInstance()->isActive()) {
    $template = $drev->getTemplateFacture();
    $t->isnt($drev->getTemplateFacture(), null, "getTemplateFacture de la DRev doit retourner un template de facture pour la campagne ".$drev->campagne." (pour pouvoir avoir des mouvements)");
    $drev->generateMouvementsFactures();
    $mouvements = $drev->mouvements->add($viti->identifiant);
    foreach ($template->cotisations as $type => $cot) {
        foreach($cot->details as $h => $d) {
            if (in_array('DRev', $d->docs->toArray())) {
                $nbMvtsAttendu++;
                break;
            }
        }
    }
}
if ($nbMvtsAttendu) {
    $t->ok(count($mouvements) > 0, "La DRev a des mouvements");
    $mouvement = $mouvements->getFirst();
    $t->ok($mouvement->facture === 0 && $mouvement->facturable === 1, "Le mouvement est non facturé et facturable");
    $t->ok($mouvement->date === $periode."-12-10" && $mouvement->date_version === $drev->validation, "Les dates du mouvement sont égale à la date de validation de la DRev");
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

if (DRevConfiguration::getInstance()->isRevendicationParLots()) {
    $drevM1->addLot();
    $drevM1->lots[1]->numero_logement_operateur = '2';
    $drevM1->lots[1]->produit_hash = $produit->getHash();
    $drevM1->lots[1]->volume = 50;
}

$drevM1->save();
$drevM1->validate();
$drevM1->generateMouvementsFactures();
$drevM1->save();

$t->comment("Test numero dossier/numero archive pour la nouvelle modificatrice");

$t->ok($drevM1->numero_archive !== $drev->numero_archive, sprintf("Le numero d'archive de la Drev (%s) est different de la Drev modificatrice (%s)", $drev->numero_archive, $drevM1->numero_archive));
$lotDrev = $drevM1->getLots()[0];
$lotDrevM1 = $drevM1->getLots()[1];
$t->ok($lotDrev->numero_dossier !== $lotDrevM1->numero_dossier, sprintf("Le numero de dossier %s et numero d'archive %s du lot de la Drev est different du celui du lot numero de dossier %s et numero d'archive %s rajouter dans Drev modificatrice", $lotDrev->numero_dossier, $lotDrev->numero_archive, $lotDrevM1->numero_dossier,$lotDrevM1->numero_archive));

if(FactureConfiguration::getInstance()->isActive() && $nbMvtsAttendu) {
    $t->ok(count($drevM1->mouvements->get($viti->identifiant)) > 0, "La DRev modificatrice a des mouvements");
} else {
    $t->pass("Test non nécessaire car la facturation n'est pas activé");
}

$t->comment("Test de la réserve interpro");

$rdm_orig = $produit2->getConfig()->getRendementCepage();
$rdm_ri = $produit2->getConfig()->getRendementReserveInterpro();
$produit2->getConfig()->add('attributs')->add('rendement', 60);
$produit2->getConfig()->add('attributs')->add('rendement_reserve_interpro', 50);
$produit2->getConfig()->add('attributs')->add('rendement_reserve_interpro_min', 10);
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
$t->is($produit2M2->getConfig()->getRendementReserveInterproMin(), 10, "le rendement interpro minimal est bien celui attendu en configuration 2");
$t->ok(!$drevM2->get($produit2->getHash())->exist('dont_volume_revendique_reserve_interpro'), "Pas de champ reserve interpro car le volume minimal n'est pas atteint");
$t->is($drevM2->get($produit2->getHash())->getVolumeReserveInterpro(), 0, "Le volume dédié à la réserve interpro est à 0 car inférieur au minimal");
$t->is($drevM2->get($produit2->getHash())->getVolumeRevendiqueCommecialisable(), $produit2M2->superficie_revendique * 50 + 5, "Le volume commercialisable est le bon");

$drevM2->devalidate();
$produit2M2->volume_revendique_total = $produit2M2->superficie_revendique * 50 + $produit2M2->getConfig()->getRendementReserveInterproMin() + 1;
$produit2M2->volume_revendique_issu_recolte = $produit2M2->volume_revendique_total;
$drevM2->save();
$drevM2->validate();
$drevM2->save();

$t->ok($drevM2->get($produit2->getHash())->exist('dont_volume_revendique_reserve_interpro'), "Le volume dédié à la réserve interpro est bien présent");
$t->is($drevM2->get($produit2->getHash())->getVolumeReserveInterpro(), $produit2M2->getConfig()->getRendementReserveInterproMin() + 1, "Le volume dédié à la réserve interpro est le bon");
$t->is($drevM2->get($produit2->getHash())->getVolumeRevendiqueCommecialisable(), $produit2M2->superficie_revendique * 50, "Le volume commercialisable est le bon");


$drevM2->devalidate();
$produit2M2->volume_revendique_total = $produit2M2->superficie_revendique * 60 + 1;
$produit2M2->volume_revendique_issu_recolte = $produit2M2->volume_revendique_total;
$drevM2->save();
$drevM2->validate();
$drevM2->save();

$t->ok($drevM2->get($produit2->getHash())->exist('dont_volume_revendique_reserve_interpro'), "Le volume dédié à la réserve interpro est bien présent quand le rendement butoir est dépassé");
$t->is($drevM2->get($produit2->getHash())->getVolumeReserveInterpro(), $produit2M2->superficie_revendique * 10, "Le volume dédié à la réserve interpro est le bon quand le rendement butoir est dépassé");
$t->is($drevM2->get($produit2->getHash())->getVolumeRevendiqueCommecialisable(), $produit2M2->superficie_revendique * 50, "Le volume commercialisable est le bon quand le rendement butoir est dépassé");


$t->ok(!$drevM2->get($produit1->getHash())->exist('dont_volume_revendique_reserve_interpro'), "Le volume dédié à la réserve interpro n'est pas présent pour le 1er produit");
$t->ok($drevM2->hasProduitsReserveInterpro(), "La Drev indique bien qu'il existe des produits en reserve interpro");
$t->ok(!$drevM1->hasProduitsReserveInterpro(), "La Drev précédente n'a pas de produit en réserve interpro");

$produits = $drevM2->getProduitsWithReserveInterpro();
$t->is(count($produits), 1, "Il existe bien un produit avec des la réserve interpro");
$t->is($produits[0]->getHash(), $produit2M2->getHash(), "Le produit avec des la réserve interpro est le bon");

$produit2->getConfig()->add('attributs')->add('rendement', $rdm_orig);
$produit2->getConfig()->add('attributs')->add('rendement_reserve_interpro', $rdm_ri);
$produit2->getConfig()->clearStorage();
