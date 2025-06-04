<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}

$t = new lime_test(295);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = TransactionClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

foreach(DegustationClient::getInstance()->getHistory(9999, '', acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

$year = (date('Y') - 1)."";
$periode = $year;
$doc_date = $year.'-09-10';
$date = $year.'-10-10 10:10:10';

//Début des tests
$t->comment("Création d'une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->constructId();
$drev->storeDeclarant();

$produits = $drev->getConfigProduits();
$nbProduit = 0;
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    $drev->addProduit($produit->getHash());
    $lot = $drev->addLot();
    $lot->produit_hash = $produit->getHash();
    $nbProduit++;
    if ($nbProduit == 3) {
      break;
    }
}
$i=1;
foreach($drev->lots as $lot) {
$lot->id_document = $drev->_id;
$lot->millesime = $periode;
$lot->numero_logement_operateur = $i;
$lot->volume = 50;
$lot->affectable = true;
$lot->specificite = 'Ma fausse 2ème dégustation';
$lot->cepages = array('CABERNET' => 25, 'PINOT' => 25);
$lot->destination_type = null;
$lot->destination_date = ($periode+1).'-'.sprintf("%02d", 1).'-'.sprintf("%02d", 1);
$lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
$i++;
}
$drev->validate($doc_date);
$drev->lots[2]->affectable = false;
$drev->lots[2]->millesime = "2015";
$drev->add('date_commission', $drev->getDateValidation('Y-m-d'));
$drev->validateOdg($doc_date);
$drev->save();

$t->is(count($drev->lots), 3, "3 lots ont automatiquement été créés");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le 1er lot est affectable");
$t->ok($drev->lots[1]->getMouvement(Lot::STATUT_AFFECTABLE), "Le 2ème lot est affectable");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_NONAFFECTABLE), "Le 3ème lot est non affectable");
$t->is($drev->lots[2]->statut, Lot::STATUT_NONAFFECTABLE, "Le 3ème lot à le statut non affectable");

$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 1 est changeable");
$t->ok($drev->lots[1]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 2 aussi");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 3 étant non affectable est donc CHANGEABLE");

$t->ok(!$drev->lots[0]->isChange(), "Le lot changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->lots[1]->isChange(), "un lot non changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->lots[2]->isChange(), "Le lot changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->hasLotsUtilises(), "La drev n'a pas de lots utilisés");


$lotsPrelevables = DegustationClient::getInstance()->getLotsEnAttente(null);
$t->is(count($lotsPrelevables), 2, "2 mouvements de lot prelevables ont été générés");
$lots = array();
$t->is(count(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null)), 3, "Les 3 lots sont cheageable");

foreach(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null) as $key => $lot) {
    if($lot->affectable) {
        continue;
    }
    $lots[] = $lot;
}

$campagne = $year.'-'.($year + 1);

$t->comment("Changement de dénom total sur DREV");

$lotFromDrev = array_shift($lots);

$date = $year.'-12-10 10:10:10';

$chgtDenomFromDrev = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lotFromDrev, $date, null);
$chgtDenomFromDrev->validate($doc_date);
$chgtDenomFromDrev->save();
$t->comment($chgtDenomFromDrev->_id);

$idChgtDenomFromDrev = "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date);
$t->is($chgtDenomFromDrev->_id, $idChgtDenomFromDrev, "id du document");
$t->is($chgtDenomFromDrev->campagne, $drev->campagne, "le chgt de denom a la campagne de la drev");
$t->is($chgtDenomFromDrev->periode, $year, "le chgt de denom a la bonne periode à $year");
$t->is($chgtDenomFromDrev->numero_archive, '00002', "le chgt de denom a bien un numero d'archive'");

$t->is($chgtDenom->changement_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, 'changement_numero_logement_operateur par defaut est le numero logement du lot origine');
$t->is($chgtDenomFromDrev->origine_affectable, $lotFromDrev->affectable, "L'affectation origine reprend l'affectation du lot de la DREV d'origine");
$t->is($chgtDenomFromDrev->origine_statut, Lot::STATUT_NONAFFECTABLE, "Le statut d'origine est réputé conforme");
$t->is($chgtDenomFromDrev->changement_affectable, $lotFromDrev->affectable, "L'affectation changé reprends l'affectation du lot d'origine comme le changement est total");

$chgtDenomFromDrev->changement_produit_hash = $drev->lots[1]->produit_hash;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenomFromDrev->validate($doc_date);
$chgtDenomFromDrev->changement_affectable = false;
$chgtDenomFromDrev->validateOdg($doc_date);
$chgtDenomFromDrev->save();

$t->is($chgtDenomFromDrev->changement_origine_id_document, $drev->_id, "Le changement a bien comme document d'origine ".$drev->_id);
$t->is($chgtDenomFromDrev->changement_origine_lot_unique_id, $lotFromDrev->unique_id, "Le changement a bien l'unique id de son origine ".$lotFromDrev->unique_id);
$t->is($chgtDenomFromDrev->changement_date_commission, $lotFromDrev->date_commission, "La date de commission du lot changé est celle du lot de la drev");
$t->is($chgtDenomFromDrev->changement_volume, $lotFromDrev->volume, "Le changement a bien le volume de son origine");
$t->is($chgtDenomFromDrev->changement_millesime, $lotFromDrev->millesime, "Le changement a bien le millesime de son origine : ".$drev->lots[2]->millesime);
$t->isnt($chgtDenomFromDrev->changement_produit_hash, $lotFromDrev->produit_hash, "Le changement a bien un produit différent");
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "C'est un changement de dénomination");
$t->is($chgtDenomFromDrev->changement_millesime, $lotFromDrev->millesime, "Le changement a bien le millesime de son origine : ".$drev->lots[2]->millesime);
$t->is($chgtDenomFromDrev->changement_specificite, "Ma fausse", "Le changement n'a plus de 2de dégustation dans sa spécificité");
$t->is($chgtDenomFromDrev->origine_specificite, "Ma fausse 2ème dégustation", "L'origine a 2de dégustation dans sa spécificité");

$t->is(count($chgtDenomFromDrev->lots), 2, "Le changement étant total, on a 2 lots");

$t->is($chgtDenomFromDrev->lots[0]->campagne, $drev->campagne, "Le lot non changé à la campagne de la drev");
$t->is($chgtDenomFromDrev->lots[0]->initial_type, DRevClient::TYPE_MODEL, "L'initial type par défaut est ".DRevClient::TYPE_MODEL);
$chgtDenomFromDrev->lots[0]->initial_type = null;
$t->is($chgtDenomFromDrev->lots[0]->initial_type, DRevClient::TYPE_MODEL, "L'initial type calculé est ".DRevClient::TYPE_MODEL);
$t->is($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_NONAFFECTABLE)->initial_type, $chgtDenomFromDrev->lots[0]->initial_type, "L'initial type du mouvement");
$t->is($chgtDenomFromDrev->lots[0]->date, $chgtDenomFromDrev->date, "La date du mouvement est celle du changement de dénom");
$t->is($chgtDenomFromDrev->lots[0]->date_commission, $lotFromDrev->date_commission, "La date de commission du lot est celle du lot de la drev");
$t->is($chgtDenomFromDrev->lots[0]->numero_archive, "00003", "Le lot du chgt a le même numéro d'archive que dans la drev");
$t->is($chgtDenomFromDrev->lots[0]->unique_id, $lotFromDrev->unique_id, "Le 1er lot du chgt a le même unique id que celui de la drev");
$t->is($chgtDenomFromDrev->lots[0]->id_document, $idChgtDenomFromDrev, "Le lot du chgt a bien id_document ".$idChgtDenomFromDrev);
$t->is($chgtDenomFromDrev->lots[0]->document_ordre, '02', "Le lot du chgt a bien comme document_ordre 02");
$t->is($chgtDenomFromDrev->lots[0]->id_document_provenance, $drev->_id, "Le lot du chgt a bien comme provenance la drev ".$drev->_id);
$t->is($chgtDenomFromDrev->lots[0]->affectable, false, "Le lot du chgt n'est pas affectable");
$t->is($chgtDenomFromDrev->lots[0]->statut, Lot::STATUT_NONAFFECTABLE, "Le statut du lot est réputé conforme");

$t->is($chgtDenomFromDrev->lots[0]->volume, 0, "Le lot 1 du chgt a un volume à 0");
$t->is($chgtDenomFromDrev->lots[0]->specificite, 'Ma fausse 2ème dégustation', "Le lot 1 du chgt a bien une spécificité");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_NONAFFECTABLE), "Le lot 1 du changement a bien un mouvement non affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le lot 1 du changement a bien un mouvement changé dest");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 1 du changement n'est pas changeable");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le lot 1 du changement n'a pas de mouvement change src");

$t->is($chgtDenomFromDrev->lots[1]->campagne, $drev->campagne, "Le lot changé à la campagne de la drev");
$t->is($chgtDenomFromDrev->lots[1]->initial_type, DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE, "L'initial type par défaut est ".DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE);
$chgtDenomFromDrev->lots[1]->initial_type = null;
$t->is($chgtDenomFromDrev->lots[1]->initial_type, DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE, "L'initial type calculé est ".DRevClient::TYPE_MODEL);
$t->is($chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_NONAFFECTABLE)->initial_type, DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE, "L'initial type du mouvement est  ".DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE);
$t->is($chgtDenomFromDrev->lots[1]->date, $chgtDenomFromDrev->date, "La date du mouvement est celle du changement de dénom");
$t->is($chgtDenomFromDrev->lots[1]->date_commission, $chgtDenomFromDrev->lots[0]->date_commission, "La date de commission du lot est celle du lot d'origine");
$t->is($chgtDenomFromDrev->lots[1]->numero_archive, "00004", "Le lot changé (2d) a un nouveau numéro d'archive");
$t->is($chgtDenomFromDrev->lots[1]->numero_dossier, $chgtDenomFromDrev->numero_archive, "Le lot changé a le même numéro de dossier que l'archive du chgmt");
$t->is($chgtDenomFromDrev->lots[1]->unique_id, $campagne.'-00002-00004', "Le lot changé a un nouveau uniq id");
$t->is($chgtDenomFromDrev->lots[1]->id_document, $idChgtDenomFromDrev, "Le lot du chgt a bien id_document ".$idChgtDenomFromDrev);
$t->is($chgtDenomFromDrev->lots[1]->document_ordre, '01', "Le lot du chgt a bien comme document_ordre 01");
$t->is($chgtDenomFromDrev->lots[1]->id_document_provenance, null, "Le lot du chgt n'a  de provenance");
$t->is($chgtDenomFromDrev->lots[1]->affectable, false, "Le lot du chgt a la même non affectation que dans la drev (réputé conforme)");
$t->is($chgtDenomFromDrev->lots[1]->volume, $lotFromDrev->volume, "Le lot 2 du chgt a le volume total");
$t->is($chgtDenomFromDrev->lots[1]->specificite, 'Ma fausse', "Le lot 1 du chgt n'a plus de spécificité");
$t->ok($chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_NONAFFECTABLE), "Le lot 1 du changement a bien un mouvement non affectable");
$t->ok(!$chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le lot 1 du changement a bien un mouvement changé dest");
$t->ok($chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 1 du changement n'est pas changeable");
$t->ok(!$chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le lot 1 du changement n'a pas de mouvement change src");


$t->ok(!$chgtDenomFromDrev->hasLotsUtilises(), "La déclaration n'a pas de lots utilisés");

$chgtDenomFromDrev->devalidate();
$chgtDenomFromDrev->validate($doc_date);
$chgtDenomFromDrev->changement_affectable = true;
$chgtDenomFromDrev->validateOdg($doc_date);
$chgtDenomFromDrev->save();
$t->is($chgtDenomFromDrev->lots[0]->affectable, false, "Le lot d'origine du chgt pas affectable");
$t->is($chgtDenomFromDrev->lots[1]->affectable, true, "Le lot du chgt est affectable");


$drev = DrevClient::getInstance()->find($drev->_id);
$t->ok($drev->hasLotsUtilises(), "La drev a des lots utilisés");
$t->is($drev->lots[2]->id_document_affectation, $idChgtDenomFromDrev, "La DREV bien le changement enregistré comme affectation ".$idChgtDenomFromDrev);
$t->ok($drev->lots[2]->isChange(), "Le lot changé dans la DREV est bien isChange()");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le changement a bien généré dans la Drev un mouvement changé src");

$t->is(count(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null)), 3, "On a toujours 3 lots cheageables");

$t->comment("On transforme le changement en déclassement");
$chgtDenomFromDrev->devalidate();

$chgtDenomFromDrev->changement_produit_hash = null;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
$chgtDenomFromDrev->validate($doc_date);
$chgtDenomFromDrev->validateOdg($doc_date);
$chgtDenomFromDrev->save();

$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Le type est déclassement");
$t->is(count($chgtDenomFromDrev->lots), 1, "Le déclassement total a un seul lot");
$t->ok(!$chgtDenomFromDrev->lots[0]->affectable, "Le lot du déclassement n'est pas affectable");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le déclassement n'a pas de mouvement affectable");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le déclassement n'a pas de mouvement changé dest");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le déclassement n'a pas de mouvement changeable");
$t->is($chgtDenomFromDrev->lots[0]->volume, 0, "Le lot du déclassement n'a plus de volume");
$t->is($chgtDenomFromDrev->lots[0]->produit_hash, $chgtDenomFromDrev->origine_produit_hash, "Le lot du déclassement conserve le produit d'origine");
$t->is($chgtDenomFromDrev->lots[0]->specificite, 'Ma fausse 2ème dégustation', "Le lot du déclassement à la spécificité");

$t->is(count(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null)), 2, "On a plus que 2 lots cheageables");

$t->comment("on remet et un produit et on rend le lot affectable");
$chgtDenomFromDrev->devalidate();
$chgtDenomFromDrev->changement_produit_hash = $lotFromDrev->produit_hash;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenomFromDrev->changement_affectable = true;
$chgtDenomFromDrev->validate($doc_date);
$chgtDenomFromDrev->validateOdg($doc_date);
$chgtDenomFromDrev->save();
$t->is(count($chgtDenomFromDrev->lots), 2, "Le changement total produit 2 lots");
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Le type est redevenu un changement de denom");
$t->ok(! $chgtDenomFromDrev->lots[0]->affectable, "Le lot d'origine du changement n'est pas affectable");
$t->is($chgtDenomFromDrev->lots[0]->volume, 0, "Le lot d'origine du changement n'a plus de volume");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot d'origine du changement a un mouvement affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le lot d'origine du changement a un mouvement changé dest");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot d'origine du changement a un mouvement changeable");
$t->ok($chgtDenomFromDrev->lots[1]->affectable, "Le lot résultant du changement est affectable");
$t->is($chgtDenomFromDrev->lots[1]->statut, null, "Le statut lot résultant du changement est vide");
$t->is($chgtDenomFromDrev->lots[1]->volume, $chgtDenomFromDrev->origine_volume, "Le lot résultant du changement a le volume d'origine");
$t->ok($chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot résultant du changement a un mouvement affectable");
$t->ok(!$chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le lot résultant du changement a un mouvement changé dest");
$t->ok($chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot résultant du changement a un mouvement changeable");
$t->is($chgtDenomFromDrev->lots[0]->document_ordre, '02', "Le lot d'origine du changement a bien un ordre à 02");
$t->is($chgtDenomFromDrev->lots[1]->document_ordre, '01', "Le lot résultant du changement a bien un ordre à 01");


$t->comment("Changement de dénom de changement de dénom depuis : ".$chgtDenomFromDrev->_id.':'.$chgtDenomFromDrev->lots[1]->unique_id);

$chgtDenomDeChgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $chgtDenomFromDrev->lots[1], $year.'-12-12 10:10:10', null);
$chgtDenomDeChgtDenom->save();
$chgtDenomDeChgtDenom->changement_produit_hash = $lotFromDrev->produit_hash;
$chgtDenomDeChgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenomDeChgtDenom->validate($doc_date);
$chgtDenomDeChgtDenom->validateOdg($doc_date);
$chgtDenomDeChgtDenom->save();

$chgtDenomFromDrev = ChgtDenomClient::getInstance()->find($chgtDenomFromDrev->_id);

$t->is($chgtDenomDeChgtDenom->lots[0]->volume, 0, "Le lot origine du changement a un volume à 0");
$t->is($chgtDenomDeChgtDenom->lots[1]->volume, $chgtDenomDeChgtDenom->origine_volume, "Le lot resultant à le volume d'origine");

$t->is($chgtDenomDeChgtDenom->lots[0]->id_document_provenance, $chgtDenomFromDrev->_id, "Le lot origine du changement a la bonne provenance");
$t->is($chgtDenomDeChgtDenom->lots[1]->id_document_provenance, null, "Le lot résultant n'a pas de provenance");

$t->is($chgtDenomDeChgtDenom->lots[0]->document_ordre, '02', "Le lot origine du changement bien un numero d'ordre à 2");
$t->is($chgtDenomDeChgtDenom->lots[1]->document_ordre, '01', "Le lot resultant a bien un numero d'ordre à 1");


$chgtDenomFromDrev = ChgtDenomClient::getInstance()->find($chgtDenomFromDrev->_id);
$t->is($chgtDenomFromDrev->lots[1]->id_document_affectation, $chgtDenomDeChgtDenom->_id, "Le lot du document d'origine a bien l'affectation");
$t->ok($chgtDenomFromDrev->lots[1]->getLotAffectation(), "Le lot du document d'origine a bien une affectation");

$t->ok($chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le lot du doc d'origine du chgmt de chgmt a un changé source src");
$t->ok(!$chgtDenomFromDrev->lots[1]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot du doc d'origine du chgmt de chgmt n'est plus changeable");

$t->is($chgtDenomFromDrev->lots[1]->unique_id, $chgtDenomDeChgtDenom->lots[0]->unique_id, "le lot du doc d'origine et le lot changé ont les même unique_id");
$t->ok(!$chgtDenomDeChgtDenom->lots[0]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le lot origine du changement n'a pas de mouvement changé src");
$t->ok($chgtDenomDeChgtDenom->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le lot origine du changement a un mouvement changé dest");
$t->ok(!$chgtDenomDeChgtDenom->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot origine du changement n'a pas de mouvement changeable");

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);
$t->is(count($lots), 3, "3 lots disponibles au changement de denomination (celui provenant du chgement de denom)");

$t->comment("Test via une desgustation");

$lotsPrelevables = DegustationClient::getInstance()->getLotsEnAttente(null);
$t->is(count($lotsPrelevables), 3, "3 lots en attentes de dégustation");

$degustation = new Degustation();
$degustation->lieu = "Test — Test";
$degustation->date = date('Y-m-d')." 14:00";
$degustation->save();
$degustation->setLots($lotsPrelevables);
$degustation->save();

$t->is(count(MouvementLotView::getInstance()->getByStatut(Lot::STATUT_AFFECTABLE)->rows), 0, "0 lots prelevables");
$t->is($degustation->lots[0]->initial_type, DRevClient::TYPE_MODEL, "L'initial type du lot est ".DRevClient::TYPE_MODEL);
$t->is($degustation->lots[1]->initial_type, DRevClient::TYPE_MODEL, "L'initial type du lot est ".DRevClient::TYPE_MODEL);
$t->is($degustation->lots[2]->initial_type, DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE, "L'initial type du lot est ".DRevClient::TYPE_MODEL.":".LotsClient::INITIAL_TYPE_CHANGE);

$degustation->lots[0]->statut = Lot::STATUT_NONCONFORME;
$degustation->lots[1]->statut = Lot::STATUT_CONFORME;
$degustation->lots[2]->statut = Lot::STATUT_CONFORME;
$degustation->save();

$t->comment("Création d'un Changement de Denom Total depuis une dégustation");

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);
$t->is(count($lots), 3, "3 mouvements disponibles au changement de dénomination");

$lotFromDegust = current($lots);
$t->is($lotFromDegust->statut, Lot::STATUT_NONCONFORME, 'le lot sélectionné de la dégust est bien NON CONFORME');
$t->is($lotFromDegust->id_document, $degustation->_id, 'le lot sélectionné provient bien de la dégustation '.$degustation->_id);
$volume = $lot->volume;
$lotFromDegustConforme = next($lots);

$date = $year.'-11-11 11:11:11';
$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lotFromDegust, $date, null);
$chgtDenom->save();
$t->comment($chgtDenom->_id);
$t->is($chgtDenom->_id, "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date), "id du document");
$t->is($chgtDenom->campagne, $drev->campagne, "le chgt de denom a la campagne de la drev");
$t->is($chgtDenom->periode, $year, "le chgt de denom a la bonne periode à $year");

$t->is($chgtDenom->changement_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, 'changement_numero_logement_operateur par defaut est le numero logement du lot origine');
$t->is($chgtDenom->origine_affectable, $lotFromDegust->affectable, "L'affectation origine reprends l'affectation du lot d'origine de la dégustation");
$t->is($chgtDenom->changement_affectable, $lotFromDegust->affectable, "L'affectation changé reprends l'affectation du lot d'origine de la dégustation comme le changement est total");
$t->is($chgtDenom->origine_statut, Lot::STATUT_NONCONFORME, "Le statut d'origine du lot est non conforme");

$chgtDenom->remove('changement_cepages');
$chgtDenom->add('changement_cepages');
$chgtDenom->changement_cepages = array('CABERNET' => $volume);
$chgtDenom->changement_produit_hash = $lotFromDegustConforme->produit_hash;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->changement_numero_logement_operateur = "2(ex1)";
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$t->is(count($chgtDenom->lots), 2, "1 seul lot généré");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->changement_volume, $volume, "Le changement est bien sur $volume hl");
$t->is($chgtDenom->changement_produit_libelle, $lotFromDegustConforme->produit_libelle, "Libellé produit est bien ".$lotFromDegustConforme->produit_libelle);
$t->is($chgtDenom->origine_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, "Le logement origine est celui du lot origine");
$t->ok($chgtDenom->isTotal(), "Le changement est bien indiqué comme total");

$lotFromChgmtOrig = $chgtDenom->lots->get(0);
$lotFromChgmtRes = $chgtDenom->lots->get(1);
$t->is($lotFromChgmtOrig->numero_archive, $lotFromDegust->numero_archive, "Le numéro d'archive n'a pas changé");
$t->is($lotFromChgmtOrig->numero_dossier, $lotFromDegust->numero_dossier, "Le numéro de dossier n'a pas changé");
$t->is($lotFromChgmtOrig->document_ordre, '03', "Le numéro d'ordre est bien 03");
$t->is($lotFromChgmtOrig->id_document_provenance, $degustation->_id, "Le lot origine provient bien de la dégustation ".$degustation->_id);
$t->is($lotFromChgmtOrig->produit_hash, $chgtDenom->origine_produit_hash, "Le produit est bien l'original dans le lot origine");
$t->is($lotFromChgmtOrig->volume, 0, "Le volume est bien à 0 dans le lot origine");
$t->is($lotFromChgmtOrig->produit_libelle, $chgtDenom->origine_produit_libelle, "Le libellé du produit est bien l'origine dans le lot");
$t->is($lotFromChgmtOrig->numero_logement_operateur, $chgtDenom->origine_numero_logement_operateur, "Le lot origine a bien logement origine");
$t->is($lotFromChgmtOrig->statut, Lot::STATUT_NONCONFORME, "Le statut du lot origine est non conforme");
$t->is($lotFromChgmtOrig->affectable, false, "le lot origine avec un volume à 0 n'est pas affectable");
$t->is($lotFromChgmtRes->produit_hash, $chgtDenom->changement_produit_hash, "Le produit est bien le nouveau dans le lot");
$t->is($lotFromChgmtRes->volume, $chgtDenom->changement_volume, "Le volume est bien le nouveau dans le lot");
$t->is($lotFromChgmtRes->produit_libelle, $chgtDenom->changement_produit_libelle, "Le libellé du produit est bien le nouveau dans le lot");
$t->is($lotFromChgmtRes->volume, $volume, "Le volume de $volume hl est bien appliqué dans le nouveau dans le lot");
$t->is($lotFromChgmtRes->cepages->toArray(), array('CABERNET' => $volume), "Le 100% cepage est bien appliqué dans dans le lot");
$t->is($lotFromChgmtRes->numero_logement_operateur, $chgtDenom->changement_numero_logement_operateur, "Le lot changé a changé de logement");
$t->is($lotFromChgmtRes->statut, Lot::STATUT_NONAFFECTABLE, "Le statut du lot changé est réputé conforme");

$t->ok($lotFromChgmtOrig->getMouvement(Lot::STATUT_CHANGE_DEST), "statut du lot change dest");
$t->ok(!$lotFromChgmtOrig->getMouvement(Lot::STATUT_NONAFFECTABLE), "statut du lot en volume à 0  n'est pas affectable");
$t->ok($lotFromChgmtOrig->getMouvement(Lot::STATUT_NONCONFORME), "mouvement du lot non conforme");
$t->ok($lotFromChgmtRes->getMouvement(Lot::STATUT_CHANGEABLE), "statut du lot changeable");
$t->ok($lotFromChgmtRes->getMouvement(Lot::STATUT_NONAFFECTABLE), "statut du lot affectable (provenant d'une non conformité)");
$t->ok(!$lotFromChgmtRes->getMouvement(Lot::STATUT_NONCONFORME), "pas de mouvement du lot non conforme");

$degustProvenance = $lotFromChgmtOrig->getLotProvenance();
$t->ok($degustProvenance && ($degustProvenance->getDocument()->_id == $degustation->_id), 'la provenance du chgt est bien la dégustation '.$degustation->_id);
$t->ok($degustProvenance && $degustProvenance->getMouvement(Lot::STATUT_CHANGE_SRC), "statut du lot de la degust à 'revendiqué changé'");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Chgt de Denom Partiel");
$chgtDenom->setLotOrigine($lotFromDegustConforme);
$chgtDenom->changement_produit_hash = $lotFromDegustConforme->produit_hash;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->changement_volume = 20;
$chgtDenom->changement_cepages = array('CABERNET' => 10, 'PINOT' => 10);
$chgtDenom->changement_numero_logement_operateur = "2(ex1)";
$chgtDenom->changement_affectable = true;
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$t->is($chgtDenom->changement_origine_id_document, $degustation->_id, "Le changement a bien comme origine ".$degustation->_id);
$t->is($chgtDenom->changement_produit_libelle, $lotFromDegustConforme->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is(count($chgtDenom->origine_cepages), 2, "L'origine du cépage a bien 2 cepages");
$t->is(count($chgtDenom->changement_cepages), 2, "Le changement a bien 2 cepages");
$t->is($chgtDenom->origine_statut, Lot::STATUT_CONFORME, "Le statut d'origine du lot est conforme");

$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$t->is($chgtDenom->lots[0]->date, $chgtDenom->date, "La date du lot est celle du changement de dénomination");
$t->is($chgtDenom->lots[0]->numero_archive, $lotFromDegustConforme->numero_archive, "numero d'archive correctement du lot 2 conservé : ".$lotFromDegustConforme->numero_archive);
$t->is($chgtDenom->lots[0]->campagne, $lotFromDegustConforme->campagne, "la campagne est conservé : ".$lotFromDegustConforme->campagne);
$t->is($chgtDenom->lots[1]->numero_archive, '00007', "numeros d'archive du lot 2 changé pour le suivant");
$t->is($chgtDenom->lots[1]->numero_archive, '00007', "numeros d'archive du lot 2 changé pour le suivant");
$t->is($chgtDenom->lots[1]->campagne, $chgtDenom->campagne, "La campagne du lot 2 est celle du changement de dénomination");
$t->is($chgtDenom->lots[1]->date, $chgtDenom->date, "La date du lot est celle du changement de dénomination");
$t->is($chgtDenom->lots[0]->document_ordre, '03', "Le lot 1 a bien 03 comme numéro d'ordre");
$t->is($chgtDenom->lots[1]->document_ordre, '01', "Le lot 2 a bien 01 comme numéro d'ordre");
$t->is($chgtDenom->lots[0]->id_document_provenance, $degustation->_id, "Le lot 1 généré provient bien de la dégustation ".$degustation->_id);
$t->is($chgtDenom->lots[1]->id_document_provenance, null, "Le lot 2 perd sa provenance de ".$degustation->_id);
$t->is($chgtDenom->lots[0]->numero_logement_operateur, $chgtDenom->origine_numero_logement_operateur, "Le numero logement opérateur n'a pas changé pour le lot origine");
$t->is($chgtDenom->lots[1]->numero_logement_operateur, $chgtDenom->changement_numero_logement_operateur, "Le logement lot 2 a changé");
$t->is($chgtDenom->lots[0]->affectable, false, "Le lot origine conforme ayant un volume positif est affectable");
$t->is($chgtDenom->lots[1]->affectable, true, "L'affectation lot 2 est bien affectable comme demandé à la racine du document (changement_affectable)");
$t->is($chgtDenom->lots[0]->cepages['CABERNET'], 15, "L'affectation du lot origine a la bonne répartition de cepages (CABERNET)");
$t->is($chgtDenom->lots[0]->cepages['PINOT'], 15, "L'affectation du lot origine a la bonne répartition de cepages (PINOT)");
$t->is($chgtDenom->lots[1]->cepages['CABERNET'], 10, "Le lot 2 du changement a les cepages (CABERNET)");
$t->is($chgtDenom->lots[1]->cepages['PINOT'], 10, "Le lot 2 du changement a les cepages (PINOT)");
$t->is($chgtDenomFromDrev->lots[0]->specificite, 'Ma fausse 2ème dégustation', "La spécificité du lot d'origine est celle d'origine");
$t->is($chgtDenomFromDrev->lots[1]->specificite, 'Ma fausse', "La spécificité du lot changé n'a plus de 2d dégust");

$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_CONFORME, "statut du lot orginel est bien conforme");
$t->ok(!$chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_NONAFFECTABLE), "Mouvement lot restant affectable");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CONFORME), "Mouvement lot restant conforme");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "Mouvement lot changé affectable ");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "Mouvement lot restant change dest");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_CHANGE_DEST), "Mouvement lot changé change dest");
$t->ok(!$chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_CONFORME), "Pas de mouvement lot conforme");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE_SRC), "le lot originel a bien un mouvement au statut changé");
$t->is($chgtDenom->lots->get(1)->statut, null, "statut du lot changé est vide");

$t->comment("On check l'historique du lot");
$lotHistoryARecup = $chgtDenom->lots[1];
$historiqueDuLot = LotsClient::getInstance()->getHistory($chgtDenom->identifiant, $lotHistoryARecup->unique_id);
$t->is(strpos($historiqueDuLot[0]->id, 'DREV') === 0, true, "On remonte jusqu'à l'origine");
$t->is($chgtDenom->lots->get(1)->isRedegustationDejaConforme(), true, "Le lot est déjà conforme");

$t->comment("Gestion de l'affectation du lot changé");
$chgtDenom->devalidate();
$chgtDenom->validate($doc_date);
$chgtDenom->changement_affectable = false;
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();
$t->is($chgtDenom->changement_affectable, false, "Le changement n'est plus affectable car changement_affectable = false");
$t->is($chgtDenom->lots[1]->affectable, false, "Le lot changé n'est plus affectable car changement_affectable = false");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_NONAFFECTABLE), "Mouvement lot restant affectable car changement_affectable = false");
$t->ok(!$chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "Mouvement lot changé affectable car changement_affectable = false");

$chgtDenom->devalidate();
$chgtDenom->validate($doc_date);
$chgtDenom->changement_affectable = true;
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();
$t->is($chgtDenom->changement_affectable, true, "Le changement n'est plus affectable car changement_affectable = true");
$t->is($chgtDenom->lots[1]->affectable, true, "Le lot changé n'est plus affectable car changement_affectable = true");
$t->ok(!$chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_NONAFFECTABLE), "Mouvement lot restant affectable car changement_affectable = true");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "Mouvement lot changé affectable car changement_affectable = true");

$chgtDenom->lots->get(1)->affectable = false;
$chgtDenom->save();
$t->is($chgtDenom->changement_affectable, false, "Le changement n'est plus affectable car lot affectable = false");
$t->is($chgtDenom->lots[1]->affectable, false, "Le lot changé n'est plus affectable car lot affectable = false");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_NONAFFECTABLE), "Mouvement lot restant affectable car lot affectable = false");
$t->ok(!$chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "Mouvement lot changé affectable car lot affectable = false");

$chgtDenom->lots->get(1)->affectable = true;
$chgtDenom->save();
$t->is($chgtDenom->changement_affectable, true, "Le changement n'est plus affectable car lot affectable = true");
$t->is($chgtDenom->lots[1]->affectable, true, "Le lot changé n'est plus affectable car lot affectable = true");
$t->ok(!$chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_NONAFFECTABLE), "Mouvement lot restant affectable car lot affectable = true");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "Mouvement lot changé affectable car lot affectable = true");

$t->comment("Création d'un Declassement Total");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = $volume;
$chgtDenom->changement_specificite = "HVE";
$chgtDenom->changement_affectable = true;
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$t->ok($chgtDenom->isTotal(), "Le changement qui a un volume identique est bien un changement total");
$t->is(count($chgtDenom->lots), 1, "Ce changement total ne génère plus que 1 lot");
$chgtDenom->generateMouvementsLots(1);
$t->is($chgtDenom->lots[0]->numero_archive, $lotFromDegust->numero_archive, "Un chgm total ne change pas le numero d'archive");
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is(count($chgtDenom->lots), 1, "Dans un declassement total, on a bien un seul lot");
$t->ok(!$chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "le mouvement du lot n'a pas de statut changé dest");
$t->ok(!$chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGEABLE), "le mouvement de lot n'est pas changeable");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_DECLASSE), "le mouvement de lot indique le déclassement");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Partiel");
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = 10;
$chgtDenom->origine_numero_logement_operateur = "2 (ex1)";
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$chgtDenom->devalidate();

$t->ok(!$chgtDenom->isTotal(), "Le changement est bien partiel vu qu'il porte sur ".round($volume / 3, 2)." hl");

$t->is($chgtDenom->changement_origine_id_document, $degustation->_id, "changement_origine_id_document est bien ".$degustation->_id);
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is($chgtDenom->origine_produit_hash, $lotFromDegust->produit_hash, "l'origine du produit est bien conservée");

$t->is(count($chgtDenom->lots), 0, "Le changement n'ayant été approuvé, il ne continet pas de lot");

$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$t->is(count($chgtDenom->lots), 1, "Pour un déclassement partiel n'a qu'un seul lot");
$t->is($chgtDenom->lots[0]->numero_archive, '00001', "Pour le déclassement, le 1er lot n'a pas changé de numéro d'archive");
$t->is($chgtDenom->lots[0]->document_ordre, '03', "Le numéro d'ordre du lot 1 est bien 03");
$t->is($chgtDenom->lots[0]->volume, 40, "le volume du lot originel a bien changé également");
$t->is(count($chgtDenom->lots[0]->cepages), 2, "Le lot non déclassé à toujours 2 cepages");
$t->is($chgtDenom->lots[0]->cepages['CABERNET'], 20, "Le nouveau volume est appliqué au 1er cépage");
$t->is($chgtDenom->lots[0]->cepages['PINOT'], 20, "Le nouveau volume est appliqué au 2d cépages");
$t->is($chgtDenom->lots[0]->id_document_provenance, $degustation->_id, "la provenance du lot 1 est bien ".$degustation->_id);
$t->is($chgtDenom->lots[0]->numero_logement_operateur, $chgtDenom->origine_numero_logement_operateur, "Le numero logement operateur d'origine a changé");
$t->is($chgtDenom->lots[0]->produit_hash, $lotFromDegust->produit_hash, "Le 1er lot a bien conservé son produit");


$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "le mouvement du lot d'origine a un statut changé dest");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGEABLE), "le mouvement du lot d'origine reste changeable");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_DECLASSE), "le mouvement du lot partiel a un statut déclassé");
$t->is($chgtDenom->getLotOrigine()->id_document_affectation, $chgtDenom->_id, "le lot d'origine a bien l'affectation du changement ".$chgtDenom->_id);
$t->ok($chgtDenom->getLotOrigine()->isChange(), "statut des mvt du lot origine a bien isChange()");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE_SRC), "statut des mvt du lot origine a bien un mouvement changé src");

$t->comment("Dévalidation d'un ChgtDenom");
$t->ok($chgtDenom->isApprouve(), "Le changement est bien validé et approuvé : ".$chgtDenom->validation_odg);
$chgtDenom->devalidate();
$chgtDenom->save();
$t->ok(!$chgtDenom->isValidee(), "Le changement est maintenant dévalidé.");
$t->ok(!count($chgtDenom->lots), "Le changement ne contient plus de lot");

$degustation = DegustationClient::getInstance()->find($degustation->_id);
$lotFromDegust = $degustation->getLot($lotFromDegust->unique_id);
$t->ok(!$lotFromDegust->id_document_affectation, "Le doc d'origine du lot n'a plus d'affectation");

$t->comment("Édition d'un logement");
$chgtDenom->generateLots();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), true, "Le lot d'origine d'un déclassement a un logement editable");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->devalidate();
$chgtDenom->save();

$chgtDenom->changement_volume = $lotFromDegust->volume;
$chgtDenom->changement_specificite = "HVE";
$chgtDenom->generateLots();

$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un déclassement total n'a pas de logement editable");
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un déclassement total après validation n'a pas de logement editable");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->devalidate();
$chgtDenom->save();

$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenom->generateLots();

$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un chgt denom total n'est pas editable");
$t->is($chgtDenom->lots->get(1)->isLogementEditable(), true, "Le lot résultant d'un chgt denom total est editable");
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un chgt denom total après validation n'a pas de logement editable");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->devalidate();
$chgtDenom->save();

$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->generateLots();

$t->is($chgtDenom->lots->get(0)->isLogementEditable(), true, "Le lot d'origine d'un chgt denom partiel a un logement editable");
$t->is($chgtDenom->lots->get(1)->isLogementEditable(), true, "Le lot resultant d'un chgt denom partiel a un logement editable");
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un chgt denom total après validation n'a pas de logement editable");
$t->is($chgtDenom->lots->get(1)->isLogementEditable(), false, "Le lot d'origine d'un chgt denom total après validation n'a pas de logement editable");

$t->comment("ajout d'un lot sans origine");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->addLot();
$lot = $drev->lots[0] ;
$lot->getUniqueId();

$date = $year.'-10-11 10:10:10';
$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lot, $date, $papier);
$chgtDenom->constructId();
$chgtdenom_sanslot_id = $chgtDenom->_id;
$t->comment($chgtdenom_sanslot_id);

$form = new ChgtDenomNewLotForm($lot, $chgtDenom);
$valuesRev = array(
    '_revision' => $chgtDenom->_rev,
    'volume' => 12,
    'millesime' => $periode,
    'numero_logement_operateur' => 'C2',
    'destination_date' => '01/07/'.$periode,
    'produit_hash' => $lotFromDegustConforme->produit_hash,
    'destination_type' => DRevClient::LOT_DESTINATION_VRAC_EXPORT,
    'specificite' => 'HVE'
);
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire de création de lot est valide");
$form->save();

$chgtDenom = ChgtDenomClient::getInstance()->find($chgtdenom_sanslot_id);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = 12;
$chgtDenom->validate($periode.'-12-01');
$chgtDenom->validateOdg($periode.'-12-01');
$chgtDenom->save();

$t->ok($chgtDenom->isValidee(), "Le changement est validé");
$t->is($chgtDenom->numero_archive, '00005', "le changement de dénomination a bien un numero d'archive");
$t->is($chgtDenom->lots[0]->numero_archive, '00007', "Le lot déclassé a le bon numéro d'archive");
$t->is($chgtDenom->lots[0]->numero_dossier, '00005', "Le lot déclassé a le bon numéro de dossier");
$t->is($chgtDenom->lots[0]->campagne, $periode.'-'.($periode + 1 ), "Le lot déclassé à la bonne campagne");
$t->is($chgtDenom->lots[0]->unique_id, $periode.'-'.($periode + 1 ).'-00005-00007', "Le lot déclassé a le bon unique_id");


$t->comment("Changement de dénomination sur une campagne différente");

$date = ($year+1).'-12-10 10:10:10';
$chgtDenomAutreCampagne = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lotFromDrev, $date, null);
$chgtDenomAutreCampagne->constructId();
$chgtDenomAutreCampagne->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenomAutreCampagne->changement_produit_hash = $lotFromDegustConforme->produit_hash;
$chgtDenomAutreCampagne->changement_volume = 1;
$chgtDenomAutreCampagne->generateLots();

$t->is($chgtDenomAutreCampagne->campagne, $drev->campagne, "La campagne du changement de dénomination est celle de la drev");
$t->is($chgtDenomAutreCampagne->lots[0]->campagne, $drev->campagne, "La campagne du lot non changé est celle de la drev");
$t->is($chgtDenomAutreCampagne->lots[1]->campagne, $drev->campagne, "La campagne du lot changé est celle de la drev");

$t->comment("Création d'un changement partiel à partir d'une drev puis dégustation sur la partie non changé");

$date = ($year+1).'-12-10 15:00:00';

$drevM01 = $drev->getMaster()->generateModificative();
$lot = $drevM01->addLot();
$lot->volume = 100;
$lot->produit_hash = $produit->getHash();
$drevM01->validate($date);
$drevM01->add('date_commission', $drev->getDateValidation('Y-m-d'));
$drevM01->validateOdg($date);
$drevM01->save();
$lot = $drevM01->lots[$lot->getKey()];

$t->is($drevM01->lots[$lot->getKey()]->date_commission, $drevM01->date_commission, "Date de commission du lot de la DREV");

$date = ($year+1).'-12-20 15:00:00';
$dateLotchange = ($year+1).'-12-22';

$chgtDenomFromDrev = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lot, $date, null);
$chgtDenomFromDrev->changement_volume = 50;
$chgtDenomFromDrev->changement_produit_hash = $lot->produit_hash;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenomFromDrev->changement_date_commission = $dateLotchange;
$chgtDenomFromDrev->validate($doc_date);
$chgtDenomFromDrev->validateOdg($doc_date);
$chgtDenomFromDrev->save();

$drevM01 = DRevClient::getInstance()->find($drevM01->_id);
$t->is($drevM01->lots[$lot->getKey()]->date_commission, $drevM01->date_commission, "Date de commission du lot de la DREV");
$t->is($chgtDenomFromDrev->lots[0]->date_commission, $drevM01->date_commission, "Date de commission du lot de Changement de dénomination");
$t->is($chgtDenomFromDrev->lots[1]->date_commission, $dateLotchange, "Date de commission du nouveau lot du changement de dénomination");

$date = ($year+1).'-12-25 15:00:00';

$degustation = new Degustation();
$degustation->lieu = "Test — Test";
$degustation->date = $date;
$degustation->save();
$degustation->setLots(array($chgtDenomFromDrev->lots[0], $chgtDenomFromDrev->lots[1]));
$degustation->lots[0]->statut = Lot::STATUT_NONCONFORME;
$degustation->save();

$drevM01 = DRevClient::getInstance()->find($drevM01->_id);
$chgtDenomFromDrev = ChgtDenomClient::getInstance()->find($chgtDenomFromDrev->_id);

$t->is($drevM01->lots[$lot->getKey()]->date_commission, $degustation->date_commission, "Date de commission du lot de la DREV est celle de la dégustation");
$t->is($chgtDenomFromDrev->lots[0]->date_commission, $degustation->date_commission, "Date de commission du lot du changement de dénomination est celle de la dégustation");
$t->is($chgtDenomFromDrev->lots[1]->date_commission, $degustation->date_commission, "Date de commission du nouveau lot du changement de dénomination est celle de la dégustation");

$date = ($year+1).'-12-28 15:00:00';

$lotDeguste = $degustation->lots[0];
$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $lotDeguste, $date, null);
$chgtDenom->changement_volume = 25;
$chgtDenom->changement_produit_hash = $lot->produit_hash;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->validate($doc_date);
$chgtDenom->validateOdg($doc_date);
$chgtDenom->save();

$drevM01 = DRevClient::getInstance()->find($drevM01->_id);
$chgtDenomFromDrev = ChgtDenomClient::getInstance()->find($chgtDenomFromDrev->_id);

$t->is($drevM01->lots[$lot->getKey()]->date_commission, $degustation->date_commission, "Date de commission du lot de la DREV est celle de la dégustation");
$t->is($chgtDenomFromDrev->lots[0]->date_commission, $degustation->date_commission, "Date de commission du lot du Changement de dénomination est celle de la dégustation");
$t->is($chgtDenomFromDrev->lots[1]->date_commission, $degustation->date_commission, "Date de commission du nouveau lot du changement de dénomination est celle de la dégustation");
$t->is($degustation->lots[0]->date_commission, $degustation->date_commission, "Date de commission du lot de la dégustation");
$t->is($chgtDenom->lots[0]->date_commission, $degustation->date_commission, "Date de commission du lot du changement de dénomination après la dégustation");

$date = ($year+1).'-12-29 15:00:00';

$degustation2 = new Degustation();
$degustation2->lieu = "Test — Test";
$degustation2->date = $date;
$degustation2->save();
$degustation2->setLots(array($chgtDenom->lots[0]));
$degustation2->lots[0]->statut = Lot::STATUT_CONFORME;
$degustation2->save();

$drevM01 = DRevClient::getInstance()->find($drevM01->_id);
$chgtDenomFromDrev = ChgtDenomClient::getInstance()->find($chgtDenomFromDrev->_id);
$degustation = DegustationClient::getInstance()->find($degustation->_id);
$degustation->save();
$chgtDenom = ChgtDenomClient::getInstance()->find($chgtDenom->_id);

$t->is($drevM01->lots[$lot->getKey()]->date_commission, $degustation->date_commission, "Date de commission du lot de la DREV est celle de la dégustation");
$t->is($chgtDenomFromDrev->lots[0]->date_commission, $degustation->date_commission, "Date de commission du lot du Changement de dénomination est celle de la dégustation");
$t->is($chgtDenomFromDrev->lots[1]->date_commission, $degustation->date_commission, "Date de commission du nouveau lot du changement de dénomination est celle de la dégustation");
$t->is($degustation->lots[0]->date_commission, $degustation->date_commission, "Date de commission du lot de la dégustation");
$t->is($chgtDenom->lots[0]->date_commission, $degustation2->date_commission, "Date de commission du lot du changement de dénomination après la dégustation");
$t->is($degustation2->lots[0]->date_commission, $degustation2->date_commission, "Date de commission du lot du changement de dénomination après la dégustation");
