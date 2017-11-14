<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(75);

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

$campagne = (date('Y')-1)."";

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération des données à partir de la DR");

$dr = DRClient::getInstance()->createDoc($viti->identifiant, $campagne);
$dr->setLibelle("DR $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier(dirname(__FILE__).'/../data/dr_douane.csv');
$dr->save();

$drev->importFromDocumentDouanier();
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
$t->is($form['produits'][$produit_hash1]['volume_revendique_issu_recolte']->getValue(), $produit1->recolte->recolte_nette - $produit1->vci->rafraichi - $produit1->vci->substitution, "Le volume revendique issu de la DR est bien calculé et initialisé dans le form");
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

$habilitation = HabilitationClient::getInstance()->createDoc($viti->identifiant, $drev->getDate());
$habilitation->addProduit($produit1->getConfig()->getHash())->updateHabilitation(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::STATUT_HABILITE);
$habilitation->save();

$produit1->getConfig()->add('attributs')->add('rendement', 50);
$produit1->getConfig()->add('attributs')->add('rendement_vci', 5);
$produit1->getConfig()->add('attributs')->add('rendement_vci_total', 15);
$produit1->getConfig()->clearStorage();

$produit2->getConfig()->add('attributs')->add('rendement', 50);
$produit2->getConfig()->add('attributs')->add('rendement_vci', 5);
$produit2->getConfig()->add('attributs')->add('rendement_vci_total', 15);
$produit2->getConfig()->clearStorage();

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->ok(!isset($erreurs['revendication_incomplete']), "Pas de point blocant sur le remplissage des données de revendication");
$t->ok(!isset($erreurs['revendication_rendement']), "Pas de point blocant sur le rendement de la revendication");
$t->ok(!isset($erreurs['vci_stock_utilise']), "Pas de point blocant sur la repartition du vci");
$t->ok(!isset($erreurs['vci_rendement_annee']), "Pas de point blocant sur le rendement à l'année du vci");
$t->ok(!isset($erreurs['vci_rendement_total']), "Pas de point blocant sur le rendement total du vci");
$t->ok(!isset($erreurs['declaration_volume_l15_complement']), "Pas de point blocant sur le respect de la ligne l15");
$t->ok(!isset($erreurs['vci_substitue_rafraichi']), "Pas de point blocant sur la subsitution ni le rafraichissement du volume de VCI");
$t->ok(!isset($erreurs['revendication_superficie']), "Pas de point blocant sur la superficie declarée sur la DR et la DRev");

$t->is(count($vigilances['declaration_habilitation']), 1, "Pas de point de vigilance sur l'habilitation du premier produit");
$t->ok(!isset($vigilences['declaration_volume_l15']), "Pas de point vigilance sur le respect de la ligne l15");
$t->ok(!isset($vigilences['declaration_neant']), "Pas de point vigilance sur la declaration neant");
$t->ok(!isset($vigilences['declaration_produits_incoherence']), "Pas de point vigilance sur les produits declarés sur la DR et la DRev");
$t->ok(!isset($vigilences['declaration_surface_bailleur']), "Pas de point vigilance sur la repartition de la surface avec le bailleur");

$drevControle = clone $drev;
$habilitation->updateHabilitation($produit1->getConfig()->getHash(), HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::STATUT_RETRAIT);
$habilitation->save();
$produitControle1 = $drevControle->get($produit1->getHash());
$produitControle2 = $drevControle->get($produit2->getHash());

$produitControle1->recolte->volume_sur_place_revendique = 1000;
$produitControle1->recolte->volume_sur_place = 1000;
$produitControle1->recolte->recolte_nette = 1000;
$produitControle1->recolte->volume_total = 1000;
$produitControle1->volume_revendique_total = 10000;
$produitControle1->vci->rafraichi = 0;
$produitControle1->vci->substitution = 50000;
$produitControle1->vci->constitue = 10000;
$produitControle1->vci->stock_final = 10000;
$produitControle1->superficie_revendique = 50;

$produitControle2->volume_revendique_issu_recolte = null;

$validation = new DRevValidation($drevControle);

$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->ok(isset($erreurs['revendication_incomplete']) && count($erreurs['revendication_incomplete']) == 1 && $erreurs['revendication_incomplete'][0]->getInfo() == $produitControle2->getLibelleComplet(), "Un point bloquant est levé car les infos de revendications n'ont pas été saisi");
$t->ok(isset($erreurs['revendication_rendement']) && count($erreurs['revendication_rendement']) == 1 && $erreurs['revendication_rendement'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le rendement sur le revendiqué n'est pas respecté");
$t->ok(isset($erreurs['vci_stock_utilise']) && count($erreurs['vci_stock_utilise']) == 1 && $erreurs['vci_stock_utilise'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le vci utilisé n'a pas été correctement réparti");
$t->ok(isset($vigilances['vci_rendement_annee']) && count($vigilances['vci_rendement_annee']) == 1 && $vigilances['vci_rendement_annee'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point de vigilance est levé car le vci déclaré de l'année ne respecte pas le rendement de l'annee");
$t->ok(isset($erreurs['vci_rendement_total']) && count($erreurs['vci_rendement_total']) == 1 && $erreurs['vci_rendement_total'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le stock vci final déclaré ne respecte pas le rendement total");
$t->is(count($vigilances['declaration_habilitation']), 2, "Des points de vigilences sur les habilitations des deux produits (un en retrait, l'autre non déclaré dans l'habilitation)");
$t->is(count($vigilances['declaration_volume_l15']), 1, "Point vigilance sur le respect de la ligne l15");

$t->is(count($erreurs['declaration_volume_l15_complement']), 1, "Point bloquant sur le respect de la ligne l15");
$t->is(count($erreurs['revendication_superficie']), 1, "Point bloquant sur la superficie declarée sur la DR et la DRev");
$t->is(count($erreurs['vci_substitue_rafraichi']), 1, "VCI rafraichi / subsitue non respect de la ligne l15");


$drevControle->remove($produit1->getHash());
$validation = new DRevValidation($drevControle);

$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->is(count($vigilances['declaration_produits_incoherence']), 1, "Tous les produits de la DR n'ont pas été revendiqués");

$drevControle->remove('declaration');
$drevControle->add('declaration');
$validation = new DRevValidation($drevControle);

$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->is(count($vigilances['declaration_neant']), 1, "DRev à néant");

$t->comment("Export CSV");

$export = new ExportDRevCSV($drev);

$csvContent = $export->export();

$t->is(count(explode("\n", $csvContent)), 4, "L'export fait 4 lignes");
