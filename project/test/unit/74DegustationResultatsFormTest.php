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
    'conformite_1' => "CONFORME",
    'conformite_2' => "CONFORME"
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot();
$t->is(count($lotConformes), 2, 'Les 2 lots sont "CONFORMES", le 3eme étant un leurre');
$t->is(count($doc->mouvements_lots->{$doc->lots[0]->declarant_identifiant}), 14, 'Il y a 14 mouvements de lot');

$doc = acCouchdbManager::getClient()->find($docid);
$form = new DegustationResultatsForm($doc, $options);
$defaults = $form->getDefaults();

$motif = "Taux de sucrosité";
$obs = "A requalifier";
$valuesRev = array(
    '_revision' => $doc->_rev,
    'conformite_0' => "CONFORME",
    'conformite_1' => "CONFORME",
    'conformite_2' => "NONCONFORME_MAJEUR",
    'motif_2' => $motif,
    'observation_2' => $obs
);

$form->bind($valuesRev);
$form->save();

$lotConformes = $doc->getLotsConformesOrNot();
$lotNonConformes = $doc->getLotsConformesOrNot(false);

$t->is(count($lotConformes), 1, '1 lot est "CONFORME"');
$t->is(count($lotNonConformes), 1, 'Un lot est considéré comme "NON CONFORME"');

$t->is(count($doc->mouvements_lots->{$doc->lots[0]->declarant_identifiant}), 16, 'Il y a toujours 16 mouvements de lot');

foreach ($lotNonConformes as $lot) {
  $t->is($lot->motif, $motif , 'Le motif de non conformité est "'.$motif.'"');
  $t->is($lot->observation, $obs, 'L\'observation de non conformité est "'.$obs.'"');
}
$etbIdentifiant = $viti->identifiant;
$t->comment('Envoie de mail de notification à '.$etbIdentifiant);

$mailEnvoye = $doc->isMailEnvoyeEtablissement($etbIdentifiant);
$t->ok(!$mailEnvoye, 'Le mail de resultats n\'a pas été envoyé');

$date_envoie = date('Y-m-d H:i:s');
$doc->setMailEnvoyeEtablissement($etbIdentifiant,$date_envoie);
$mailEnvoye = $doc->isMailEnvoyeEtablissement($etbIdentifiant);
$t->ok($mailEnvoye, 'Le mail de resultats a été envoyé');

$doc->save();
