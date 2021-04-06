<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_functionnal') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
      $soc->delete();
    }
}

$b = new sfTestFunctional(new Browser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$t->comment("Création d'une société");

$b->post('/societe-creation', array('societe-creation' => array("raison_sociale" => 'Societe TESTFUNCTIONNAL '.uniqid())))->followRedirect()->followRedirect()->followRedirect();
$t->is($b->getResponse()->getStatuscode(), '200', 'Société créé');
$t->like($b->getRequest()->getUri(), '|/societe/[^/]+/modification|', "Page de modification");

$b->click('button#btn_valider')->followRedirect();
$t->is($b->getResponse()->getStatuscode(), '200', 'Formulaire de modification validé');
$t->like($b->getRequest()->getUri(), '|/societe/[^/]+/visualisation|', "Page de visualisation");

preg_match("|/societe/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);

$societeIdentifiant = $matches[1];

$societe = SocieteClient::getInstance()->find($societeIdentifiant);
$compteSociete = $societe->getMasterCompte();
$compteSociete->addTag('test', 'test_functionnal');
$compteSociete->addTag('test', 'test_functionnal_societe');
$compteSociete->addInGroupes('test', 'testeurs');
$compteSociete->save();

$b->get('/etablissement/'.$societeIdentifiant.'/nouveau')->click('#btn_valider')->followRedirect();

$b->post('/societe-creation', array('societe-creation' => array("raison_sociale" => 'Societe TESTFUNCTIONNAL '.uniqid())))->followRedirect()->followRedirect()->followRedirect()->click('button#btn_valider')->followRedirect();
preg_match("|/societe/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);

$societeAnnexe = SocieteClient::getInstance()->find($matches[1]);

$compteSociete2 = $societeAnnexe->getMasterCompte();
$compteSociete2->addTag('test', 'test_functionnal');
$compteSociete2->addTag('test', 'test_functionnal_societe_2');
$compteSociete2->save();

$b->post('/societe-creation', array('societe-creation' => array("raison_sociale" => 'Societe TESTFUNCTIONNAL '.uniqid())))->followRedirect()->followRedirect()->followRedirect()->click('button#btn_valider')->followRedirect();
preg_match("|/societe/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);

$societeAutre = SocieteClient::getInstance()->find($matches[1]);
$compteSocieteAutre = $societeAutre->getMasterCompte();
$compteSocieteAutre->addTag('test', 'test_functionnal');
$compteSocieteAutre->addTag('test', 'test_functionnal_societe_autre');
$compteSocieteAutre->save();

$t->comment('En mode stalker');

$b->get('/logout');


$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array(myUser::CREDENTIAL_STALKER)));
$b->restart();

$b->get('/')->followRedirect();
$t->is($b->getResponse()->getStatuscode(), 200, "Page d'accueil accessible");
$b->isForwardedTo('compte', 'search');

$b->get('/societe/'.$societeIdentifiant.'/visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation d'une société de type \"OPERATEUR\" accessible");
$b->isForwardedTo('societe', 'visualisation');

$b->isForwardedTo('societe', 'visualisation');
testVisualisationLimite($b, $societeIdentifiant);

$b->get('/societe/'.$societeAutre->getIdentifiant().'/visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation d'une société de type \"AUTRE\" accessible");

$t->comment('En mode habilitation');

$b->get('/logout');

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation')));
$b->restart();


if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()) {
    $b->get('/societe/'.$societeIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation d'une société de type \"OPERATEUR\" accessible");
    $b->isForwardedTo('societe', 'visualisation');
    testVisualisationLimite($b, $societeIdentifiant);

    $b->get('/societe/'.$societeAutre->getIdentifiant().'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'une société de type \"AUTRE\" protégée");
} else {
    $b->get('/societe/'.$societeIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'une société protégée");
}

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societeIdentifiant)));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()) {
    $b->get('/societe/'.$societeIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation de la société accessible");
    $b->isForwardedTo('societe', 'visualisation');
    testVisualisationLimite($b, $societeIdentifiant);
    $b->get('/societe/'.$societeAnnexe->getIdentifiant().'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'une autre société protégée");
} else {
    $b->get('/societe/'.$societeIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation de la société protégée");
}

function testVisualisationLimite($b, $societeIdentifiant) {

    $t = $b->test();

    $c = new sfDomCssSelector($b->getResponseDom());
    $t->is($c->matchSingle('a[href*="/modification"]')->getNode(), null, "Bouton \"Editer\" absent");
    $t->is($c->matchSingle('a[href*="/switchStatus"]')->getNode(), null, "Bouton \"Archiver\" absent");
    $t->is($c->matchSingle('a[href*="/switchEnAlerte"]')->getNode(), null, "Bouton \"Mettre en alerte\" absent");
    $t->is($c->matchSingle('a[href*="/compte/groupe"]')->getNode(), null, "Liens vers les groupe absent");
    $t->is($c->matchSingle('a[href*="/nouveau"]')->getNode(), null, "Liens vers les boutons d'ajout absent");

    $b->get('/societe-creation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de création protégé");

    $b->get('/societe/'.$societeIdentifiant.'/modification');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page modification protégé");

    $b->get('/societe/'.$societeIdentifiant.'/switchStatus');
    $t->is($b->getResponse()->getStatuscode(), 403, "Action d'archivage protégé");
}
