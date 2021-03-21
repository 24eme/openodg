<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$socVitiCompte = $viti->getSociete()->getMasterCompte();

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

//Suppression des factures précédentes
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}

foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

// Selection des produits
$path = dirname(__FILE__).'/../data/facturation_produits_'.$application.'.csv';

if(!file_exists($path)){
  $t = new lime_test(1);
  $t->ok(true, "Test disabled => pas de fichier de produit");
  exit;
}

$csvProduits = str_getcsv(file_get_contents($path));
$appelations = explode(';',$csvProduits[0]);

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
$produit3 = null;
$produit4 = null;
$produit5 = null;
$i = 0;
$appelation = $appelations[0];
foreach($config->getProduits() as $hash => $produit) {
    if($produit->getRendement() <= 0 || !$appelation) {
        continue;
    }
    if(!$produit1 && !strpos($hash,$appelation)) {
        $produit1 = $produit;
        $appelation = $appelations[$i++];
        continue;
    } elseif(!$produit2 && !strpos($hash,$appelation)) {
        $produit2 = $produit;
        $appelation = $appelations[$i++];
        continue;
    }elseif(!$produit3 && !strpos($hash,$appelation)) {
        $produit3 = $produit;
        $appelation = $appelations[$i++];
        continue;
    }elseif(!$produit4 && !strpos($hash,$appelation)) {
        $produit4 = $produit;
        $appelation = $appelations[$i++];
        continue;
    }elseif(!$produit5 && !strpos($hash,$appelation)) {
        $produit5 = $produit;
        $appelation = $appelations[$i++];
        break;
    }
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane_facturation.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(
                               array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%","%code_inao_3%", "%libelle_produit_3%","%code_inao_4%", "%libelle_produit_4%","%code_inao_5%", "%libelle_produit_5%"),
                               array($viti->cvi, $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet(),$produit3->getCodeDouane(), $produit3->getLibelleComplet(),$produit4->getCodeDouane(), $produit4->getLibelleComplet(),$produit5->getCodeDouane(), $produit5->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/dr_douane_facturation.csv");

$campagne = (date('Y')-1)."";
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->comment("Récupération des données à partir de la DR");

$dr = DRClient::getInstance()->createDoc($viti->identifiant, $campagne);
$dr->setLibelle("DR $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier($csvTmpFile);

/* A placer dans la génération mouvements (selon la conf?) */
$dr->generateDonnees();
$dr->save();

$t->comment("Création Drev à partir de la DR");

$drev->importFromDocumentDouanier();
$drev->save();
$t->comment($drev->_id);

$produits = $drev->getProduits();

$valeurs_superficie_revendique =   array(10,1,20,50,80);
$valeurs_revendique_issu_recolte = array(500,50,1000,2500,4000);

for ($i=1; $i <= 5; $i++) {

  $p = current($produits);
  $p->superficie_revendique = $valeurs_superficie_revendique[$i];
  $p->volume_revendique_issu_recolte = $valeurs_revendique_issu_recolte[$i];
  ${"produit_hash".$i} = $p->getHash();
  ${"produit".$i} = $p;
  next($produits);
}


$drev->save();
$t->comment("Produits, volumes revendiqués et superficies qui seront à facturer");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet()." (sup=".$produit1->superficie_revendique."ha,vol=".$produit1->volume_revendique_total.")");
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet()." (sup=".$produit2->superficie_revendique."ha,vol=".$produit2->volume_revendique_total.")");
$t->comment("%libelle_produit_3% = ".$produit3->getLibelleComplet()." (sup=".$produit3->superficie_revendique."ha,vol=".$produit3->volume_revendique_total.")");
$t->comment("%libelle_produit_4% = ".$produit4->getLibelleComplet()." (sup=".$produit4->superficie_revendique."ha,vol=".$produit4->volume_revendique_total.")");
$t->comment("%libelle_produit_5% = ".$produit5->getLibelleComplet()." (sup=".$produit5->superficie_revendique."ha,vol=".$produit5->volume_revendique_total.")");

$t->ok(!count($drev->mouvements),"La Drev n'a pas encore de mouvements facturables");

$drev->addLot();
$drev->lots[0]->volume = 1;
$drev->lots[1] = clone $drev->lots[0];
$drev->lots[1]->volume = 2;
$drev->validate();
$drev->validateOdg();
$drev->save();

$t->ok(count($drev->mouvements),"La Drev a maintenant des mouvements facturables");

$templatesFactures = TemplateFactureClient::getInstance()->findAll();
$t->ok(count($templatesFactures),"Il existe plusieurs template de facture");

$cm = new CampagneManager(date('m-d'),CampagneManager::FORMAT_PREMIERE_ANNEE);
$uniqueTemplateFactureName = FactureConfiguration::getinstance()->getUniqueTemplateFactureName($cm->getCurrentPrevious());

$templateFactureAttendu = TemplateFactureClient::getInstance()->findByCampagne($drev->campagne);

$t->is($uniqueTemplateFactureName,$templateFactureAttendu->_id,"Le template de facture est : $uniqueTemplateFactureName");


$form = new FacturationDeclarantForm(array(), array('modeles' => $templatesFactures,'uniqueTemplateFactureName' => $uniqueTemplateFactureName));
$defaults = $form->getDefaults();

$valuesRev['date_facturation'] = "01/01/".($drev->getCampagne()+1);
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire est valide");

$templateFacture = TemplateFactureClient::getInstance()->find($uniqueTemplateFactureName);

$socCompte = $viti->getMasterCompte();
$generation = FactureClient::getInstance()->createFactureByTemplateWithGeneration($templateFacture, $socCompte->_id, $valuesRev['date_facturation'], null, $templateFacture->arguments->toArray(true, false));
$g = $generation->save();

$t->ok($g, "Une génération de facture a bien été créée");

$facturesSoc = FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant);
$t->is(count($facturesSoc), 1, "Une facture a été générée");

foreach ($facturesSoc as $f) {
  $facture = $f;
}

$t->ok(count($templateFacture->cotisations), "Il y a ".count($templateFacture->cotisations)." cotisation(s) dans la facturation ".$campagne);


// Affichage Cotisations
$cptCotisTotal = 0;
$cptCotisDrev = 0;
$cptCotisDr = 0;
$cptCotisChgtDenom = 0;

foreach ($templateFacture->cotisations as $c_name => $c) {
  $t->comment("Cotisation = ".$c_name);
  foreach ($c->details as $d_name => $detail) {
    $regles = "tarif=".$detail->prix." tva=".$detail->tva;
    $regles = ($detail->exist("unite"))? $regles." sur=".$detail->unite : $regles;
    $regles = $regles." docs=".implode(",",$detail->docs->toArray(0,1))." fct=".$detail->callback;
    $regles = ($detail->exist("callback_parameters"))? $regles." filtre=".implode(",",$detail->callback_parameters->toArray(0,1)) : $regles;

    $t->comment("### ".$d_name." ".$regles);

    if(in_array("DRev",$detail->docs->toArray(0,1))){
      $cptCotisDrev++;
    }
    if(in_array("ChgtDenom",$detail->docs->toArray(0,1))){
      $cptCotisChgtDenom++;
    }
    if(in_array("DR",$detail->docs->toArray(0,1))){
      $cptCotisDr++;
    }
    $cptCotisTotal++;

  }
}

$t->ok(count($facture->getLignes()),"Il y a bien plusieurs lignes dans facture");
$montantHt = $facture->total_ht;
$montantTtc = $facture->total_ttc;
$t->ok(($montantHt > 0),"Le montant HT est supérieur à 0");
$t->ok(($montantTtc > 0),"Le montant TTC est supérieur à 0");

$degustation = new Degustation();
$degustation->lieu = "Test — Test Facturation";
$degustation->date = date('Y-m-d')." 14:00";
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 2, "2 lots en attentes de dégustation");
$degustation->save();

$degustation->setLots($lotsPrelevables);

$t->comment("Conformité des lots");
$degustation->lots[0]->statut = Lot::STATUT_CONFORME;
$degustation->lots[1]->statut = Lot::STATUT_NONCONFORME;
$degustation->save();

$lot = $degustation->lots[1];
$t->ok($lot->getMouvement(Lot::STATUT_NONCONFORME), "Le lot ".$lot->unique_id." est non conforme");
$t->is(MouvementLotView::getInstance()->getNombrePassage($lot), 1, "C'est le premier passage du lot");

$nonconformeId = $lot->unique_id;

$lot->redegustation();
$degustation->save();

$t->ok($lot->getMouvement(Lot::STATUT_NONCONFORME), "Le lot est toujours non conforme");


$t->comment('Deuxième degustation');
$degustation2 = new Degustation();
$form = new DegustationCreationForm($degustation2);
$values = array('date' => date("d/m/Y"), 'time' => "18:00", 'lieu' => "Test — Test Facturation 2nd Degustation");
$form->bind($values);
$degustation2 = $form->save();

$t->comment("Sélection des lots");
$form = new DegustationPrelevementLotsForm($degustation2);
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation2->_rev,
);
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;
$form->bind($valuesRev);
$form->save();

$lot2 = $degustation2->lots[0];

$t->is($lot2->unique_id, $nonconformeId, "Le lot non conforme est bien celui de la degustation 2 ");
$degustation2->save();

$degustation2->remove('mouvements');
$degustation2->generateMouvementsFactures();
$degustation2->save();
$t->is(count($degustation2->getMouvementsFactures()), 1, "On a bien 1 mouvement de facture");
