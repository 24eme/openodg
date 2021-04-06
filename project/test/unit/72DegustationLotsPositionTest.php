<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

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

$t->is(count($degustation->lots), 11, '11 lots dans la degustation');
$t->is($l1_t1->getPosition(), '01000', 'l1_t1 position 01000');
$t->is($l2_t1->getPosition(), '01001', 'l2_t1 position 01001');
$t->is($l3_t1->getPosition(), '01002', 'l3_t1 position 01002');
$t->is($l4_t1->getPosition(), '01003', 'l4_t1 position 01003');
$t->is($l5_t1->getPosition(), '01004', 'l5_t1 position 01004');
$t->is($l6_t1->getPosition(), '01005', 'l6_t1 position 01005');
$t->is($l7_t1->getPosition(), '01006', 'l7_t1 position 01006');
$t->is($l8_t2->getPosition(), '02007', 'l1_t1 position 02007');
$t->is($l9_t2->getPosition(), '02008', 'l2_t1 position 02008');
$t->is($l10_t2->getPosition(), '02009', 'l3_t1 position 02009');
$t->is($l11_t2->getPosition(), '02010', 'l4_t1 position 02010');

$t->comment('Test du up');
$l4_t1->upPosition();
$t->is($l3_t1->getPosition(), '01003', 'l3_t1 nouvelle position 01003');
$t->is($l4_t1->getPosition(), '01002', 'l4_t1 nouvelle position 01002');

$t->comment('Test du down');
$l4_t1->downPosition();
$t->is($l3_t1->getPosition(), '01002', 'l3_t1 nouvelle position 01002');
$t->is($l4_t1->getPosition(), '01003', 'l4_t1 nouvelle position 01003');

$t->comment('Test des bornes');
$l1_t1->upPosition();
$t->is($l1_t1->getPosition(), '01000', 'l1_t1 non bougé');
$l7_t1->downPosition();
$t->is($l7_t1->getPosition(), '01006', 'l7_t1 non bougé');

$t->comment('Test deplacement table');
$l4_t1->numero_table = null;
$t->is($l4_t1->getPosition(), '99003', 'l4_t1 en attente d\'attribution de table');
$l4_t1->numero_table = 2;
$t->is($l4_t1->getPosition(), '02011', 'l4_t1 attribué a la table 2');
