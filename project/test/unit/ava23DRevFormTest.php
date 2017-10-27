<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(60);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}

$campagne = (date('Y')-1)."";


$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération des données à partir de la DR");

$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane.csv');
$csvContent = $csv->convert();
file_put_contents("/tmp/dr.csv", $csvContent);
$csv = new DRCsvFile("/tmp/dr.csv");

$drev->importCSVDouane($csv->getCsv());
$drev->save();

$t->is(count($drev->getProduits()), 2, "La DRev a repris 2 produits du csv de la DR");

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

$produit2 = current($produits);
$produit_hash2 = $produit2->getHash();
next($produits);
$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();
$produit1->vci->stock_precedent = 3;

$drev->save();

$t->is($produit1->getLibelleComplet(), "Saint Joseph Rouge", "Le libelle du produit est Saint Joseph Rouge");
$t->is($produit1->recolte->superficie_total, 2.4786, "La superficie total de la DR pour le produit est de 333.87");
$t->is($produit1->recolte->volume_sur_place, 105.18, "Le volume sur place pour ce produit est de 108.94");
$t->is($produit1->recolte->usages_industriels_total, 3.03, "Les usages industriels la DR pour ce produit sont de 4.32");
$t->is($produit1->recolte->recolte_nette, 104.1, "La récolte nette de la DR pour ce produit est de 104.1");
$t->is($produit1->recolte->volume_total, 105.18, "Le volume total de la DR pour ce produit est de 169.25");
$t->is($produit1->recolte->vci_constitue, 2, "Le vci de la DR pour ce produit est de 2");
$t->is($produit1->vci->constitue, 2, "Le vci de l'année de la DR pour ce produit est de 2");
$t->is($produit2->getLibelleComplet(), "Côtes du Rhône Villages Puymeras Rouge", "Le libelle du produit estTranquilles CdR Villages avec NG Puymeras Rouge");

$t->comment('Formulaire de revendication des superficies');

if($drev->storeEtape(DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE)) {
    $drev->save();
}

$form = new DRevSuperficieForm($drev);

$defaults = $form->getDefaults();

$t->is($form['produits'][$produit_hash1]['recolte']['superficie_total']->getValue(), $produit1->recolte->superficie_total, "La superficie totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), $produit1->superficie_revendique, "La superficie revendique est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['has_stock_vci']->getValue(), true, "La checkbox de vci du premier produit est coché");
$t->is($form['produits'][$produit_hash2]['has_stock_vci']->getValue(), false, "La checkbox de vci du 2ème produit n'est pas coché");
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

$t->is(count($form['produits']), 1, "La form a 1 seul produit");
$t->is($form['produits'][$produit_hash1]['stock_precedent']->getValue(), 3, "Le stock VCI avant récolte du formulaire est de 3");
$t->is($form['produits'][$produit_hash1]['destruction']->getValue(), null, "Le VCI desctruction est nul");
$t->is($form['produits'][$produit_hash1]['complement']->getValue(), null, "Le VCI en complément est nul");
$t->is($form['produits'][$produit_hash1]['substitution']->getValue(), null, "Le VCI en substitution est nul");
$t->is($form['produits'][$produit_hash1]['rafraichi']->getValue(), null, "Le VCI rafraichi est nul");

$valuesVCI = array(
    'produits' => array(
        $produit_hash1 => array(
            "stock_precedent" => 3,
            "destruction" => 0,
            "substitution" => 0,
            "complement" => 1,
            "rafraichi" => 2,
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
$t->is($produit1->vci->complement, 1, "Le VCI en complément de la DR du produit du doc est de 2");
$t->is($produit1->vci->substitution, 0, "Le VCI en substitution de la DR du produit du doc est de 0");
$t->is($produit1->vci->rafraichi, 2, "Le VCI rafraichi du produit est de 1");
$t->is($produit1->volume_revendique_issu_vci, $produit1->vci->complement + $produit1->vci->substitution + $produit1->vci->rafraichi, "Le volume revendiqué issu du vci est calculé à partir de la répartition vci");

$t->comment("Formulaire de revendication");

if($drev->storeEtape(DrevEtapes::ETAPE_REVENDICATION)) {
    $drev->save();
}

$form = new DRevRevendicationForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), count($drev->getProduits()), "La form à le même nombre de produit que dans la drev");
$t->is($form['produits'][$produit_hash1]['recolte']['volume_total']->getValue(), $produit1->recolte->volume_sur_place, "Le volume total récolté est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['recolte']['recolte_nette']->getValue(), $produit1->recolte->recolte_nette, "La récolté nette de la DR sont initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['recolte']['volume_sur_place']->getValue(), $produit1->recolte->volume_sur_place, "Le volume sur place est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['volume_revendique_issu_recolte']->getValue(), $produit1->volume_revendique_issu_recolte, "Le volume revendique issu de la DR est initialisé dans le form");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['superficie_total']), "La superficie totale de la DR n'est pas proposé dans le formulaire");


$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produit_hash1]['volume_revendique_issu_recolte'] = 100;
$valuesRev['produits'][$produit_hash2]['volume_revendique_issu_recolte'] = 100;

$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is($produit1->recolte->volume_sur_place, $valuesRev['produits'][$produit_hash1]['recolte']['volume_sur_place'], "La superficie total de la DR est enregistré");
$t->is($produit1->recolte->volume_total, $valuesRev['produits'][$produit_hash1]['recolte']['volume_total'], "Le volume total de la DR est enregistré");
$t->is($produit1->recolte->recolte_nette, $valuesRev['produits'][$produit_hash1]['recolte']['recolte_nette'], "La récolte nette de la DR a été enregistrée");
$t->is($produit1->volume_revendique_issu_recolte, $valuesRev['produits'][$produit_hash1]['volume_revendique_issu_recolte'], "Le volume revendiqué issu de la récolte est enregistré");
$t->is($produit1->volume_revendique_total, $produit1->volume_revendique_issu_recolte + $produit1->volume_revendique_issu_vci, "Le volume revendique total est calculé");

$t->comment("Validation");

if($drev->storeEtape(DrevEtapes::ETAPE_VALIDATION)) {
    $drev->save();
}

$t->is($produit1->vci->stock_final, 4, "Le stock VCI après récolte du produit du doc est 4");
$t->is($produit1->vci->stock_final, $produit1->vci->constitue + $produit1->vci->rafraichi, "Le VCI stock après récolte du produit du doc est le même que le calculé");

$drev->cleanDoc();

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');

$produit1->getConfig()->add('attributs')->add('rendement', 50);
$produit1->getConfig()->add('attributs')->add('rendement_vci', 5);
$produit1->getConfig()->add('attributs')->add('rendement_vci_total', 15);
$produit1->getConfig()->clearStorage();

$t->ok(!isset($erreurs['revendication_incomplete']), "Pas de point blocant sur le remplissage des données de revendication");
$t->ok(!isset($erreurs['revendication_rendement']), "Pas de point blocant sur le rendement de la revendication");
$t->ok(!isset($erreurs['vci_stock_utilise']), "Pas de point blocant sur la repartition du vci");
$t->ok(!isset($erreurs['vci_rendement_annee']), "Pas de point blocant sur le rendement à l'année du vci");
$t->ok(!isset($erreurs['vci_rendement_total']), "Pas de point blocant sur le rendement total du vci");

$drevControle = clone $drev;

$produitControle1 = $drevControle->get($produit1->getHash());
$produitControle2 = $drevControle->get($produit2->getHash());

$produitControle1->recolte->volume_total = 10000;
$produitControle1->volume_revendique_total = 10000;
$produitControle1->vci->rafraichi = 0;
$produitControle1->vci->constitue = 10000;
$produitControle1->vci->stock_final = 10000;

$produitControle2->volume_revendique_issu_recolte = null;

$validation = new DRevValidation($drevControle);

$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->ok(isset($erreurs['revendication_incomplete']) && count($erreurs['revendication_incomplete']) == 1 && $erreurs['revendication_incomplete'][0]->getInfo() == $produitControle2->getLibelleComplet(), "Un point bloquant est levé car les infos de revendications n'ont pas été saisi");

$t->ok(isset($erreurs['revendication_rendement']) && count($erreurs['revendication_rendement']) == 1 && $erreurs['revendication_rendement'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le rendement sur le revendiqué n'est pas respecté");

$t->ok(isset($erreurs['vci_stock_utilise']) && count($erreurs['vci_stock_utilise']) == 1 && $erreurs['vci_stock_utilise'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le vci utilisé n'a pas été correctement réparti");

$t->ok(isset($vigilances['vci_rendement_annee']) && count($vigilances['vci_rendement_annee']) == 1 && $vigilances['vci_rendement_annee'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point de vigilance est levé car le vci déclaré de l'année ne respecte pas le rendement de l'annee");

$t->ok(isset($erreurs['vci_rendement_total']) && count($erreurs['vci_rendement_total']) == 1 && $erreurs['vci_rendement_total'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le stock vci final déclaré ne respecte pas le rendement total");

$t->comment("Export CSV");

$export = new ExportDRevCSV($drev);

$csvContent = $export->export();

$t->is(count(explode("\n", $csvContent)), 4, "L'export fait 4 lignes");
