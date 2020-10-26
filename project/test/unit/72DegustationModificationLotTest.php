<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$campagne = (string) date('Y') - 1;
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";

$degustation = acCouchdbManager::getClient()->find($docid);
if ($degustation == null) {
    exit('Doc null');
}

$config = ConfigurationClient::getCurrent();
$produitconfig1 = null;
foreach(array_reverse($config->getProduits()) as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(!$produitconfig1) {
        $produitconfig1 = $produitconfig->getCepage();
        continue;
    }
    break;
}

$t->comment('On modifie un lot');
$lot = $degustation->lots->get(0);
$drev = DRevClient::getInstance()->find($lot->id_document);
$hash = $lot->produit_hash;
$produit = $lot->getProduitLibelle();
$volume = $lot->volume;

$t->comment('hash originale : '.$hash);
$t->comment('produit original : '.$produit);
$t->comment('volume original : '.$volume);

$lot->volume += 1.12;
$lot->setProduitHash($produitconfig1->getHash());

$degustation->updateLot(0, $lot);
$lot = $degustation->lots->get(0);

$lot_drev = $drev->lots->get(0);
$lot_drev = $lot;
$drev->generateMouvementsLots();

//$lot = $degustation->lots->get(0);
//$lot_drev = $drev->lots->get(0);

$t->comment('Changement dans la dégustation');
$t->is($lot->volume, $volume + 1.12, 'Le volume est changé');
$t->is($lot->produit_hash, $produitconfig1->getHash(), 'La hash a changé');
$t->is($lot->getProduitLibelle(), $produitconfig1->getLibelleComplet(), 'Le nom du produit a changé');

$t->comment('Changement dans la drev');
$t->is($lot_drev->volume, $volume + 1.12, 'Le volume est changé : '.$lot_drev->volume);
