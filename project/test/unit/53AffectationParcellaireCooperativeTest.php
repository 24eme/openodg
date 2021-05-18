<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (!in_array($application, array('provence'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas de parcellaire coopérative activé");
    return;
}

$t = new lime_test();

$coop =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_coop')->getEtablissement();

$t->comment("test on ".$coop->identifiant);

$campagne = (date('Y')-1)."";

$sv11 = SV11Client::getInstance()->find("SV11-".$coop->identifiant."-".$campagne, acCouchdbClient::HYDRATE_JSON);
if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv11_douane.csv');

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
foreach($config->getProduits() as $produit) {
    if($produit->getRendement() <= 0) {
        continue;
    }
    if(!$produit1) {
        $produit1 = $produit;
        continue;
    } elseif(!$produit2) {
        $produit2 = $produit;
        continue;
    }
    break;
}
$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($viti->cvi, $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/sv11_douane.csv");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet());

$sv11 = SV11Client::getInstance()->createDoc($coop->identifiant, $campagne);
$sv11->setLibelle("SV11 $campagne issue de Prodouane (Papier)");
$sv11->setDateDepot("$campagne-12-15");
$sv11->save();
$sv11->storeFichier($csvTmpFile);
$sv11->save();

$t->ok($sv11->_rev, "Création de la sv11");
$t->ok(count($sv11->getCsv()), "Le csv a au moins une ligne");
$t->is(count($sv11->getApporteurs()), 4, "Il y a 4 apporteurs");

