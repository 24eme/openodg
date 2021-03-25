<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$annee = (date('Y')-1)."";
$degust_date = $annee.'-09-01 12:45';
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$degustid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date));
$degust = DegustationClient::getInstance()->find($degustid);
if ($degust) {
    $degust->delete();
}
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}
$config = ConfigurationClient::getCurrent();
$produitconfig1 = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(!$produitconfig1) {
        $produitconfig1 = $produitconfig->getCepage();
        continue;
    }
    break;
}
$produitconfig_hash1 = $produitconfig1->getHash();
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $annee);
$drev->save();
$produit1 = $drev->addProduit($produitconfig_hash1);
$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$drev->addLot();
$drev->lots[0]->numero_logement_operateur = '1';
$drev->lots[0]->volume = 1;
$drev->addLot();
$drev->lots[1]->numero_logement_operateur = '2';
$drev->lots[1]->volume = 2;
$drev->addLot();
$drev->lots[2]->numero_logement_operateur = '3';
$drev->lots[2]->volume = 3;
$drev->validate();
$drev->validateOdg();
$drev->save();
$degust = DegustationClient::getInstance()->createDoc($degust_date);
$t->comment("Les deux premiers lots sont prélevés");
$lot1 = $degust->addLot($drev->lots[0]);
$lot1->statut = Lot::STATUT_PRELEVE;
$t->is($lot1->numero_archive, '00001', "le numéro d'archive du lot 1 est bien 00001");
$lot2 = $degust->addLot($drev->lots[1]);
$lot2->statut = Lot::STATUT_PRELEVE;
$t->is($lot2->numero_archive, '00002', "le numéro d'archive du lot 1 est bien 00002");
$t->comment("Le 3ème n'est pas prélevés");
$lot3 = $degust->addLot($drev->lots[2]);
$lot3->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
$t->is($lot3->numero_archive, '00003', "le numéro d'archive du lot 1 est bien 00003");
$degust->generateMouvementsLots();
$degust->save();

$degust = DegustationClient::getInstance()->find($degustid);

$t->comment("On attable les 2 1er lot mais pas le 3ème");
$degust->lots[0]->numero_table = 1;
$degust->lots[1]->numero_table = 1;
$degust->save();

$t->is($degust->lots[0]->statut, Lot::STATUT_ATTABLE, "Le 1er lot est attablé");
$t->is($degust->lots[1]->statut, Lot::STATUT_ATTABLE, "Le 2ème lot est attablé");
$t->is($degust->lots[2]->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le 3ème lot est toujours en attente de prelevement");

$t->comment('On ajoute un leurre en 4ème lot');
$lot4 = $degust->addLeurre($lot1->produit_hash, null, 1);
$t->is($lot4->numero_archive, null, "le lot leurre n'a pas de numero d'archive");
$degust->save();
$degust = DegustationClient::getInstance()->find($degustid);

$t->is($degust->lots[2]->id_document_provenance, $drev->_id, "La provenance du 3ème lot est bien ".$drev->_id);
$t->is($degust->lots[2]->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le 3ème lot est bien pas attablé");
$lotProvenance = $degust->lots[2]->getLotProvenance();
$idDocumentProvenance = $lotProvenance->getDocument()->_id;
$t->is($idDocumentProvenance, $drev->_id, "La provenance du lot est bien ". $drev->_id);

$t->ok($lotProvenance->isAffecte(),'Le lot 3 est affecté dans la DREV');

$t->comment('On a 2 lots normaux / 1 Leurre sur la table A, 1 lot normal qui n\'a pas de table');

$t->comment('On test l\'anonymat');
$t->is(array_keys($degust->getLotsNonAnonymisable()), array('/lots/2'), "Seul le /lots/2 n'est pas anonymisable");

$isAnonymized = $degust->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est pas "anonymisée"');

$t->comment('Apposement de l\'anonymat');
$degust->anonymize();
$degust->save();

$degust = DegustationClient::getInstance()->find($degustid);
$t->is(count($degust->lots),3,'La dégustation n\'a plus que 3 lots');

$drevProvenance = DRevClient::getInstance()->find($idDocumentProvenance);
$t->is($lotProvenance->getHash(), '/lots/2', "La hash du lot de provenance est celle attendue");
$actuelLotProvenance = $drevProvenance->get($lotProvenance->getHash());

$t->ok(!$actuelLotProvenance->isAffecte(),'Le lot pere dans la DRev '.$idDocumentProvenance.' n\'est plus affectée');
exit;
$isAnonymized = $degust->isAnonymized();
$t->ok($isAnonymized, 'La dégustation est "anonymisée"');
$t->is(count($degust->mouvements_lots->{$degust->lots[0]->declarant_identifiant}), 15, "15 mouvements ont été générés (5 mvts × 3 lots)");

$numero_anonymats = array();
$numero_anonymats_attendu = array("A1","A2","A3");

foreach ($degust->getLotsByTable(1) as $lot) {
  $numero_anonymats[] = $lot->numero_anonymat;
}
$t->is($numero_anonymats, $numero_anonymats_attendu, 'Les numéros d\'anonymat sont corrects');
$degust->desanonymize();
$isAnonymized = $degust->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est plus "anonymisée"');

$degust->anonymize();
$degust->save();
