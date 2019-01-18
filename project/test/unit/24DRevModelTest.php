<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(28);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$campagne = (date('Y')-1)."";

//Début des tests
$t->comment("Création d'une DRev");

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

$produit3 = $drev->addProduit($produit_hash2, "BIO");

$produit_hash1 = $produit1->getHash();
$produit_hash2 = $produit2->getHash();
$produit_hash3 = $produit3->getHash();
$drev->save();

$t->is($drev->exist($produit_hash1), true, "La produit ajouté existe");
$t->is($drev->get($produit_hash1)->getHash(), $produit_hash1, "La produit ajouté est récupérable");

$t->is($drev->get($produit_hash2)->getKey(), "DEFAUT", "La clé du produit est DEFAUT");
$t->is($drev->get($produit_hash2)->denomination_complementaire, null, "La dénomination complémentaire est null");
$t->is($drev->get($produit_hash2)->libelle, $drev->get($produit_hash2)->getConfig()->getLibelleComplet(), "Le libellé est enregistré");

$t->ok($drev->get($produit_hash3)->getKey() != "DEFAUT", "La clé du produit bio n'est pas DEFAUT");
$t->is($drev->get($produit_hash3)->denomination_complementaire, "BIO", "La dénomination complémentaire est BIO");
$t->is($drev->get($produit_hash3)->libelle, $drev->get($produit_hash3)->getConfig()->getLibelleComplet()." BIO", "La dénomination complémentaire est dans le libellé");

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);
$produit3 = $drev->get($produit_hash3);

$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;

$produit2->superficie_revendique = 150;
$produit2->recolte->superficie_total = 150;
$produit2->volume_revendique_issu_recolte = 110;
$drev->save();

$t->is(count($drev->getProduits()), 3, "La drev a 3 produits");
$t->is($drev->declaration->getTotalTotalSuperficie(), 350, "La supeficie revendiqué totale est 350");
$t->is($drev->declaration->getTotalVolumeRevendique(), 190, "Le volume revendiqué totale est 190");

$t->comment("Validation");

$drev->validate();
$drev->save();

$t->is($drev->declaration->getTotalTotalSuperficie(), 350, "La supeficie revendiqué totale est toujours de 350");
$t->is($drev->declaration->getTotalVolumeRevendique(), 190, "Le volume revendiqué totale est toujours de 190");

$t->is($drev->validation, date('Y-m-d'), "La DRev a la date du jour comme date de validation");
$t->is(count($drev->mouvements->get($viti->identifiant)), 2, "La DRev a 2 mouvements");

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
$produit1M1->superficie_revendique = 120;
$produit1M1->recolte->superficie_total = 120;
$produit1M1->volume_revendique_issu_recolte = 90;
$produit1M1->volume_revendique_total = 90;

$t->ok($drevM1->isModifiedMother($produit1->getHash(), 'superficie_revendique'), "La superficie vinifiee est marquée comme modifié par rapport à la précedente");

$t->comment("Validation de la modificatrice");

$drevM1->validate();
$drevM1->save();

$t->is(count($drevM1->mouvements->get($viti->identifiant)), 1, "La DRev modificatrice a 1 un seul mouvement");
$mouvementM1 = $drevM1->mouvements->get($viti->identifiant)->getFirst();
$t->is($mouvementM1->quantite, $produit1M1->get($mouvementM1->type_hash) - $produit1->get($mouvementM1->type_hash), "La quantité du mouvement correspond à la différence entre les 2 DRev");
