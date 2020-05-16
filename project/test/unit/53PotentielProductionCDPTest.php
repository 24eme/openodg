<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

if($application != 'provence') {
    $t = new lime_test(0);
    exit;
}

$t = new lime_test(10);

$ppmanager = new PotentielProductionManager(null);
$generator = $ppmanager->getGenerator(); 

$superficies = [
    'principaux' =>
        [
            'CINSAUT N' => 30.8305,
            'SYRAH N' => 27.6127,
            'GRENACHE N' => 54.7249,
            'MOURVEDRE N' => 4.7927,
            'TIBOUREN N' => 0.216,
            'TOTAL' => 118.1768
        ],
    'secondaires' =>
        [
            'CABERNET SAUVIGNON N' => 8.1396,
            'CARIGNAN N' => 1.0196,
            'CALITOR NOIR N' => 0,
            'BARBAROUX RS' => 0,
            'VERMENTINO B' => 12.857,
            'SEMILLON B' => 2.5733,
            'CLAIRETTE B' => 1.4985,
            'UGNI BLANC B' => 2.1667,
            'TOTAL' => 28.2547
        ],
    'TOTAL' => 146.4315
];
// Test 0 : verification des cepages
$cepages = $generator->getCepages();
$principaux = $superficies['principaux'];
$secondaires = $superficies['secondaires'];
unset($principaux['TOTAL'], $secondaires['TOTAL']);
$principaux = array_keys($principaux);
$secondaires = array_keys($secondaires);
sort($principaux);
sort($secondaires);
sort($cepages['principaux']);
sort($cepages['secondaires']);

$t->is_deeply($cepages['principaux'], $principaux, "Règle 0 : Cépages principaux respectés.");
$t->is_deeply($cepages['secondaires'], $secondaires, "Règle 0b : Cépages secondaires respectés.");

// Test 1 : regle des 2 cepages principaux min
$regle1 = $generator->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
$t->is($regle1, $superficies['principaux']['TOTAL'], "Règle 1 OK : 2 au moins des cépages principaux sont présents dans l'encépagement.");

// Test 2 : regle des 90% cépages principaux
$regle2 = $generator->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 90);
$t->is($regle2, $superficies['principaux']['TOTAL'], "Règle 2 OK : la proportion d'un des cépages principaux n'est pas supérieure à 90% de l'encépagement");

$regle3 = $generator->regleRatioMax_GetRevendicable($regle2, 30/70, $superficies['secondaires']['TOTAL']);
// Test 3 : regle des 70% cepages principaux
$test3 = round(($regle2+$superficies['secondaires']['TOTAL']) * 0.7, 4);
$t->ok($regle2 >= $test3, "Règle 3 OK : La proportion de l'ensemble des cépages principaux est supérieure ou égale à 70% de l'encépagement");

// Test 4 : regle des 30% cepages secondaires
$test4 = round(($regle2+$superficies['secondaires']['TOTAL']) * 0.3, 4);
$t->ok($regle3 <= $test4, "Règle 4 OK : La proportion de l'ensemble des cépages secondaires est inférieure ou égale à 30% de l'encépagement");

$blancs = ['VERMENTINO B' => $superficies['secondaires']['VERMENTINO B'], 'SEMILLON B' => $superficies['secondaires']['SEMILLON B'], 'CLAIRETTE B' => $superficies['secondaires']['CLAIRETTE B'], 'UGNI BLANC B' => $superficies['secondaires']['UGNI BLANC B']];
$revendicableSecondairesTotalBlancs = $generator->regleRatioMax_GetRevendicable($regle2, 20/70, round(array_sum($blancs), 4));
unset($blancs['VERMENTINO B']);
$revendicableSecondairesAutresBlancs = $generator->regleRatioMax_GetRevendicable($regle2, 10/70, round(array_sum($blancs), 4));
// Test 5 : regle des 20% de blancs max
$test5 = round(($regle2+$regle3) * 0.2, 4);
$t->ok($revendicableSecondairesTotalBlancs <= $test5, "Règle 5 OK : La proportion de l'ensemble des cépages secondaires blancs est inférieure ou égale à 20% de l'encépagement");

// Test 6 : regle des 10% des autres blancs max
$test6 = round(($regle2+$regle3) * 0.1, 4);
$t->ok($revendicableSecondairesAutresBlancs <= $test6, "Règle 6 OK : La proportion de l'ensemble des cépages secondaires autres blancs est inférieure ou égale à 10% de l'encépagement");

// Test 7 : verification du potentiel de production calculé
$revendicables = $generator->calculateRevendicableCDP($superficies);
$result = ['principaux' => 118.1768, 'secondairesnoirs' => 9.1592, 'secondairesblancs' => 19.0955, 'secondairesvermentinob' => 12.857, 'secondairesautresblancs' => 6.2385];
$t->is_deeply($revendicables, $result, "Règle 7 : Calcul du potentiel de production OK.");

// Test 8 : comparaison avec la calculette
$result = ['principaux' => 118.1768, 'secondairesnoirs' => 9.1592, 'secondairesblancs' => 19.0955, 'secondairesvermentinob' => 12.857, 'secondairesautresblancs' => 6.2385];
$t->is_deeply($revendicables, $result, "Règle 8 : Comparaison avec la calculette");