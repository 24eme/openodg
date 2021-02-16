<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13' && $application != 'igpardeche') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(5);

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

$produit1 = current($produits);
$produit1->superficie_revendique = 208;
$produit1->volume_revendique_issu_recolte = 66;
$produit_hash1 = $produit1->getHash();

next($produits);
$produit2 = current($produits);
$produit2->superficie_revendique = 0.8601;
$produit2->volume_revendique_issu_recolte= 60.31;
$produit_hash2 = $produit2->getHash();

next($produits);
$produit3 = current($produits);
$produit3->superficie_revendique = 0.81;
$produit3->volume_revendique_issu_recolte = 57;
$produit_hash3 = $produit3->getHash();

next($produits);
$produit4 = current($produits);
$produit4->superficie_revendique = 0.1;
$produit4->volume_revendique_issu_recolte = 2.23;
$produit_hash4 = $produit4->getHash();

next($produits);
$produit5 = current($produits);
$produit5->superficie_revendique = 0.2;
$produit5->volume_revendique_issu_recolte = 6.21;
$produit_hash5 = $produit5->getHash();


$drev->save();
$t->comment("Produits, volumes revendiqués et superficies qui seront à facturer");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet()." (sup=".$produit1->superficie_revendique."ha,vol=".$produit1->volume_revendique_total.")");
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet()." (sup=".$produit2->superficie_revendique."ha,vol=".$produit2->volume_revendique_total.")");
$t->comment("%libelle_produit_3% = ".$produit3->getLibelleComplet()." (sup=".$produit3->superficie_revendique."ha,vol=".$produit3->volume_revendique_total.")");
$t->comment("%libelle_produit_4% = ".$produit4->getLibelleComplet()." (sup=".$produit4->superficie_revendique."ha,vol=".$produit4->volume_revendique_total.")");
$t->comment("%libelle_produit_5% = ".$produit5->getLibelleComplet()." (sup=".$produit5->superficie_revendique."ha,vol=".$produit5->volume_revendique_total.")");

$t->ok(!count($drev->mouvements),"La Drev n'a pas encore de mouvements facturables");

$drev->validate();
$drev->validateOdg();
$drev->save();

$t->ok(count($drev->mouvements),"La Drev a maintenant des mouvements facturables");

$templatesFactures = TemplateFactureClient::getInstance()->findAll();
$t->ok(count($templatesFactures),"Il existe plusieurs template de facture");

$cm = new CampagneManager(date('m-d'),CampagneManager::FORMAT_PREMIERE_ANNEE);
$uniqueTemplateFactureName = FactureConfiguration::getinstance()->getUniqueTemplateFactureName($cm->getCurrentPrevious());

$templateFactureAttendu = "TEMPLATE-FACTURE-".strtoupper(sfConfig::get('sf_app')) ."-".$drev->campagne;

$t->is($uniqueTemplateFactureName,$templateFactureAttendu,"Le template de facture est : $uniqueTemplateFactureName");


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
      continue;
    }

    if(in_array("ChgtDenom",$detail->docs->toArray(0,1))){
      $cptCotisChgtDenom++;
    }
    if(in_array("DR",$detail->docs->toArray(0,1))){
      $cptCotisDr++;
    }

  }
}

$cptFactureDetails = 0;
foreach ($facture->getLignes() as $ligne) {
  foreach ($ligne->details as $d) {
  $cptFactureDetails++;
  }
}

$t->is($cptFactureDetails,$cptCotisDrev+$cptCotisDr,"Il y a bien $cptFactureDetails lignes de facture : DRev=$cptCotisDrev et DR=$cptCotisDr définies");

foreach(DegustationClient::getInstance()->getHistory(1, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $d) {
    $degustation = $d;
}

$t->is($cptFactureLigneChgtDenom, 0, "Il n'y a pas de lignes de facture ChgtDenom");




var_dump($degustation->_id);
