<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test();

$viti = EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$campagne = (date('Y')-2)."";

foreach(ParcellaireAffectationClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($k);
    $parcellaireAffectation->delete(false);
}

foreach (ParcellaireClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$produits = ConfigurationClient::getCurrent()->getProduits();
$parcellaire = ParcellaireClient::getInstance()->createDoc($viti->identifiant, $campagne);
$parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_RI']->getHash(),
    "RIESLING",
    "1958-1959",
    "PARIS",
    "04",
    "95",
    "MONTMARTRE"
);
$parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_AU']->getHash(),
    "AUXERROIS",
    "1968-1969",
    "PARIS",
    "06",
    "75",
    "MONTPARNASSE"
);
$parcellaire->save();

$t->comment("Création d'une déclaration d'affectation parcellaire");

$parcellaireAffectation = ParcellaireAffectationClient::getInstance()->findOrCreate($viti->identifiant, $campagne);
$parcellaireAffectation->initProduitFromLastParcellaire();
$parcellaireAffectation->save();

$t->is($parcellaireAffectation->_id, "PARCELLAIREAFFECTATION-".$viti->identifiant."-".$campagne, "ID de l'affectation parcellaire : ".$parcellaireAffectation->_id);

$t->comment("Étape destination du raisin");

$form = new ParcellaireAffectationDestinationForm($parcellaireAffectation);

$t->pass("Fomulaire étape destinaion du raisin");

$t->comment("Étape Parcelles");

$appellation = ParcellaireAffectationClient::getInstance()->getFirstAppellation($parcellaireAffectation->getTypeParcellaire());
$appellationNode = $parcellaireAffectation->getAppellationNodeFromAppellationKey($appellation, true);
$parcelles = $appellationNode->getDetailsSortedByParcelle(false);

$form = new ParcellaireAffectationAjoutParcelleForm($parcellaireAffectation, $appellation);
$form = new ParcellaireAffectationAppellationEditForm($parcellaireAffectation, $appellation, $parcelles);

$t->pass("Fomulaires étape Parcelles");

$t->comment("Étape Acheteurs");

$form = new ParcellaireAffectationAcheteursForm($parcellaireAffectation);
$form = new ParcellaireAffectationAcheteursParcellesForm($parcellaireAffectation);

$t->pass("Fomulaires étape Acheteur");

$t->comment("Étape Validation");

$form = new ParcellaireAffectationValidationForm($parcellaireAffectation);

$t->pass("Fomulaire étape Validation");

////////////////////////////
$t->comment("Création d'une déclaration d'affectation parcellaire crémant");

$parcellaireAffectationCremant = ParcellaireAffectationClient::getInstance()->findOrCreate($viti->identifiant, $campagne, ParcellaireAffectationClient::TYPE_COUCHDB_PARCELLAIRE_CREMANT);
$parcellaireAffectationCremant->initProduitFromLastParcellaire();
$parcellaireAffectationCremant->save();

$t->is($parcellaireAffectationCremant->_id, "PARCELLAIRECREMANTAFFECTATION-".$viti->identifiant."-".$campagne, "ID de l'affectation parcellaire : ".$parcellaireAffectationCremant->_id);

$t->comment("Étape Parcelles");

$appellation = ParcellaireAffectationClient::getInstance()->getFirstAppellation($parcellaireAffectationCremant->getTypeParcellaire());
$t->is($appellation, ParcellaireAffectationClient::APPELLATION_CREMANT, "L'appellation est $appellation");
$appellationNode = $parcellaireAffectationCremant->getAppellationNodeFromAppellationKey($appellation, true);
$parcelles = $appellationNode->getDetailsSortedByParcelle(false);

$form = new ParcellaireAffectationAjoutParcelleForm($parcellaireAffectationCremant, $appellation);
$form = new ParcellaireAffectationAppellationEditForm($parcellaireAffectationCremant, $appellation, $parcelles);

$t->is(count($parcellaireAffectationCremant->getProduits()), 6, "Il y a 6 cépages");
$t->is(count($parcelles), 69, "Il y a 69 parcelles");

$t->pass("Fomulaires étape Parcelles");
