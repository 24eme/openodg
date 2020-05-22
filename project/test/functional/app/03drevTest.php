<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();
$societe = $etablissement->getSociete();

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
    if(!$produit->getRendement()) {
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

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
    file_put_contents($csvTmpFile, str_replace(array("%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));


$b = new sfTestFunctional(new sfBrowser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$t->comment("Saisie d'une DRev");

$b->get('/declarations/'.$etablissement->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");

$b->click('a[href*="/drev/creation-papier/"]')->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'exploitation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape exploitation");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'dr');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape dr scrapping");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'drUpload');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape dr upload");

$b->click('button[type="submit"]', array('fichier' => array('file' => $csvTmpFile)))->followRedirect();
$b->isForwardedTo('drev', 'revendicationSuperficie');
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire upload et étape superficie");

unlink($csvTmpFile);

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'vci');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape vci");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('drev', 'revendication');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape volume");

$b->click('button[type="submit"]')->followRedirect();
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
