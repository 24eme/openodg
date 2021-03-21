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
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date));

$doc = acCouchdbManager::getClient()->find($docid);
$lot1 = $doc->getLots()[0];

$t->is($doc->hasFreeLots(), true, 'Il y a des lots non assignés');

$t->comment('On attribue le lot à la première table');
$lot1->attributionTable(1);
$t->is(count($doc->getLotsTableOrFreeLots(1)), 1, 'La table 1 à un lot');
$t->is($doc->hasFreeLots(), false, "Il n'y a plus de lot non assigné");

$doc->generateMouvementsLots();
$t->is(count($doc->mouvements_lots->{$lot1->declarant_identifiant}), 4, 'La génération de mouvement a généré 4 lots');

$t->comment('On créé un leurre');
$produitLeurreHash = $lot1->getProduitHash();
$produitLeurre = $doc->lots->add();
$produitLeurre->setProduitHash($produitLeurreHash);
$produitLeurre->leurre = true;
$produitLeurre->declarant_nom = 'SARL Leurre';
$produitLeurre->numero_logement_operateur = '999';

$t->is($produitLeurre->leurre, true, 'Le produit est un leurre');
$t->is($produitLeurre->produit_hash, $produitLeurreHash, "Le hash produit est $produitLeurreHash");
$t->is($produitLeurre->getIntitulePartiel(), 'lot SARL Leurre (999) de Alpilles Rouge', 'Le libellé est correct');

$t->is($doc->hasFreeLots(), true, 'Le leurre n\'est pas assigné');

$t->comment('On assigne le lot à la table 1');
$produitLeurre->attributionTable(1);
$t->is($doc->hasFreeLots(), false, "Le leurre est assigné");
$t->is(count($doc->getLotsTableOrFreeLots(1)), 2, "Il est assigné à la table 1");

$t->comment('On ajoute une table');
$t->is($doc->getLastNumeroTable(), 1, 'La table courante est la 1');
$doc->lots->add();
$doc->lots[2] = clone $lot1;
$doc->lots[2]->numero_logement_operateur = $lot1->numero_logement_operateur + 1;
$doc->lots[2]->attributionTable(2);
$t->is($doc->getLastNumeroTable(), 2, 'La dernière table est la 2');

$t->comment('On ajoute un leurre à la table 2');
$leurreTable2 = $doc->addLeurre($produitLeurreHash, 'Cepage leurre', 2);
$t->is($leurreTable2->leurre, true, 'C\'est un leurre');
$t->is($leurreTable2->getProduitHash(), $produitLeurreHash, 'Le hash est le même');
$t->is($leurreTable2->numero_table, 2, 'Le numéro de table est le 2');
$t->is($leurreTable2->details, 'Cepage leurre', 'Le cepage du leurre est "Cepage leurre"');
$doc->lots->remove(3);
$doc->save();

$t->comment('puis on la retire');
$doc->lots->remove(2);
$t->is($doc->getLastNumeroTable(), 1, 'La dernière table est la 1');
