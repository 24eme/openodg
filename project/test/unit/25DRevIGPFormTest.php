<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "pass AOC");
    return;
}

$t = new lime_test(78);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}

foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
    HabilitationClient::getInstance()->deleteDoc(HabilitationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    if(!$produit1) {
        $produit1 = $produit;
        continue;
    } elseif(!$produit2) {
        $produit2 = $produit;
        continue;
    }

    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(array("%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/dr_douane.csv");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet());

$campagne = (date('Y')-1)."";

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération des données à partir de la DR");

$dr = DRClient::getInstance()->createDoc($viti->identifiant, $campagne);
$dr->setLibelle("DR $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier($csvTmpFile);
$dr->save();

$drev->importFromDocumentDouanier();
$drev->save();

$t->is(count($drev->getProduits()), 2 + (!DRevConfiguration::getInstance()->hasDenominationAuto()) * 2, "La DRev a repris le bon nombre de produits du csv de la DR");

$i = 0;
$produits2Delete = array();
foreach($drev->getProduits() as $produit) {
    $i++;
    if($i > 2) {
        $produits2Delete[$produit->getHash()] = $produit->getHash();
    }
}

foreach($produits2Delete as $hash) {
    $drev->remove($hash);
}

$produits = $drev->getProduits();


$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();

next($produits);
$produit2 = current($produits);
$produit_hash2 = $produit2->getHash();

$produit1->vci->stock_precedent = 3;

$drev->save();

$t->is($produit1->recolte->superficie_total, 2.4786 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "La superficie total de la DR pour le produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_sur_place, 105.18 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Le volume sur place pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->usages_industriels_total, 3.03 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Les usages industriels la DR pour ce produit ".$produit1->getLibelleComplet()." sont OK");
$t->is($produit1->recolte->recolte_nette, 104.1 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "La récolte nette de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_total, 105.18 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Le volume total de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->vci_constitue, 2 * (DRevConfiguration::getInstance()->hasDenominationAuto()), "Le vci de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->vci->constitue, 2 * (DRevConfiguration::getInstance()->hasDenominationAuto()), "Le vci de l'année de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");

$t->comment('Formulaire de revendication des superficies');

if($drev->storeEtape(DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE)) {
    $drev->save();
}

$form = new DRevSuperficieForm($drev);

$defaults = $form->getDefaults();

$t->is($form['produits'][$produit_hash1]['recolte']['superficie_total']->getValue(), $produit1->recolte->superficie_total, "La superficie totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), $produit1->superficie_revendique, "La superficie revendique est initialisé dans le form");
#Ignore le test si la configuration ne permet pas de faire du VCI
$t->is(!isset($form['produits'][$produit_hash1]['has_stock_vci']) || $form['produits'][$produit_hash1]['has_stock_vci']->getValue(), true, "La checkbox de vci du premier produit est coché");
$t->is(isset($form['produits'][$produit_hash1]['has_stock_vci']) && $form['produits'][$produit_hash2]['has_stock_vci']->getValue(), false, "La checkbox de vci du 2ème produit n'est pas coché");

$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['volume_total']), "Le volume total de la DR n'est pas proposé dans le formulaire");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['recolte_nette']), "Le volume de récolte nette de la DR n'est pas proposé dans le formulaire");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['volume_sur_place']), "Le volume sur place de la DR n'est pas proposé dans le formulaire");

$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produit_hash1]['superficie_revendique'] = 10;
$valuesRev['produits'][$produit_hash1]['recolte']['superficie_total'] = 10;
$valuesRev['produits'][$produit_hash2]['recolte']['superficie_total'] = 300;
$valuesRev['produits'][$produit_hash2]['superficie_revendique'] = 2;
$valuesRev['produits'][$produit_hash2]['has_stock_vci'] = false;

$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is($produit1->recolte->superficie_total, $valuesRev['produits'][$produit_hash1]['recolte']['superficie_total'], "La superficie total de la DR est enregistré");
$t->is($produit1->superficie_revendique, $valuesRev['produits'][$produit_hash1]['superficie_revendique'], "La superficie revendique est enregistré");
$t->ok($produit1->hasVci(), "Le produit 1 est déclaré ayant du vci");
$t->ok(!$produit2->hasVci(), "Le produit 2 n'est pas déclaré ayant du vci");

$t->comment("Formulaire du VCI");

if($drev->storeEtape(DrevEtapes::ETAPE_VCI)) {
    $drev->save();
}

$form = new DRevVciForm($drev);

$defaults = $form->getDefaults();
$destruction = $produit1->vci->stock_precedent - $produit1->getPlafondStockVci();
if ($destruction < 0) {
	$destruction = null;
}
$t->is(count($form['produits']), 1, "La form a 1 seul produit");
$t->is($form['produits'][$produit_hash1]['stock_precedent']->getValue(), 3, "Le stock VCI avant récolte du formulaire est de 3");
$t->is($form['produits'][$produit_hash1]['destruction']->getValue(), $destruction, "Le VCI desctruction est de $destruction");
$t->is($form['produits'][$produit_hash1]['complement']->getValue(), null, "Le VCI en complément est nul");
$t->is($form['produits'][$produit_hash1]['substitution']->getValue(), null, "Le VCI en substitution est nul");
$t->is($form['produits'][$produit_hash1]['rafraichi']->getValue(), null, "Le VCI rafraichi est nul");

$valuesVCI = array(
    'produits' => array(
        $produit_hash1 => array(
            "stock_precedent" => 3,
            "destruction" => 0,
            "substitution" => 0,
            "complement" => 3,
            "rafraichi" => 0,
        ),
    ),
    '_revision' => $drev->_rev,
);

$form->bind($valuesVCI);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$produit1 = $drev->get($produit_hash1);
$t->is($produit1->vci->stock_precedent, 3, "Le stock VCI avant récolte du produit du doc est de 3");
$t->is($produit1->vci->destruction, null, "Le VCI en destruction du produit du doc est null");
$t->is($produit1->vci->complement, 3, "Le VCI en complément de la DR du produit du doc est de 3");
$t->is($produit1->vci->substitution, 0, "Le VCI en substitution de la DR du produit du doc est de 0");
$t->is($produit1->vci->rafraichi, 0, "Le VCI rafraichi du produit est de 0");
$t->is($produit1->volume_revendique_issu_vci, $produit1->vci->complement + $produit1->vci->substitution + $produit1->vci->rafraichi, "Le volume revendiqué issu du vci est calculé à partir de la répartition vci");
