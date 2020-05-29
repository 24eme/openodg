<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

if($application != 'provence') {
    $t = new lime_test(0);
    exit;
}

$t = new lime_test(8);

$ppmanager = new PotentielProductionManager(null);
$generator = $ppmanager->getGenerator(); 

$superficiesRouge = [
    'principaux' =>
        [
            'SYRAH N' => 6.6127,
            'GRENACHE N' => 12.7249,
            'MOURVEDRE N' => 4.7927,
            'TOTAL' => 24.1303
        ],
    'secondaires' =>
        [
            'CABERNET SAUVIGNON N' => 3.1396,
            'CARIGNAN N' => 1.0196,
            'TOTAL' => 4.1592
        ],
    'TOTAL' => 28.2895
];
$superficiesRose = [
    'principaux' =>
    [
        'GRENACHE N' => 12.7249,
        'CINSAUT N' => 4.8305,
        'SYRAH N' => 6.6127,
        'TOTAL' => 24.1681
    ],
    'secondaires' =>
    [
        'CLAIRETTE B' => 0,
        'MOURVEDRE N' => 4.7927,
        'TIBOUREN N' => 0.216,
        'SEMILLON B' => 1.5733,
        'UGNI BLANC B' => 2.1667,
        'VERMENTINO B' => 5.857,
        'TOTAL' => 14.6057
    ],
    'TOTAL' => 38.7738
];
// Test 0 : verification des cepages
$cepages = $generator->getCepages('PIE', 'rouge');
$principaux = $superficiesRouge['principaux'];
$secondaires = $superficiesRouge['secondaires'];
unset($principaux['TOTAL'], $secondaires['TOTAL']);
$principaux = array_keys($principaux);
$secondaires = array_keys($secondaires);
sort($principaux);
sort($secondaires);
sort($cepages['principaux']);
sort($cepages['secondaires']);

$t->is_deeply($cepages['principaux'], $principaux, "Règle 0 : Cépages principaux respectés pour le rouge.");
$t->is_deeply($cepages['secondaires'], $secondaires, "Règle 0 : Cépages secondaires respectés pour le rouge.");

$cepages = $generator->getCepages('PIE', 'rose');
$principaux = $superficiesRose['principaux'];
$secondaires = $superficiesRose['secondaires'];
unset($principaux['TOTAL'], $secondaires['TOTAL']);
$principaux = array_keys($principaux);
$secondaires = array_keys($secondaires);
sort($principaux);
sort($secondaires);
sort($cepages['principaux']);
sort($cepages['secondaires']);

$t->is_deeply($cepages['principaux'], $principaux, "Règle 0b : Cépages principaux respectés pour le rose.");
$t->is_deeply($cepages['secondaires'], $secondaires, "Règle 0b : Cépages secondaires respectés pour le rose.");

// Test 1 : verification du potentiel de production calculé pour le rosé
$revendicablesRose = $generator->calculateRevendicablePIERose($superficiesRose);
$result = ['principaux' => 24.1681,  'secondairesnoirs' => 0,  'secondairesblancs' => 6.042,  'secondairesvermentinob' => 3.021,  'secondairesautresblancs' => 3.021];
$t->is_deeply($revendicablesRose, $result, "Règle 1 : Calcul du potentiel de production PIE Rose OK.");

// Test 2 : verification du potentiel de production calculé pour le rouge
$revendicablesRouge = $generator->calculateRevendicablePIERouge($superficiesRouge);
$result = ['principaux' => 24.1303,  'secondaires' => 4.1592];
$t->is_deeply($revendicablesRouge, $result, "Règle 2 : Calcul du potentiel de production PIE Rouge OK.");

// Test 3 : comparaison rosé avec la calculette
$result = ['principaux' => 24.1681,  'secondairesnoirs' => 5.0087,  'secondairesblancs' => 1.0333,  'secondairesvermentinob' => 0,  'secondairesautresblancs' => 1.0333];
$t->is_deeply($revendicablesRose, $result, "Règle 3 : Comparaison rosé avec la calculette");

// Test 4 : comparaison rouge avec la calculette
$result = ['principaux' => 24.1303,  'secondaires' => 6.0326];
$t->is_deeply($revendicablesRouge, $result, "Règle 4 : Comparaison rouge avec la calculette");