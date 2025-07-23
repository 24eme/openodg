<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
if ( ! DRevConfiguration::getInstance()->hasEtapesAOC() ) {
    $t = new lime_test(1);
    $t->ok(true, "pass IGPxAOP");
    return;
}

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$periode = (date('Y')-1)."";
$campagne = $periode."-".($periode + 1);
$drev_date = $periode."-10-01";

$should_have_mouvements = (TemplateFactureClient::getInstance()->findAll());

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
foreach(DegustationClient::getInstance()->getHistory(100, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DegustationClient::getInstance()->deleteDoc(DegustationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}

foreach(ArchivageAllView::getInstance()->getDocsByTypeAndCampagne('Revendication', $campagne, 0, 99999, "%05d") as $r) {
    $doc = acCouchdbManager::getClient()->find($r->id);
    $doc->delete();
}

$config = ConfigurationClient::getConfigurationByCampagne($campagne);
$produitconfig_aop = null;
$produitconfig_igp = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(strpos($produitconfig->getCertification()->getKey(), 'AO') === 0 && !$produitconfig_aop) {
        $produitconfig_aop = $produitconfig->getCepage();
        continue;
    } elseif(strpos($produitconfig->getCertification()->getKey(), 'IGP') === 0 && !$produitconfig_igp) {
        $produitconfig_igp = $produitconfig->getCepage();
        continue;
    }
    if (!$produitconfig_aop || !$produitconfig_igp) {
        continue;
    }

    break;
}
if ( ! $produitconfig_aop || ! $produitconfig_igp ) {
    $t = new lime_test(1);
    $t->comment($config->_id);
    $t->pass("no DREV AOC and IGP for ".$application);
    return;
}
$t = new lime_test(126);

$t->comment('campagne '.$campagne);
$t->comment($config->_id);

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($viti->cvi, $produitconfig_aop->getCodeDouane(), $produitconfig_aop->getLibelleComplet(), $produitconfig_igp->getCodeDouane(), $produitconfig_igp->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/dr_douane.csv");
$t->comment("%libelle_produit_1% = ".$produitconfig_aop->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produitconfig_igp->getLibelleComplet());

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$t->comment('Etape superficie');
$t->ok(!DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE, $drev), "On a accès à l'étape superficie");

$drev->addProduit($produitconfig_igp->getHash());

$t->comment("Étape lots");
$t->ok(!DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_LOTS, $drev), "On a accès à l'étape lots");
$t->ok(DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION, $drev), "On a pas accès à l'étape AOC revendication");

$drevConfig = DRevConfiguration::getInstance();
$t->ok(count($drevConfig->getSpecificites()), "La configuration retourne bien des spécificités");
$t->is($drev->getConfiguration()->_id, $config->_id, "Recupère le bon catalogue produit ($configuration_id)");

if($drev->storeEtape(DrevEtapes::ETAPE_LOTS)) {
    $drev->save();
}
$drev->addLot();
$form = new DRevLotsForm($drev);

$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $drev->_rev,
);
$valuesRev['lots']['0']['numero_logement_operateur'] = "Cuve A";
$valuesRev['lots']['0']['volume'] = 1008.2;
$valuesRev['lots']['0']['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$valuesRev['lots']['0']['destination_date'] = '30/11/'.$periode;
$valuesRev['lots']['0']['produit_hash'] = $produitconfig_igp->getHash();
if($drevConfig->hasSpecificiteLot()){
    $valuesRev['lots']['0']['specificite'] = "";
}
$valuesRev['lots']['0']['millesime'] = $periode;
$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();
$drev->validate();
$drev->validateOdg();
$drev->save();

$t->comment($drev->_id);
$t->is(count($drev->lots), 1, "Seulement le lot non vide est conservé");

$t->is(count($drev->mouvements_lots), 1, 'Il y a bien des mouvements de lots');

$t->comment("Nouvelle drev pour déclarer de l'AOC sans DR");

$drev_m01 = $drev->generateModificative();
$drev_m01->save();

$t->comment($drev_m01->_id);

$t->ok(DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION, $drev_m01), "On a pas accès à l'étape AOC revendication");

$t->comment("Etape superficie");

$produitAop = $drev_m01->addProduit($produitconfig_aop->getHash());

$form = new DRevSuperficieForm($drev_m01, array('disabled_dr' => true));
$valuesRev = [
    '_revision' => $drev_m01->_rev,
    'produits' => [$produitAop->getHash() => [ "superficie_revendique" => 1]]
];
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->comment("Etape volume");

$t->ok(!DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION, $drev_m01), "On a accès à l'étape AOC revendication");

$form = new DRevRevendicationForm($drev_m01, array('disabled_dr' => true));
$valuesRev = [
    '_revision' => $drev_m01->_rev,
    'produits' => [$produitAop->getHash() => [ "volume_revendique_issu_recolte" => 40]]
];
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$drev_m01->validate();
$drev_m01->validateOdg();
$drev_m01->save();

$drev_m02 = $drev_m01->generateModificative();
$drev_m02->save();

$t->comment("Récupération des données à partir de la DR");

$dr = DRClient::getInstance()->createDoc($viti->identifiant, $periode);
$dr->setLibelle("DR $periode issue de Prodouane (Papier)");
$dr->setDateDepot("$periode-12-15");
$dr->save();
$dr = DRClient::getInstance()->find($dr->_id);
$dr->storeFichier($csvTmpFile);
$dr->save();
unlink($csvTmpFile);

$drev_m02->resetAndImportFromDocumentDouanier();
$drev_m02->save();
$t->comment($drev_m02->_id);
$t->is(count($drev_m02->declaration), 2, "Après la DR, on retrouve les deux produits dans déclaration");
$t->ok($drev_m02->exist($produitconfig_aop->getHash()), "La DR a permis d'avoir le produit AOC");
$t->ok($drev_m02->exist($produitconfig_igp->getHash()), "Le produit IGP existe toujours");

$t->ok(!DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_LOTS, $drev_m02), "Après l'import de la DR, on a accès à l'étape lots");
$t->ok(!DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION, $drev_m02), "Après l'import de la DR, on a accès à l'étape AOC revendication");

$produits = $drev_m02->getProduits();
$produit1 = current($drev_m02->get($produitconfig_aop->getCepage()->getHash())->getProduits());
$produit_hash1 = $produit1->getCepage()->getHash();
next($produits);
$produit2 = end($drev_m02->get($produitconfig_igp->getCepage()->getHash())->getProduits());
$produit_hash2 = $produit2->getCepage()->getHash();
$t->is($produit_hash1, $produitconfig_aop->getHash(), "Le premier produit est le produit AOC");
$t->is($produit_hash2, $produitconfig_igp->getHash(), "Le premier produit est le produit IGP");
