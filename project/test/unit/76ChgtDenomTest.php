<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(107);

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

foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

$campagne = (date('Y')-1)."";

//Début des tests
$t->comment("Création d'une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->constructId();
$drev->storeDeclarant();

$produits = $drev->getConfigProduits();
$nbProduit = 0;
foreach($produits as $produit) {
    if(!$produit->isRevendicationParLots()) {
        continue;
    }
    $drev->addProduit($produit->getHash());
    $nbProduit++;
    if ($nbProduit == 3) {
      break;
    }
}
$i=1;
foreach($drev->lots as $lot) {
$lot->id_document = $drev->_id;
$lot->millesime = $campagne;
$lot->numero_logement_operateur = $i;
$lot->volume = 50;
$lot->affectable = true;
$lot->destination_type = null;
$lot->destination_date = ($campagne+1).'-'.sprintf("%02d", 1).'-'.sprintf("%02d", 1);
$lot->destination_type = DRevClient::LOT_DESTINATION_VRAC_EXPORT;
$i++;
}
$drev->validate();
$drev->lots[2]->affectable = false;
$drev->lots[2]->millesime = "2015";
$drev->validateOdg();
$drev->save();

$t->is(count($drev->lots), 3, "3 lots ont automatiquement été créés");
$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le 1er lot est affectable");
$t->ok($drev->lots[1]->getMouvement(Lot::STATUT_AFFECTABLE), "Le 2ème lot est affectable");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_NONAFFECTABLE), "Le 3ème lot est non affectable");
$t->ok(!$drev->lots[2]->isChange(), "Le lot changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->lots[1]->isChange(), "un lot non changeable dans la DREV n'est pas isChange()");


$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 2, "2 mouvements de lot prelevables ont été générés");
$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant);
$t->is(count($lots), 1, "un seul des 3 lots de la DREV est changeables");

$year = date('Y') - 1;
$campagne = $year.'-'.($year + 1);

$t->comment("Changement de dénom sur DREV");

$date = $year.'-10-10 10:10:10';
$chgtDenomFromDrev = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date);
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->save();
$t->comment($chgtDenomFromDrev->_id);

$idChgtDenomFromDrev = "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date);
$t->is($chgtDenomFromDrev->_id, $idChgtDenomFromDrev, "id du document");
$t->is($chgtDenomFromDrev->campagne, $campagne, "le chgt de denom a la bonne campagne à $campagne");
$t->is($chgtDenomFromDrev->periode, $year, "le chgt de denom a la bonne periode à $year");

$lotFromDrev = array_shift($lots);
$chgtDenomFromDrev->setLotOrigine($lotFromDrev);
$chgtDenomFromDrev->changement_produit_hash = $drev->lots[1]->produit_hash;
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->save();

$t->is($chgtDenomFromDrev->changement_origine_id_document, $drev->_id, "Le changement a bien comme document d'origine ".$drev->_id);
$t->is($chgtDenomFromDrev->changement_origine_lot_unique_id, $lotFromDrev->unique_id, "Le changement a bien l'unique id de son origine ".$lotFromDrev->unique_id);
$t->is($chgtDenomFromDrev->changement_volume, $lotFromDrev->volume, "Le changement a bien le volume de son origine");
$t->is($chgtDenomFromDrev->changement_millesime, $lotFromDrev->millesime, "Le changement a bien le millesime de son origine : ".$drev->lots[2]->millesime);
$t->isnt($chgtDenomFromDrev->changement_produit_hash, $lotFromDrev->produit_hash, "Le changement a bien un produit différent");
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Le lot ");
$t->is($chgtDenomFromDrev->changement_millesime, $lotFromDrev->millesime, "Le changement a bien le millesime de son origine : ".$drev->lots[2]->millesime);

$t->is(count($chgtDenomFromDrev->lots), 1, "Le changement étant total, on a un seul lot");
$t->is($chgtDenomFromDrev->lots[0]->id_document, $idChgtDenomFromDrev, "Le lot du chgt a bien id_document ".$idChgtDenomFromDrev);
$t->is($chgtDenomFromDrev->lots[0]->document_ordre, '02', "Le lot du chgt a bien comme document_ordre 02");
$t->is($chgtDenomFromDrev->lots[0]->id_document_provenance, $drev->_id, "Le lot du chgt a bien comme provenance la drev ".$drev->_id);
$t->is($chgtDenomFromDrev->lots[0]->affectable, $lotFromDrev->affectable, "Le lot du chgt a la même non affectation que dans la drev (réputé conforme)");

$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_NONAFFECTABLE), "Le changement a bien un mouvement non affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le changement a bien un mouvement changé dest");

$drev = DrevClient::getInstance()->find($drev->_id);
$t->is($drev->lots[2]->id_document_affectation, $idChgtDenomFromDrev, "La DREV bien le changement enregistré comme affectation ".$idChgtDenomFromDrev);
$t->ok($drev->lots[2]->isChange(), "Le lot changé dans la DREV est bien isChange()");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le changement a bien généré dans la Drev un mouvement changé src");

$t->comment("On transforme le changement en déclassement");
$chgtDenomFromDrev->changement_produit_hash = null;
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->save();
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Le type est déclassement");
$t->ok(!$chgtDenomFromDrev->lots[0]->affectable, "Le lot du changement n'est pas affectable");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le changement a bien un mouvement affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le changement a bien un mouvement changé dest");


$t->comment("on remet et un produit et on rend le lot affectable");
$chgtDenomFromDrev->changement_produit_hash = $lotFromDrev->produit_hash;
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->lots[0]->affectable = true;
$chgtDenomFromDrev->save();
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Le type est redevenu un changement de denom");
$t->ok($chgtDenomFromDrev->lots[0]->affectable, "Le lot du changement est bien affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le changement a bien un mouvement affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le changement a bien un mouvement changé dest");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le changement a bien un mouvement changeable");

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant);
$t->is(count($lots), 1, "1 lot disponible au changement de denomination (celui provenant du chgement de denom)");

$t->comment("Test via une desgustation");

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 3, "3 lots en attentes de dégustation");

$degustation = new Degustation();
$degustation->lieu = "Test — Test";
$degustation->date = date('Y-m-d')." 14:00";
$degustation->save();
$degustation->setLots($lotsPrelevables);
$degustation->save();

$t->is(count(MouvementLotView::getInstance()->getByStatut(Lot::STATUT_AFFECTABLE)->rows), 0, "0 lots prelevables");

$degustation->lots[0]->statut = Lot::STATUT_NONCONFORME;
$degustation->lots[1]->statut = Lot::STATUT_CONFORME;
$degustation->lots[2]->statut = Lot::STATUT_CONFORME;
$degustation->save();

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant);
$t->is(count($lots), 3, "3 mouvements disponibles au changement de dénomination");

$lotFromDegust = current($lots);
$t->is($lotFromDegust->statut, Lot::STATUT_NONCONFORME, 'le lot sélectionné de la dégust est bien NON CONFORME');
$t->is($lotFromDegust->id_document, $degustation->_id, 'le lot sélectionné provient bien de la dégustation '.$degustation->_id);
$volume = $lot->volume;
$autreLot = next($lots);

$date = $year.'-11-11 11:11:11';
$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date);
$chgtDenom->save();
$t->comment($chgtDenom->_id);
$t->is($chgtDenom->_id, "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date), "id du document");
$t->is($chgtDenom->campagne, $campagne, "le chgt de denom a la bonne campagne à $campagne");
$t->is($chgtDenom->periode, $year, "le chgt de denom a la bonne periode à $year");

$t->comment("Création d'un Changement de Denom Total");

$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->changement_cepages = array('CABERNET' => $volume);
$chgtDenom->changement_produit_hash = $autreLot->produit_hash;
$chgtDenom->validate();
$chgtDenom->save();

$t->is(count($chgtDenom->lots), 1, "1 seul lot généré");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->changement_volume, $volume, "Le changement est bien sur $volume hl");
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit est bien ".$autreLot->produit_libelle);
$t->ok($chgtDenom->isTotal(), "Le changement est bien indiqué comme total");

$lotFromChgmt = $chgtDenom->lots->get(0);
$t->is($lotFromChgmt->numero_archive, $lotFromDegust->numero_archive, "Le numéro d'archive n'a pas changé");
$t->is($lotFromChgmt->numero_dossier, $lotFromDegust->numero_dossier, "Le numéro de dossier n'a pas changé");
$t->is($lotFromChgmt->produit_hash, $chgtDenom->changement_produit_hash, "Le produit est bien le nouveau dans le lot");
$t->is($lotFromChgmt->volume, $chgtDenom->changement_volume, "Le volume est bien le nouveau dans le lot");
$t->is($lotFromChgmt->produit_libelle, $chgtDenom->changement_produit_libelle, "Le libellé du produit est bien le nouveau dans le lot");
$t->is($lotFromChgmt->volume, $volume, "Le volume de $volume hl est bien appliqué dans le nouveau dans le lot");
$t->is($lotFromChgmt->cepages->toArray(), array('CABERNET' => $volume), "Le 100% cepage est bien appliqué dans dans le lot");
$t->is($lotFromChgmt->document_ordre, '03', "Le numéro d'ordre est bien 03");
$t->is($lotFromChgmt->id_document_provenance, $degustation->_id, "Le lot généré provient bien de la dégustation ".$degustation->_id);

$t->ok($lotFromChgmt->getMouvement(Lot::STATUT_CHANGE_DEST), "statut du lot change dest");
$t->ok($lotFromChgmt->getMouvement(Lot::STATUT_NONAFFECTABLE), "statut du lot affectable (provenant d'une non conformité)");
$t->ok($lotFromChgmt->getMouvement(Lot::STATUT_CHANGEABLE), "statut du lot changeable");

$degustProvenance = $lotFromChgmt->getLotProvenance();
$t->is($degustProvenance->getDocument()->_id, $degustation->_id, 'la provenance du chgt est bien la dégustation '.$degustation->_id);
$t->ok($degustProvenance->getMouvement(Lot::STATUT_CHANGE_SRC), "statut du lot de la degust à 'revendiqué changé'");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Chgt de Denom Partiel");
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->changement_produit_hash = $autreLot->produit_hash;
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->validate();
$chgtDenom->save();

$t->is($chgtDenom->changement_origine_id_document, $degustation->_id, "Le changement a bien comme origine ".$degustation->_id);
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");

$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$t->is($chgtDenom->lots[0]->numero_archive, $lotFromDegust->numero_archive.'a', "numeros d'archive correctement postfixés : ".$lotFromDegust->numero_archive.'a');
$t->is($chgtDenom->lots[1]->numero_archive, $lotFromDegust->numero_archive.'b', "numeros d'archive correctement postfixés : ".$lotFromDegust->numero_archive.'b');
$t->is($chgtDenom->lots[0]->document_ordre, '03', "Le lot 1 a bien 03 comme numéro d'ordre");
$t->is($chgtDenom->lots[1]->document_ordre, '03', "Le lot 1 a bien 03 comme numéro d'ordre");
$t->is($chgtDenom->lots[0]->id_document_provenance, $degustation->_id, "Le lot 1 généré provient bien de la dégustation ".$degustation->_id);
$t->is($chgtDenom->lots[1]->id_document_provenance, $degustation->_id, "Le lot 2 généré provient bien de la dégustation ".$degustation->_id);

$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_NONCONFORME, "statut du lot orginel est bien non conforme");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE_SRC), "le lot originel a bien un mouvement au statut changé");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Total");
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = $volume;
$chgtDenom->validate();

$t->ok($chgtDenom->isTotal(), "Le changement qui a un volume identique est bien un changement total");
$t->is(count($chgtDenom->lots), 1, "Ce changement total ne génère plus que 1 lot");
$chgtDenom->generateMouvementsLots(1);
$t->is($chgtDenom->lots[0]->numero_archive, $lotFromDegust->numero_archive, "Un chgm total ne change pas le numero d'archive");
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is(count($chgtDenom->lots), 1, "Dans un declassement total, on a bien un seul lot");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "le mouvement du lot a un statut changé dest");
$t->ok(!$chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGEABLE), "le mouvement de lot n'est pas changeable");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_DECLASSE), "le mouvement de lot indique le déclassement");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Partiel");
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->validate();
$chgtDenom->save();

$t->ok(!$chgtDenom->isTotal(), "Le changement est bien partiel vu qu'il porte sur ".round($volume / 2, 2)." hl");

$t->is($chgtDenom->changement_origine_id_document, $degustation->_id, "changement_origine_id_document est bien ".$degustation->_id);
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");

$t->is(count($chgtDenom->lots), 2, "Pour un déclassement partiel il y a 2 lot généré");
$t->is($chgtDenom->lots[0]->numero_archive, $lotFromDegust->numero_archive.'a', "Pour le déclassement, le 1er lot est postfixé par a");
$t->is($chgtDenom->lots[1]->numero_archive, $lotFromDegust->numero_archive.'b', "Pour le déclassement, le 2d lot est postfixé par b");
$t->is($chgtDenom->lots[0]->document_ordre, '03', "Le numéro d'ordre du lot 1 est bien 03");
$t->is($chgtDenom->lots[1]->document_ordre, '03', "Le numéro d'ordre du lot 2 est bien 03");
$t->is($chgtDenom->lots[0]->volume, $volume - round($volume / 2, 2), "le volume du lot originel a bien changé également");
$t->is($chgtDenom->lots[1]->volume, round($volume / 2, 2), "volume du lot changé est bon");
$t->is($chgtDenom->lots[0]->id_document_provenance, $degustation->_id, "la provenance du lot 1 est bien ".$degustation->_id);
$t->is($chgtDenom->lots[1]->id_document_provenance, $degustation->_id, "la provenance du lot 2 est bien ".$degustation->_id);

$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "le mouvement du lot d'origine a bien toujours un statut changé dest");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_DECLASSE), "le mouvement du lot d'origine est bien indiqué comme déclassé");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_CHANGE_DEST), "le mouvement du lot partiel a un statut changé dest");
$t->is($chgtDenom->getLotOrigine()->id_document_affectation, $chgtDenom->_id, "le lot d'origine a bien l'affectation du changement ".$chgtDenom->_id);
$t->ok($chgtDenom->getLotOrigine()->isChange(), "statut des mvt du lot origine a bien isChange()");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE_SRC), "statut des mvt du lot origine a bien un mouvement changé src");
