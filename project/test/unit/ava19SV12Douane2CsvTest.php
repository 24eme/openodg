<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(30);

$csv = new SV12DouaneCsvFile(dirname(__FILE__).'/../data/sv12_douane.csv');


$csv = $csv->convert();

$lines = explode("\n", $csv);

$t->is($lines[0], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B525;CONDRIEU;;07;Quantité de VF;2700,00;7523700100;\"ACTUALYS JEAN\";;SARRAS", "La ligne 0 est ok");
$t->is($lines[1], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B525;CONDRIEU;;09;Superficie de récolte;0,4579;7523700100;\"ACTUALYS JEAN\";;SARRAS", "La ligne 1 est ok");
$t->is($lines[2], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B525;CONDRIEU;;10;Volume issu de VF;19,42;7523700100;\"ACTUALYS JEAN\";;SARRAS", "La ligne 2 est ok");
$t->is($lines[3], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B541;Hermitage ou Ermitage bl;;07;Quantité de VF;850,00;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 3 est ok");
$t->is($lines[4], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B541;Hermitage ou Ermitage bl;;09;Superficie de récolte;0,1484;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 4 est ok");
$t->is($lines[5], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B541;Hermitage ou Ermitage bl;;10;Volume issu de VF;6,75;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 5 est ok");
$t->is($lines[6], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;07;Quantité de VF;7000,00;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 6 est ok");
$t->is($lines[7], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;09;Superficie de récolte;1,0800;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 7 est ok");
$t->is($lines[8], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;10;Volume issu de VF;54,00;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 8 est ok");
$t->is($lines[9], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R526;CORNAS;;07;Quantité de VF;3000,00;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 9 est ok");
$t->is($lines[10], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R526;CORNAS;;09;Superficie de récolte;0,5495;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 10 est ok");
$t->is($lines[11], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R526;CORNAS;;10;Volume issu de VF;23,00;7523700101;\"ACTUALYS JEAN 1\";;MERCUROL-VEAUNES", "La ligne 11 est ok");
$t->is($lines[12], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B542;Crozes-Hermitage bl;;07;Quantité de VF;5000,00;7523700102;\"ACTUALYS JEAN 2\";;MERCUROL-VEAUNES", "La ligne 12 est ok");
$t->is($lines[13], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B542;Crozes-Hermitage bl;;09;Superficie de récolte;0,8866;7523700102;\"ACTUALYS JEAN 2\";;MERCUROL-VEAUNES", "La ligne 13 est ok");
$t->is($lines[14], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B542;Crozes-Hermitage bl;;10;Volume issu de VF;30,96;7523700102;\"ACTUALYS JEAN 2\";;MERCUROL-VEAUNES", "La ligne 14 est ok");
$t->is($lines[15], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;07;Quantité de VF;5528,00;7523700102;\"ACTUALYS JEAN 2\";;MERCUROL-VEAUNES", "La ligne 15 est ok");
$t->is($lines[16], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;09;Superficie de récolte;0,7200;7523700102;\"ACTUALYS JEAN 2\";;MERCUROL-VEAUNES", "La ligne 16 est ok");
$t->is($lines[17], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;10;Volume issu de VF;37,14;7523700102;\"ACTUALYS JEAN 2\";;MERCUROL-VEAUNES", "La ligne 17 est ok");
$t->is($lines[18], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B542;Crozes-Hermitage bl;;07;Quantité de VF;800,00;7523700103;\"ACTUALYS JEAN 3\";;VINSOBRES", "La ligne 18 est ok");
$t->is($lines[19], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B542;Crozes-Hermitage bl;;09;Superficie de récolte;0,2550;7523700103;\"ACTUALYS JEAN 3\";;VINSOBRES", "La ligne 19 est ok");
$t->is($lines[20], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1B542;Crozes-Hermitage bl;;10;Volume issu de VF;5,04;7523700103;\"ACTUALYS JEAN 3\";;VINSOBRES", "La ligne 20 est ok");
$t->is($lines[21], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;07;Quantité de VF;15454,00;7523700103;\"ACTUALYS JEAN 3\";;VINSOBRES", "La ligne 21 est ok");
$t->is($lines[22], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;09;Superficie de récolte;2,2352;7523700103;\"ACTUALYS JEAN 3\";;VINSOBRES", "La ligne 22 est ok");
$t->is($lines[23], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;10;Volume issu de VF;112,86;7523700103;\"ACTUALYS JEAN 3\";;VINSOBRES", "La ligne 23 est ok");
$t->is($lines[24], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;07;Quantité de VF;2000,00;7523700104;\"ACTUALYS JEAN 4\";;SAINT-BARDOUX", "La ligne 24 est ok");
$t->is($lines[25], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;09;Superficie de récolte;0,3234;7523700104;\"ACTUALYS JEAN 4\";;SAINT-BARDOUX", "La ligne 25 est ok");
$t->is($lines[26], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;10;Volume issu de VF;16,00;7523700104;\"ACTUALYS JEAN 4\";;SAINT-BARDOUX", "La ligne 26 est ok");
$t->is($lines[27], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;07;Quantité de VF;2082,00;7523700105;\"ACTUALYS JEAN 5\";;ROCHE-DE-GLUN", "La ligne 27 est ok");
$t->is($lines[28], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;09;Superficie de récolte;0,3120;7523700105;\"ACTUALYS JEAN 5\";;ROCHE-DE-GLUN", "La ligne 28 est ok");
$t->is($lines[29], "SV12;2017;7523700100;\"ACTUALYS JEAN\";;NEUILLY;;;;;;;;1R542;Crozes-Hermitage rg;;10;Volume issu de VF;15,00;7523700105;\"ACTUALYS JEAN 5\";;ROCHE-DE-GLUN", "La ligne 29 est ok");
