<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$societe = SocieteClient::getInstance()->findByIdentifiantSociete('999990');
if ($societe) {
  $societe->delete();
}
$etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-99999001');
if ($etablissement) {
  $etablissement->delete();
}
for ($i = 0 ; $i < 10 ; $i++) {
  $client = CompteClient::getInstance()->find('COMPTE-9999900'.$i);
  if ($client) {
    $client->delete();
  }
}
$t = new lime_test(11);

$t->comment("création d'une société d'après l'import");
$societefile = tempnam("/tmp", "test");
file_put_contents($societefile, "999990;RESSORTISSANT;\"Nego VINICOLE\";\"Nego VINICOLE\";ACTIF;41199999;;;;;\"Rue du Comte           \";;;;92310;\"Bourg\";;;FR;;01.45.15.00.00;;;01.45.15.00.00;;\n");
$csv = new SocieteCsvFile($societefile, array('throw_exception' => true));
$csv->importSocietes();

$societe = SocieteClient::getInstance()->findByIdentifiantSociete('999990');
$compteSociete = CompteClient::getInstance()->findByIdentifiant('999990');

$etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-99999001');
$t->isnt($societe, null, "La societe existe");
$t->is($compteSociete && true, true, "Le compte de société existe");
$t->is($etablissement, null, "L'etablissement n'existe pas");


$t->comment("création d'un établissement d'après l'import");
$etablissementfile = tempnam("/tmp", "test");
file_put_contents($etablissementfile, "99999001;SOCIETE-999990;NEGOCIANT;\"Nego VINICOLE\";ACTIF;REGION_HORS_CVO;;;;;;\"Rue du Comte           \";;;;92310;\"Bourg\";;;FR;;01.45.15.00.00;;;01.45.15.00.00;;\n");
$csv = new EtablissementCsvFile($etablissementfile, array('throw_exception' => true));
$csv->importEtablissements();
unlink($societefile);
unlink($etablissementfile);

$t->comment("Tests de récupération de la société et du chais créé");
$etablissement = EtablissementClient::getInstance()->findByIdentifiant('99999001');
$compte01 = CompteClient::getInstance()->find('COMPTE-99999001');
$compte02 = CompteClient::getInstance()->find('COMPTE-99999002');

$t->is($etablissement && true, true, "L'etablissement 01 existe");
$t->is($compte01 && true, true, "Le compte 01 existe");
$t->is($compte02 && true, false, "Le compte 02 n'existe pas");
$societe = SocieteClient::getInstance()->findByIdentifiantSociete('999990');
$t->is(count($societe->contacts), 2, "La société est liées à deux contacts");

$compte01->addTag('test', 'test');
$compte01->save();

$t->comment("Tests de suppression de la société");

$societe = SocieteClient::getInstance()->find($societe->_id);
$societe->delete();
$compte01 = CompteClient::getInstance()->findByIdentifiant('99999001');
$compteSociete = CompteClient::getInstance()->findByIdentifiant('999990');
$societe = SocieteClient::getInstance()->findByIdentifiantSociete('999990');
$etablissement = EtablissementClient::getInstance()->findByIdentifiant('99999001');
$t->is($societe, null, "La societe n'existe plus");
$t->is($etablissement, null, "L'etablissement 01 n'existe plus");
$t->is($compte01, null, "Le compte 01 n'existe plus");
$t->is($compteSociete, null, "Le compte de société n'existe plus");
