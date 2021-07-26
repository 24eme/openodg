<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}
$region = "IGP13";
$emetteurs[$region] = array(
    "adresse" => "rue",
    "code_postal" => "cp",
    "ville" => "ville cedex 1",
    "service_facturation" => "Syndicat des vins",
    "telephone" => "00 00 00 00 00 - 00 00 00 00 00",
    "email" => "bonjour@email.fr",
    "responsable" => "responsable",
    "iban" => "iban",
    "tva_intracom" => "tva_intracom",
    "siret" => "siret"
);
sfConfig::set('app_facture_emetteur' , $emetteurs);

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

//Suppression des ChgtDenom précédents
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

//Suppression des factures précédentes
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}

//Suppression des generation précédentes
foreach(GenerationClient::getInstance()->findHistory() as $k => $g) {
    $generation = GenerationClient::getInstance()->find($g->id);
    $generation->delete(false);
}

//Suppression des dégustation précédentes
foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

//Suppression MandatSepa
$mandatSepa = MandatSepaClient::getInstance()->findLastBySociete($socVitiCompte->identifiant);
if($mandatSepa){
    $mandatSepa->delete(true);
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

for ($i=0; $i < 5 ; $i++) {
    $appelation = $appelations[$i];
    foreach($config->getProduits() as $hash => $produit) {
        $p = ${"produit".($i+1)};
        if(!$p && strpos($hash,$appelation)) {
            ${"produit".($i+1)} = $produit;
            break;
        }
    }
}

$campagne = (date('Y')-1)."";
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();

$t->ok(!count($drev->mouvements),"La Drev n'a pas encore de mouvements facturables");

$t->comment("On ajoute 5 lots à la DREV : ");
for ($i=0; $i < 5 ; $i++) {
    $drev->addLot();
    $produit = ${"produit".($i+1)};
    $produit_hash = $produit->getHash();
    if(preg_match("/MED/",$produit_hash)){
        $produit_hash_med = $produit_hash;
    }
    if(preg_match("/D13/",$produit_hash)){
        $produit_hash_d13 = $produit_hash;
    }
    $t->comment("=> ".$produit_hash);
    $drev->lots[$i]->numero_logement_operateur = 'CUVE '.$i;
    $drev->lots[$i]->produit_hash = $produit_hash;
    $drev->lots[$i]->volume = $i+1;
}

$drev->save();
$t->ok(!count($drev->mouvements),"La Drev n'a toujours pas de mouvements facturables, elle n'est pas validée ODG");

$drev->validate();
$drev->save();
$drev->validateOdg();
$drev->save();

$t->ok(count($drev->mouvements),"La Drev a des mouvements facturables, elle est validée ODG");

$mvtDrev = null;
foreach ($drev->getMouvementsFactures() as $mvtsOp) {
    foreach ($mvtsOp as $uniqkey => $mvt) {
        $mvtDrev = $mvt;
        break;
    }
    break;
}

$t->is($mvtDrev->detail_identifiant,$drev->lots[0]->numero_dossier, "Le mouvements de facture a bien le numéro de dossier du lot de DREV");

$t->comment("Vérification du template de facturation");

$templatesFactures = TemplateFactureClient::getInstance()->findAll();
$t->ok(count($templatesFactures),"Il existe au moins un template de facture");
$templateFacture = TemplateFactureClient::getInstance()->findByCampagne($drev->campagne);
$t->ok($templateFacture->_id,"Le template de facture est : $templateFacture->_id");
$t->ok(count($templateFacture->cotisations), "Il y a ".count($templateFacture->cotisations)." cotisation(s) dans la facturation ".$campagne);

$codes_comptables = array();
foreach ($templateFacture->getCotisations() as $cotisName => $cotis) {
    $cotis->code_comptable = 'CODE_COMPTABLE_'.$cotisName;
    $codes_comptables[$cotisName] = $cotis->code_comptable;
}
$templateFacture->save();

$t->comment("Création de la facture par formulaire de génération");

$form = new FactureGenerationForm();
$defaults = $form->getDefaults();
$valuesRev['date_facturation'] = date('d/m/Y');
$valuesRev['date_mouvement'] = date('d/m/Y');
$valuesRev['type_document'] = DRevClient::TYPE_MODEL;
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire est valide");
$gForm = $form->save();
$gForm->arguments->add('modele', $templateFacture->_id);
$gForm->arguments->add('region', strtoupper($application));
$gForm->save();

$socCompte = $viti->getMasterCompte();

$t->ok($gForm, "Une génération de facture a bien été créée");

$generationsids = GenerationClient::getInstance()->getGenerationIdEnAttente();
$t->is(count($generationsids),1, "Il y a une génération de facture en attente");

foreach ($generationsids as $gid) {
    $generation = GenerationClient::getInstance()->find($gid);
    $g = GenerationClient::getInstance()->getGenerator($generation,null,null);
    $g->generate();
}

$facturesSoc = FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant);
$t->is(count($facturesSoc), 1, "Une facture a été générée");

foreach ($facturesSoc as $f) {
  $facture = $f;
}

$t->is($facture->_id, "FACTURE-".$socVitiCompte->identifiant."-".date("Ymd")."01", "ID de la facture");
$t->is($facture->numero_facture,date("Ymd")."01", "Numero de la facture");
$t->is($facture->numero_archive,"00001", "Numéro d'archive de la facture");
$t->is($facture->getNumeroOdg(),$facture->campagne."00001", "Numéro odg de la facture");
$t->is($facture->date_echeance , $facture->date_facturation, "Date d'échéance à récéption");

$keyCotis = null;
foreach ($facture->lignes as $keyCotis => $cotis) {
    if(preg_match("/igp13/", $keyCotis)){
        break;
    }
}

$t->is($facture->lignes->$keyCotis->libelle, "IGP13", "Libellé de la ligne");
$t->ok($facture->lignes->$keyCotis->details[0]->unite, "hl", "Unité du détail");
$t->ok($facture->lignes->$keyCotis->details[0]->libelle, "Libellé du détail de la ligne");
$t->like($facture->lignes->$keyCotis->details[0]->libelle, "/N° ".$drev->numero_archive."/", "Libellé du détail de la ligne avec le numéro d'archive");
$t->is($facture->lignes->$keyCotis->details[0]->getLibelleComplet(), $facture->lignes->$keyCotis->libelle." ".$facture->lignes->$keyCotis->details[0]->libelle, "Libellé complet de la ligne");

$t->is($facture->lignes->$keyCotis->produit_identifiant_analytique, $templateFacture->cotisations->$keyCotis->code_comptable, "Le code comptable est bien renseigné");

$t->comment("Modificatrice DREV, on ajoute un lot volume 100 et on supprime le dernier lot");

$drevM01 = $drev->generateModificative();
$drevM01->save();
$lotToRemove = null;
$volumeFacturable = 0;
foreach ($drevM01->lots as $num => $lot) {
    $lotToRemove = $num;
    $volumeFacturable = $lot->volume * -1;
}
$volumeFacturable += 100;

$drevM01->lots->remove($num);
$drevM01Lot = $drevM01->addLot();
$drevM01Lot->numero_logement_operateur = 'CUVE M01';
$drevM01Lot->produit_hash = $produit_hash_d13;
$drevM01Lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$drevM01Lot->volume = 100;

$drevM01->validate();
$drevM01->save();

$drevM01->validateOdg();
$drevM01->save();

$t->ok(count($drevM01->mouvements),"La Drev Modificatrice a des mouvements facturables");

$mvtDrevM01 = null;
foreach ($drevM01->getMouvementsFactures() as $mvtsOp) {
    foreach ($mvtsOp as $uniqkey => $mvt) {
        $mvtDrevM01 = $mvt;
        break;
    }
    break;
}

$t->is($volumeFacturable, $mvtDrevM01->quantite, "Le volume facturable M01 est bien le simple ajout de lot +100 et le retrait du lot de -5 = +95");
$t->is($mvtDrevM01->detail_identifiant, $drevM01->numero_archive, "Le numéro de dossier du mouvement est celui de la Modificatrice");
$t->isnt($mvtDrevM01->detail_identifiant, $drev->numero_archive, "Le numéro de dossier du mouvement n'est pas le même que celui de la DRev de base");

$form = new FactureGenerationForm();
$defaults = $form->getDefaults();
$valuesRev['date_facturation'] = date('d/m/Y');
$valuesRev['date_mouvement'] = date('d/m/Y');
$valuesRev['type_document'] = DRevClient::TYPE_MODEL;
$form->bind($valuesRev);
$gForm = $form->save();
$gForm->arguments->add('modele', $templateFacture->_id);
$gForm->arguments->add('region', strtoupper($application));
$gForm->save();

$generationsids = GenerationClient::getInstance()->getGenerationIdEnAttente();

foreach ($generationsids as $gid) {
    $generation = GenerationClient::getInstance()->find($gid);
    $g = GenerationClient::getInstance()->getGenerator($generation,null,null);
    $g->generate();
}


$facturesSoc = FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant);
$t->is(count($facturesSoc), 2, "La facture pour la modificatrice a bien été générée");

$t->comment("Test d'avoir");
$avoir = null;
foreach ($facturesSoc as $f) {
    $avoir = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($f);
    break;
}

$t->ok($avoir, "Il y a maintenant 1 avoir");

$t->comment("On annule la validation ODG de la Drev");

$drev = DRevClient::getInstance()->find($drev->_id);
$drev->validation_odg = null;
$drev->save();

$mouvementsFactures = MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($socVitiCompte->getSociete());
foreach ($mouvementsFactures as $mvtFact) {
    $t->isnt($mvtFact->id, $drev->_id, "Les mouvements facturables ne sont pas ceux de la DRev");
}

$t->comment("On revalide ODG la DRev");
$drev = DRevClient::getInstance()->find($drev->_id);
$drev->validateOdg();
$drev->save();

$mouvementsFactures = MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($socVitiCompte->getSociete());
foreach ($mouvementsFactures as $mvtFact) {
    $t->isnt($mvtFact->id, $drev->_id, "Les mouvements facturables ne sont toujours pas ceux de la DRev");
}

$t->comment("Ajout de paiements");
$t->is(count($facture->paiements),0,"Le nombre de paiements de la facture est 0 ");
$paiementForm = new FacturePaiementsMultipleForm($facture);
$valuesPaiement = array('_revision' => $facture->_rev);
$valuesPaiement['paiements'][0]['montant'] = 1;
$valuesPaiement['paiements'][0]['date'] = "01/01/".($drev->getCampagne()+1);
$valuesPaiement['paiements'][0]['type_reglement'] = FactureClient::FACTURE_PAIEMENT_CHEQUE;
$paiementForm->bind($valuesPaiement);
$t->ok($paiementForm->isValid(), "Le formulaire est valide");

$facture = $paiementForm->save();
$t->is(count($facture->paiements),1,"Le nombre de paiements de la facture est 1 ");
$t->is($facture->paiements[0]->date, ($drev->getCampagne()+1).'-01-01', "Le paiement a la bonne date au format iso");
$t->is($facture->paiements[0]->versement_comptable, false,"Le paiement n'a pas été versé comptablement");
$t->is($facture->date_paiement, ($drev->getCampagne()+1).'-01-01', "La date de paiement est bien appliquée");

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

$t->ok(count($facture->getLignes()),"Il y a bien plusieurs lignes dans la facture");
$montantHt = $facture->total_ht;
$montantTtc = $facture->total_ttc;
$t->ok(($montantHt > 0),"Le montant HT est supérieur à 0");
$t->ok(($montantTtc > 0),"Le montant TTC est supérieur à 0");

$degustation = new Degustation();
$degustation->lieu = "Test — Test Facturation";
$degustation->date = date('Y-m-d')." 14:00";
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 5, "5 lots en attentes de dégustation");
$degustation->save();

$degustation->setLots($lotsPrelevables);
$degustation->save();

$t->ok(!count($degustation->mouvements), "La dégustation n'étant pas anonymisée il n'y a pas encore de mouvements");

$t->comment("On met les lots à table");
foreach ($degustation->getLots() as $lot) {
    $lot->numero_table = 1;
}
$degustation->save();

$degustation->anonymize();

$t->ok(!count($degustation->mouvements), "La dégustation est anonymisée mais il n'y a toujours pas des mouvements car les mouvements sont présents lors d'une 2nd dégustation");


$t->comment("Conformité des lots");
$degustation->lots[0]->statut = Lot::STATUT_CONFORME;
$degustation->lots[1]->statut = Lot::STATUT_NONCONFORME;
$degustation->save();

$lot = $degustation->lots[1];
$t->ok($lot->getMouvement(Lot::STATUT_NONCONFORME), "Le lot ".$lot->unique_id." est non conforme");
$t->is($lot->getNombrePassage(), 1, "C'est le premier passage du lot");

$nonconformeId = $lot->unique_id;

$lot->redegustation();
$degustation->save();

$t->ok($lot->getMouvement(Lot::STATUT_NONCONFORME), "Le lot est toujours non conforme");

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 1, "Il y a 1 lot prélevable");

$lotPrelevable = current($lotsPrelevables);


$t->comment('Deuxième degustation');
$degustation2 = new Degustation();
$form = new DegustationCreationForm($degustation2);
$values = array('date' => date("d/m/Y"), 'time' => "18:00", 'lieu' => "Test — Test Facturation 2nd Degustation");
$form->bind($values);
$degustation2 = $form->save();

$t->comment("Sélection du lot");
$l = $degustation2->addLot($lot);
$l->preleve = true;
$degustation2->save();

$lot2 = $degustation2->lots[0];


$t->is($lot2->unique_id, $nonconformeId, "Le lot non conforme est bien celui de la degustation 2 ");
$degustation2->save();

$t->ok(!count($degustation2->getMouvementsFactures()), "Degustation 2 : on a toujours pas de mouvements car on est pas anonymisée");
$degustation2->anonymize();
$degustation2->save();

$t->is(count($degustation2->getMouvementsFactures()), 1, "Degustation 2 : on a bien le mouvement de facture de la redegustation $degustation2->_id");

$mvtRedegust = null;
foreach ($degustation2->getMouvementsFactures() as $mvtsOp) {
    foreach ($mvtsOp as $uniqkey => $mvt) {
        $mvtRedegust = $mvt;
        break;
    }
    break;
}

$t->is($mvtRedegust->date, $degustation2->getDateFormat(), "Date du mouvement");
$t->is($mvtRedegust->date_version, $degustation2->getDateFormat(), "Date version du mouvement");
$t->is($mvtRedegust->facture, 0, "Mouvement non facturé");
$t->is($mvtRedegust->facturable, 1, "Mouvement facturable");
$t->is($mvtRedegust->detail_identifiant,$lot2->numero_dossier, "Degustation 2 : Le mouvements de facture a bien le numéro de dossier du lot redégusté");

$t->comment("Conformité du lot de la degustation 2 ");
$degustation2->lots[0]->statut = Lot::STATUT_CONFORME;
$degustation2->save();

$t->comment('Création chgt Deno vers D13');

$dateDeno = $campagne.'-12-15 11:00:00';

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);

$lot = current($lots);

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lot, $dateDeno, null);
$chgtDenom->constructId();
$chgtDenom->save();

$chgtDenom->changement_produit_hash = $produit_hash_d13;
$chgtDenom->changement_volume = $lot->volume-0.5;
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenom->generateLots();
$chgtDenom->generateMouvementsLots(1);
$chgtDenom->save();

$t->ok(!count($chgtDenom->getMouvementsFactures()),"Changement Deno : on a pas de mouvement de facture parce qu'on est pas valideOdg");

$chgtDenom->validate();
$chgtDenom->validateOdg();
$chgtDenom->save();

$t->ok(!count($chgtDenom->getMouvementsFactures()),"Changement Deno : on a pas de mouvement de facture parce qu'on fait un chgt vers D13");

$t->comment('Création chgt Deno vers MED');

$dateDeno = $campagne.'-12-25 12:00:00';

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);
$lot = current($lots);

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lot, $dateDeno, null);
$chgtDenom->constructId();
$chgtDenom->save();

$chgtDenom->changement_produit_hash = $produit_hash_med;
$chgtDenom->changement_volume = $lot->volume-0.1;
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenom->generateLots();
$chgtDenom->generateMouvementsLots(1);
$chgtDenom->save();

$t->ok(!count($chgtDenom->getMouvementsFactures()),"Changement Deno : on a pas de mouvement de facture parce qu'on est pas valideOdg");

$chgtDenom->validate();
$chgtDenom->validateOdg();
$chgtDenom->save();

$t->ok(count($chgtDenom->getMouvementsFactures()),"Changement Deno : on a un mouvement de facture car on est valideOdg et MED");

$mvtChgtDenom = null;
foreach ($chgtDenom->getMouvementsFactures() as $mvtsOp) {
    foreach ($mvtsOp as $uniqkey => $mvt) {
        $mvtChgtDenom = $mvt;
        break;
    }
    break;
}

$t->is($mvtChgtDenom->detail_identifiant, $lot->numero_dossier."ab" ,"Changement Deno :le mouvement de facture du changement a le numéro de dossier correspondant à celui du 2nd chgtDenom");

$mouvementsFacturables = MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($socVitiCompte);
$f = FactureClient::getInstance()->createDocFromView($mouvementsFacturables, $socVitiCompte, date('Y-m-d'), null, $region, $templateFacture);

$keyLignes = array_keys($f->getLignes()->toArray());
$keyLignesSorted = $keyLignes;
sort($keyLignesSorted);
$t->is($keyLignes,$keyLignesSorted, "Dans les factures les noms de cotisations servent pour le trie quel que soit l'ordre de sortie des mouvements de facture ou de l'ordre dans le template");
