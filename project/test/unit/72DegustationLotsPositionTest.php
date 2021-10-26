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
$produit_rose = null;
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
    if ($produit->getCouleur()->getKey() == 'rose') {
        $produit_rose = $produit->getHash();
    }
    if ($produit_rouge && $produit_blanc && $produit_rose) {
        break;
    }
}


$t = new lime_test(110);

$degustation = new Degustation();

$l1_t1_rouge = $degustation->addLeurre($produit_rouge, 'Leurre 1 table 1 rouge', null);
$l2_t1_rouge = $degustation->addLeurre($produit_rouge, 'Leurre 2 table 1 rouge', null);
$l3_t1_rouge = $degustation->addLeurre($produit_rouge, 'Leurre 3 table 1 rouge', null);
$l4_t1_rouge = $degustation->addLeurre($produit_rouge, 'Leurre 4 table 1 rouge', null);
$l5_t1_blanc = $degustation->addLeurre($produit_blanc, 'Leurre 5 table 1 blanc', null);
$l6_t2_rouge = $degustation->addLeurre($produit_rouge, 'Leurre 6 table 2 rouge', 2);
$l7_t2_rouge = $degustation->addLeurre($produit_rouge, 'Leurre 7 table 2 rouge', 2);
$l8_t2_blanc = $degustation->addLeurre($produit_blanc, 'Leurre 8 table 2 blanc', 2);
$l9_t2_blanc = $degustation->addLeurre($produit_blanc, 'Leurre 9 table 2 blanc', 2);
$l10_t1_blanc = $degustation->addLeurre($produit_blanc, 'Leurre 10 table 1 blanc', null);
$l11_t1_blanc = $degustation->addLeurre($produit_blanc, 'Leurre 11 table 1 blanc', null);
$l12_sanstable_rose = $degustation->addLeurre($produit_rose, 'leure sans table rosé', null);
$l13_sanstable_blanc = $degustation->addLeurre($produit_blanc, 'leure sans table blanc', null);

$l11_t1_blanc->setNumeroTable(1);
$l10_t1_blanc->setNumeroTable(1);
$l5_t1_blanc->setNumeroTable(1);
$l4_t1_rouge->setNumeroTable(1);
$l3_t1_rouge->setNumeroTable(1);
$l2_t1_rouge->setNumeroTable(1);
$l1_t1_rouge->setNumeroTable(1);
$t->is($degustation->tri, "Couleur|Appellation|Cépage", "La dégustation a un tri par defaut Couleur|Appellation|Cépage");

$t->is(count($degustation->lots), 13, '13 lots dans la degustation');
$t->is(count($degustation->getLotsTableCustomSort(1)), 7, '7 lots sur la table 1');
$t->is(count($degustation->getLotsTableCustomSort(2)), 4, '4 lots sur la table 2');

$t->is($l5_t1_blanc->getPosition(), '010010', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010010');
$t->is($l10_t1_blanc->getPosition(), '010020', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010020');
$t->is($l11_t1_blanc->getPosition(), '010030', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010030');
$t->is($l1_t1_rouge->getPosition(), '010040', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010040');
$t->is($l2_t1_rouge->getPosition(), '010050', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010050');
$t->is($l3_t1_rouge->getPosition(), '010060', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010060');
$t->is($l4_t1_rouge->getPosition(), '010070', 'l4_t1_rouge '.$l4_t1_rouge->details.' position 010070');
$t->is($l9_t2_blanc->getPosition(), '020010', 'l9_t2_blanc '.$l9_t2_blanc->details.' position 020010');
$t->is($l8_t2_blanc->getPosition(), '020020', 'l8_t2_blanc '.$l8_t2_blanc->details.' position 020020');
$t->is($l6_t2_rouge->getPosition(), '020030', 'l6_t2_rouge '.$l6_t2_rouge->details.' position 020030');
$t->is($l7_t2_rouge->getPosition(), '020040', 'l7_t2_rouge '.$l7_t2_rouge->details.' position 020040');

$t->is($l12_sanstable_rose->getPosition(), '999900', 'l12 non attablée position 999900');
$t->is($l13_sanstable_blanc->getPosition(), '999900', 'l13 non attablée position 999900');

$t->comment('Test du up sur l4_t1_rouge');
$l4_t1_rouge->changePosition(1);
$t->is($l3_t1_rouge->getPosition(), '010070', 'l3_t1_rouge '.$l3_t1_rouge->details.' a pris comme position celle de l4_t1_rouge');
$t->is($l4_t1_rouge->getPosition(), '010061', 'l4_t1_rouge '.$l4_t1_rouge->details.' a pris comme position celle de l3_t1_rouge');
$l4_t1_rouge->changePosition(1);
$t->is($l4_t1_rouge->getPosition(), '010051', 'l4_t1_rouge '.$l4_t1_rouge->details.' a pris comme position celle de l3_t1_rouge');
$t->is($l2_t1_rouge->getPosition(), '010060', 'l2_t1_rouge '.$l4_t1_rouge->details.' a pris comme position celle de l2_t1_rouge');

$t->is($degustation->tri, DegustationClient::DEGUSTATION_TRI_MANUEL."|Couleur|Appellation|Cépage", "Après un changement de position, le tri de la dégustation est Manuel");


$t->comment('Test du down sur l4_t1_rouge');
$l4_t1_rouge->changePosition(-1);
$l4_t1_rouge->changePosition(-1);
$t->is($l3_t1_rouge->getPosition(), '010060', 'l3_t1_rouge '.$l3_t1_rouge->details." a repris sa position d'originie");
$t->is($l4_t1_rouge->getPosition(), '010070', 'l4_t1_rouge '.$l4_t1_rouge->details." a repris sa position d'originie");


$t->comment('Test des bornes');
$t->is($l7_t2_rouge->getPosition(), '020040', 'l7_t2_rouge '.$l7_t2_rouge->details.' n a pas bougé');
$l7_t2_rouge->changePosition(-1);
$t->is($l7_t2_rouge->getPosition(), '020040', 'down sur l7_t2_rouge '.$l7_t2_rouge->details.' ne le fait pas bouger');
$t->is($l9_t2_blanc->getPosition(), '020010', 'l9_t2_blanc '.$l9_t2_blanc->details.' pas bougé');
$l9_t2_blanc->changePosition(1);
$t->is($l9_t2_blanc->getPosition(), '020010', 'up sur l9_t2_blanc '.$l9_t2_blanc->details.' ne le fait pas bouger');

$t->comment('Test deplacement table');
$l4_t1_rouge->numero_table = null;
$t->is($l4_t1_rouge->getPosition(), '999900', 'l4_t1_rouge '.$l4_t1_rouge->details.' en attente d\'attribution de table');
$t->is($l12_sanstable_rose->getPosition(), '999900', "le leure sans table n'a pas changé de position");
$l4_t1_rouge->numero_table = 2;
$t->is($l8_t2_blanc->getPosition(), '020020', 'l8_t2_blanc '.$l8_t2_blanc->details." qui est sur la table 2 n'a pas changé de position (020020)");
$t->is($l9_t2_blanc->getPosition(), '020010', 'l9_t2_blanc '.$l9_t2_blanc->details." qui est sur la table 2 n'a pas changé de position (020010)");
$t->is($l4_t1_rouge->getPosition(), '020030', 'l4_t1_rouge '.$l4_t1_rouge->details.' attribué a la table 2 (020050)');
$t->is($l6_t2_rouge->getPosition(), '020040', 'l6_t2_rouge '.$l6_t2_rouge->details." qui est sur la table 2 s'est décalé d'une position pour faire place au nouveau lot attablé (020030)");
$t->is($l7_t2_rouge->getPosition(), '020050', 'l7_t2_rouge '.$l7_t2_rouge->details." qui est sur la table 2 s'est décalé d'une position pour faire place au nouveau lot attablé (020040)");
$t->is($l12_sanstable_rose->getPosition(), '999900', "le lot sans table n'a pas changé de position (999900)");

$t->comment('Changement de tri');
$degustation->setTri("Cépage|Appellation|Couleur");
$t->is($degustation->tri, "Cépage|Appellation|Couleur", "La dégustation a un tri par defaut Couleur|Appellation|Cépage");
$t->is($l5_t1_blanc->getPosition(), '010010', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010010');
$t->is($l10_t1_blanc->getPosition(), '010020', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010020');
$t->is($l11_t1_blanc->getPosition(), '010030', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010030');
$t->is($l1_t1_rouge->getPosition(), '010040', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010040');
$t->is($l2_t1_rouge->getPosition(), '010050', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010050');
$t->is($l3_t1_rouge->getPosition(), '010060', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010060');
$t->is($l8_t2_blanc->getPosition(), '020010', 'l8_t2_blanc '.$l8_t2_blanc->details.' position 020010');
$t->is($l9_t2_blanc->getPosition(), '020020', 'l9_t2_blanc '.$l9_t2_blanc->details.' position 020020');
$t->is($l4_t1_rouge->getPosition(), '020030', 'l4_t1_rouge '.$l4_t1_rouge->details.' position 020030');
$t->is($l6_t2_rouge->getPosition(), '020040', 'l6_t2_rouge '.$l6_t2_rouge->details.' position 020040');
$t->is($l7_t2_rouge->getPosition(), '020050', 'l7_t2_rouge '.$l7_t2_rouge->details.' position 020050');

$degustation->setTri("Appellation|Couleur|Cépage");
$t->is($degustation->tri, "Appellation|Couleur|Cépage", "le tri est repassé en Appellation|Couleur|Cépage");
$t->comment('on met un rouge en tête de tri');
$l3_t1_rouge->changePosition(1);
$l3_t1_rouge->changePosition(1);
$l3_t1_rouge->changePosition(1);
$l3_t1_rouge->changePosition(1);
$l3_t1_rouge->changePosition(1);
$t->is($degustation->tri, DegustationClient::DEGUSTATION_TRI_MANUEL."|Appellation|Couleur|Cépage", "le tri est activé (manuel|Appellation|Couleur|Cépage)");
$t->is($l3_t1_rouge->getPosition(), '010011', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010011');
$t->is($l5_t1_blanc->getPosition(), '010020', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010020');
$t->is($l10_t1_blanc->getPosition(), '010030', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010030');
$t->is($l11_t1_blanc->getPosition(), '010040', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010040');
$t->is($l1_t1_rouge->getPosition(), '010050', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010050');
$t->is($l2_t1_rouge->getPosition(), '010060', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010060');

$autrelot1_sanstable_blanc = $degustation->addLeurre($produit_blanc, 'leure sans table blanc', null);
$autrelot2_sanstable_blanc = $degustation->addLeurre($produit_blanc, 'leure sans table blanc', null);
$autrelot3_sanstable_rouge = $degustation->addLeurre($produit_rouge, 'leure sans table rouge', null);
$autrelot4_sanstable_rouge = $degustation->addLeurre($produit_rouge, 'leure sans table rouge', null);
$t->comment("on ajout deux lots de deux couleurs différentes");
$l12_sanstable_rose->setNumeroTable(1);
$l13_sanstable_blanc->setNumeroTable(1);

$t->is($l3_t1_rouge->getPosition(), '010011', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010011');
$t->is($l5_t1_blanc->getPosition(), '010020', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010020');
$t->is($l10_t1_blanc->getPosition(), '010030', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010030');
$t->is($l11_t1_blanc->getPosition(), '010040', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010040');
$t->is($l13_sanstable_blanc->getPosition(), '010050', 'l13 maintenant attablié en 1 ('.$l13_sanstable_blanc->details.') position 010050');
$t->is($l12_sanstable_rose->getPosition(), '010060', 'l12 maintenant attablié en 1 ('.$l12_sanstable_rose->details.') position 010060');
$t->is($l1_t1_rouge->getPosition(), '010070', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010070');
$t->is($l2_t1_rouge->getPosition(), '010080', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010080');

$t->comment('on remet le rouge à sa place');
$l3_t1_rouge->changePosition(-1);
$l3_t1_rouge->changePosition(-1);
$l3_t1_rouge->changePosition(-1);
$l3_t1_rouge->changePosition(-1);
$l3_t1_rouge->changePosition(-1);
$l3_t1_rouge->changePosition(-1);
$l3_t1_rouge->changePosition(-1);
$t->is($l5_t1_blanc->getPosition(), '010010', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010010');
$t->is($l10_t1_blanc->getPosition(), '010020', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010020');
$t->is($l11_t1_blanc->getPosition(), '010030', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010030');
$t->is($l13_sanstable_blanc->getPosition(), '010040', 'l13 maintenant attablié en 1 ('.$l13_sanstable_blanc->details.') position 010040');
$t->is($l12_sanstable_rose->getPosition(), '010050', 'l12 maintenant attablié en 1 ('.$l12_sanstable_rose->details.') position 010050');
$t->is($l1_t1_rouge->getPosition(), '010060', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010060');
$t->is($l2_t1_rouge->getPosition(), '010070', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010070');
$t->is($l3_t1_rouge->getPosition(), '010080', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010080');
$t->comment('on retire les deux sans tables pour revenir aux conditions originales');
$l12_sanstable_rose->setNumeroTable(null);
$l13_sanstable_blanc->setNumeroTable(null);
$t->is($l5_t1_blanc->getPosition(), '010010', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010010');
$t->is($l10_t1_blanc->getPosition(), '010020', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010020');
$t->is($l11_t1_blanc->getPosition(), '010030', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010030');
$t->is($l1_t1_rouge->getPosition(), '010040', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010040');
$t->is($l2_t1_rouge->getPosition(), '010050', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010050');
$t->is($l3_t1_rouge->getPosition(), '010060', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010060');
$t->is($l13_sanstable_blanc->getPosition(), '999900', 'l13 maintenant attablié en 1 ('.$l13_sanstable_blanc->details.') position 999900');
$t->is($l12_sanstable_rose->getPosition(), '999900', 'l12 maintenant attablié en 1 ('.$l12_sanstable_rose->details.') position 999900');

$t->comment("on met un blanc en queue");
$l5_t1_blanc->changePosition(-1);
$l5_t1_blanc->changePosition(-1);
$l5_t1_blanc->changePosition(-1);
$l5_t1_blanc->changePosition(-1);
$l5_t1_blanc->changePosition(-1);
$t->is($l10_t1_blanc->getPosition(), '010010', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010010');
$t->is($l11_t1_blanc->getPosition(), '010020', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010020');
$t->is($l1_t1_rouge->getPosition(), '010030', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010030');
$t->is($l2_t1_rouge->getPosition(), '010040', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010040');
$t->is($l3_t1_rouge->getPosition(), '010050', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010050');
$t->is($l5_t1_blanc->getPosition(), '010061', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010061');
$t->comment("on ajout trois lots de couleurs différentes");
$autrelot3_sanstable_rouge->setNumeroTable(1);
$t->is($l10_t1_blanc->getPosition(), '010010', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010010');
$t->is($l11_t1_blanc->getPosition(), '010020', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010020');
$t->is($l1_t1_rouge->getPosition(), '010030', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010030');
$t->is($l2_t1_rouge->getPosition(), '010040', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010040');
$t->is($l3_t1_rouge->getPosition(), '010050', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010050');
$t->is($autrelot3_sanstable_rouge->getPosition(), '010060', 'autrelot3_sanstable_rouge '.$autrelot3_sanstable_rouge->details.' position 010060');
$t->is($l5_t1_blanc->getPosition(), '010071', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010071');

$l12_sanstable_rose->setNumeroTable(1);

$t->is($l10_t1_blanc->getPosition(), '010010', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010010');
$t->is($l11_t1_blanc->getPosition(), '010020', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010020');
$t->is($l12_sanstable_rose->getPosition(), '010030', 'l12_sanstable_rose '.$l12_sanstable_rose->details.' position 010030');
$t->is($l1_t1_rouge->getPosition(), '010040', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010040');
$t->is($l2_t1_rouge->getPosition(), '010050', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010050');
$t->is($l3_t1_rouge->getPosition(), '010060', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010060');
$t->is($autrelot3_sanstable_rouge->getPosition(), '010070', 'autrelot3_sanstable_rouge '.$autrelot3_sanstable_rouge->details.' position 010070');
$t->is($l5_t1_blanc->getPosition(), '010081', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010081');


$l13_sanstable_blanc->setNumeroTable(1);
$t->is($l10_t1_blanc->getPosition(), '010010', 'l10_t1_blanc '.$l10_t1_blanc->details.' position 010010');
$t->is($l11_t1_blanc->getPosition(), '010020', 'l11_t1_blanc '.$l11_t1_blanc->details.' position 010020');
$t->is($l13_sanstable_blanc->getPosition(), '010030', 'l13_sanstable_blanc '.$l13_sanstable_blanc->details.' position 010030');
$t->is($l12_sanstable_rose->getPosition(), '010040', 'l12_sanstable_rose '.$l12_sanstable_rose->details.' position 010040');
$t->is($l1_t1_rouge->getPosition(), '010050', 'l1_t1_rouge '.$l1_t1_rouge->details.' position 010050');
$t->is($l2_t1_rouge->getPosition(), '010060', 'l2_t1_rouge '.$l2_t1_rouge->details.' position 010060');
$t->is($l3_t1_rouge->getPosition(), '010070', 'l3_t1_rouge '.$l3_t1_rouge->details.' position 010070');
$t->is($autrelot3_sanstable_rouge->getPosition(), '010080', 'autrelot3_sanstable_rouge '.$autrelot3_sanstable_rouge->details.' position 010080');
$t->is($l5_t1_blanc->getPosition(), '010091', 'l5_t1_blanc '.$l5_t1_blanc->details.' position 010091');
