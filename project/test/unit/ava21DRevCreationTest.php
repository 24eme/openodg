<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(18);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
  $drev = DRevClient::getInstance()->find($k);
  $drev->delete(false);
}

$campagne = (date('Y')-1)."";

//Début des tests
$t->comment("création d'une DRev");

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
    $produit_hash1 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    $produit_hash2 = $produit->getHash();
}

$produit1 = $drev->addProduit($produit_hash1);
$produit2 = $drev->addProduit($produit_hash2);

$produit1->superficie_vinifiee = 100;
$produit1->superficie_revendique = 200;
$produit1->volume_revendique = 80;

$produit2->superficie_vinifiee = 150;
$produit2->superficie_revendique = 150;
$produit2->volume_revendique = 110;

$drev->save();

$t->is(count($drev->getProduits()), 2, "La drev a 2 produits");
$t->is($drev->declaration->getTotalTotalSuperficie(), 350, "La supeficie revendiqué totale est 350");
$t->is($drev->declaration->getTotalSuperficieVinifiee(), 250, "La superficie vinifié totale est 250");
$t->is($drev->declaration->getTotalVolumeRevendique(), 190, "Le volume revendiqué totale est 190");

$t->comment("Validation");

$drev->validate();
$drev->save();

$t->is($drev->validation, date('Y-m-d'), "La DRev à la date du jour comme date de validation");
$t->is(count($drev->mouvements->get($viti->identifiant)), 6, "La DRev a 6 mouvements");

$mouvement = $drev->mouvements->get($viti->identifiant)->getFirst();

$t->ok($mouvement->produit_hash === $produit1->getHash() && $mouvement->produit_libelle === $produit1->getLibelleComplet(), "Le hash et le libellé du produit du mouvement sont corrects");
$t->is($mouvement->quantite, $produit1->get($mouvement->type_hash), "La quantité du premier mouvement correspond à ce qui a été saisie dans la DRev");
$t->ok($mouvement->facture === 0 && $mouvement->facturable === 1, "Le mouvement est non facturé et facturable");
$t->ok($mouvement->date === $drev->validation && $mouvement->date_version === $drev->validation, "Les dates du mouvement sont égale à la date de validation de la DRev");

$t->comment("Génération d'une modificatrice");

$drevM1 = $drev->generateModificative();
$drevM1->save();

$t->is($drevM1->_id, $drev->_id."-M01", "L'id de la drev est ".$drev->_id."-M01");
$t->ok($drevM1->validation === null && $drevM1->validation_odg === null, "La drev modificatrice est dévalidée");

$drevMaster = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($viti->identifiant, $campagne);
$t->is($drevM1->_id, $drevMaster->_id, "La récupération de la drev master renvoi la drev ".$drevM1->_id);

$produit1M1 = $drevM1->get($produit1->getHash());
$produit1M1->superficie_vinifiee = 120;

$t->comment("Validation de la modificatrice");

$drevM1->validate();
$drevM1->save();

$t->is(count($drevM1->mouvements->get($viti->identifiant)), 1, "La DRev modificatrice a 1 un seul mouvement");

$mouvementM1 = $drevM1->mouvements->get($viti->identifiant)->getFirst();
$t->is($mouvementM1->quantite, $produit1M1->get($mouvementM1->type_hash) - $produit1->get($mouvementM1->type_hash), "La quantité du mouvement correspond à la différence entre les 2 DRev");
