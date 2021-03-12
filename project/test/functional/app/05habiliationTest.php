<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();
$societe = $etablissement->getSociete();

foreach(HabilitationClient::getInstance()->getHistory($etablissement->identifiant) as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}

$config = ConfigurationClient::getCurrent();
$produit1 = null;
foreach(HabilitationClient::getInstance()->getProduitsConfig($config) as $p) {
    $produit1 = $produit;

    break;
}

$b = new sfTestFunctional(new Browser());
if (!$produit1)  {
    $t = $b->test();
    $t->ok(true, "test disabled");
    return;
}

$t = $b->test();

$t->comment('En mode admin');

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant());
$b->isForwardedTo('habilitation', 'declarant');
$t->is($b->getResponse()->getStatusCode(), 200, "Page d'habilitation d'un établissement");

if(!HabilitationConfiguration::getInstance()->isSuiviParDemande()) {
    exit;
}

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant().'/demande/creation');
$b->isForwardedTo('habilitation', 'demandeCreation');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de création d'une demande");

$b->click('.modal-demande .modal-footer button[type="submit"]', array('habilitation_demande_creation' =>
    array('demande' => HabilitationClient::DEMANDE_HABILITATION,
          'produit' => $produit1->getHash(),
          'activites' => array(HabilitationClient::ACTIVITE_PRODUCTEUR, HabilitationClient::ACTIVITE_VINIFICATEUR),
          'statut' => 'DEPOT',
          'date' => date('d/m/Y'),
          'commentaire' => 'test création')))->followRedirect();
$b->isForwardedTo('habilitation', 'declarant');
$t->is($b->getResponse()->getStatusCode(), 200, "Formulaire de création d'une demande");

$b->click('#tableaux_des_demandes a[href*="/declarant/'.$etablissement->getIdentifiant().'/demande/edition/"]');
$b->isForwardedTo('habilitation', 'demandeEdition');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de d'édition d'une demande");

preg_match("|/declarant/".$etablissement->getIdentifiant()."/demande/edition/([^/]+)|", $b->getRequest()->getUri(), $matches);
$demandeKey = $matches[1];

$b->click('.modal-demande .modal-footer button[type="submit"]', array('habilitation_demande_edition' =>
    array('statut' => 'COMPLET',
          'date' => date('d/m/Y'),
          'commentaire' => 'test édition')))->followRedirect();
$b->isForwardedTo('habilitation', 'declarant');
$t->is($b->getResponse()->getStatusCode(), 200, "Formulaire d'édition d'une demande");

$t->comment('En mode habilitation INAO');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('habilitation:inao')));
$b->restart();

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant().'/demande/creation');
$b->isForwardedTo('habilitation', 'demandeCreation');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de création d'une demande");

$b->click('#tableaux_des_demandes a[href*="declarant/'.$etablissement->getIdentifiant().'/demande/visualisation/"]');
$b->isForwardedTo('habilitation', 'demandeVisualisation');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de visualisation d'une demande");

$c = new sfDomCssSelector($b->getResponseDom());
$t->is($c->matchSingle('.modal-demande .modal-footer button[type="submit"]')->getNode(), null, "Pas de bouton de soumission");

HabilitationClient::getInstance()->updateDemandeAndSave($etablissement->identifiant, $demandeKey, date('Y-m-d'), 'TRANSMIS_INAO', "", "");

$t->comment("Passage de la demande au statut TRANSMIS_INAO");

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant());
$b->click('#tableaux_des_demandes a[href*="declarant/'.$etablissement->getIdentifiant().'/demande/edition/"]');
$b->isForwardedTo('habilitation', 'demandeEdition');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de d'édition d'une demande");

$c = new sfDomCssSelector($b->getResponseDom());
$t->ok($c->matchSingle('.modal-demande #btn_demande_separer')->getNode(), "Pas de bouton \"Séparer\"");

$b->click('.modal-demande .modal-footer button[type="submit"]', array('habilitation_demande_edition' =>
    array('statut' => 'VALIDE_INAO',
          'date' => date('d/m/Y'),
          'commentaire' => 'test édition')))->followRedirect();
$b->isForwardedTo('habilitation', 'declarant');
$t->is($b->getResponse()->getStatusCode(), 200, "Formulaire d'édition d'une demande");

$t->comment('En mode admin');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));
$b->restart();

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant());
$b->click('#tableaux_des_demandes a[href*="declarant/'.$etablissement->getIdentifiant().'/demande/edition/"]');
$b->isForwardedTo('habilitation', 'demandeEdition');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de d'édition d'une demande cloturé");
$c = new sfDomCssSelector($b->getResponseDom());

$t->is($c->matchSingle('.modal-demande .modal-footer button[type="submit"]')->getNode(), null, "Pas de bouton de soumission car la demande est cloturée");

$t->comment('En mode stalker');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => array('stalker')));
$b->restart();

$b->get('/habilitation_demande');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de listing des habilitations protégées");

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant());
$t->is($b->getResponse()->getStatusCode(), 200, "Page d'habilitation");

$b->click('#tableaux_des_demandes a[href*="declarant/'.$etablissement->getIdentifiant().'/demande/visualisation/"]');
$b->isForwardedTo('habilitation', 'demandeVisualisation');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de visualisation d'une demande");

$b->get(str_replace('visualisation', 'edition', $b->getRequest()->getUri()));
$t->is($b->getResponse()->getStatusCode(), 403, "Page d'édition d'une demande protégée");

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant().'/demande/creation');
$t->is($b->getResponse()->getStatusCode(), 403, "Page de création d'une demande protégée");

$b->get('/habilitation/visualisation/HABILITATION-'.$etablissement->getIdentifiant().'-'.date('Ymd'));
$t->is($b->getResponse()->getStatusCode(), 200, "Page de visualisation d'une habilitation");

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societe->getIdentifiant())));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

$b->get('/habilitation_demande');
$t->is($b->getResponse()->getStatusCode(), 403, "Page de listing des habilitations protégées");

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant());
$t->is($b->getResponse()->getStatusCode(), 200, "Page d'habilitation");

$b->click('#tableaux_des_demandes a[href*="declarant/'.$etablissement->getIdentifiant().'/demande/visualisation/"]');
$b->isForwardedTo('habilitation', 'demandeVisualisation');
$t->is($b->getResponse()->getStatusCode(), 200, "Page de visualisation d'une demande");

$b->get(str_replace('visualisation', 'edition', $b->getRequest()->getUri()));
$t->is($b->getResponse()->getStatusCode(), 403, "Page d'édition d'une demande protégée");

$b->get('/habilitation/declarant/'.$etablissement->getIdentifiant().'/demande/creation');
$t->is($b->getResponse()->getStatusCode(), 403, "Page de création d'une demande protégée");

$b->get('/habilitation/visualisation/HABILITATION-'.$etablissement->getIdentifiant().'-'.date('Ymd'));
$t->is($b->getResponse()->getStatusCode(), 200, "Page de visualisation d'une habilitation");
