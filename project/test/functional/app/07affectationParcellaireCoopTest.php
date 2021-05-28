<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissementCoop = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement_coop')->getEtablissement();

$application = getenv('APPLICATION');

if ($application != 'provence') {
    $b = new sfTestFunctional(new Browser());
    $t = $b->test();
    return;
}

$b = new sfTestFunctional(new Browser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null, 'app_facture_emetteur' => $facture_emetteur_test));

$t->comment("Saisie des affectations parcellaires des apporteurs d'une d'une cave coop");

$b->get('/declarations/'.$etablissementCoop->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");

$b->click('a#btn_affection_parcellaire_coop');
$b->isForwardedTo('parcellaireAffectationCoop', 'create');
$t->is($b->getResponse()->getStatuscode(), 200, "Création du document");

$b->click('button#btn_creation_affection_parcellaire_coop')->followRedirect();
$b->isForwardedTo('parcellaireAffectationCoop', 'apporteurs');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape apporteurs");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('parcellaireAffectationCoop', 'liste');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape liste");

$b->click('a.btn_saisie_affectation_parcellaire');
$b->isForwardedTo('parcellaireAffectationCoop', 'saisie');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape saisie d'une affectation parcellaire");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('parcellaireAffectationCoop', 'liste');
$t->is($b->getResponse()->getStatuscode(), 200, "Retour à l'étape liste");

$b->click('a#btn_etape_suivante');
$b->isForwardedTo('parcellaireAffectationCoop', 'validation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape validation");

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array(), 'app_facture_emetteur' => $facture_emetteur_test));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societe->getIdentifiant())));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

$b->get('/degustation');
$t->is($b->getResponse()->getStatuscode(), 403, "Accueil des dégustations interdite");

$t->comment('En mode non connecté');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array(), 'app_facture_emetteur' => $facture_emetteur_test));
$b->restart();

$b->get('/degustation');
$b->isForwardedTo('auth', 'login');
$t->is($b->getResponse()->getStatuscode(), 200, "Redirection sur la page de login");

$b->get($uriConformiteProtege);
$b->isForwardedTo('auth', 'login');
$t->is($b->getResponse()->getStatuscode(), 200, "Le PDF de conformités par url classique est protégé");

$b->get($uriConformiteAuthentifiante);
$b->isForwardedTo('degustation', 'getCourrierWithAuth');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "Le PDF de conformités par url authentifiant est accessible");
