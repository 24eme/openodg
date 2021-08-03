<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test();

$viti = EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$campagne = (date('Y')-2)."";

foreach(ParcellaireAffectationClient::getInstance()->getHistory($viti->identifiant, ParcellaireAffectationClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($k);
    $parcellaireAffectation->delete(false);
}

foreach(ParcellaireAffectationClient::getInstance()->getHistory($viti->identifiant, ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($k);
    $parcellaireAffectation->delete(false);
}

foreach(ParcellaireAffectationClient::getInstance()->getHistory($viti->identifiant, ParcellaireAffectationClient::TYPE_COUCHDB_PARCELLAIRE_CREMANT, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($k);
    $parcellaireAffectation->delete(false);
}

foreach (ParcellaireClient::getInstance()->getHistory($viti->identifiant, '9999-99-99', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$produits = ConfigurationClient::getCurrent()->getProduits();
$parcellaire = ParcellaireClient::getInstance()->createDoc($viti->identifiant, $campagne);
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_RI']->getHash(),
    "RIESLING",
    "1958-1959",
    "PARIS",
    "04",
    "95",
    "MONTMARTRE"
);
$nouvelle_parcelle->superficie = 1;
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_RI']->getHash(),
    "RIESLING",
    "1948-1949",
    "PARIS",
    "03",
    "12",
    "MONTMARTRE"
);
$nouvelle_parcelle->superficie = 2;
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_AU']->getHash(),
    "AUXERROIS",
    "1968-1969",
    "PARIS",
    "06",
    "75",
    "MONTPARNASSE"
);
$nouvelle_parcelle->superficie = 3;
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
$parcellaireAffectationCremant->updateAffectationCremantFromCVI();
$parcellaireAffectationCremant->save();

$t->is($parcellaireAffectationCremant->_id, "PARCELLAIREAFFECTATIONCREMANT-".$viti->identifiant."-".$campagne, "ID de l'affectation parcellaire : ".$parcellaireAffectationCremant->_id);

$t->comment("Étape Parcelles");

$appellation = ParcellaireAffectationClient::getInstance()->getFirstAppellation($parcellaireAffectationCremant->getTypeParcellaire());
$t->is($appellation, ParcellaireAffectationClient::APPELLATION_CREMANT, "L'appellation est $appellation");
$appellationNode = $parcellaireAffectationCremant->getAppellationNodeFromAppellationKey($appellation, true);
$parcelles = $appellationNode->getDetailsSortedByParcelle(false);

$form = new ParcellaireAffectationAjoutParcelleForm($parcellaireAffectationCremant, $appellation);
$form = new ParcellaireAffectationAppellationEditForm($parcellaireAffectationCremant, $appellation, $parcelles);

$t->is(count($parcellaireAffectationCremant->getProduits()), 2, "Il y a 2 cépages");
$t->is(count($parcellaireAffectationCremant->getAllParcellesByAppellation($appellation)), 3, "Il y a 3 parcelles");
$t->is(array_keys($parcellaireAffectationCremant->getAllParcellesByAppellation($appellation)), [
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_AU/detail/AUXERROIS-1968-1969-PARIS-06-75-00-MONTPARNASSE",
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_RI/detail/RIESLING-1958-1959-PARIS-04-95-00-MONTMARTRE",
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_RI/detail/RIESLING-1948-1949-PARIS-03-12-00-MONTMARTRE"
], "Les clés de parcelles sont correctes");
$t->is(current($parcellaireAffectationCremant->getAllParcellesByAppellation($appellation))->superficie, 3, "On retrouve la superficie");

$t->comment('Nouveau parcellaire (cvi)');
$parcellaire = ParcellaireClient::getInstance()->createDoc($viti->identifiant, $campagne+1);
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_RI']->getHash(),
    "RIESLING",
    "1958-1959",
    "PARIS",
    "04",
    "95",
    "MONTMARTRE"
);
$nouvelle_parcelle->superficie = 1;
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_PG']->getHash(),
    "PINOT GRIS",
    "1999-2000",
    "PARIS",
    "13",
    "16",
    "MONTMARTRE"
);
$nouvelle_parcelle->superficie = 4;
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_AU']->getHash(),
    "AUXERROIS",
    "1968-1969",
    "PARIS",
    "06",
    "75",
    "MONTPARNASSE"
);
$nouvelle_parcelle->superficie = 3;
$nouvelle_parcelle->superficie_cadastrale = 9;
$nouvelle_parcelle = $parcellaire->addParcelle(
    $produits['/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur/cepage_AU']->getHash(),
    "AUXERROIS",
    "1968-1969",
    "PARIS",
    "06",
    "75",
    "MONTPARNASSE"
);
$nouvelle_parcelle->superficie = 6;
$nouvelle_parcelle->superficie_cadastrale = 9;
$parcellaire->save();

$t->comment("Création d'une déclaration d'affectation parcellaire crémant");

$intentionCremant = ParcellaireAffectationClient::getInstance()->createDoc($viti->identifiant, $campagne, ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT);
$intentionCremant->declaration = $parcellaireAffectationCremant->declaration;
$intentionCremant->save();

$parcellaireAffectationCremant = ParcellaireAffectationClient::getInstance()->findOrCreate($viti->identifiant, $campagne+1, ParcellaireAffectationClient::TYPE_COUCHDB_PARCELLAIRE_CREMANT);
$parcellaireAffectationCremant->initProduitFromLastParcellaire();
$parcellaireAffectationCremant->updateAffectationCremantFromCVI();
$parcellaireAffectationCremant->updateAffectationCremantFromLastTwoIntentions();
$parcellaireAffectationCremant->save();

$t->is($parcellaireAffectationCremant->_id, "PARCELLAIREAFFECTATIONCREMANT-".$viti->identifiant."-".($campagne+1), "ID de l'affectation parcellaire : ".$parcellaireAffectationCremant->_id);

$t->comment("Étape Parcelles");

$appellation = ParcellaireAffectationClient::getInstance()->getFirstAppellation($parcellaireAffectationCremant->getTypeParcellaire());
$t->is($appellation, ParcellaireAffectationClient::APPELLATION_CREMANT, "L'appellation est $appellation");
$appellationNode = $parcellaireAffectationCremant->getAppellationNodeFromAppellationKey($appellation, true);
$parcelles = $appellationNode->getDetailsSortedByParcelle(false);

$form = new ParcellaireAffectationAjoutParcelleForm($parcellaireAffectationCremant, $appellation);
$form = new ParcellaireAffectationAppellationEditForm($parcellaireAffectationCremant, $appellation, $parcelles);

$t->is(count($parcellaireAffectationCremant->getProduits()), 3, "Il y a 3 cépages");
$t->is(count($parcellaireAffectationCremant->getAllParcellesByAppellation($appellation)), 4, "Il y a 4 parcelles");
$t->is(array_keys($parcellaireAffectationCremant->getAllParcellesByAppellation($appellation)), [
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_AU/detail/AUXERROIS-1968-1969-PARIS-06-75-00-MONTPARNASSE",
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_AU/detail/AUXERROIS-1968-1969-PARIS-06-75-01-MONTPARNASSE",
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_RI/detail/RIESLING-1958-1959-PARIS-04-95-00-MONTMARTRE",
    "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_PG/detail/PINOT-GRIS-1999-2000-PARIS-13-16-00-MONTMARTRE"
], "Les clés de parcelles sont correctes");

try {
    $parcellaireAffectationCremant->get("/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_RI/detail/RIESLING-1948-1949-PARIS-03-12-00-MONTMARTRE");
    $t->fail();
} catch (Exception $e) {
    $t->pass("L'ancienne parcelle n'est plus là");
}
$t->is($parcellaireAffectationCremant->get("/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_AU/detail/AUXERROIS-1968-1969-PARIS-06-75-00-MONTPARNASSE")->active, true, "L'ancienne parcelle d'auxerrois est active");
$t->is($parcellaireAffectationCremant->get("/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_RI/detail/RIESLING-1958-1959-PARIS-04-95-00-MONTMARTRE")->active, true, "L'ancienne parcelle de riesling est active");
$t->is($parcellaireAffectationCremant->get("/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_PG/detail/PINOT-GRIS-1999-2000-PARIS-13-16-00-MONTMARTRE")->active, 0, "La nouvelle parcelle n'est pas active");

$parcellesAffectationCremant = $parcellaireAffectationCremant->getAllParcellesByAppellation($appellation);
$t->is(end($parcellesAffectationCremant)->superficie, 4, "On retrouve la superficie");
reset($parcellesAffectationCremant);
$t->is(key($parcellesAffectationCremant), "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_AU/detail/AUXERROIS-1968-1969-PARIS-06-75-00-MONTPARNASSE", "La clé est la clé 0");
$t->is(current($parcellesAffectationCremant)->superficie, 3, "La superficie de la premiere parcelle est 3");
$t->is(next($parcellesAffectationCremant)->superficie, 6, "La superficie de la deuxieme parcelle est 6");
$t->is(key($parcellesAffectationCremant), "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_AU/detail/AUXERROIS-1968-1969-PARIS-06-75-01-MONTPARNASSE", "La clé est la clé 1");

$t->pass("Fomulaires étape Parcelles");
