<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'loire') {
    $t = new lime_test(1);
    $t->ok(true, "test disabled");
    return;
}

$t = new lime_test(48);

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
$t->comment($drev->_id);

$t->comment("Initialisation des produits");

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    $produit_hash1 = $produit->getHash();
    break;
}
$produit1 = $drev->addProduit($produit_hash1);
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    if($produit_hash1 == $produit->getHash()) {
        continue;
    }
    if ($produit1->getConfig()->getCouleur()->getKey() == $produit->getCouleur()->getKey()) {
        continue;
    }
    $produit_hash2 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    if($produit->isRevendicationParLots()) {
        continue;
    }
    $produit_hash_aoc = $produit->getHash();
    break;
}

$produit2 = $drev->addProduit($produit_hash2);
$produit_aoc = $drev->addProduit($produit_hash_aoc);

$produit_hash1 = $produit1->getHash();
$produit_hash2 = $produit2->getHash();

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);

$produit1->superficie_revendique = 200;
$produit2->superficie_revendique = 150;

$drev->save();

$t->is(count($drev->lots), 2, "2 lots ont automatiquement été créés (1 pour chaqun des 2 produits IGP)");
$t->is($drev->lots[0]->produit_libelle, $produit1->getLibelle(), "Le produit du lot est est initialisé avec le libellé ".$produit1->getLibelle());
$t->is($drev->lots[1]->produit_libelle, $produit2->getLibelle(), "Le produit du lot est est initialisé avec le libellé ".$produit1->getLibelle());

$t->comment("Création d'un lot");

$lot = $drev->addLot();

$lot->millesime = $campagne;
$lot->numero = "1";
$lot->volume = 30.4;
$lot->destination_type = null;
$lot->destination_date = ($campagne+1).'-04-15';
$lot->produit_hash = $produit1->getConfig()->getHash();
$lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
$lot->addCepage("PINOT G", 60);
$lot->addCepage("SAUVIGN.B", 40);

$drev->save();

$drev = DRevClient::getInstance()->find("DREV-".$viti->identifiant."-".$campagne);
$lot = $drev->lots->getLast();

$t->is(count($drev->lots), 3, "Le lot a été ajouté");
$t->is($lot->numero, "1", "Le numéro de lot est 1");
$t->is($lot->millesime, $campagne, "Le millésime est ".$campagne);
$t->is($lot->volume, 30.4, "Le volume est 30.4");
$t->is($lot->destination_date, ($campagne+1).'-04-15', "La date est ".($campagne+1).'-04-15');
$t->is($lot->getDestinationDateFr(), '15/04/'.($campagne+1), "La date est 15/04/".($campagne+1));
$t->is($lot->produit_hash, $produit1->getConfig()->getHash(), "Le hash produit produit est ".$produit1->getConfig()->getHash());
$t->is($lot->produit_libelle, $produit1->getConfig()->getLibelleComplet(), "Le libelle produit est ".$produit1->getConfig()->getLibelleComplet());
$t->is($lot->destination_type, DRevClient::LOT_DESTINATION_VRAC_EXPORT, "La destination_type est ".DRevClient::LOT_DESTINATION_VRAC_EXPORT);
$t->is($lot->cepages->getFirstKey(), "PINOT G", "Le premier cépage est du Pinot");
$t->is($lot->cepages->getFirst(), 60, "Le premier cépage représente 60% des cépages");
$t->is($lot->cepages->getLastKey(), "SAUVIGN.B", "Le dernier cépage est du Sauvignon");
$t->is($lot->cepages->getLast(), 40, "Le dernier cépage représente 40% des cépages");
$t->is($lot->getCepagesLibelle(), "PINOT G (60%), SAUVIGN.B (40%)", "Le dernier cépage représente 40% des cépages");

$drev->addLot();
$t->is(count($drev->lots), 4, "Le lot a été ajouté");
$drev->cleanLots();
$t->is(count($drev->lots), 1, "Le clean a supprimé les derniers lots ajoutés car ils étaient vides");

$drev->lotsImpactRevendication();
$t->is($drev->get($produit_hash1)->volume_revendique_issu_recolte, 30.4, "Le volume a été impacté dans la revendication");
$drev->save();

$drev->lotsImpactRevendication();
$t->is($drev->get($produit_hash1)->volume_revendique_issu_recolte, 30.4, "Le volume a été n'a pas été ré-impacté dans la revendication");

$t->comment("Formulaire lots");

$drev->addLot();
$form = new DRevLotsForm($drev);
$defaults = $form->getDefaults();
$t->is($defaults["_revision"], $drev->_rev, "La revision du doc est indiqué par défaut");

$values = $defaults;

$values['lots'][1]["volume"] = 5;
$values['lots'][1]["destination_date"] = "15/05/".($campagne+1);
$values['lots'][1]["millesime"] = ($campagne-2)."";
$values['lots'][1]["produit_hash"] = $produit2->getConfig()->getHash();
$values['lots'][1]["destination_type"] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$values['lots'][1]["numero"] = "A";
$values['lots'][1]["cepage_0"] = "MELON B";
$values['lots'][1]["repartition_0"] = "85";
$values['lots'][1]["cepage_1"] = "GROLLEAU N";
$values['lots'][1]["repartition_1"] = "15";

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

foreach($form->getErrorSchema()->getErrors() as $error) {
    throw $error;
}

$form->save();

$drev = DRevClient::getInstance()->find("DREV-".$viti->identifiant."-".$campagne);
$t->comment($drev->_id);
$lot = $drev->lots->getLast();
$t->is(count($drev->lots), 2, "La DRev contient le bon nombre de lots");
$t->is($lot->volume, 5, "Le volume est 5");
$t->is($lot->millesime, ($campagne-2)."", "Le millesime est ".($campagne-2));
$t->is($lot->destination_date, ($campagne+1).'-05-15', "La destination_date est ".($campagne+1).'-05-15');
$t->is($lot->produit_hash, $produit2->getConfig()->getHash(), "La hash produit a été changé");
$t->is($lot->produit_libelle, $produit2->getConfig()->getLibelleComplet(), "Le libellé produit est ".$produit2->getConfig()->getLibelleComplet());
$t->is($lot->destination_type, DRevClient::LOT_DESTINATION_VRAC_FRANCE, "La destination est ".DRevClient::LOT_DESTINATION_VRAC_FRANCE);
$t->is($lot->numero, "A", "Le numero est A");
$t->is(count($lot->cepages->toArray(true, false)), 2, "2 cépages déclarés");
$t->is($lot->cepages->getFirstKey(), "MELON B", "Le cépage est MELON B");
$t->is($lot->cepages->getFirst(), 85, "La repartition du cépage est de 85");
$t->is($lot->cepages->getLastKey(), "GROLLEAU N", "Le cépage est GROLLEAU N");
$t->is($lot->cepages->getLast(), 15, "La repartition du cépage est de 15");
$t->is($drev->get($produit_hash1)->volume_revendique_issu_recolte, 30.4, "Le volument de revendentication du produit ".$produit1->getConfig()->getLibelleComplet()." a été synchronisé par rapport aux lots");
$t->is($drev->get($produit_hash2)->volume_revendique_issu_recolte, 5, "Le volument de revendentication du produit ".$produit2->getConfig()->getLibelleComplet()." a été synchronisé par rapport aux lots");

$drev = DRevClient::getInstance()->find("DREV-".$viti->identifiant."-".$campagne);

$t->comment("Formulaire lots suppression");

$form = new DRevLotsForm($drev);
$defaults = $form->getDefaults();
$values = $defaults;

unset($values['lots'][1]);
$form->bind($values);

$t->ok($form->isValid(), "Le formulaire après suppression d'une ligne est valide");
$form->save();
$drev->cleanLots();

$t->is(count($drev->lots), 1, "il reste 1 lot dans la Drev");
$t->is($drev->lots[0]->produit_hash,$produit1->getParent()->getHash(),"Le 1er lot restant est correct");
$t->ok(!isset($drev->lots[1]),"Le 2nd lot est absent");



$t->comment("Test de la visu des lots");

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);

$produit1->volume_revendique_total = 55;
$produit2->volume_revendique_total = 10;

$produit1->recolte->recolte_nette = 55;
$produit2->recolte->recolte_nette = 10;

$produit1->recolte->volume_total = 55;
$produit2->recolte->volume_total = 10;

$lot3 = $drev->addLot();

$lot3->millesime = $campagne;
$lot3->numero = "5";
$lot3->volume = 20;
$lot3->destination_type = null;
$lot3->destination_date = ($campagne+1).'-04-15';
$lot3->produit_hash = $produit1->getConfig()->getHash();
$lot3->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;

$lot4 = $drev->addLot();
$lot4->millesime = $campagne;
$lot4->numero = "6";
$lot4->volume = 30;
$lot4->destination_type = null;
$lot4->destination_date = ($campagne+1).'-04-15';
$lot4->produit_hash = $produit1->getConfig()->getHash();
$lot4->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;

$lot5 = $drev->addLot();
$lot5->millesime = $campagne;
$lot5->numero = "7";
$lot5->volume = 5;
$lot5->destination_type = null;
$lot5->destination_date = ($campagne+1).'-04-15';
$lot5->produit_hash = $produit2->getConfig()->getHash();
$lot5->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;

$drev->save();

$produitLotsByCouleur = $drev->summerizeProduitsLotsByCouleur();

$t->is(count($produitLotsByCouleur), 2,"Le recap des lots ne contient deux couleurs");
$couleurLibelle = array_shift(array_keys($produitLotsByCouleur));
$couleurValues = array_shift(array_values($produitLotsByCouleur));
$t->is($couleurLibelle,$produit1->getLibelleComplet(),"Le libellé de ce total est le même que celui du produit du noeud declaration");
$t->is($couleurValues["volume_lots"],80.4,"La somme des volumes lot pour ce produit est 60");
$t->is($couleurValues["volume_restant"],0,"Il ne reste plus de volume à revendiquer");
