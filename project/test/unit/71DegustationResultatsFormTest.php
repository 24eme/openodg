<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$campagne = (date('Y')-1)."";
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$doc = acCouchdbManager::getClient()->find($docid);

$t->comment('Apposement de l\'anonymat');
$isAnonymized = $doc->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est pas "anonymisée"');

$doc->anonymize();
$isAnonymized = $doc->isAnonymized();
$t->ok($isAnonymized, 'La dégustation est "anonymisée"');

$numero_anonymats = array();
$numero_anonymats_attendu = array("A1","A2");

foreach ($doc->getLotsByTable(1) as $lot) {
  $numero_anonymats[] = $lot->numero_anonymat;
}
$t->is($numero_anonymats, $numero_anonymats_attendu, 'Les numéros d\'anonymat sont corrects');
$doc->desanonymize();
$isAnonymized = $doc->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est plus "anonymisée"');

$doc->anonymize();

$t->comment('Résultat de conformité / non conformité');
$lotConformes = $doc->getLotsConformesOrNot();
$lotNonConformes = $doc->getLotsConformesOrNot(false);

$t->is(count($lotConformes), 0, 'Aucun lot n\'est considéré comme "CONFORME"');
$t->is(count($lotNonConformes), 0, 'Aucun lot n\'est considéré comme "NON CONFORME"');

$options = array('numero_table' => 1);
$form = new DegustationResultatsForm($doc, $options);
$defaults = $form->getDefaults();

$valuesRev = array(
    '_revision' => $doc->_rev,
    'conformite_0' => "CONFORME",
    'conformite_1' => "CONFORME"
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot();
$t->is(count($lotConformes), 2, 'Les 2 lots sont "CONFORMES"');

$doc = acCouchdbManager::getClient()->find($docid);
$form = new DegustationResultatsForm($doc, $options);
$defaults = $form->getDefaults();

$motif = "Taux de sucrosité";
$obs = "A requalifier";
$valuesRev = array(
    '_revision' => $doc->_rev,
    'conformite_0' => "CONFORME",
    'conformite_1' => "NONCONFORME_MAJEUR",
    'motif_1' => $motif,
    'observation_1' => $obs
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot();
$lotNonConformes = $doc->getLotsConformesOrNot(false);

$t->is(count($lotConformes), 1, 'Un lot est considéré comme "CONFORME"');
$t->is(count($lotNonConformes), 1, 'Un lot est considéré comme "NON CONFORME"');

foreach ($lotNonConformes as $lot) {
  $t->is($lot->motif, $motif , 'Le motif de non conformité est "'.$motif.'"');
  $t->is($lot->observation, $obs, 'L\'observation de non conformité est "'.$obs.'"');
}
$etbIdentifiant = $viti->identifiant;
$t->comment('Envoie de mail de notification à '.$etbIdentifiant);

$mailEnvoye = $doc->isMailEnvoyeEtablissement($etbIdentifiant);
$t->ok(!$mailEnvoye, 'Le mail de resultats n\'a pas été envoyé');

$doc->setMailEnvoyeEtablissement($etbIdentifiant);
$mailEnvoye = $doc->isMailEnvoyeEtablissement($etbIdentifiant);
$t->ok($mailEnvoye, 'Le mail de resultats a été envoyé');

$doc->save();
