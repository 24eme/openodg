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
$doc->desanonymize();
$doc->save();

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
$doc->lots[3]->numero_dossier = "00001";
$doc->lots[3]->numero_archive = "00002";
$doc->save();
$doc = acCouchdbManager::getClient()->find($docid);

$t->comment('On a 3 lots normaux / 1 Leurre');

$lotProvenance = ($doc->lots[3]->getLotProvenance());
$idDocumentProvenance = $lotProvenance->getDocument()->_id;

$t->ok($lotProvenance->isAffecte(),'Le lot 3 est affecté dans la DREV');

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

$ancienPere = acCouchdbManager::getClient()->find($idDocumentProvenance);
$actuelLotProvenance = $ancienPere->get($lotProvenance->getHash());

$t->ok(!$actuelLotProvenance->isAffecte(),'Le lot pere dans la DRev '.$idDocumentProvenance.' n\'est plus affectée');

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
$doc->save();
