<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();
$societe = $etablissement->getSociete();

foreach(DRevClient::getInstance()->getHistory($etablissement->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$produit_hash = null;

foreach(ConfigurationClient::getCurrent()->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    $produit_hash = $produit->getHash();
    break;
}

$b = new sfTestFunctional(new sfBrowser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$t->comment("Saisie d'une DRev");

$b->get('/declarations/'.$etablissement->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");

$b->click('a[href*="/drev/creation-papier"]')->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'exploitation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape exploitation");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'dr');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape dr scrapping");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'drUpload');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape dr upload");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'revendicationSuperficie');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape superficie");

$b->click('#popupForm button[type="submit"]', array('drev_revendication_ajout_produit' => array('hashref' => $produit_hash)))->followRedirect();
$b->isForwardedTo('drev', 'revendicationSuperficie');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape superficie après ajout d'un produit");

$b->click('button[type="submit"]', array("drev_superficie" => array("produits" => array("/declaration/certifications/AOP/genres/TRANQ/appellations/CDR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT/DEFAUT" => array("recolte" => array("superficie_total" => 100), "superficie_revendique" => 100)))))->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'revendication');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape volume");

$b->click('button[type="submit"]', array("drev_produits" => array("produits" => array("/declaration/certifications/AOP/genres/TRANQ/appellations/CDR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT/DEFAUT" => array("recolte" => array("volume_total" => 50, "volume_sur_place" => 50, "recolte_nette" => 49, "vci_constitue" => 0), "volume_revendique_issu_recolte" => 49)))))->followRedirect();
$b->isForwardedTo('drev', 'validation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape validation");

$b->click('button[type="submit"]', array('validation' => array('date' => date('d/m/Y'))))->followRedirect();
$b->isForwardedTo('drev', 'visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de confirmation");

preg_match("|/drev/visualisation/([^/]+)|", $b->getRequest()->getUri(), $matches);
$drevId = $matches[1];

$t->comment('En mode habilitation');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation')));
$b->restart();

$b->get('/declarations/'.$etablissement->identifiant);
$t->is($b->getResponse()->getStatuscode(), 403, "Page declaration protégé");

$b->get('/drev/visualisation/'.$drevId);
$t->is($b->getResponse()->getStatuscode(), 403, "Visu de la DRev protégé");

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
