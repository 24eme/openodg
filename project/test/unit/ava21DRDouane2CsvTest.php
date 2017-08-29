<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(18);

$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane.csv');

$csvFinal = $csv->convert();

$lines = explode("\n", $csvFinal);

$t->is($lines[0], "7523700100;SUR PLACE;7523700100;ACTUALYS JEAN;Saint-Joseph rouge;1R545;;C1A;247.86;105.18;3.03;247.86;105.18;3.03;2", "La ligne 1 est ok");
$t->is($lines[1], "7523700800;CAVE COOP DE NEUILLY;7523700100;ACTUALYS JEAN;COL RHODANIENNES BL VIOGNIER B;3B073 AO;;C1A;20.8;6.66;0.13;20.8;6.66;0.13;0", "La ligne 2 est ok");
$t->is($lines[2], "7523700100;SUR PLACE;7523700100;ACTUALYS JEAN;ARDECHE RG SYRAH N;3R071 AI;;C1A;;3.76;;86.01;64.07;1.29;0", "La ligne 3 est ok");
$t->is($lines[3], "7523700800;CAVE COOP DE NEUILLY;7523700100;ACTUALYS JEAN;ARDECHE RG SYRAH N;3R071 AI;;C1A;;60.31;;86.01;64.07;1.29;0", "La ligne 4 est ok");
