<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (!DRevConfiguration::getInstance()->isModuleEnabled()) {
    $t = new lime_test();
    $t->pass('no drev for '.$application);
    return;
}
$t = new lime_test(4);

$nego =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_nego')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($nego->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}

$campagne = (date('Y')-1)."";
$sv12 = SV12Client::getInstance()->find("SV12-".$nego->identifiant.'-'.$campagne, acCouchdbClient::HYDRATE_JSON);
if ($sv12) {SV12Client::getInstance()->deleteDoc($sv12);}

$config = ConfigurationClient::getCurrent();
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv12_douane.csv');
$t->comment("test avec test/data/sv12_douane.csv");
$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg.').".csv";
file_put_contents(
    $csvTmpFile,
    str_replace(
        array("%cvi", "%code_inao%", "%libelle_produit%"),
        array($nego->cvi, $produit->getCodeDouane(), $produit->getLibelleComplet()),
        $csvContentTemplate
    )
);

$t->comment("SV12-".$nego->identifiant."-".$campagne);
$dr = SV12Client::getInstance()->createDoc($nego->identifiant, $campagne);
$dr->setLibelle("SV12 $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier($csvTmpFile);
$dr->save();

$drev = DRevClient::getInstance()->createDoc($nego->identifiant, $campagne);
$drev->save();

$drev->resetAndImportFromDocumentDouanier();
$drev->save();
unlink($csvTmpFile);
$t->is(count($drev->getProduits()), 1, "La DRev a repris 1 produit du csv de la SV12");

$produits = $drev->getProduits();

$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();

$t->is($produit1->recolte->superficie_total, 6.202, "La superficie de récolte totale est récupéré sur csv");
$t->is($produit1->recolte->volume_total, 180, "Le volume total est récupéré du csv");
$t->is($produit1->recolte->recolte_nette, 180, "Le volume de récolte net est récupéré du csv");
