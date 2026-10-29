<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (in_array($application, array('nantes', 'loire', 'igp13'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas d'affectation parcellaire activé");
    return;
}

$t = new lime_test();
$t->ok(true, 'desactivé');
return;
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$yearprevious = date('Y') - 2;
$dateprevious = $yearprevious.'-12-01';
$campagneprevious = $yearprevious.'-'.($yearprevious + 1);
foreach(ParcellaireClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}
foreach(ParcellaireIntentionAffectationClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireIntentionAffectationClient::getInstance()->find($k);
    $parcellaire->delete(false);
}
foreach(ParcellaireAffectationClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireAffectationClient::getInstance()->find($k);
    $parcellaire->delete(false);
}


$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $dateprevious, "DOUANE");
$parcellaire->save();

$t->comment("Parcellaire au $dateprevious : ".$parcellaire->_id);

$configProduit = null;
foreach($parcellaire->getConfigProduits() as $produit) {
    $configProduit = $produit;
    break;
}

$communesConf = CommunesConfiguration::getInstance()->getByCodeCommune();
$communesDenominations = sfConfig::get('app_communes_denominations');

$communes = array();
foreach($communesDenominations as $lieu => $codeCommunes) {
    foreach($codeCommunes as $codeCommune) {
        $communes[] = $communesConf[$codeCommune];
        break;
    }
}
$cepages_autorises_0 = is_array($configProduit->cepages_autorises) ? $configProduit->cepages_autorises[0] : 'GRENACHE N';
$cepages_autorises_1 = is_array($configProduit->cepages_autorises) ? $configProduit->cepages_autorises[1] : 'SYRAH N';
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), $cepages_autorises_0, "2010", $communes[0], '', "A", "1");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), $cepages_autorises_1, "2011", $communes[0], '', "A", "12");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), $cepages_autorises_1, "2013", $communes[0], '', "B", "24");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), $cepages_autorises_1, "2016", $communes[0], '', "B", "24");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), $cepages_autorises_1, "2016", "75036", '', "C", "99");
$parcellaire->save();
$parcellaire_id = $parcellaire->_id;
$t->ok($parcellaire_id, 'parcellaire créé '.$parcellaire->_id);
$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $dateprevious, "DOUANE");
$t->is($parcellaire->_id, $parcellaire_id, 'On retrouve le bon parcellaire');
$affectation = ParcellaireAffectationClient::getInstance()->createDoc($viti->identifiant, $yearprevious + 1);
$t->ok($affectation, 'On trouve une affectation');
$affectation->updateParcellesAffectation();
$t->is(count($affectation->getParcelles()), 0, "L'affectation a aucune parcelle car elle n'a pas de declaration d'intention");


$intention = ParcellaireIntentionClient::getInstance()->createDoc($viti->identifiant, $yearprevious + 1, false, $dateprevious);

$t->comment("Intention Parcellaire au $dateprevious : ".$intention->_id);

$t->is(count($intention->getParcellesFromReference()), 5, "L'intention a bien accès au 4 parcelles du parcellaire");
$t->is(count($intention->getParcelles()), 4, "L'intention a les 4 parcelles du dernier parcellaire");
foreach($intention->getParcelles() as $parcelle) {
    $parcelle->affectation = 1;
}
$parcelle = array_shift($intention->getParcelles());
$parcelle->affectation = 0;
$intention->save();

$affectation = ParcellaireAffectationClient::getInstance()->createDoc($viti->identifiant, $yearprevious + 1);
$t->is(count($affectation->getParcelles()), 3, "L'affectation a 3 parcelles comme le nombre de parcelle affectable dans la déclaration d'intention");
foreach($affectation->getParcelles() as $parcelle) {
    $t->is($parcelle->origine_doc, $intention->_id, "L'origine est ".$intention->_id);
    $t->is($parcelle->affectation, 1, "Les parcellaires ont hérité du caractère affectable");
    $t->is($parcelle->affectee, null, "Les parcellaires ne sont pas affectées par defaut");
    $parcelle->affectee = 1;
}
$affectation->save();
$affectation->validate();
$affectation->save();

$t->comment("Affectation Parcellaire ".$affectation->campagne." : ".$affectation->_id);

$year = date('Y') - 1;
$date = $year.'-12-01';
$campagne = $year.'-'.($year + 1);
$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $date, "DOUANE");
$parcellaire->save();
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Grenache", "2010", $communes[0], '', "A", "1");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Syrah", "2011", $communes[0], '', "A", "12");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Syrah", "2013", $communes[0], '', "B", "24");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Syrah", "2016", "Paris", '', "C", "99");
$parcellaire->save();

$t->comment("Parcellaire au $dateprevious : ".$parcellaire->_id);

$intention = ParcellaireIntentionClient::getInstance()->createDoc($viti->identifiant, $year + 1, false, $date);

$t->comment("Intention Parcellaire au $date : ".$intention->_id);

$t->is(count($intention->getParcelles()), 3, "L'intention a les 3 parcelles du dernier parcellaire");
foreach($intention->getParcelles() as $parcelle) {
    $parcelle->affectation = 1;
}
$intention->save();

$affectation = ParcellaireAffectationClient::getInstance()->createDoc($viti->identifiant, $year + 1);
$t->is(count($affectation->getParcelles()), 3, "L'affectation a 3 parcelles comme le nombre de parcelle affectable dans la déclaration d'intention");
foreach($affectation->getParcelles() as $parcelle) {
    $t->is($parcelle->origine_doc, $intention->_id, "L'origine est ".$intention->_id);
    $t->is($parcelle->affectation, 1, "La parcelle ".$parcelle->getKey()." a héritée du caractère affectable");
    $t->is($parcelle->affectee, 1, "La parcelle ".$parcelle->getKey()." a héritée du caractère affectée");
}
$affectation->save();

$t->comment("Affectation Parcellaire ".$affectation->campagne." : ".$affectation->_id);
