<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(21);

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

$produits = $drev->getConfigProduits();
foreach($produits as $produit) {
    $produit_hash1 = $produit->getHash();
    break;
}
foreach($produits as $produit) {
    if($produit_hash1 == $produit->getHash()) {
        continue;
    }
    $produit_hash2 = $produit->getHash();
    break;
}

$madenomination = "Denomination de test";
$produit1 = $drev->addProduit($produit_hash1, $madenomination);
$produit2 = $drev->addProduit($produit_hash2);

$produit_hash1 = $produit1->getHash();
$produit_hash2 = $produit2->getHash();

$produit1 = $drev->get($produit_hash1);
$produit2 = $drev->get($produit_hash2);

$produit1->superficie_revendique = 200;
$produit2->superficie_revendique = 150;

$drev->save();

$t->comment("Création d'un lot");

$lot = $drev->addLot();

$lot->millesime = $campagne;
$lot->numero = "1";
$lot->volume = 30.4;
$lot->destination = null;
$lot->date = ($campagne+1).'-04-15';
$lot->produit_hash = $produit1->getConfig()->getHash();
$lot->destination = DRevClient::LOT_DESTINATION_VRAC_EXPORT;

$drev->save();

$drev = DRevClient::getInstance()->find("DREV-".$viti->identifiant."-".$campagne);
$lot = $drev->lots->getFirst();

$t->is(count($drev->lots), 1, "Le lot a été ajouté");
$t->is($lot->numero, "1", "Le numéro de lot est 1");
$t->is($lot->millesime, $campagne, "Le millésime est ".$campagne);
$t->is($lot->volume, 30.4, "Le volume est 30.4");
$t->is($lot->date, ($campagne+1).'-04-15', "La date est ".($campagne+1).'-04-15');
$t->is($lot->getDateFr(), '15/04/'.($campagne+1), "La date est 15/04/".($campagne+1));
$t->is($lot->produit_hash, $produit1->getConfig()->getHash(), "Le hash produit produit est ".$produit1->getConfig()->getHash());
$t->is($lot->produit_libelle, $produit1->getConfig()->getLibelleComplet(), "Le libelle produit est ".$produit1->getConfig()->getLibelleComplet());
$t->is($lot->destination, DRevClient::LOT_DESTINATION_VRAC_EXPORT, "La destination est ".DRevClient::LOT_DESTINATION_VRAC_EXPORT);

$t->comment("Formulaire lots");

$form = new DRevLotsForm($drev);
$defaults = $form->getDefaults();
$t->is($defaults["_revision"], $drev->_rev, "La revision du doc est indiqué par défaut");
$t->is($defaults["lots"][0]["volume"], $lot->volume, "Le volume du premier lot est prérempli");
$t->is($defaults["lots"][0]["date"], $lot->getDateFr(), "La date est prérempli au format français");
$t->is($defaults["lots"][0]["millesime"], $lot->millesime, "Le millésime est prérempli");
$t->is($defaults["lots"][0]["numero"], $lot->numero, "Le numéro est prérempli");

$values = $defaults;

$values['lots'][0]["volume"] = 5;
$values['lots'][0]["date"] = "15/05/".($campagne+1);
$values['lots'][0]["millesime"] = ($campagne-2)."";
$values['lots'][0]["produit_hash"] = $produit2->getConfig()->getHash();
$values['lots'][0]["destination"] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$values['lots'][0]["numero"] = "A";

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

foreach($form->getErrorSchema()->getErrors() as $error) {
    echo $error."\n";
}

$form->save();

$drev = DRevClient::getInstance()->find("DREV-".$viti->identifiant."-".$campagne);
$lot = $drev->lots->getFirst();

$t->is($lot->volume, 5, "Le volume est 5");
$t->is($lot->millesime, ($campagne-2)."", "Le millesime est ".($campagne-2));
$t->is($lot->date, ($campagne+1).'-05-15', "La date est ".($campagne+1).'-05-15');
$t->is($lot->produit_hash, $produit2->getConfig()->getHash(), "La hash produit a été changé");
$t->is($lot->produit_libelle, $produit2->getConfig()->getLibelleComplet(), "Le libellé produit est ".$produit2->getConfig()->getLibelleComplet());
$t->is($lot->destination, DRevClient::LOT_DESTINATION_VRAC_FRANCE, "La destination est ".DRevClient::LOT_DESTINATION_VRAC_FRANCE);
$t->is($lot->numero, "A", "Le numero est A");
