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

$t->comment('On ajoute le lot 2 à la dégustation');

$doc->lots[2] = clone $doc->lots[0];
$doc->lots[2]->numero_logement_operateur = $doc->lots[0]->numero_logement_operateur + 1;
$doc->lots[2]->numero_table = 1;
$doc->lots[2]->numero_dossier = "99999";
$doc->save();
$doc = acCouchdbManager::getClient()->find($docid);

//FAIRE une DREV

$t->comment('On ajoute le lot 3 à la dégustation SANS table');
$doc->lots->add();
$doc->lots[3] = clone $doc->lots[0];
$doc->lots[3]->numero_logement_operateur = $doc->lots[0]->numero_logement_operateur + 1;
$doc->lots[3]->numero_table = null;
$doc->lots[3]->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
$doc->lots[3]->numero_dossier = "00002";
$doc->lots[3]->numero_archive = "00001";
$doc->save();
$doc = acCouchdbManager::getClient()->find($docid);

$t->comment('On a 3 lots normaux / 1 Leurre');

$lotPere = ($doc->lots[3]->getLotPere());
$idDocumentSrc = $lotPere->getDocument()->_id;

$t->ok($lotPere->isAffecte(),'Le lot 3 est affecté dans la DREV');
$t->is($lotPere->document_fils, $doc->_id, 'Le lot 3 est dans la DREV à pour fils la dégustation');

$t->comment('On a 2 lots normaux / 1 Leurre sur la table A, 1 lot normal qui n\'a pas de table');

$t->comment('On test l\'anonymat');
$t->is(array_keys($doc->getLotsNonAnonymisable()), array('/lots/3'), "Le /lots/3 n'est pas anonymisable");

$t->comment('Apposement de l\'anonymat');
$isAnonymized = $doc->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est pas "anonymisée"');

$doc->anonymize();
$doc->save();
$doc = acCouchdbManager::getClient()->find($docid);

$t->is(count($doc->lots),3,'La dégustation n\'a plus que 3 lots');

$ancienPere = acCouchdbManager::getClient()->find($idDocumentSrc);
$actuelLotPere = $ancienPere->get($lotPere->getHash());

$t->is($actuelLotPere->document_fils, null,'Le document fils du lot de la DRev '.$idDocumentSrc.' est null');
$t->ok(!$actuelLotPere->isAffecte(),'Le lot pere dans la DRev '.$idDocumentSrc.' n\'est plus affectée');

$isAnonymized = $doc->isAnonymized();
$t->ok($isAnonymized, 'La dégustation est "anonymisée"');
$t->is(count($doc->mouvements_lots->{$doc->lots[0]->declarant_identifiant}), 10, "10 mouvements ont été générés (5 mvts × 2 lots)");

$numero_anonymats = array();
$numero_anonymats_attendu = array("A1","A2","A3");

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
