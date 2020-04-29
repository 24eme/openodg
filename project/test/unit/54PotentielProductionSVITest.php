<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(8);

$ppmanager = new PotentielProductionManager(null);
$generator = $ppmanager->getGenerator(); 

$superficiesRouge = $superficiesRose = [
    'principaux' =>
        [
            'CINSAUT N' => 4.8305,
            'SYRAH N' => 6.6127,
            'GRENACHE N' => 12.7249,
            'TOTAL' => 24.1681
        ],
    'secondaires' =>
        [
            'CABERNET SAUVIGNON N' => 3.1396,
            'CARIGNAN N' => 1.0196,
            'MOURVEDRE N' => 0,
            'VERMENTINO B' => 5.857,
            'SEMILLON B' => 1.5733,
            'CLAIRETTE B' => 0,
            'UGNI BLANC B' => 2.1667,
            'TOTAL' => 16.7758
        ],
    'TOTAL' => 37.9243
];
// Test 0 : verification des cepages
$cepages = $generator->getCepages('SVI', 'rouge');
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
$t->is_deeply($cepages['secondaires'], $secondaires, "Règle 0b : Cépages secondaires respectés pour le rose.");

$cepages = $generator->getCepages('SVI', 'rose');
$principaux = $superficiesRose['principaux'];
$secondaires = $superficiesRose['secondaires'];
unset($principaux['TOTAL'], $secondaires['TOTAL']);
$principaux = array_keys($principaux);
$secondaires = array_keys($secondaires);
sort($principaux);
sort($secondaires);
sort($cepages['principaux']);
sort($cepages['secondaires']);

$t->is_deeply($cepages['principaux'], $principaux, "Règle 0 : Cépages principaux respectés pour le rouge.");
$t->is_deeply($cepages['secondaires'], $secondaires, "Règle 0b : Cépages secondaires respectés pour le rose.");

// Test 1 : regle des 2 cepages principaux sup ou egal a encepagement
$regle1 = $generator->reglePourcentageCepageMinPrincipaux_GetRevendicable(["GRENACHE N" => $superficiesRose['principaux']["GRENACHE N"], "SYRAH N" => $superficiesRose['principaux']["SYRAH N"]], $superficiesRose['TOTAL'], 50, $superficiesRose['principaux']['TOTAL']);
$t->is($regle1, $superficiesRose['principaux']["TOTAL"], "Règle 1 OK : GRENACHE N et SYRAH N >= 50% encépagement");

// Test 2 : regle pour le rouge des 10% du CABERNET SAUVIGNON N max
$regle2 = $generator->regleRatioMax_GetRevendicable($superficiesRouge['principaux']["TOTAL"], 10/80, $superficiesRouge['secondaires']['CABERNET SAUVIGNON N']);
$t->ok($regle2 <= round($superficiesRouge['TOTAL'] * 0.1), "Règle 2 OK : 10% du CABERNET SAUVIGNON N max");

// Test 3 : verification du potentiel de production calculé pour le rosé
$revendicables = $generator->calculateRevendicableSVIRose($superficiesRose);
$result = ['principaux' => 24.1681, 'secondairesnoirs' => 0, 'secondairesblancs' => 6.042, 'secondairesvermentinob' => 3.021, 'secondairesautresblancs' => 3.021];
$t->is_deeply($revendicables, $result, "Règle 3 : Calcul du potentiel de production SVI Rose OK.");

// Test 4 : verification du potentiel de production calculé pour le rouge
$revendicables = $generator->calculateRevendicableSVIRouge($superficiesRouge);
$result = ['principaux' => 24.1681, 'secondairesnoirs' => 0, 'secondairesblancs' => 6.042, 'secondairesvermentinob' => 3.021, 'secondairesautresblancs' => 3.021];
$t->is_deeply($revendicables, $result, "Règle 4 : Calcul du potentiel de production SVI Rouge OK.");