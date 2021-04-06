<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application == 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "pass IGP");
    return;
}

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$has_habilitation_inao = 0;
if ($application == 'loire' || $application == 'nantes') {
    $has_habilitation_inao = 1;
}

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
    if($produit->getRendement() <= 0) {
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

$produitConfigMutage = null;

foreach($config->getProduits() as $produit) {
    if($produit->getRendement() <= 0) {
        continue;
    }
    if(!$produit->hasMutageAlcoolique()) {
        continue;
    }

    $produitConfigMutage = $produit;
    break;
}

if ($produitConfigMutage) {
    $t = new lime_test(92 + !$has_habilitation_inao * 2);
}else {
    $t = new lime_test(79 + !$has_habilitation_inao * 2);
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($viti->cvi, $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/dr_douane.csv");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet());

$campagne = (date('Y'))."";

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
$t->comment($drev->_id);
$t->is(count($drev->getProduits()), 2 * (1 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire()), "La DRev a repris les produits du csv de la DR");

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
$produit2 = end($produits);
$produit_hash2 = $produit2->getHash();

$produit1->vci->stock_precedent = 3;

$drev->save();

$t->is($produit1->recolte->superficie_total, 4.9572 / (1 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() ), "La superficie total de la DR pour le produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_sur_place, 210.36 / (1 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() ), "Le volume sur place pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->usages_industriels_total, 6.06 / (1 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() ), "Les usages industriels la DR pour ce produit ".$produit1->getLibelleComplet()." sont OK");
$t->is($produit1->recolte->recolte_nette, 208.2 / (1 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() ), "La récolte nette de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_total, 210.36 / (1 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() ), "Le volume total de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->vci_constitue, 2, "Le vci de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->vci->constitue, 2, "Le vci de l'année de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");

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
$t->is(isset($form['produits'][$produit_hash2]['has_stock_vci']) && $form['produits'][$produit_hash2]['has_stock_vci']->getValue(), false, "La checkbox de vci du 2ème produit n'est pas coché");

$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['volume_total']), "Le volume total de la DR n'est pas proposé dans le formulaire");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['recolte_nette']), "Le volume de récolte nette de la DR n'est pas proposé dans le formulaire");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['volume_sur_place']), "Le volume sur place de la DR n'est pas proposé dans le formulaire");

$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produit_hash1]['superficie_revendique'] = 10;
$valuesRev['produits'][$produit_hash1]['recolte']['superficie_total'] = 10;
$valuesRev['produits'][$produit_hash2]['superficie_revendique'] = 2;
$valuesRev['produits'][$produit_hash2]['recolte']['superficie_total'] = 300;
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
$t->is(count($form['produits']), 1, "La form a le bon nombre de produit (colonnes)");
$t->is($form['produits'][$produit_hash1]['stock_precedent']->getValue(), 3, "Le stock VCI avant récolte du formulaire est de 3");
$t->is($form['produits'][$produit_hash1]['destruction']->getValue(), $destruction, "Le VCI desctruction est de $destruction");
$t->is($form['produits'][$produit_hash1]['complement']->getValue(), null, "Le VCI en complément est nul");
$t->is($form['produits'][$produit_hash1]['substitution']->getValue(), null, "Le VCI en substitution est nul");
$t->is($form['produits'][$produit_hash1]['rafraichi']->getValue(), null, "Le VCI rafraichi est nul");

$valuesVCI = array(
    'produits' => array(
        $produit_hash1 => array(
            "stock_precedent" => 6,
            "destruction" => 0,
            "substitution" => 0,
            "complement" => 3,
            "rafraichi" => 3,
        ),
    ),
    '_revision' => $drev->_rev,
);

$form->bind($valuesVCI);

$t->ok($form->isValid(), "Le formulaire est valide");

$form->save();

$produit1 = $drev->get($produit_hash1);
$t->is($produit1->vci->stock_precedent, 6, "Le stock VCI avant récolte du produit du doc est de 3");
$t->is($produit1->vci->destruction, null, "Le VCI en destruction du produit du doc est null");
$t->is($produit1->vci->complement, 3, "Le VCI en complément de la DR du produit du doc est de 3");
$t->is($produit1->vci->substitution, 0, "Le VCI en substitution de la DR du produit du doc est de 0");
$t->is($produit1->vci->rafraichi, 3, "Le VCI rafraichi du produit est de 3");
$t->is($produit1->volume_revendique_issu_vci, $produit1->vci->complement + $produit1->vci->substitution + $produit1->vci->rafraichi, "Le volume revendiqué issu du vci est calculé à partir de la répartition vci");
$t->is($produit1->getTheoriticalVolumeRevendiqueIssuRecole(), $produit1->recolte->recolte_nette - $produit1->vci->substitution - $produit1->vci->rafraichi, "Le volume issu de la récolte prend en compte les volumes de vci");

$t->comment("Formulaire de revendication");

if($drev->storeEtape(DrevEtapes::ETAPE_REVENDICATION)) {
    $drev->save();
}

$form = new DRevRevendicationForm($drev);

$defaults = $form->getDefaults();

$t->is(count($form['produits']), count($drev->getProduitsWithoutLots()), "La form à le même nombre de produit que dans la drev");
$t->is($form['produits'][$produit_hash1]['recolte']['volume_total']->getValue(), $produit1->recolte->volume_sur_place, "Le volume total récolté est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['recolte']['recolte_nette']->getValue(), $produit1->recolte->recolte_nette, "La récolté nette de la DR sont initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['recolte']['volume_sur_place']->getValue(), $produit1->recolte->volume_sur_place, "Le volume sur place est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['volume_revendique_issu_recolte']->getValue(), $produit1->recolte->recolte_nette - $produit1->vci->rafraichi - $produit1->vci->substitution, "Le volume revendique issu de la DR est bien calculé et initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['volume_revendique_issu_recolte']->getValue(), $produit1->getTheoriticalVolumeRevendiqueIssuRecole(), "Le volume revendique issu de la DR est bien issu to volume théorique issu de la récolte");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['superficie_total']), "La superficie totale de la DR n'est pas proposé dans le formulaire");


$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produit_hash1]['volume_revendique_issu_recolte'] = 101.1;

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

$t->is($produit1->vci->stock_final, 5, "Le stock VCI après récolte du produit du doc est bon");
$t->is($produit1->vci->stock_final, $produit1->vci->constitue + $produit1->vci->rafraichi, "Le VCI stock après récolte du produit du doc est le même que le calculé");

$drev->cleanDoc();

$habilitation = HabilitationClient::getInstance()->createDoc($viti->identifiant, date('Ymd',strtotime("-1 days")));

if (!$has_habilitation_inao) {
$habilitation->addProduit($produit1->getConfig()->getHash())->updateHabilitation(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::STATUT_HABILITE);
$habilitation->save();
$t->ok($habilitation->isHabiliteFor($produit1->getConfig()->getHash(), HabilitationClient::ACTIVITE_VINIFICATEUR), "L'habilitation a bien enregistré la demande d'habilitation pour le produit1 (".$produit1->getLibelle().") et l'activité vinificateur (".$habilitation->_id.")");
}

$produit1->getConfig()->add('attributs')->add('rendement', 55);
$produit1->getConfig()->add('attributs')->add('rendement_conseille', 45);
$produit1->getConfig()->add('attributs')->add('rendement_vci', 5);
$produit1->getConfig()->add('attributs')->add('rendement_vci_total', 15);
$produit1->getConfig()->clearStorage();

$produit2->getConfig()->add('attributs')->add('rendement', 55);
$produit2->getConfig()->add('attributs')->add('rendement_vci', 5);
$produit2->getConfig()->add('attributs')->add('rendement_vci_total', 15);
$produit2->getConfig()->clearStorage();

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');
$t->is(!isset($erreurs['revendication_incomplete_volume']) ? 0 : count($erreurs['revendication_incomplete_volume']), !DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire(),  "Point blocant : tous les volumes de revendication n'ont pas été rempli (que le 1er produit)");
$t->is($erreurs['revendication_incomplete_superficie'], null, "Pas de point blocant sur le remplissage des superficies de revendication");
$t->is($erreurs['revendication_rendement'], null, "Pas de point blocant sur le rendement de la revendication");
$t->is(!isset($vigilances['revendication_rendement_conseille']) ? 0 : count($vigilances['revendication_rendement_conseille']), 0 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire(), "Point de vigilance sur le dépassement du rendement conseillé de la revendication");
$t->is($erreurs['vci_stock_utilise'], null, "Pas de point blocant sur la repartition du vci");
$t->is($erreurs['vci_rendement_annee'], null, "Pas de point blocant sur le rendement à l'année du vci");
$t->is($vigilances['vci_rendement_total'], null, "Pas de point de vigilance sur le rendement total du vci");
$t->is($erreurs['declaration_volume_l15_complement'], null, "Pas de point bloquant sur le respect de la ligne l15");
$t->is($erreurs['vci_substitue_rafraichi'], null, "Pas de point blocant sur la subsitution ni le rafraichissement du volume de VCI");
$t->is($erreurs['revendication_superficie'], null, "Pas de point blocant sur la superficie declarée sur la DR et la DRev");
$t->is(!isset($vigilances['declaration_habilitation']) ? 0 : count($vigilances['declaration_habilitation']), 0, "Pas de point de vigilance sur l'habilitation du premier produit");

$t->is(!isset($vigilances['declaration_volume_l15']) ? 0 : count($vigilances['declaration_volume_l15']), 1 * !DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire(), "Pas de point vigilance sur le respect de la ligne l15");
$t->is($vigilances['declaration_neant'], null, "Pas de point vigilance sur la declaration neant");
$t->is(!isset($vigilances['declaration_produits_incoherence']) ? 0 : count($vigilances['declaration_produits_incoherence']), 0 + DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire(), "Point vigilance sur les produits declarés sur la DR et pas dans la DRev");
$t->is($vigilances['declaration_surface_bailleur'], null, "Pas de point vigilance sur la repartition de la surface avec le bailleur");
$t->is($vigilances['vci_complement'], null, "Pas de point vigilance sur le complement vci");

$drevControle = clone $drev;
if (!$has_habilitation_inao) {
$habilitation->updateHabilitation($produit1->getConfig()->getHash(), array(HabilitationClient::ACTIVITE_VINIFICATEUR), HabilitationClient::STATUT_RETRAIT);
$habilitation->save();
}
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
$produitControle1->volume_revendique_issu_recolte = 5;

$produitControle2->volume_revendique_issu_recolte = null;

$validation = new DRevValidation($drevControle);

$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->ok(isset($erreurs['revendication_incomplete_volume']) && count($erreurs['revendication_incomplete_volume']) == 1 && $erreurs['revendication_incomplete_volume'][0]->getInfo() == $produitControle2->getLibelleComplet(), "Un point bloquant est levé car les infos de revendications n'ont pas été saisi");
$t->ok(!isset($erreurs['revendication_rendement']), "Un point bloquant n'est pas levé car le rendement sur le revendiqué n'est pas respecté (car en warning)");
$t->ok(isset($vigilances['revendication_rendement_warn']) && count($vigilances['revendication_rendement_warn']) == 1 && $vigilances['revendication_rendement_warn'][0]->getInfo() == $produitControle1->getLibelleComplet(), "Un point de vigilance est levé car le rendement sur le revendiqué n'est pas respecté");
$t->ok(!isset($vigilances['revendication_rendement_conseille']), "Le point de vigilance sur le rendement conseil n'est pas levé car le rendement maximum sur le revendiqué n'est pas respecté");
$t->ok(isset($erreurs['vci_stock_utilise']) && count($erreurs['vci_stock_utilise']) == 1 && $erreurs['vci_stock_utilise'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point bloquant est levé car le vci utilisé n'a pas été correctement réparti");
$t->ok(isset($vigilances['vci_rendement_total']) && count($vigilances['vci_rendement_total']) == 1 && $vigilances['vci_rendement_total'][0]->getInfo() == $produitControle1->getLibelleComplet() , "Un point de vigilance est levé car le stock vci final déclaré ne respecte pas le rendement total");
if (!$has_habilitation_inao) {
    $t->isnt($vigilances['declaration_habilitation'], null, "Des points de vigilences sur les habilitations des deux produits (un en retrait, l'autre non déclaré dans l'habilitation)");
}
$t->is(count($vigilances['declaration_volume_l15']), 1  + 1 * DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire(), "Point vigilance sur le respect de la ligne l15");
$t->is(count($erreurs['declaration_volume_l15_complement']), 1, "Point bloquant sur le respect de la ligne l15");
$t->is(count($erreurs['revendication_superficie']), 1, "Point bloquant sur la superficie declarée sur la DR et la DRev");
$t->is(count($erreurs['vci_substitue_rafraichi']), 1, "VCI rafraichi / subsitue non respect de la ligne l15");
$t->isnt($vigilances['vci_complement'], null, "Pas de point vigilance sur le complement vci");

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

if (!$produitConfigMutage) {
    return;
}
$t->comment("Mutage alcool revendique pour VDN");

$produitMutage = $drev->addProduit($produitConfigMutage->getHash());

$produitMutage->recolte->superficie_total = 100 / $produitConfigMutage->getRendement();
$produitMutage->superficie_revendique = 100 / $produitConfigMutage->getRendement();


$form = new DRevRevendicationForm($drev);

$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produitMutage->getHash()]['volume_revendique_issu_recolte'] = 100;
$valuesRev['produits'][$produitMutage->getHash()]['volume_revendique_issu_mutage'] = 5;

$form->bind($valuesRev);

$t->ok(!isset($form['produits'][$produit_hash1]['volume_revendique_issu_mutage']), "Pas de champs mutage");
$t->ok(isset($form['produits'][$produitMutage->getHash()]['volume_revendique_issu_mutage']), "Champs volume en mutage");

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is($produitMutage->volume_revendique_issu_mutage, $valuesRev['produits'][$produitMutage->getHash()]['volume_revendique_issu_mutage'], "Le volume revendique issu de mutage a été enregsitré");
$produitMutage->volume_revendique_issu_mutage = 0;
$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');

$t->is(count($erreurs['mutage_ratio']), 1, "Point bloquant concernant le volume d'alcool respectant 5% à 10% de la récolte");

$produitMutage->volume_revendique_issu_mutage = 4.99;
$validation = new DRevValidation($drev); $erreurs = $validation->getPointsByCodes('erreur');
$t->is(count($erreurs['mutage_ratio']), 1, "Point bloquant concernant le volume d'alcool respectant 5% à 10% de la récolte");

$produitMutage->volume_revendique_issu_mutage = 5;
$validation = new DRevValidation($drev); $erreurs = $validation->getPointsByCodes('erreur');
$t->ok(!isset($erreurs['mutage_ratio']), "Pas de point bloquant concernant le volume d'alcool respectant 5% à 10% de la récolte");

$vigilances = $validation->getPointsByCodes('vigilance');
$t->is($produitMutage->getVolumeRevendiqueRendement(), 100, "Le volume rendement ne tient pas en compte le volume issu du mutage");
$t->is($produitMutage->getRendementEffectif(), 30, "Le rendement ne tient pas en compte le volume issu du mutage");

$produitMutage->volume_revendique_issu_mutage = 10.42;
$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$t->ok(!isset($erreurs['mutage_ratio']), "Point bloquant concernant le volume d'alcool respectant 5% à 10% de la récolte n'est pas activé sous 10,42");

$produitMutage->volume_revendique_issu_mutage = 10.45;
$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$t->is($produitMutage->getVolumeRevendiqueRendement(), 94.55, "Le volume rendement ne tient pas en compte le volume issu du mutage");
$t->is($produitMutage->getRendementEffectif(), 28.365, "Le rendement ne tient pas en compte le volume issu du mutage");
$t->ok(isset($erreurs['mutage_ratio']), "Point bloquant concernant le volume d'alcool respectant 5% à 10% de la récolte (activé au dessus de 10,42)");
