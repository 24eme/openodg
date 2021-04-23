<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();
$societe = $etablissement->getSociete();

foreach(PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, true) as $piece) {
    if(strpos($piece->id, 'FICHIER-') === false) {
        continue;
    }

    $fichier = FichierClient::getInstance()->find($piece->id);
    $fichier->delete();
}

foreach(DRevClient::getInstance()->getHistory($etablissement->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->validate();
    $drev->validation_odg = date('c');
    $drev->save();
}

$b = new sfTestFunctional(new Browser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null, 'app_facture_emetteur' => $facture_emetteur_test));

$b->get('/documents/'.$etablissement->identifiant);
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatusCode(), 200, 'Page Historique');

$b->click('a[href*="/fichier/upload/"]');
$b->isForwardedTo('fichier', 'upload');
$t->is($b->getResponse()->getStatusCode(), 200, "Page d'upload de document");

$b->deselect('fichier_visibilite')->click('.row-button button[type="submit"]', array('fichier' => array('libelle' => "Fichier déposé par l'admin", 'file' => dirname(__FILE__).'/../../data/dr_douane.csv')))->followRedirect();
$b->isForwardedTo('fichier', 'piecesHistorique');
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

preg_match("|/fichier/get/([^/]+)|", $b->getRequest()->getUri(), $matches);

$fichierId = $matches[1];

$b->get('/fichier/upload/'.$etablissement->identifiant."?fichier_id=".$fichierId);
$b->isForwardedTo('fichier', 'upload');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de modification accessible");

$t->comment('En mode stalker');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('stalker')));
$b->restart();

$b->get("/documents");
$t->is($b->getResponse()->getStatuscode(), 200, "Page d'accueil");

$b->get('/documents/'.$etablissement->identifiant);
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatuscode(), 200, "Page Historique");

$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('.page-header a[href*="/fichier/upload/"]')->getNode(), null, "Bouton \"Ajouter un document\"");
$t->is($c->matchSingle('.list-group a[href*="/fichier/upload/"]')->getNode(), null, "Boutons \"Modifier un document\" absent");

$b->get('/fichier/upload/'.$etablissement->identifiant);
$t->is($b->getResponse()->getStatusCode(), 403, "Page d'upload protégé");

$t->comment('En mode habilitation');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation'), 'app_facture_emetteur' => $facture_emetteur_test));
$b->restart();

$b->get('/documents/'.$etablissement->identifiant);
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatusCode(), 200, 'Page Historique');

$c = new sfDomCssSelector($b->getResponseDom());
$t->ok($c->matchSingle('.page-header a[href*="/fichier/upload/"]')->getNode(), "Bouton \"Ajouter un document\"");
$t->is($c->matchSingle('.list-group a[href*="/fichier/upload/"]')->getNode(), null, "Boutons \"Modifier un document\" absent");

$b->get('/documents/'.$etablissement->identifiant."?categorie=fichier");
$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('a[href*="/piece/get/FICHIER-"]')->getNode(), null, "Aucun fichier");

$b->get('/piece/get/'.$fichierId.'/0')->followRedirect();
$t->is($b->getResponse()->getStatusCode(), 403, "Téléchargement du fichier protégé");

$b->get('/documents/'.$etablissement->identifiant."?categorie=drev");
$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('a[href*="/drev/visualisation"]')->getNode(), null, "Lien vers la visu de la DREV absent");
$b->click('a[href*="/piece/get/DREV-"]')->followRedirect();
$b->isForwardedTo('drev', 'PDF');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du PDF de la DREV");

$b->get('/documents/'.$etablissement->identifiant."?categorie=dr");
$b->click('a[href*="/piece/get/DR-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du CSV de la DR");

$b->get('/fichier/upload/'.$etablissement->identifiant);
$t->is($b->getResponse()->getStatusCode(), 200, "Page d'upload accessible");

$b->click('button[type="submit"]', array('fichier' => array('libelle' => 'Document déposé par un compte ayant le droit habilition', 'file' => dirname(__FILE__).'/../../data/dr_douane.csv')))->followRedirect();
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatusCode(), 200, "Formulaire d'upload d'un document");

$b->get('/documents/'.$etablissement->identifiant.'?categorie=identification');
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatusCode(), 200, "Page historique ayant la catégorie \"Identification\"");

$b->click('a[href*="/piece/get/FICHIER-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du fichier Identification");

preg_match("|/fichier/get/([^/]+)|", $b->getRequest()->getUri(), $matches);

$fichierIdentificationId = $matches[1];

$b->get('/fichier/upload/'.$etablissement->identifiant."?fichier_id=".$fichierIdentificationId);
$t->is($b->getResponse()->getStatusCode(), 403, "Page de modification de ce fichier protégé");

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array(), 'app_facture_emetteur' => $facture_emetteur_test));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societe->getIdentifiant())));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

$b->get('/documents/'.$etablissement->identifiant);
$b->isForwardedTo('fichier', 'piecesHistorique');
$t->is($b->getResponse()->getStatuscode(), 200, "Page Historique");

$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('.page-header a[href*="/fichier/upload/"]')->getNode(), null, "Bouton \"Ajouter un document\"");
$t->is($c->matchSingle('.list-group a[href*="/fichier/upload/"]')->getNode(), null, "Boutons \"Modifier un document\" absent");

$b->get('/fichier/upload/'.$etablissement->identifiant);
$t->is($b->getResponse()->getStatusCode(), 403, "Page d'upload protégé");

$b->get('/documents/'.$etablissement->identifiant."?categorie=fichier");
$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('a[href*="/piece/get/FICHIER-"]')->getNode(), null, "Aucun fichier");

$b->get('/piece/get/'.$fichierId.'/0')->followRedirect();
$t->is($b->getResponse()->getStatusCode(), 403, "Téléchargement du fichier protégé");

$b->get('/documents/'.$etablissement->identifiant."?categorie=drev");
$c = new sfDomCssSelector($b->getResponseDom());
$t->ok($c->matchSingle('a[href*="/drev/visualisation"]')->getNode(), "Lien vers la visu de la DREV");
$b->click('a[href*="/piece/get/DREV-"]')->followRedirect();
$b->isForwardedTo('drev', 'PDF');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du PDF de la DREV");

$b->get('/documents/'.$etablissement->identifiant."?categorie=dr");
$b->click('a[href*="/piece/get/DR-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du CSV de la DR");

$b->get('/documents/'.$etablissement->identifiant."?categorie=identification");
$b->click('a[href*="/piece/get/FICHIER-"]')->followRedirect();
$b->isForwardedTo('fichier', 'get');
$t->is($b->getResponse()->getStatusCode(), 200, "Téléchargement du fichier identifiation");
