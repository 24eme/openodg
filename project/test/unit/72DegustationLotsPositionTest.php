<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}


$produits = $config = ConfigurationClient::getCurrent()->getProduits();
$produit_rouge = null;
$produit_blanc = null;
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    if ($produit->getCouleur()->getKey() == 'rouge') {
        $produit_rouge = $produit->getHash();
    }
    if ($produit->getCouleur()->getKey() == 'blanc') {
        $produit_blanc = $produit->getHash();
    }
    if ($produit_rouge && $produit_blanc) {
        break;
    }
}


$t = new lime_test(45);

$degustation = new Degustation();

$l1_t1 = $degustation->addLeurre($produit_rouge, 'Leurre 1 table 1', null);
$l2_t1 = $degustation->addLeurre($produit_rouge, 'Leurre 2 table 1', null);
$l3_t1 = $degustation->addLeurre($produit_rouge, 'Leurre 3 table 1', null);
$l4_t1 = $degustation->addLeurre($produit_rouge, 'Leurre 4 table 1', null);
$l5_t1 = $degustation->addLeurre($produit_blanc, 'Leurre 5 table 1', null);
$l6_t2 = $degustation->addLeurre($produit_rouge, 'Leurre 6 table 2', 2);
$l7_t2 = $degustation->addLeurre($produit_rouge, 'Leurre 7 table 2', 2);
$l8_t2 = $degustation->addLeurre($produit_blanc, 'Leurre 8 table 2', 2);
$l9_t2 = $degustation->addLeurre($produit_blanc, 'Leurre 9 table 2', 2);
$l10_t1 = $degustation->addLeurre($produit_blanc, 'Leurre 10 table 1', null);
$l11_t1 = $degustation->addLeurre($produit_blanc, 'Leurre 11 table 1', null);
$l12_sanstable = $degustation->addLeurre($produit_blanc, 'leure sans table', null);
$l11_t1->setNumeroTable(1);
$l10_t1->setNumeroTable(1);
$l5_t1->setNumeroTable(1);
$l4_t1->setNumeroTable(1);
$l3_t1->setNumeroTable(1);
$l2_t1->setNumeroTable(1);
$l1_t1->setNumeroTable(1);
$t->is($degustation->tri, "Couleur|Appellation|Cépage", "La dégustation a un tri par defaut Couleur|Appellation|Cépage");

$t->is(count($degustation->lots), 12, '12 lots dans la degustation');
$t->is(count($degustation->getLotsTableCustomSort(1)), 7, '7 lots sur la table 1');
$t->is(count($degustation->getLotsTableCustomSort(2)), 4, '4 lots sur la table 2');

$t->is($l1_t1->getPosition(), '010040', 'l1_t1 position 010040');
$t->is($l2_t1->getPosition(), '010050', 'l2_t1 position 010050');
$t->is($l3_t1->getPosition(), '010060', 'l3_t1 position 010060');
$t->is($l4_t1->getPosition(), '010070', 'l4_t1 position 010070');
$t->is($l5_t1->getPosition(), '010010', 'l5_t1 position 010010');
$t->is($l6_t2->getPosition(), '020030', 'l6_t2 position 020030');
$t->is($l7_t2->getPosition(), '020040', 'l7_t2 position 020040');
$t->is($l8_t2->getPosition(), '020020', 'l8_t2 position 020020');
$t->is($l9_t2->getPosition(), '020010', 'l9_t2 position 020010');
$t->is($l10_t1->getPosition(), '010020', 'l10_t2 position 010020');
$t->is($l11_t1->getPosition(), '010030', 'l11_t2 position 010030');
$t->is($l12_sanstable->getPosition(), '999999', 'l12 non attablée position 999999');

$t->comment('Test du up sur l4_t1');
$l4_t1->changePosition(1);
$t->is($l3_t1->getPosition(), '010070', 'l3_t1 a pris comme position celle de l4_t1');
$t->is($l4_t1->getPosition(), '010060', 'l4_t1 a pris comme position celle de l3_t1');
$t->is($degustation->tri, DegustationClient::DEGUSTATION_TRI_MANUEL."|Couleur|Appellation|Cépage", "Après un changement de position, le tri de la dégustation est Manuel");


$t->comment('Test du down sur l4_t1');
$l4_t1->changePosition(-1);
$t->is($l3_t1->getPosition(), '010060', "l3_t1 a repris sa position d'originie");
$t->is($l4_t1->getPosition(), '010070', "l4_t1 a repris sa position d'originie");

$t->comment('Test des bornes');
$t->is($l7_t2->getPosition(), '020040', 'l7_t2 n a pas bougé');
$l7_t2->changePosition(-1);
$t->is($l7_t2->getPosition(), '020040', 'down sur l7_t2 ne le fait pas bouger');
$t->is($l9_t2->getPosition(), '020010', 'l9_t2 pas bougé');
$l9_t2->changePosition(1);
$t->is($l9_t2->getPosition(), '020010', 'up sur l9_t2 ne le fait pas bouger');

$t->comment('Test deplacement table');
$l4_t1->numero_table = null;
$t->is($l4_t1->getPosition(), '999999', 'l4_t1 en attente d\'attribution de table');
$t->is($l12_sanstable->getPosition(), '999999', "le leure sans table n'a pas changé de position");
$l4_t1->numero_table = 2;
$t->is($l4_t1->getPosition(), '020050', 'l4_t1 attribué a la table 2');
$t->is($l8_t2->getPosition(), '020020', "l8_t2 qui est sur la table 2 n'a pas changé de position (020020)");
$t->is($l9_t2->getPosition(), '020010', "l9_t2 qui est sur la table 2 n'a pas changé de position (020010)");
$t->is($l6_t2->getPosition(), '020030', "l6_t2 qui est sur la table 2 n'a pas changé de position (010030)");
$t->is($l7_t2->getPosition(), '020040', "l7_t2 qui est sur la table 2 n'a pas changé de position (020040)");
$t->is($l12_sanstable->getPosition(), '999999', "le lot sans table n'a pas changé de position (999999)");

$t->comment('Changement de tri');
$degustation->setTri("Cépage|Appellation|Couleur");
$t->is($degustation->tri, "Cépage|Appellation|Couleur", "La dégustation a un tri par defaut Couleur|Appellation|Cépage");
$t->is($l1_t1->getPosition(), '010040', 'l1_t1 position 010040');
$t->is($l2_t1->getPosition(), '010050', 'l2_t1 position 010050');
$t->is($l3_t1->getPosition(), '010060', 'l3_t1 position 010060');
$t->is($l4_t1->getPosition(), '020030', 'l4_t1 position 020030');
$t->is($l5_t1->getPosition(), '010010', 'l5_t1 position 010010');
$t->is($l6_t2->getPosition(), '020040', 'l6_t2 position 020040');
$t->is($l7_t2->getPosition(), '020050', 'l7_t2 position 020050');
$t->is($l8_t2->getPosition(), '020010', 'l8_t2 position 020010');
$t->is($l9_t2->getPosition(), '020020', 'l9_t2 position 020020');
$t->is($l10_t1->getPosition(), '010020', 'l10_t2 position 020020');
$t->is($l11_t1->getPosition(), '010030', 'l11_t2 position 020030');
