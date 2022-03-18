<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(9);

$coop =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_coop')->getEtablissement();

$t->comment("test on ".$coop->identifiant);

$campagne = (date('Y')-1)."";

//Suppression des DRev précédentes
$ids = array();
foreach(DRevClient::getInstance()->getHistory($coop->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $ids[$k] = $k;
}
$ids['DREV-'.$coop->identifiant.'-'.$campagne] = 'DREV-'.$coop->identifiant.'-'.$campagne;
foreach($ids as $k) {
    $drev = DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON);
    if ($drev) { DRevClient::getInstance()->deleteDoc($drev); }
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}



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
file_put_contents($csvTmpFile, str_replace(array("%cvi_1%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($coop->cvi, $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/sv11_douane.csv");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet());

$dr = SV11Client::getInstance()->createDoc($coop->identifiant, $campagne);
$dr->setLibelle("SV11 $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier($csvTmpFile);
$dr->save();

$drev = DRevClient::getInstance()->createDoc($coop->identifiant, $campagne);
$drev->save();

$t->is($drev->getDocumentDouanierType(), "SV11", "Le document douanier de la DRev est de type SV11");

$drev->importFromDocumentDouanier();
$drev->save();
$t->is(count($drev->getProduits()), 2 + DRevConfiguration::getInstance()->hasDenominationAuto(), "La DRev a repris 2 produits du csv de la SV11 (+1 si on prend la mention complémentaire)");

$produits = $drev->getProduits();

$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();

$t->is($produit1->recolte->superficie_total, 1.4885, "La superficie de récolte totale est récupéré sur csv");
$t->is($produit1->recolte->volume_total, 53.59, "Le volume total est récupéré du csv");
$t->is($produit1->recolte->recolte_nette, 53.59, "Le volume de récolte net est récupéré du csv");
$t->is($produit1->recolte->volume_sur_place, 53.59, "Le volume sur place est récupéré du csv");
$t->is($produit1->superficie_revendique, $produit1->recolte->superficie_total, "La superificie totale est mise automatiquement");

$produit1->vci->stock_precedent = 10;
$produit1->vci->rafraichi = 1;
$produit1->vci->complement = 9;
$produit1->update();

$t->is($produit1->vci->stock_final, 1, "Le stock final de vci est bon");
$t->is($produit1->getTheoriticalVolumeRevendiqueIssuRecole(), 52.59, "Le volume revendiqué théorique de récolte est le bon");
