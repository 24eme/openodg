<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_functionnal') as $k => $v) {
    if (preg_match('/ETABLISSEMENT-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $etablissement = EtablissementClient::getInstance()->find($m[1]);
      $etablissement->delete();
    }
}

$societe = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_societe')->getSociete();
$societeAnnexe = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_societe_2')->getSociete();

$b = new sfTestFunctional(new sfBrowser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH'));

$societeIdentifiant = $societe->getIdentifiant();

$t->comment("Création et modification d'un établissement ");

$b->get('/etablissement/'.$societeIdentifiant.'/nouveau');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de création d'un établisement");
$b->click('#btn_valider')->followRedirect();
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire de création d'un établissement");

preg_match("|/etablissement/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);
$etablissementIdentifiant = $matches[1];

$etablissement = EtablissementClient::getInstance()->find($etablissementIdentifiant);
$compteEtablissement = $etablissement->getMasterCompte();
$compteEtablissement->addTag('test', 'test_functionnal');
$compteEtablissement->addTag('test', 'test_functionnal_etablissement');
$compteEtablissement->save();

$b->get('/etablissement/'.$societeAnnexe->getIdentifiant().'/nouveau')->click('#btn_valider')->followRedirect();
preg_match("|/etablissement/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);

$etablissementAnnexe = EtablissementClient::getInstance()->find($matches[1]);
$compteEtablissementAnnexe = $etablissementAnnexe->getMasterCompte();
$compteEtablissementAnnexe->addTag('test', 'test_functionnal');
$compteEtablissementAnnexe->addTag('test', 'test_functionnal_etablissement_2');
$compteEtablissementAnnexe->save();

$b->get('/etablissement/'.$etablissementIdentifiant.'/chai-ajout');
$b->click('#btn_valider')->followRedirect();
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire d'ajout d'un chai");

$t->comment('En mode habilitation');

$b->get('/logout');

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation')));
$b->restart();

if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()) {
    $b->get('/etablissement/'.$etablissementIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Visualisation établissement accessible");
    $b->isForwardedTo('etablissement', 'visualisation');
    testVisualisationLimite($b, $societeIdentifiant, $etablissementIdentifiant);

    $b->get('/etablissement/'.$etablissementAnnexe->getIdentifiant().'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation d'un établissement d'une autre société accessible");
} else {
    $b->get('/etablissement/'.$etablissementIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation établissement protégé");
}

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societeIdentifiant)));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");


if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()) {
    $b->get('/etablissement/'.$etablissementIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation de l'établissement accessible");
    $b->isForwardedTo('etablissement', 'visualisation');
    testVisualisationLimite($b, $societeIdentifiant, $etablissementIdentifiant);
    $b->get('/etablissement/'.$etablissementAnnexe->getIdentifiant().'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'un établissement d'une autre société protégée");
} else {
    $b->get('/etablissement/'.$etablissementIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation établissement protégée");
}

function testVisualisationLimite($b, $societeIdentifiant, $etablissementIdentifiant) {

    $t = $b->test();

    $c = new sfDomCssSelector($b->getResponseDom());
    $t->is($c->matchSingle('a[href*="/modification"]')->getNode(), null, "Bouton \"Editer\" absent");
    $t->is($c->matchSingle('a[href*="/switchStatus"]')->getNode(), null, "Bouton \"Archiver\" absent");
    $t->is($c->matchSingle('a[href*="/switchEnAlerte"]')->getNode(), null, "Bouton \"Mettre en alerte\" absent");
    $t->is($c->matchSingle('a[href*="/chai-ajout"]')->getNode(), null, "Bouton \"Ajouter un chai\" absent");
    $t->is($c->matchSingle('a[href*="/chai-modification/"]')->getNode(), null, "Bouton \"Modifier un chai\" absent");
    $t->is($c->matchSingle('a[href*="/relation-ajout"]')->getNode(), null, "Bouton \"Ajouter une relation\" absent");
    $t->is($c->matchSingle('a[href*="/compte/search"]')->getNode(), null, "Liens vers la recherche absent");
    $t->is($c->matchSingle('a[href*="/nouveau"]')->getNode(), null, "Liens vers les boutons d'ajout absent");
    $t->is($c->matchSingle('form.form_ajout_tag')->getNode(), null, "Form d'ajout d'un tag absent");

    $b->get('/etablissement/'.$societeIdentifiant.'/nouveau');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page ajouter un établissement protégée");

    $b->get('/etablissement/'.$etablissementIdentifiant.'/modification');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de modification d'un établissement protégée");

    $b->get('/etablissement/'.$etablissementIdentifiant.'/switchStatus');
    $t->is($b->getResponse()->getStatuscode(), 403, "Action archiver un établissement protégé");

    $b->get('/etablissement/'.$etablissementIdentifiant.'/chai-ajout');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page ajouter un chai dans un établissement protégé");

    $b->get('/etablissement/'.$etablissementIdentifiant.'/chai-modification/0');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page modifier un chai dans un établissement protégée");

    $b->get('/etablissement/'.$etablissementIdentifiant.'/relation-ajout');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page ajouter une relation dans un établissement protégée");

}
