<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();
$societe = $etablissement->getSociete();

$application = getenv('APPLICATION');

$b = new sfTestFunctional(new Browser());
$t = $b->test();

if (!DRevConfiguration::getInstance()->isModuleEnabled()){
    $t->pass('No DREV for '.$application);
    return;
}

$has_etape_lot = false;
$has_produit_lot = false;
$has_aoc = true;

if ($application == 'igp13') {
    $has_etape_lot = true;
    $has_produit_lot = true;
    $has_aoc = false;
}
if ($application == 'loire') {
    $has_etape_lot = true;
    $has_produit_lot = false;
}


foreach(DRevClient::getInstance()->getHistory($etablissement->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, true) as $piece) {
    if(strpos($piece->id, 'DR-') === false) {
        continue;
    }

    $fichier = FichierClient::getInstance()->find($piece->id);
    $fichier->delete();
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

if (RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash())) {
    if (!sfConfig::get('app_region') && RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash())) {
        sfConfig::set('app_region', RegionConfiguration::getInstance()->getOdgRegion($produit1->getHash()));
    }
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../../data/dr_douane.csv');
$has_vci = $produit1->hasRendementVCI();

if (!$has_vci) {
    $csvContentTemplate = preg_replace('/Volume complémentaire individuel .VCI..;;2;0/', 'Volume complémentaire individuel (VCI)";;0;0', $csvContentTemplate);
}


$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').'.csv';
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($etablissement->cvi, $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));


$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' =>  array(myUser::CREDENTIAL_ADMIN), 'app_facture_emetteur' => $facture_emetteur_test));

$t->comment("Saisie d'une DRev");

$b->get('/declarations/'.$etablissement->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");

$b->click('a[href*="/drev/creation"]')->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'exploitation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape exploitation");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'dr');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape dr scrapping");

$b->click('input[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'drUpload');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape dr upload");

$b->click('button[type="submit"]', array('fichier' => array('file' => $csvTmpFile)))->followRedirect();
if ($b->getResponse()->getStatuscode() == 200) {
    $b->isForwardedTo('drev', 'revendicationSuperficie');
    $t->is($b->getResponse()->getStatuscode(), 200, "Formulaire upload et étape superficie");

    $b->click('button[type="submit"]')->followRedirect();
}else{
    $b->isForwardedTo('drev', 'revendicationSuperficie');
    $t->is($b->getResponse()->getStatuscode(), 302, "Formulaire upload et étape superficie");
    $b->followRedirect();
}
unlink($csvTmpFile);
$b->isForwardedTo('drev', 'vci');

if ($has_vci) {
    $t->is($b->getResponse()->getStatuscode(), 200, "Page VCI en 200");
    $b->click('button[type="submit"]')->followRedirect();
}else{
    $t->is($b->getResponse()->getStatuscode(), 302, "Sans VCI, on est redirigé");
    $b->followRedirect();
}

if($has_etape_lot) {
    $b->isForwardedTo('drev', 'lots');
    if ($has_produit_lot) {
        $t->is($b->getResponse()->getStatuscode(), 200, "Étape lot");
        $b->click('button[id="lots_continue"]')->followRedirect();
    }else{
        $t->is($b->getResponse()->getStatuscode(), 302, "Étape lot sans produit");
        $b->followRedirect();
    }
}

if($has_aoc) {
    $b->isForwardedTo('drev', 'revendication');
    $t->is($b->getResponse()->getStatuscode(), 200, "Étape volume");
    $b->click('button[type="submit"]')->followRedirect();
}else{
    $t->is($b->getResponse()->getStatuscode(), 302, "Étape volume est passée");
    $b->followRedirect();
}

$b->isForwardedTo('drev', 'validation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape validation");
$data_form = array('date_depot' => date('d/m/Y'));
if (preg_match('/engagement_DEPASSEMENT_CONSEIL/', $b->getResponse()->getContent())) {
    $data_form['engagement_DEPASSEMENT_CONSEIL'] = true;
}
$b->click('button[id="submit-confirmation-validation"]', array('validation' => $data_form));
$t->is($b->getResponse()->getStatuscode(), 302, "Étape validation");
$b->followRedirect();
$b->isForwardedTo('drev', 'visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de confirmation");

preg_match("|/drev/visualisation/([^/]+)|", $b->getRequest()->getUri(), $matches);
$drevId = $matches[1];

$b->click('a#lien-telechargement-pdf-drev');
$b->isForwardedTo('drev', 'PDF');
$t->is($b->getResponse()->getStatuscode(), 200, "Pdf de la drev");

$t->comment('En mode habilitation');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation')));
$b->restart();

$b->get('/declarations/'.$etablissement->identifiant);
$t->is($b->getResponse()->getStatuscode(), 403, "Page declaration protégé");
$b->resetCurrentException();

$b->get('/drev/visualisation/'.$drevId);
$t->is($b->getResponse()->getStatuscode(), 403, "Visu de la DRev protégé");
$b->resetCurrentException();

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societe->getIdentifiant())));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

$b->get('/declarations/'.$etablissement->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");

$b->get('/drev/visualisation/'.$drevId);
$b->isForwardedTo('drev', 'visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Visu de la DRev");
