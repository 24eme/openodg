<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_functionnal_interlocuteur') as $k => $v) {
    if (preg_match('/COMPTE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
      $compte = CompteClient::getInstance()->find($m[1]);
      $compte->delete();
    }
}

$societe = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_societe')->getSociete();
$societeAnnexe = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_societe_2')->getSociete();
$societeAutre = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_societe_autre')->getSociete();

$b = new sfTestFunctional(new Browser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$societeIdentifiant = $societe->getIdentifiant();

$b->get('/compte/search');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de recherche de contact accessible");

$t->comment("Création et modification d'un interlocuteur");

$b->get('/compte/'.$societeIdentifiant.'/nouveau');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de création d'un interlocuteur stalker");
$b->click('#btn_valider', array('compte_modification' => array('nom' => 'Vincent')))->followRedirect();
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire de création d'un interlocuteur stalker");
$b->isForwardedTo('compte', 'visualisation');

preg_match("|/compte/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);
$compteIdentifiant = $matches[1];

$compte = CompteClient::getInstance()->find($compteIdentifiant);
$compte->addTag('test', 'test_functionnal');
$compte->addTag('test', 'test_functionnal_interlocuteur');
$compte->addInGroupes('test', 'testeurs');
$compte->addTag('manuel', 'test_manuel');
$compte->save();

$b->get('/compte/'.$societeAnnexe->getIdentifiant().'/nouveau')->click('#btn_valider', array('compte_modification' => array('nom' => 'Testeur')))->followRedirect();
preg_match("|/compte/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);

$compteAnnexe = CompteClient::getInstance()->find($matches[1]);
$compteAnnexe->addTag('test', 'test_functionnal');
$compteAnnexe->addTag('test', 'test_functionnal_interlocuteur_2');
$compteAnnexe->save();

$b->get('/compte/'.$societeAutre->getIdentifiant().'/nouveau')->click('#btn_valider', array('compte_modification' => array('nom' => 'Testeur')))->followRedirect();
preg_match("|/compte/([^/]+)/visualisation|", $b->getRequest()->getUri(), $matches);

$compteAutre = CompteClient::getInstance()->find($matches[1]);
$compteAutre->addTag('test', 'test_functionnal');
$compteAutre->addTag('test', 'test_functionnal_interlocuteur_autre');
$compteAutre->save();

$t->comment('En mode stalker');

$b->get('/logout');

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array(myUser::CREDENTIAL_STALKER)));
$b->restart();

$b->get('/compte/search');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de recherche de contact accessible");

$b->get('/compte/'.$compteIdentifiant.'/visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Visualisation d'un interlocuteur accessible");
$b->isForwardedTo('compte', 'visualisation');
testVisualisationLimite($b, $societeIdentifiant, $compte);

$b->get('/compte/'.$compteAutre->getIdentifiant().'/visualisation');
$t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'un interlocuteur d'une société \"AUTRE\" protégée");

$t->comment('En mode habilitation');

$b->get('/logout');

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation')));
$b->restart();

$b->get('/compte/search');
$t->is($b->getResponse()->getStatuscode(), 403, "Page de recherche de contact protégé");

if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()) {
    $b->get('/compte/'.$compteIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Visualisation d'un interlocuteur accessible");
    $b->isForwardedTo('compte', 'visualisation');
    testVisualisationLimite($b, $societeIdentifiant, $compte);

    $b->get('/compte/'.$compteAutre->getIdentifiant().'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'un interlocuteur d'une société \"AUTRE\" protégée");
} else {
    $b->get('/compte/'.$compteIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'un interlocuteur protégé");
}

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societeIdentifiant)));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

$b->get('/compte/search');
$t->is($b->getResponse()->getStatuscode(), 403, "Page de recherche de contact protégé");

if(SocieteConfiguration::getInstance()->isVisualisationTeledeclaration()) {
    $b->get('/compte/'.$compteIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 200, "Page de visualisation de l'interlocuteur accessible");
    $b->isForwardedTo('compte', 'visualisation');
    testVisualisationLimite($b, $societeIdentifiant, $compte);
    $b->get('/compte/'.$compteAnnexe->getIdentifiant().'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'un interlocuteur d'une société \"AUTRE\" protégée");
} else {
    $b->get('/compte/'.$compteIdentifiant.'/visualisation');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de visualisation d'une interlocuteur protégée");
}

function testVisualisationLimite($b, $societeIdentifiant, $compte) {

    $t = $b->test();

    $c = new sfDomCssSelector($b->getResponseDom());
    $t->is($c->matchSingle('a[href*="/modification"]')->getNode(), null, "Bouton \"Editer\" absent");
    $t->is($c->matchSingle('a[href*="/switchStatus"]')->getNode(), null, "Bouton \"Archiver\" absent");
    $t->is($c->matchSingle('a[href*="/switchEnAlerte"]')->getNode(), null, "Bouton \"Mettre en alerte\" absent");
    $t->is($c->matchSingle('a[href*="/suppression/"]')->getNode(), null, "Bouton \"Supprimer\" absent");
    $t->is($c->matchSingle('a[href*="/compte/groupe"]')->getNode(), null, "Liens vers les groupe absent");
    $t->is($c->matchSingle('a[href*="/nouveau"]')->getNode(), null, "Liens vers les boutons d'ajout absent");
    $t->is($c->matchSingle('form.form_ajout_tag')->getNode(), null, "Form d'ajout d'un tag absent");
    $t->is($c->matchSingle('form.form_ajout_groupe')->getNode(), null, "Form d'ajout d'un groupe absent");

    $b->get('/compte/'.$societeIdentifiant.'/nouveau');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page ajouter un interlocuteur protégée");

    $b->get('/compte/'.$compte->getIdentifiant().'/modification');
    $t->is($b->getResponse()->getStatuscode(), 403, "Page de modification d'un interlocuteur protégée");

    $b->get('/compte/'.$compte->getIdentifiant().'/switchStatus');
    $t->is($b->getResponse()->getStatuscode(), 403, "Action archiver un interlocuteur protégé");

    $b->get('/compte/'.$compte->getIdentifiant().'/suppression');
    $t->is($b->getResponse()->getStatuscode(), 403, "Action archiver un interlocuteur protégé");

}
