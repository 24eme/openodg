<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test(43);

$degustation = new Degustation();

$l1_t1 = $degustation->addLeurre(null, 'Leurre 1 table 1', 1);
$l2_t1 = $degustation->addLeurre(null, 'Leurre 2 table 1', 1);
$l3_t1 = $degustation->addLeurre(null, 'Leurre 3 table 1', 1);
$l4_t1 = $degustation->addLeurre(null, 'Leurre 4 table 1', 1);
$l5_t1 = $degustation->addLeurre(null, 'Leurre 5 table 1', 1);
$l6_t1 = $degustation->addLeurre(null, 'Leurre 6 table 1', 1);
$l7_t1 = $degustation->addLeurre(null, 'Leurre 7 table 1', 1);
$l8_t2 = $degustation->addLeurre(null, 'Leurre 8 table 2', 2);
$l9_t2 = $degustation->addLeurre(null, 'Leurre 9 table 2', 2);
$l10_t2 = $degustation->addLeurre(null, 'Leurre 10 table 2', 2);
$l11_t2 = $degustation->addLeurre(null, 'Leurre 11 table 2', 2);
$l12_sanstable = $degustation->addLeurre(null, 'leure sans table', null);

$t->is($degustation->tri, "Couleur|Appellation|Cépage", "La dégustation a un tri par defaut Couleur|Appellation|Cépage");

$t->is(count($degustation->lots), 12, '12 lots dans la degustation');
$t->is(count($degustation->getLotsTableCustomSort(1)), 7, '7 lots sur la table 1');
$t->is(count($degustation->getLotsTableCustomSort(2)), 4, '4 lots sur la table 2');

$t->is($l1_t1->getPosition(), '010010', 'l1_t1 position 010010');
$t->is($l2_t1->getPosition(), '010020', 'l2_t1 position 010020');
$t->is($l3_t1->getPosition(), '010030', 'l3_t1 position 010030');
$t->is($l4_t1->getPosition(), '010040', 'l4_t1 position 010040');
$t->is($l5_t1->getPosition(), '010050', 'l5_t1 position 010050');
$t->is($l6_t1->getPosition(), '010060', 'l6_t1 position 010060');
$t->is($l7_t1->getPosition(), '010070', 'l7_t1 position 010070');
$t->is($l8_t2->getPosition(), '020010', 'l8_t2 position 020010');
$t->is($l9_t2->getPosition(), '020020', 'l9_t2 position 020020');
$t->is($l10_t2->getPosition(), '020030', 'l10_t2 position 020030');
$t->is($l11_t2->getPosition(), '020040', 'l11_t2 position 020040');
$t->is($l12_sanstable->getPosition(), '990010', 'l12 non attablée position 990010');

$t->comment('Test du up sur l4_t1');
$l4_t1->changePosition(1);
$t->is($l3_t1->getPosition(), '010040', 'l3_t1 a pris comme position celle de l4_t1');
$t->is($l4_t1->getPosition(), '010030', 'l4_t1 a pris comme position celle de l3_t1');
$t->is($degustation->tri, DegustationClient::DEGUSTATION_TRI_MANUEL."|Couleur|Appellation|Cépage", "Après un changement de position, le tri de la dégustation est Manuel");


$t->comment('Test du down sur l4_t1');
$l4_t1->changePosition(-1);
$t->is($l3_t1->getPosition(), '010030', "l3_t1 a repris sa position d'originie");
$t->is($l4_t1->getPosition(), '010040', "l4_t1 a repris sa position d'originie");

$t->comment('Test des bornes');
$l1_t1->changePosition(1);
$t->is($l1_t1->getPosition(), '010010', 'up sur l1_t1 ne le fait pas bouger');
$l7_t1->changePosition(-1);
$t->is($l7_t1->getPosition(), '010070', 'down sur l7_t1 ne le fait pas bouger');

$t->comment('Test deplacement table');
$l4_t1->numero_table = null;
$t->is($l4_t1->getPosition(), '990020', 'l4_t1 en attente d\'attribution de table');
$t->is($l12_sanstable->getPosition(), '990010', "le leure sans table n'a pas changé de position");
$l4_t1->numero_table = 2;
$t->is($l4_t1->getPosition(), '020050', 'l4_t1 attribué a la table 2');
$t->is($l8_t2->getPosition(), '020010', "l8_t2 qui est sur la table 2 n'a pas changé de position (020010)");
$t->is($l9_t2->getPosition(), '020020', "l9_t2 qui est sur la table 2 n'a pas changé de position (020020)");
$t->is($l10_t2->getPosition(), '020030', "l10_t2 qui est sur la table 2 n'a pas changé de position (020030)");
$t->is($l11_t2->getPosition(), '020040', "l11_t2 qui est sur la table 2 n'a pas changé de position (020040)");
$t->is($l12_sanstable->getPosition(), '990010', "le lot sans table n'a pas changé de position (990010)");


$t->comment('Changement de tri');
$degustation->setTri("Cépage|Appellation|Couleur");
$t->is($degustation->tri, "Cépage|Appellation|Couleur", "La dégustation a un tri par defaut Couleur|Appellation|Cépage");
$t->is($l1_t1->getPosition(), '010010', 'l1_t1 position 010010');
$t->is($l2_t1->getPosition(), '010020', 'l2_t1 position 010020');
$t->is($l3_t1->getPosition(), '010030', 'l3_t1 position 010030');
$t->is($l4_t1->getPosition(), '020010', 'l4_t1 position 020010');
$t->is($l5_t1->getPosition(), '010040', 'l5_t1 position 010040');
$t->is($l6_t1->getPosition(), '010050', 'l6_t1 position 010050');
$t->is($l7_t1->getPosition(), '010060', 'l7_t1 position 010060');
$t->is($l8_t2->getPosition(), '020020', 'l1_t1 position 020020');
$t->is($l9_t2->getPosition(), '020030', 'l2_t1 position 020030');
$t->is($l10_t2->getPosition(), '020040', 'l3_t1 position 020040');
$t->is($l11_t2->getPosition(), '020050', 'l4_t1 position 020050');

