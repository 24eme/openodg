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
        'TOTAL' => 17.5554
    ],
    'secondaires' =>
    [
        'CARIGNAN N' => 1.0196,
        'CLAIRETTE B' => 0,
        'MOURVEDRE N' => 4.7927,
        'SEMILLON B' => 1.5733,
        'SYRAH N' => 6.6127,
        'TIBOUREN N' => 0.216,
        'UGNI BLANC B' => 2.1667,
        'VERMENTINO B' => 5.857,
        'TOTAL' => 22.238
    ],
    'TOTAL' => 39.7934
];
$superficiesBlanc = [
    'principaux' =>
    [
        'VERMENTINO B' => 5.857,
        'TOTAL' => 5.857
    ],
    'secondaires' =>
    [
        'SEMILLON B' => 1.5733,
        'CLAIRETTE B' => 0,
        'UGNI BLANC B' => 2.1667,
        'TOTAL' => 3.74
    ],
    'TOTAL' => 9.597
];
// Test 0 : verification des cepages
$cepages = $generator->getCepages('LLO', 'rouge');
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

$cepages = $generator->getCepages('LLO', 'rose');
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

$cepages = $generator->getCepages('LLO', 'blanc');
$principaux = $superficiesBlanc['principaux'];
$secondaires = $superficiesBlanc['secondaires'];
unset($principaux['TOTAL'], $secondaires['TOTAL']);
$principaux = array_keys($principaux);
$secondaires = array_keys($secondaires);
sort($principaux);
sort($secondaires);
sort($cepages['principaux']);
sort($cepages['secondaires']);

$t->is_deeply($cepages['principaux'], $principaux, "Règle 0t : Cépages principaux respectés pour le blanc.");
$t->is_deeply($cepages['secondaires'], $secondaires, "Règle 0t : Cépages secondaires respectés pour le blanc.");

// Test 1 : verification du potentiel de production calculé pour le rosé
$revendicablesRose = $generator->calculateRevendicableLLORose($superficiesRose);
$result = ['principaux' => 17.5554, 'secondairesnoirs' => 0, 'secondairesblancs' => 4.3889,  'secondairesvermentinob' => 2.1945,  'secondairesautresblancs' => 2.1944];
$t->is_deeply($revendicablesRose, $result, "Règle 1 : Calcul du potentiel de production LLO Rose OK.");

// Test 2 : verification du potentiel de production calculé pour le rouge
$revendicablesRouge = $generator->calculateRevendicableLLORouge($superficiesRouge);
$result = ['principaux' => 24.1303,  'secondaires' => 4.0359];
$t->is_deeply($revendicablesRouge, $result, "Règle 2 : Calcul du potentiel de production LLO Rouge OK.");

// Test 3 : verification du potentiel de production calculé pour le blanc
$revendicablesBlanc = $generator->calculateRevendicableLLOBlanc($superficiesBlanc);
$result = ['principaux' => 5.857,  'secondaires' => 3.74];
$t->is_deeply($revendicablesBlanc, $result, "Règle 3 : Calcul du potentiel de production LLO blanc OK.");

// Test 4 : comparaison rosé avec la calculette
$result = ['principaux' => 17.5554,  'secondairesnoirs' => 4.3889,  'secondairesblancs' => 0,  'secondairesvermentinob' => 0,  'secondairesautresblancs' => 0];
$t->is_deeply($revendicablesRose, $result, "Règle 4 : Comparaison rosé avec la calculette");

// Test 5 : comparaison rouge avec la calculette
$result = ['principaux' => 24.1303,  'secondaires' => 3.8140];
$t->is_deeply($revendicablesRouge, $result, "Règle 5 : Comparaison rouge avec la calculette");

// Test 6 : comparaison blanc avec la calculette
$result = ['principaux' => 5.857,  'secondaires' => 3.74];
$t->is_deeply($revendicablesBlanc, $result, "Règle 6 : Comparaison blanc avec la calculette");