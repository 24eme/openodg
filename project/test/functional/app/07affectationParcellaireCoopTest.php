<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissementCoop = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement_coop')->getEtablissement();
$etablissementviti = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();

$application = getenv('APPLICATION');

if ($application != 'provence') {
    $b = new sfTestFunctional(new Browser());
    $t = $b->test();
    return;
}
$currentCampagne = intval(ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent());
$sv11 = SV11Client::getInstance()->find('SV11-'.$etablissementCoop->identifiant.'-'.$currentCampagne);
if ($sv11) {
    $sv11->delete(false);
}
$p = ParcellaireAffectationClient::getInstance()->getLast($etablissementviti->identifiant);
if ($p) {
    $p->delete(false);
}
$affectationcoop = parcellaireAffectationCoopClient::getInstance()->find('PARCELLAIREAFFECTATIONCOOP-'.$etablissementCoop->identifiant.'-2024');
if ($affectationcoop) {
    $affectationcoop->delete(false);
}
$b = new sfTestFunctional(new Browser());
$t = $b->test();

$config = ConfigurationClient::getCurrent();
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    break;
}
$sv11 = SV11Client::getInstance()->createDoc($etablissementCoop->identifiant, $currentCampagne);
$sv11->save();
$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../../data/sv11_douane.csv');
$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg');
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%cvi_1%", "%code_inao_1%", "%libelle_produit_1%"), array($etablissementCoop->cvi, $etablissementviti->cvi, $produit->getCodeDouane(), $produit->getLibelleComplet()), $csvContentTemplate));
$csv = new SV11DouaneCsvFile($csvTmpFile, $sv11);
$csvConvert = $csv->convert();
unlink($csvTmpFile);
$sv11->save();
$t->comment($sv11->_id);

$parcellaire = ParcellaireClient::getInstance()->findOrCreate($etablissementviti->identifiant, date('Y-m-d'), "INAO");
$parcellaire->save();
$communes = CommunesConfiguration::getInstance()->getByCodeCommune();
$t->ok($communes, "config/communes.yml contient des communes");
$commune = current($communes);
$code_commune = key($communes);
$numero_ordre_key = "00";
$parcelle = $parcellaire->addParcelleWithProduit($produit->getHash(), $produit->getLibelleComplet(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT");
$parcellaire->addParcelleWithProduit($produit->getHash(), $produit->getLibelleComplet(), "Grenache", "2010", "PEYNIER", "", "AK", "47", null);
$parcellaire->addParcelleWithProduit($produit->getHash(), $produit->getLibelleComplet(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT",25);
$parcellaire->addParcelleWithProduit($produit->getHash(), $produit->getLibelleComplet(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT",26);
$parcellaire->save();
$t->comment($parcellaire->_id);
$intention = ParcellaireIntentionClient::getInstance()->createDoc($etablissementviti->identifiant, $currentCampagne);
foreach($intention->getParcelles() as $p) {
    $p->active = 1;
}
$intention->save();
$t->comment($intention->_id);
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null, 'app_facture_emetteur' => $facture_emetteur_test));

$t->comment("Saisie des affectations parcellaires des apporteurs d'une d'une cave coop");

$b->get('/declarations/'.$etablissementCoop->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");
$b->click('a#btn_affection_parcellaire_coop');
$b->isForwardedTo('parcellaireAffectationCoop', 'edit');
$b->followRedirect();
$t->is($b->getResponse()->getStatuscode(), 200, "Création du document");

$b->click('button#btn_creation_affection_parcellaire_coop');
$t->is($b->getResponse()->getStatuscode(), 302, "Traitement de la SV11");
$b->followRedirect();
$b->isForwardedTo('parcellaireAffectationCoop', 'liste');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape Apporteurs passée");

$b->click('a#bnt_affectation_retour_liste_coop');
$b->isForwardedTo('parcellaireAffectationCoop', 'apporteurs');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape apporteurs");

$b->click('button[type="submit"]');
$b->followRedirect();
$b->isForwardedTo('parcellaireAffectationCoop', 'liste');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape liste");

$b->click('a.btn_saisie_affectation_parcellaire');
$b->isForwardedTo('parcellaireAffectationCoop', 'saisie');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape saisie d'une affectation parcellaire");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('parcellaireAffectationCoop', 'liste');
$t->is($b->getResponse()->getStatuscode(), 200, "Retour à l'étape liste");

$sv11->delete(false);
$parcellaire->delete(false);
