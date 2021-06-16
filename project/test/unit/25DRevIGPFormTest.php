<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "pass AOC");
    return;
}

$t = new lime_test(124);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$periode = (date('Y')-1)."";
$campagne = $periode."-".($periode + 1);

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

foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $cd = ChgtDenomClient::getInstance()->find($k);
    $cd->delete(false);
}
foreach(DegustationClient::getInstance()->getHistory(100, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DegustationClient::getInstance()->deleteDoc(DegustationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}

foreach(ArchivageAllView::getInstance()->getDocsByTypeAndCampagne('Revendication', $campagne, 0, 99999, "%05d") as $r) {
    $doc = acCouchdbManager::getClient()->find($r->id);
    $doc->delete();
}

$config = ConfigurationClient::getCurrent();
$produitconfig1 = null;
$produitconfig2 = null;
$produitconfig_horsDR = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(!$produitconfig1) {
        $produitconfig1 = $produitconfig->getCepage();
        continue;
    } elseif(!$produitconfig2) {
        $produitconfig2 = $produitconfig->getCepage();
        continue;
    } elseif(!$produitconfig_horsDR) {
        $produitconfig_horsDR = $produitconfig->getCepage();
        continue;
    }

    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($viti->cvi, $produitconfig1->getCodeDouane(), $produitconfig1->getLibelleComplet(), $produitconfig2->getCodeDouane(), $produitconfig2->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/dr_douane.csv");
$t->comment("%libelle_produit_1% = ".$produitconfig1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produitconfig2->getLibelleComplet());

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();
$t->comment($drev->_id);
$t->comment("Récupération des données à partir de la DR");

$dr = DRClient::getInstance()->createDoc($viti->identifiant, $periode);
$dr->setLibelle("DR $periode issue de Prodouane (Papier)");
$dr->setDateDepot("$periode-12-15");
$dr->save();
$dr->storeFichier($csvTmpFile);
$dr->save();

$drev->importFromDocumentDouanier();
$drev->save();

$t->is(count(array_keys($drev->getProduits())), 2 + (!DRevConfiguration::getInstance()->hasDenominationAuto()) * 2, "La DRev a repris le bon nombre (". (2 + (!DRevConfiguration::getInstance()->hasDenominationAuto()) * 2).") de produits du csv de la DR");

$produits = $drev->getProduits();


$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();

next($produits);
$produit2 = current($produits);
$produit_hash2 = $produit2->getHash();

$drev->save();

$t->is($produit1->recolte->superficie_total, 2.4786 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "La superficie total de la DR pour le produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_sur_place, 105.18 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Le volume sur place pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->usages_industriels_total, 3.03 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Les usages industriels la DR pour ce produit ".$produit1->getLibelleComplet()." sont OK");
$t->is($produit1->recolte->recolte_nette, 104.1 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "La récolte nette de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_total, 105.18 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Le volume total de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");

$t->comment('Formulaire de revendication des superficies');

if($drev->storeEtape(DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE)) {
    $drev->save();
}

$form = new DRevSuperficieForm($drev);

$defaults = $form->getDefaults();

$t->is(count(array_keys($form['produits']->getValue())), count(array_keys($drev->getProduits())), "le formulaire a le même nombre de produit (".count(array_keys($drev->getProduits())).") que la DRev (declaration)");

$t->is($form['produits'][$produit_hash1]['recolte']['superficie_total']->getValue(), $produit1->recolte->superficie_total, "La superficie totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), $produit1->superficie_revendique, "La superficie revendique est initialisé dans le form");
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

$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is($produit1->recolte->superficie_total, $valuesRev['produits'][$produit_hash1]['recolte']['superficie_total'], "La superficie total de la DR est enregistré");
$t->is($produit1->superficie_revendique, $valuesRev['produits'][$produit_hash1]['superficie_revendique'], "La superficie revendique est enregistré");

$t->comment("Étape lots");
$drevConfig = DRevConfiguration::getInstance();
$t->is($drevConfig->hasSpecificiteLot(), true, "La configuration a des spécificités de Lots");
$t->ok(count($drevConfig->getSpecificites()), "La configuration retourne bien des spécificités");

if($drev->storeEtape(DrevEtapes::ETAPE_LOTS)) {
    $drev->save();
}

$form = new DRevLotsForm($drev);
$defaults = $form->getDefaults();

$t->is(count($form['lots']), 2, "autant de lots que de colonnes dans le DR");
$t->is($form['lots']['0']['produit_hash']->getValue(), $produit1->getParent()->getHash(), 'lot 1 : un produit est déjà sélectionné');
$t->is($form['lots']['0']['millesime']->getValue(), $periode, 'lot 1 : le millesime est prérempli');

$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $drev->_rev,
);
$valuesRev['lots']['0']['numero_logement_operateur'] = "Cuve A";
$valuesRev['lots']['0']['volume'] = 1008.2;
$valuesRev['lots']['0']['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$valuesRev['lots']['0']['destination_date'] = '30/11/'.$periode;
if($drevConfig->hasSpecificiteLot()){
  $t->is($valuesRev['lots']['0']['specificite'], 'UNDEFINED', "Pas de spécificité choisie donc par defaut aucune");
}
$valuesRev['lots']['0']['specificite'] = "";

$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is(count($drev->lots), 2, "Les deux lots sont conservés dans la DRev");
$t->is($drev->lots[0]->numero_logement_operateur, $valuesRev['lots']['0']['numero_logement_operateur'], "Le numéro de cuve du lot 1 est bien enregistré");
$t->is($drev->lots[0]->volume, $valuesRev['lots']['0']['volume'], "Le volume du lot 1 est bien enregistré");
$t->is($drev->lots[0]->destination_type, $valuesRev['lots']['0']['destination_type'], "Le type de destination lot 1 est bien enregistré");
$t->is($drev->lots[0]->destination_date, join('-', array_reverse(explode('/', $valuesRev['lots']['0']['destination_date']))), "La date de destination du lot 1 est bien enregistré");
$t->is($drev->lots[0]->produit_hash, $valuesRev['lots']['0']['produit_hash'], "La hash du produit du lot 1 est bien enregistré");
$t->is($drev->lots[0]->produit_libelle, $produit1->getLibelle(), "Le libellé du produit du lot 1 est bien enregistré");
$t->is($drev->lots[0]->millesime, $valuesRev['lots']['0']['millesime'], "Le millesime du lot 1 est bien enregistré");
$t->is($drev->lots[0]->specificite, "", "La spécificité Aucune est enregistrée");
$t->is($drev->lots[0]->id_document_provenance, null, "Le lot n'a pas de provenance");
$t->is($drev->lots[0]->id_document_affectation, null, "Le lot n'a pas de fils");
$t->ok($drev->lots[0]->isAffectable(), "Le lot est affectable");
$t->ok(!$drev->lots[0]->isAffecte(), "Le lot n'est pas affecté");
$t->is($drev->lots[0]->getTypeProvenance(), null, "pas de provenance");

if($drev->storeEtape(DrevEtapes::ETAPE_VALIDATION)) {
    $drev->save();
}

$t->comment("Étape validation");

$drev->lots[0]->specificite = Lot::SPECIFICITE_UNDEFINED;
$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$t->ok(array_key_exists('lot_incomplet', $erreurs), "Point bloquant sur la spécificité");

$drev->lots[0]->specificite = "";

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->ok(!array_key_exists('lot_incomplet', $erreurs), "Pas de point bloquant sur la spécificité");
$t->ok(array_key_exists('lot_volume_total_depasse', $erreurs), "Le volume total du lot est dépassé");
$t->is(count($erreurs), 1, 'Il y a un point bloquant');
$t->is($vigilances, null, "un point de vigilance à la validation");

$drev->lots[0]->volume = 8.2;
$drev->lotsImpactRevendication();
$drev->save();

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->is($erreurs, null, "pas d'erreur à la validation");
$t->is($vigilances, null, "un point de vigilance à la validation");

$t->comment("DRev validée");
$drev->validate();
$drev->save();

$t->is(count($drev->lots), 1, "La DRev validée contient uniquement le lot saisi");
$t->is($drev->lots[0]->specificite, "", "La spécificité du lot est vide");
$t->ok(!$drev->mouvements_lots->exist($drev->identifiant), "La DRev non validée ODG ne contient pas de mouvement de lots");

$t->comment("DRev Validée ODG");

$drev->validateOdg();
$drev->save();

$t->is(count($drev->mouvements_lots->get($drev->identifiant)->toArray(true, false)), 3, "La DRev validée contient le mouvement correspondant au lot saisi");
$t->is($drev->numero_archive, "00001", "Numéro d'archive de la DRev à 00001");

$t->comment("Génération d'un mouvement à partir d'un lot");

$mouvement = $drev->mouvements_lots->get($drev->identifiant)->getFirst();
$lot = $mouvement->getLot();

$t->is($lot->id_document_provenance, null, "Le lot n'a pas de provenance");
$t->is($lot->id_document_affectation, null, "Le lot n'a pas de fils");
$t->is(count($lot->getMouvements()), 3, "Le lot à 3 mouvements");
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot à un mouvement affectable");
$t->ok($lot->getMouvement(Lot::STATUT_REVENDIQUE), "Le lot à un mouvement revendique");
$t->is($mouvement->getUnicityKey(), $lot->getUnicityKey()."-".KeyInflector::slugify(Lot::STATUT_REVENDIQUE), "Clé unique des mouvements");
$t->is($mouvement->date, $lot->date, "Mouvement date");
$t->is($mouvement->statut, Lot::STATUT_REVENDIQUE, "Mouvement statut");
$t->is($mouvement->numero_dossier, $lot->numero_dossier, "Mouvement numero de dossier");
$t->is($mouvement->numero_archive, $lot->numero_archive, "Mouvement numero d'archive");
$t->is($mouvement->detail, null, "Mouvement détail");
$t->is($mouvement->libelle, $lot->getLibelle(), "Mouvement libellé");
$t->is($mouvement->version, $lot->getVersion(), "Mouvement version");
$t->is($mouvement->document_ordre, "01", "Mouvement numéro d'ordre");
$t->is($mouvement->document_type, DRevClient::TYPE_MODEL, "Mouvement document type");
$t->is($mouvement->document_id, $drev->_id, "Mouvement document id");
$t->is($mouvement->lot_unique_id, $lot->getUnicityKey(), "Mouvement lot unique id");
$t->is($mouvement->lot_hash, $lot->getHash(), "Mouvement lot has");
$t->is($mouvement->declarant_identifiant, $drev->identifiant, "Mouvement declarant identifiant");
$t->is($mouvement->declarant_nom, $drev->declarant->raison_sociale, "Mouvement declarant raison sociale");
$t->is($mouvement->campagne, $drev->getCampagne(), "Mouvement campagne");

$t->comment("Test de la synthèse des lots (visu/validation/_recap)");

$synthese = $drev->summerizeProduitsLotsByCouleur();
$t->is(count(array_keys($synthese)), 3, "On a bien toutes les couleurs de la DR en synthèse des lots + une ligne total");
$t->ok(isset($synthese[$drev->lots[0]->getCouleurLibelle()]), "On a bien la couleur du produit 1 en synthèse des lots");
$t->is($synthese[$drev->lots[0]->getCouleurLibelle()]['volume_lots'], 8.2, "On a le bon volume total en synthèse des lots");
$t->is($synthese[$drev->lots[0]->getCouleurLibelle()]['volume_max'], 208.2, "On a le bon volume issu de la dr en synthèse des lots");
$t->is($synthese[$drev->lots[0]->getCouleurLibelle()]['volume_restant'], 200, "On a le bon volume restant en synthèse des lots");

$t->comment("Historique de mouvements");
$t->is(count($lot->getMouvements()), 3, "3 mouvements pour le lot");
$t->ok($lot->getMouvement(Lot::STATUT_REVENDIQUE), 'Le lot est revendiqué');
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTABLE), 'Le lot est affectable');


$t->comment("Génération d'un document intermédiaire pour tester les numéros d'archive");
$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $drev->getCampagne(), $drev->getDate());
$conditionnement->save();
$conditionnement->validate();
$conditionnement->validateOdg();
$conditionnement->save();

$t->is($conditionnement->numero_archive, "00002", "Numéro d'archive du conditionnement à 00002");

$t->comment("Modificatrice ".$drev->_id."-M01");
$drev_modif = $drev->generateModificative();
$drev_modif->validate();
$drev_modif->validateOdg();
$drev_modif->save();
$drev_modif = $drev->findMaster();
$t->is($drev_modif->_id, $drev->_id.'-M01', "La modification a l'identifiant attendu");
$t->is(count($drev_modif->lots[0]->getMouvements()), 0, "La modificatrice n'a pas de mouvements pour ce lot");
$t->is($drev_modif->numero_archive, "00003", "Numéro d'archive de la DRev à 00003");

$t->comment("Suppression de lots");
$drev_modif->remove('lots');
$drev_modif->add('lots');
$drev_modif->validate();
$drev_modif->validateOdg();
$drev_modif->save();
$drev = $drev_modif->getMother();
$t->is(count($drev_modif->lots), 0, "Le Lot de la DRev modificatrice est correctement supprimé");
$t->ok(!$drev_modif->mouvements_lots->exist($drev_modif->identifiant), "Aucun mouvement de lot dans la modificatrice");
$t->is(count($drev->lots[0]->getMouvements()), 1, "Un seul mouvement pour le lot supprimé");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_REVENDICATION_SUPPRIMEE), "Mouvement revendication suprimée");

$t->comment("Dévalidation de ".$drev->_id."-M01");
$drev_modif = $drev->findMaster();
$drev_modif->devalidate();
$drev_modif->save();
$drev = $drev_modif->getMother();
$t->is(count($drev->lots[0]->getMouvements()), 3, "3 mouvements pour le lot");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot de la drev d'origine est affectable");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_REVENDIQUE), "Le lot de la drev d'origine est revendiqué");

$drev_modif = $drev->findMaster();
$drev_modif->validate();
$drev_modif->validateOdg();
$drev_modif->save();
$drev = $drev_modif->getMother();
$t->is(count($drev->lots[0]->getMouvements()), 1, "Un seul mouvement pour le lot supprimé");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_REVENDICATION_SUPPRIMEE), "Mouvement revendication suprimée");

$t->comment("Suppression de la drev modif ".$drev_modif->_id);
$drev_modif = $drev->findMaster();
$drev_modif->delete();
$drev = DRevClient::getInstance()->find($drev->_id);
$t->is(count($drev->lots[0]->getMouvements()), 3, "3 mouvements pour le lot");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot de la drev d'origine est affectable");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_REVENDIQUE), "Le lot de la drev d'origine est revendiqué");

$t->comment("Ajout de lots");

$drev = DRevClient::getInstance()->find($drev->_id);
$drev_modif = $drev->generateModificative();

if($drev_modif->storeEtape(DrevEtapes::ETAPE_LOTS)) {
    $drev_modif->save();
}

$drev_modif->addLot();
$drev_modif->addLot();
$form = new DRevLotsForm($drev_modif);
$defaults = $form->getDefaults();
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $drev_modif->_rev,
);

$valuesRev['lots']['1']['numero_logement_operateur'] = "Cuve B";
$valuesRev['lots']['1']['volume'] = 1;
$valuesRev['lots']['1']['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$valuesRev['lots']['1']['destination_date'] = '30/11/'.$periode;
$valuesRev['lots']['1']['produit_hash'] = $produitconfig2->getHash();
$valuesRev['lots']['1']['millesime'] = date('Y') - 1;
$valuesRev['lots']['1']['specificite'] = '';

$valuesRev['lots']['2'] = $valuesRev['lots']['1'];
$valuesRev['lots']['2']['numero_logement_operateur'] = "Cuve C";
$valuesRev['lots']['2']['produit_hash'] = $produitconfig_horsDR->getHash();
$valuesRev['lots']['1']['specificite'] = '';

$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$errors = null;
foreach ($form->getErrorSchema() as $key => $err) {
    if ($key) {
        $errors[$key] = $err->getMessage();
    }
}
$t->is($errors, null, "Pas d'erreur dans le formulaire validé");
$form->save();
$t->is($drev_modif->lots[1]->numero_logement_operateur, $valuesRev['lots']['1']['numero_logement_operateur'], "Le numéro de cuve du lot 2 est bien enregistré");
$t->is($drev_modif->lots[1]->volume, $valuesRev['lots']['1']['volume'], "Le volume du lot 2 est bien enregistré");
$t->is($drev_modif->lots[1]->produit_libelle, $produitconfig2->getLibelleComplet(), "Le libellé du produit du lot 2 est bien enregistré");
$t->is($drev_modif->lots[1]->millesime, $valuesRev['lots']['1']['millesime'], "Le millesime du lot 2 est bien enregistré");
$t->is($drev_modif->lots[2]->numero_logement_operateur, $valuesRev['lots']['2']['numero_logement_operateur'], "Le numéro de cuve du lot 3 est bien enregistré");
$t->is($drev_modif->lots[2]->volume, $valuesRev['lots']['2']['volume'], "Le volume du lot 3 est bien enregistré");
$t->is($drev_modif->lots[2]->produit_libelle, $produitconfig_horsDR->getLibelleComplet(), "Le libellé du produit du lot 3 est bien enregistré");
$t->is($drev_modif->lots[2]->millesime, $valuesRev['lots']['2']['millesime'], "Le millesime du lot 3 est bien enregistré");

//$drev_modif->remove('/lots/0');
$drev_modif->save();
$t->is(count($drev_modif->lots), 3, "l'ajout des deux produit a bien impacté le nombre de lots");

if($drev_modif->storeEtape(DrevEtapes::ETAPE_VALIDATION)) {
    $drev_modif->save();
}
$validation = new DRevValidation($drev_modif);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');

$t->is($erreurs, null, "pas d'erreur");
$t->is(count($vigilances), 1, "un point de vigilances");
$t->ok(isset($vigilances['lot_igp_inexistant_dans_dr_warn']), "le point vigilance indique que le produit du 2d lot ne fait pas partie de la DR comme attendu");

$form = new DRevLotsForm($drev_modif);
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $drev_modif->_rev,
);
$valuesRev['lots']['2']['produit_hash'] = $produitconfig1->getHash();
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire de changement de nom du produit est valide");
$form->save();

$validation = new DRevValidation($drev_modif);
$erreurs = $validation->getPointsByCodes('erreur');
$vigilances = $validation->getPointsByCodes('vigilance');
$t->is($vigilances, null, "après le changement de produit hors DR, plus de point de vigilances");
$t->is($erreurs, null, "après le changement de produit hors DR, toujours pas d'erreur");

$drev_modif->validate();
$drev_modif->save();
$t->is(count($drev_modif->lots), 3, "Après la validation, le nombre de lots n'a pas changé");
$t->is($drev_modif->lots[0]->produit_libelle, $produitconfig1->getLibelleComplet(), "Après la validation, le lot 1 n'a pas changé");
$t->is($drev_modif->lots[1]->produit_libelle, $produitconfig2->getLibelleComplet(), "Après la validation, le lot 2 n'a pas changé");
$t->is($drev_modif->lots[2]->produit_libelle, $produitconfig1->getLibelleComplet(), "Après la validation, le lot 3 n'a pas changé");

$lot = $drev_modif->lots[0];

$t->is($lot->version, 'M01', 'Le mouvement a le bon numéro de version');
$t->is($lot->produit_hash, $produitconfig1->getHash(), 'Le mouvement a le bon hash');
$t->is($lot->declarant_identifiant, $drev_modif->identifiant, 'Le mouvement a le bon identifiant de déclarant');
$t->is($lot->declarant_nom, $drev->declarant->raison_sociale, 'Le mouvement a le bon nom de déclarant');

$drev_modif2 = $drev_modif->generateModificative();

$drev_modif2->remove('/lots/0');
$t->is($drev_modif2->lots[1]->produit_libelle, $produitconfig2->getLibelleComplet(), "Après la suppression du lot 1, le 1er lot qui vient d'être ajouté est à la bonne place");
$t->is($drev_modif2->lots[2]->produit_libelle, $produitconfig1->getLibelleComplet(), "Après la suppression du lot 1, le 2d lot qui vient d'être ajouté est à la bonne place");
$drev_modif2->validate();
$drev_modif2->save();
$t->is($drev_modif2->lots[0]->produit_libelle, $produitconfig2->getLibelleComplet(), "Après la validation de la suppression du lot 1, le 1er lot est à la bonne place");
$t->is($drev_modif2->lots[1]->produit_libelle, $produitconfig1->getLibelleComplet(), "Après la validation de la suppression du lot 1, le 2d lot est à la bonne place");

$lot = $drev_modif2->lots[1];

$t->is($lot->version, 'M02', 'Le mouvement a le bon numéro de version');
$t->is($lot->produit_hash, $produitconfig1->getHash(), 'Le mouvement a le bon hash');
