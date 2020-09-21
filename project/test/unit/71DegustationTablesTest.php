<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$campagne = (date('Y')-1)."";
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";

$doc = acCouchdbManager::getClient()->find($docid);

$t->is($doc->hasFreeLots(), true, 'Il y a des lots non assignés');

$t->comment('On attribue le lot à la première table');
$doc->getLots()[0]->numero_table = 1;
$t->is(count($doc->getLotsTableOrFreeLots(1)), 1, 'La table 1 à un lot');
$t->is($doc->hasFreeLots(), false, "Il n'y a plus de lot non assigné");

$t->comment('On créé un leurre');
$produitLeurreHash = $doc->getLots()[0]->getProduitHash();
$produitLeurre = $doc->lots->add();
$produitLeurre->setProduitHash($produitLeurreHash);
$produitLeurre->leurre = true;
$produitLeurre->declarant_nom = 'SARL Leurre';
$produitLeurre->numero = '999';

$t->is($produitLeurre->leurre, true, 'Le produit est un leurre');
$t->is($produitLeurre->produit_hash, $produitLeurreHash, "Le hash produit est $produitLeurreHash");
$t->is($produitLeurre->getIntitulePartiel(), 'lot SARL Leurre (999) de Alpilles Rouge', 'Le libellé est correct');

$t->is($doc->hasFreeLots(), true, 'Le leurre n\'est pas assigné');

$t->comment('On assigne le lot à la table 1');
$produitLeurre->numero_table = 1;
$t->is($doc->hasFreeLots(), false, "Le leurre est assigné");
$t->is(count($doc->getLotsTableOrFreeLots(1)), 2, "Il est assigné à la table 1");

$t->comment('On ajoute une table');
$t->is($doc->getLastNumeroTable(), 1, 'La table courante est la 1');
$doc->lots->add();
$doc->lots[2] = clone $doc->lots[0];
$doc->lots[2]->numero = $doc->lots[0]->numero + 1;
$doc->lots[2]->numero_table = 2;
$t->is($doc->getLastNumeroTable(), 2, 'La dernière table est la 2');

$t->comment('puis on la retire');
$doc->lots->remove(2);
$t->is($doc->getLastNumeroTable(), 1, 'La dernière table est la 1');
