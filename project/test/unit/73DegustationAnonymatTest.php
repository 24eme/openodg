<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}
$t = new lime_test(40);

$annee = (date('Y')-1)."";
$campagne = $annee.'-'.($annee + 1);
$drev_date = $annee.'-09-01 14:45:00';
$degust_date = $annee.'-10-01 14:45:00';
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}
foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $cd = ChgtDenomClient::getInstance()->find($k);
    $cd->delete(false);
}
foreach(DegustationClient::getInstance()->getHistory(100, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DegustationClient::getInstance()->deleteDoc(DegustationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}
foreach(ArchivageAllView::getInstance()->getDocsByTypeAndCampagne('Revendication', $campagne, 0, 99999, "%05d") as $r) {
    $doc = acCouchdbManager::getClient()->find($r->id);
    $doc->delete();
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
$drev->lots[0]->numero_logement_operateur = 'L1';
$drev->lots[0]->volume = 1;
$drev->lots[0]->getUniqueId();
$drev->lots[0]->produit_hash = $produitconfig_hash1;
$drev->addLot();
$drev->lots[1]->numero_logement_operateur = 'L2';
$drev->lots[1]->volume = 2;
$drev->lots[1]->getUniqueId();
$drev->lots[1]->produit_hash = $produitconfig_hash1;
$drev->addLot();
$drev->lots[2]->numero_logement_operateur = 'L3';
$drev->lots[2]->volume = 3;
$drev->lots[2]->getUniqueId();
$drev->lots[2]->produit_hash = $produitconfig_hash1;
$drev->validate($drev_date);
$drev->validateOdg($drev_date);
$drev->save();
$t->is($drev->lots[0]->unique_id, $campagne.'-00001-00001', 'Le lot 1 de la drev a le bon numéro darchive 2020-2021-00001-00001');
$t->is($drev->lots[1]->unique_id, $campagne.'-00001-00002', 'Le lot 2 de la drev a le bon numéro darchive 2020-2021-00001-00002');
$t->is($drev->lots[2]->unique_id, $campagne.'-00001-00003', 'Le lot 3 de la drev a le bon numéro darchive 2020-2021-00001-00003');
$degust = DegustationClient::getInstance()->createDoc($degust_date);
$t->comment("Les deux premiers lots sont prélevés");
$lot1 = $degust->addLot($drev->lots[0]);
$lot1->statut = Lot::STATUT_PRELEVE;
$lot1->preleve = date('Y-m-d');
$t->is($lot1->numero_archive, '00001', "le numéro d'archive du lot 1 est bien 00001");
$t->is($lot1->unique_id, $campagne.'-00001-00001', "le numéro d'archive du lot 1 est bien $campagne-00001-00001");
$lot2 = $degust->addLot($drev->lots[1]);
$lot2->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
$t->is($lot2->numero_archive, '00002', "le numéro d'archive du lot 2 est bien 00002");
$t->is($lot2->unique_id, $campagne.'-00001-00002', "le numéro d'archive du lot 2 est bien $campagne-00001-00002");
$t->comment("Le 3ème n'est pas prélevés");
$lot3 = $degust->addLot($drev->lots[2]);
$lot3->statut = Lot::STATUT_PRELEVE;
$lot3->preleve = date('Y-m-d');
$t->is($lot3->numero_archive, '00003', "le numéro d'archive du lot 3 est bien 00003");
$t->is($lot3->unique_id, $campagne.'-00001-00003', "le numéro d'archive du lot 3 est bien $campagne-00001-00003");
$degust->generateMouvementsLots();
$degust->save();

$t->ok($lot1->getMouvement(Lot::STATUT_PRELEVE), "Le lot 1 est prélevé");
$t->ok($lot3->getMouvement(Lot::STATUT_PRELEVE), "Le lot 3 est prélevé");

$degustid = $degust->_id;
$t->comment($degustid);

$degust = DegustationClient::getInstance()->find($degustid);
$t->comment("On attable les 2 1er lot mais pas le 3ème");
$degust->lots[0]->numero_table = 1;
$degust->lots[2]->numero_table = 1;
$degust->save();

$t->is($degust->lots[0]->statut, Lot::STATUT_ATTABLE, "Le 1er lot est attablé");
$t->is($degust->lots[1]->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le 2ème lot est toujours en attente de prelevement");
$t->is($degust->lots[2]->statut, Lot::STATUT_ATTABLE, "Le 3ème lot est attablé");

$t->comment('On ajoute deux leurres en 4ème et 5ème lot');
$lot4 = $degust->addLeurre($lot1->produit_hash, null, date('Y'), 1);
$t->is($lot4->numero_archive, null, "le lot leurre n'a pas de numero d'archive");
$lot5 = $degust->addLeurre($lot1->produit_hash, null, date('Y'), 1);
$t->is($lot5->numero_archive, null, "le lot leurre n'a pas de numero d'archive");
$degust->save();
$degust = DegustationClient::getInstance()->find($degustid);

$t->is($degust->lots[0]->id_document_provenance, $drev->_id, "La provenance du 1er lot est bien ".$drev->_id);
$t->is($degust->lots[1]->id_document_provenance, $drev->_id, "La provenance du 2ème lot est bien ".$drev->_id);
$t->is($degust->lots[2]->id_document_provenance, $drev->_id, "La provenance du 3ème lot est bien ".$drev->_id);
$t->is($degust->lots[1]->statut, Lot::STATUT_ATTENTE_PRELEVEMENT, "Le 3ème lot est bien pas attablé");
$lotProvenance = $degust->lots[1]->getLotProvenance();
$idDocumentProvenance = $lotProvenance->getDocument()->_id;
$t->is($idDocumentProvenance, $drev->_id, "La provenance du lot est bien ". $drev->_id);

$t->ok($lotProvenance->isAffecte(),'Le lot 3 est affecté dans la DREV');

$t->comment('On a 2 lots normaux / 2 Leurre sur la table A, 1 lot normal qui n\'a pas de table');

$t->comment('On test l\'anonymat');

$t->ok($degust->lots[0]->isAnonymisable(), "Le lot 0 est anonymisable");
$t->ok(!$degust->lots[1]->isAnonymisable(), "Le lot 1 n'est pas anonymisable");
$t->ok($degust->lots[2]->isAnonymisable(), "Le lot 2 est anonymisable");
$t->ok($degust->lots[3]->isAnonymisable(), "Le lot 3 est anonymisable");
$t->ok($degust->lots[4]->isAnonymisable(), "Le lot 4 est anonymisable");

$isAnonymized = $degust->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est pas "anonymisée"');

$t->comment('Apposement de l\'anonymat');
$t->is(count($degust->getLots()), 5, "Avant l'apposement il ya 5 lots");
$degust->anonymize();
$t->is(count($degust->getLots()), 4, "Après l'apposement il ya 4 lots dont 2 lots Drev et 2 Leurre et 1 lot non-attablé");
$degust->save();

$degust = DegustationClient::getInstance()->find($degustid);
$t->is(count($degust->lots), 4,'La dégustation n\'a plus que 4 lots le 2ème lot étant non anonymisable');
$t->is($degust->lots[0]->unique_id, $campagne."-00001-00001", "Le lot 1 a bien d'id 2020-2021-00001-00001");
$t->is($degust->lots[1]->unique_id, $campagne."-00001-00003", "Le lot 2 a bien d'id de l'ancien lot 3 (le 2 ayant été retiré) : 20202020-2021-00001-00003");
$t->is($degust->lots[2]->unique_id, "", "Le leurre (lot 3) n'a pas de unique_id");

$drevProvenance = DRevClient::getInstance()->find($idDocumentProvenance);
$t->is($lotProvenance->getHash(), '/lots/1', "La hash du lot de provenance est celle attendue");
$actuelLotProvenance = $drevProvenance->get($lotProvenance->getHash());

$t->ok(!$actuelLotProvenance->isAffecte(),'Le lot pere dans la DRev '.$idDocumentProvenance.' n\'est plus affectée');

$isAnonymized = $degust->isAnonymized();
$t->ok($isAnonymized, 'La dégustation est "anonymisée"');
$t->is(count($degust->mouvements_lots->{$degust->lots[0]->declarant_identifiant}), 10, "10 mouvements ont été générés (5 mvts × 2 lots)");

$numero_anonymats = array();
$tableid = (DegustationConfiguration::getInstance()->hasAlwaysIdentifiantTable()) ? 'A': '';
$numero_anonymats_attendu = array($tableid."01",$tableid."02",$tableid."03",$tableid."04");

foreach ($degust->getLotsByTable(1) as $lot) {
  $numero_anonymats[] = $lot->numero_anonymat;
}
$t->is($numero_anonymats, $numero_anonymats_attendu, 'Les numéros d\'anonymat sont corrects');
$degust->desanonymize();
$isAnonymized = $degust->isAnonymized();
$t->ok(!$isAnonymized, 'La dégustation n\'est plus "anonymisée"');

$degust->anonymize();
$degust->save();
