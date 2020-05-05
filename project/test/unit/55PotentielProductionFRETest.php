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
        ],
    'TOTAL' => 24.1303
];
$superficiesRose = [
    'principaux' =>
    [
        'SYRAH N' => 6.6127,
        'GRENACHE N' => 12.7249,
        'MOURVEDRE N' => 4.7927,
        'TIBOUREN N' => 0.216,
        'TOTAL' => 24.3463
    ],
    'secondaires' =>
    [
        'CINSAUT N' => 4.8305,
        'TOTAL' => 4.8305
    ],
    'TOTAL' => 29.1768
];
// Test 0 : verification des cepages
$cepages = $generator->getCepages('FRE', 'rouge');
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

$cepages = $generator->getCepages('FRE', 'rose');
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
$revendicablesRose = $generator->calculateRevendicableFRERose($superficiesRose);
$result = ['principaux' => 24.3463, 'secondaires' => 4.8305];
$t->is_deeply($revendicablesRose, $result, "Règle 1 : Calcul du potentiel de production FRE Rose OK.");

// Test 2 : verification du potentiel de production calculé pour le rouge
$revendicablesRouge = $generator->calculateRevendicableFRERouge($superficiesRouge);
$result = ['principaux' => 24.1303];
$t->is_deeply($revendicablesRouge, $result, "Règle 2 : Calcul du potentiel de production FRE Rouge OK.");

// Test 3 : comparaison rosé avec la calculette
$result = ['principaux' => 2.16, 'secondaires' => 0.54];
$t->is_deeply($revendicablesRose, $result, "Règle 3 : Comparaison rosé avec la calculette");

// Test 4 : comparaison rouge avec la calculette
$result = ['principaux' => 24.1303];
$t->is_deeply($revendicablesRouge, $result, "Règle 4 : Comparaison rouge avec la calculette");