<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();

foreach(PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, true) as $piece) {
    if(strpos($piece->id, 'FICHIER-') === false) {
        continue;
    }

    $fichier = FichierClient::getInstance()->find($piece->id);
    $fichier->delete();
}

$b = new sfTestFunctional(new sfBrowser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$b->get('/documents/'.$etablissement->identifiant);
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatusCode(), 200, 'Page Historique');

$b->click('a[href*="/fichier/upload/"]');
$b->isForwardedTo('fichier', 'upload');
$t->is($b->getResponse()->getStatusCode(), 200, "Page d'upload de document");

$b->click('button[type="submit"]', array('fichier' => array('libelle' => 'DI', 'file' => dirname(__FILE__).'/../../data/dr_douane.csv', 'categorie' => 'Identification')))->followRedirect();
$b->isForwardedTo('fichier', 'upload');
$t->is($b->getResponse()->getStatusCode(), 200, "Formulaire d'upload d'un document");

$b->get('/documents/'.$etablissement->identifiant);
$b->click('a[href*="/piece/get/DREV-"]')->followRedirect();
$b->isForwardedTo('drev', 'PDF');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du PDF de la DREV");

$b->get('/documents/'.$etablissement->identifiant);
$b->click('a[href*="/piece/get/DR-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du CSV de la DR");

$b->get('/documents/'.$etablissement->identifiant);
$b->click('a[href*="/piece/get/FICHIER-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du fichier uploadé");

$t->comment('En mode habilitation');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation')));
$b->restart();

$b->get('/documents/'.$etablissement->identifiant);
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatusCode(), 200, 'Page Historique');

$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('a[href*="/fichier/upload/"]')->getNode(), null, "Bouton \"Ajouter un document\" et \"Modification de document\" absents");
$t->ok($c->matchSingle('a[href*="/piece/get/FICHIER-"]')->getNode(), "Ligne du fichier uploadé");
$t->ok($c->matchSingle('a[href*="/piece/get/DREV-"]')->getNode(), "Ligne de la DREV");
$t->ok($c->matchSingle('a[href*="/piece/get/DR-"]')->getNode(), "Ligne de la DR");
$t->is($c->matchSingle('a[href*="/drev/visualisation"]')->getNode(), null, "Lien vers la visu de la DREV absent");

$b->get('/documents/'.$etablissement->identifiant);
$b->click('a[href*="/piece/get/FICHIER-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du fichier uploadé");

$b->get('/documents/'.$etablissement->identifiant);
$b->click('a[href*="/piece/get/DREV-"]')->followRedirect();
$b->isForwardedTo('drev', 'PDF');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du PDF de la DREV");

$b->get('/documents/'.$etablissement->identifiant);
$b->click('a[href*="/piece/get/DR-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du CSV de la DR");

$b->get('/fichier/upload/'.$etablissement->identifiant);
$t->is($b->getResponse()->getStatusCode(), 403, "Page d'upload protégé");
