<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}

$t = new lime_test(158);

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

$periode = (date('Y')-1)."";

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
$lot->destination_type = null;
$lot->destination_date = ($periode+1).'-'.sprintf("%02d", 1).'-'.sprintf("%02d", 1);
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

$t->ok($drev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 1 est changeable");
$t->ok($drev->lots[1]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 2 aussi");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_CHANGEABLE), "Le lot 3 étant non affectable est donc CHANGEABLE");

$t->ok(!$drev->lots[0]->isChange(), "Le lot changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->lots[1]->isChange(), "un lot non changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->lots[2]->isChange(), "Le lot changeable dans la DREV n'est pas isChange()");
$t->ok(!$drev->hasLotsUtilises(), "La drev n'a pas de lots utilisés");


$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$t->is(count($lotsPrelevables), 2, "2 mouvements de lot prelevables ont été générés");
$lots = array();
$t->is(count(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null)), 3, "Les 3 lots sont cheageable");

foreach(ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null) as $key => $lot) {
    if($lot->affectable) {
        continue;
    }
    $lots[] = $lot;
}

$year = date('Y') - 1;
$campagne = $year.'-'.($year + 1);

$t->comment("Changement de dénom sur DREV");

$date = $year.'-10-10 10:10:10';
$chgtDenomFromDrev = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date, null);
$chgtDenomFromDrev->constructId();
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->save();
$t->comment($chgtDenomFromDrev->_id);

$idChgtDenomFromDrev = "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date);
$t->is($chgtDenomFromDrev->_id, $idChgtDenomFromDrev, "id du document");
$t->is($chgtDenomFromDrev->campagne, $campagne, "le chgt de denom a la bonne campagne à $campagne");
$t->is($chgtDenomFromDrev->periode, $year, "le chgt de denom a la bonne periode à $year");
$t->is($chgtDenomFromDrev->numero_archive, '00002', "le chgt de denom a bien un numero d'archive'");

$lotFromDrev = array_shift($lots);
$chgtDenomFromDrev->setLotOrigine($lotFromDrev);

$t->is($chgtDenom->changement_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, 'changement_numero_logement_operateur par defaut est le numero logement du lot origine');
$t->cmp_ok($chgtDenomFromDrev->origine_affectable, '===', $lotFromDrev->affectable, "L'affectation du lot origine reprends l'affectation du lot d'origine");
$t->cmp_ok($chgtDenomFromDrev->changement_affectable, '===', $lotFromDrev->affectable, "L'affectation du lot changé reprends l'affectation du lot d'origine comme le changement est total");

$chgtDenomFromDrev->changement_produit_hash = $drev->lots[1]->produit_hash;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->save();

$t->is($chgtDenomFromDrev->changement_origine_id_document, $drev->_id, "Le changement a bien comme document d'origine ".$drev->_id);
$t->is($chgtDenomFromDrev->changement_origine_lot_unique_id, $lotFromDrev->unique_id, "Le changement a bien l'unique id de son origine ".$lotFromDrev->unique_id);
$t->is($chgtDenomFromDrev->changement_volume, $lotFromDrev->volume, "Le changement a bien le volume de son origine");
$t->is($chgtDenomFromDrev->changement_millesime, $lotFromDrev->millesime, "Le changement a bien le millesime de son origine : ".$drev->lots[2]->millesime);
$t->isnt($chgtDenomFromDrev->changement_produit_hash, $lotFromDrev->produit_hash, "Le changement a bien un produit différent");
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Le lot ");
$t->is($chgtDenomFromDrev->changement_millesime, $lotFromDrev->millesime, "Le changement a bien le millesime de son origine : ".$drev->lots[2]->millesime);
$t->is($chgtDenomFromDrev->lots[0]->numero_archive, "00003", "Le lot du chgt a le même numéro d'archive que dans la drev");

$t->is(count($chgtDenomFromDrev->lots), 1, "Le changement étant total, on a un seul lot");
$t->is($chgtDenomFromDrev->lots[0]->id_document, $idChgtDenomFromDrev, "Le lot du chgt a bien id_document ".$idChgtDenomFromDrev);
$t->is($chgtDenomFromDrev->lots[0]->document_ordre, '02', "Le lot du chgt a bien comme document_ordre 02");
$t->is($chgtDenomFromDrev->lots[0]->id_document_provenance, $drev->_id, "Le lot du chgt a bien comme provenance la drev ".$drev->_id);
$t->is($chgtDenomFromDrev->lots[0]->affectable, $lotFromDrev->affectable, "Le lot du chgt a la même non affectation que dans la drev (réputé conforme)");

$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_NONAFFECTABLE), "Le changement a bien un mouvement non affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le changement a bien un mouvement changé dest");
$t->ok(!$chgtDenomFromDrev->hasLotsUtilises(), "La déclaration n'a pas de lots utilisés");

$drev = DrevClient::getInstance()->find($drev->_id);
$t->ok($drev->hasLotsUtilises(), "La drev a des lots utilisés");
$t->is($drev->lots[2]->id_document_affectation, $idChgtDenomFromDrev, "La DREV bien le changement enregistré comme affectation ".$idChgtDenomFromDrev);
$t->ok($drev->lots[2]->isChange(), "Le lot changé dans la DREV est bien isChange()");
$t->ok($drev->lots[2]->getMouvement(Lot::STATUT_CHANGE_SRC), "Le changement a bien généré dans la Drev un mouvement changé src");

$t->comment("On transforme le changement en déclassement");
$chgtDenomFromDrev->changement_produit_hash = null;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->save();
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Le type est déclassement");
$t->ok(!$chgtDenomFromDrev->lots[0]->affectable, "Le lot du déclassement n'est pas affectable");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le déclassement n'a pas de mouvement affectable");
$t->ok(!$chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le déclassement n'a pas de mouvement changé dest");


$t->comment("on remet et un produit et on rend le lot affectable");
$chgtDenomFromDrev->changement_produit_hash = $lotFromDrev->produit_hash;
$chgtDenomFromDrev->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenomFromDrev->validate();
$chgtDenomFromDrev->lots[0]->affectable = true;
$chgtDenomFromDrev->save();
$t->is($chgtDenomFromDrev->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Le type est redevenu un changement de denom");
$t->ok($chgtDenomFromDrev->lots[0]->affectable, "Le lot du changement est bien affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_AFFECTABLE), "Le changement a bien un mouvement affectable");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGE_DEST), "Le changement a bien un mouvement changé dest");
$t->ok($chgtDenomFromDrev->lots[0]->getMouvement(Lot::STATUT_CHANGEABLE), "Le changement a bien un mouvement changeable");

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);
$t->is(count($lots), 3, "3 lots disponible au changement de denomination (celui provenant du chgement de denom)");

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

$lots = ChgtDenomClient::getInstance()->getLotsChangeable($viti->identifiant, null);
$t->is(count($lots), 3, "3 mouvements disponibles au changement de dénomination");

$lotFromDegust = current($lots);
$t->is($lotFromDegust->statut, Lot::STATUT_NONCONFORME, 'le lot sélectionné de la dégust est bien NON CONFORME');
$t->is($lotFromDegust->id_document, $degustation->_id, 'le lot sélectionné provient bien de la dégustation '.$degustation->_id);
$volume = $lot->volume;
$autreLot = next($lots);

$date = $year.'-11-11 11:11:11';
$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date, null);
$chgtDenom->save();
$t->comment($chgtDenom->_id);
$t->is($chgtDenom->_id, "CHGTDENOM-".$viti->identifiant."-".preg_replace("/[-\ :]+/", "", $date), "id du document");
$t->is($chgtDenom->campagne, $campagne, "le chgt de denom a la bonne campagne à $campagne");
$t->is($chgtDenom->periode, $year, "le chgt de denom a la bonne periode à $year");

$t->comment("Création d'un Changement de Denom Total");

$chgtDenom->setLotOrigine($lotFromDegust);

$t->is($chgtDenom->changement_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, 'changement_numero_logement_operateur par defaut est le numero logement du lot origine');
$t->cmp_ok($chgtDenomFromDrev->origine_affectable, '===', $lotFromDegust->affectable, "L'affectation du lot origine reprends l'affectation du lot d'origine");
$t->cmp_ok($chgtDenomFromDrev->changement_affectable, '===', $lotFromDegust->affectable, "L'affectation du lot changé reprends l'affectation du lot d'origine comme le changement est total");

$chgtDenom->changement_cepages = array('CABERNET' => $volume);
$chgtDenom->changement_produit_hash = $autreLot->produit_hash;
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->changement_numero_logement_operateur = "2(ex1)";
$chgtDenom->validate();
$chgtDenom->save();

$t->is(count($chgtDenom->lots), 1, "1 seul lot généré");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");
$t->is($chgtDenom->changement_volume, $volume, "Le changement est bien sur $volume hl");
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit est bien ".$autreLot->produit_libelle);
$t->is($chgtDenom->origine_numero_logement_operateur, $lotFromDegust->numero_logement_operateur, "Le logement origine est celui du lot origine");
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
$t->is($lotFromChgmt->numero_logement_operateur, $chgtDenom->changement_numero_logement_operateur, "Le lot changé a changé de logement");

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
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->changement_numero_logement_operateur = "2(ex1)";
$chgtDenom->changement_affectable = true;
$chgtDenom->validate();
$chgtDenom->save();

$t->is($chgtDenom->changement_origine_id_document, $degustation->_id, "Le changement a bien comme origine ".$degustation->_id);
$t->is($chgtDenom->changement_produit_libelle, $autreLot->produit_libelle, "Libellé produit");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT, "Type de changement à CHANGEMENT");

$t->is(count($chgtDenom->lots), 2, "2 lot généré");
$t->is($chgtDenom->lots[0]->numero_archive, $lotFromDegust->numero_archive, "numero d'archive correctement du lot 1 conservé : ".$lotFromDegust->numero_archive);
$t->is($chgtDenom->lots[1]->numero_archive, '00004', "numeros d'archive du lot 2 changé pour le suivant");
$t->is($chgtDenom->lots[0]->document_ordre, '03', "Le lot 1 a bien 03 comme numéro d'ordre");
$t->is($chgtDenom->lots[1]->document_ordre, '01', "Le lot 2 a bien 01 comme numéro d'ordre");
$t->is($chgtDenom->lots[0]->id_document_provenance, $degustation->_id, "Le lot 1 généré provient bien de la dégustation ".$degustation->_id);
$t->is($chgtDenom->lots[1]->id_document_provenance, null, "Le lot 2 perd sa provenance de ".$degustation->_id);
$t->is($chgtDenom->lots[0]->numero_logement_operateur, $chgtDenom->origine_numero_logement_operateur, "Le numero logement opérateur n'a pas changé pour le lot origine");
$t->is($chgtDenom->lots[1]->numero_logement_operateur, $chgtDenom->changement_numero_logement_operateur, "Le logement lot 2 a changé");
$t->is($chgtDenom->lots[0]->affectable, $chgtDenom->origine_affectable, "L'affectation du lot origine n'a pas changé");
$t->is($chgtDenom->lots[1]->affectable, $chgtDenom->changement_affectable, "L'affectation lot 2 a changé");

$t->is($chgtDenom->lots->get(0)->statut, Lot::STATUT_NONCONFORME, "statut du lot orginel est bien non conforme");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_NONAFFECTABLE), "Mouvement lot restant affectable");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_AFFECTABLE), "Mouvement lot changé affectable ");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "Mouvement lot restant change dest");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_CHANGE_DEST), "Mouvement lot changé change dest");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE_SRC), "le lot originel a bien un mouvement au statut changé");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Total");
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = $volume;
$chgtDenom->changement_specificite = "HVE";
$chgtDenom->changement_affectable = true;
$chgtDenom->validate();

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
$t->is($chgtDenom->lots->get(0)->specificite, "HVE DECLASSÉ en VSIG", "le lot a une spécificité HVE DECLASSE");
$t->is($chgtDenom->lots->get(0)->affectable, ! $chgtDenom->changement_affectable, "Le lot n'est pas affectable");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();

$t->comment("Création d'un Declassement Partiel");
$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = round($volume / 3, 2);
$chgtDenom->origine_numero_logement_operateur = "(ex1)";
$chgtDenom->validate();
$chgtDenom->save();

$t->ok(!$chgtDenom->isTotal(), "Le changement est bien partiel vu qu'il porte sur ".round($volume / 3, 2)." hl");

$t->is($chgtDenom->changement_origine_id_document, $degustation->_id, "changement_origine_id_document est bien ".$degustation->_id);
$t->is($chgtDenom->changement_produit_hash, null, "Pas de produit");
$t->is($chgtDenom->changement_produit_libelle, null, "Pas de produit libelle");
$t->is($chgtDenom->changement_type, ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT, "Type de changement à DECLASSEMENT");
$t->is($chgtDenom->origine_produit_hash, $lotFromDegust->produit_hash, "l'origine du produit est bien conservée");

$t->is(count($chgtDenom->lots), 2, "Pour un déclassement partiel il y a 2 lot généré");
$t->is($chgtDenom->lots[0]->numero_archive, '00001', "Pour le déclassement, le 1er lot n'a pas changé de numéro d'archive");
$t->is($chgtDenom->lots[1]->numero_archive, '00005', "Pour le déclassement, le 2d lot a un nouveau numéro d'archive");
$t->is($chgtDenom->lots[0]->document_ordre, '03', "Le numéro d'ordre du lot 1 est bien 03");
$t->is($chgtDenom->lots[1]->document_ordre, '01', "Le numéro d'ordre du lot 2 est bien 01");
$t->is($chgtDenom->lots[0]->volume, $volume - round($volume / 3, 2), "le volume du lot originel a bien changé également");
$t->is($chgtDenom->lots[1]->volume, round($volume/ 3, 2), "volume du lot changé est bon");
$t->is($chgtDenom->lots[0]->id_document_provenance, $degustation->_id, "la provenance du lot 1 est bien ".$degustation->_id);
$t->is($chgtDenom->lots[1]->id_document_provenance, null, "le lot 2 perd sa provenance");
$t->is($chgtDenom->lots[0]->numero_logement_operateur, $chgtDenom->origine_numero_logement_operateur, "Le numero logement operateur d'origine a changé");
$t->is($chgtDenom->lots[1]->numero_logement_operateur, $chgtDenom->getLotOrigine()->numero_logement_operateur, "L'autre partie du chgt denom n'a pas bougé de logement");
$t->is($chgtDenom->lots[0]->produit_hash, $lotFromDegust->produit_hash, "Le 1er lot a bien conservé son produit");
$t->is($chgtDenom->lots[1]->produit_hash, null, "Pour le déclassement partiel, le 2d lot n'a pas de produit");


$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGE_DEST), "le mouvement du lot d'origine a un statut changé dest");
$t->ok($chgtDenom->lots->get(0)->getMouvement(Lot::STATUT_CHANGEABLE), "le mouvement du lot d'origine reste changeable");
$t->ok($chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_DECLASSE), "le mouvement du lot partiel a un statut déclassé");
$t->ok(!$chgtDenom->lots->get(1)->getMouvement(Lot::STATUT_CHANGEABLE), "le mouvement du lot déclassé n'est plus changeable");
$t->is($chgtDenom->getLotOrigine()->id_document_affectation, $chgtDenom->_id, "le lot d'origine a bien l'affectation du changement ".$chgtDenom->_id);
$t->ok($chgtDenom->getLotOrigine()->isChange(), "statut des mvt du lot origine a bien isChange()");
$t->ok($chgtDenom->getLotOrigine()->getMouvement(Lot::STATUT_CHANGE_SRC), "statut des mvt du lot origine a bien un mouvement changé src");

$t->comment("Dévalidation d'un ChgtDenom");
$t->ok(!$chgtDenom->isApprouve(), "Le changement n'est pas approuvé.");
$chgtDenom->validateOdg();
$t->ok($chgtDenom->isApprouve(), "Le changement est bien validé et approuvé : ".$chgtDenom->validation_odg);
$chgtDenom->devalidate();
$chgtDenom->save();
$t->ok(!$chgtDenom->isValidee(), "Le changement est maintenant dévalidé.");

$t->comment("Édition d'un logement");
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), true, "Le lot d'origine d'un déclassement a un logement editable");
$t->is($chgtDenom->lots->get(1)->isLogementEditable(), false, "Le lot changé d'un déclassement n'a pas de logement editable");
$chgtDenom->validate();
$t->is($chgtDenom->lots->get(1)->isLogementEditable(), false, "Le lot changé d'un déclassement n'a plus de logement editable après la validation");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->devalidate();

$chgtDenom->changement_volume = $lotFromDegust->volume;
$chgtDenom->changement_specificite = "HVE";
$chgtDenom->generateLots();

$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un déclassement total n'a pas de logement editable");
$chgtDenom->validate();
$chgtDenom->save();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un déclassement total après validation n'a pas de logement editable");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->devalidate();

$chgtDenom->setLotOrigine($lotFromDegust);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
$chgtDenom->generateLots();

$t->is($chgtDenom->lots->get(0)->isLogementEditable(), true, "Le lot d'origine d'un chgt denom total a un logement editable");
$chgtDenom->validate();
$chgtDenom->save();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un chgt denom total après validation n'a pas de logement editable");

$chgtDenom->clearMouvementsLots();
$chgtDenom->clearLots();
$chgtDenom->devalidate();

$chgtDenom->changement_volume = round($volume / 2, 2);
$chgtDenom->generateLots();

$t->is($chgtDenom->lots->get(0)->isLogementEditable(), true, "Le lot d'origine d'un chgt denom partiel a un logement editable");
$chgtDenom->validate();
$chgtDenom->save();
$t->is($chgtDenom->lots->get(0)->isLogementEditable(), false, "Le lot d'origine d'un chgt denom total après validation n'a pas de logement editable");


$t->comment("ajout d'un lot sans origine");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->addLot();
$lot = $drev->lots[0] ;
$lot->getUniqueId();

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, null, $papier);
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
    'produit_hash' => $autreLot->produit_hash,
    'destination_type' => DRevClient::LOT_DESTINATION_VRAC_EXPORT,
    'specificite' => 'HVE'
);
$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire de création de lot est valide");
$form->save();
$chgtDenom = ChgtDenomClient::getInstance()->find($chgtdenom_sanslot_id);
$chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
$chgtDenom->changement_volume = 12;
$chgtDenom->validate($periode.'-05-01');
$chgtDenom->validateOdg($periode.'-05-01');
$chgtDenom->save();

$t->ok($chgtDenom->isValidee(), "Le changement est validé");
$t->is($chgtDenom->numero_archive, '00004', "le changement de dénomination a bien un numero d'archive");
$t->is($chgtDenom->lots[0]->numero_archive, '00005', "Le lot déclassé a le bon numéro d'archive");
$t->is($chgtDenom->lots[0]->numero_dossier, '00004', "Le lot déclassé a le bon numéro de dossier");
$t->is($chgtDenom->lots[0]->unique_id, $periode.'-'.($periode + 1 ).'-00004-00005', "Le lot déclassé a le bon unique_id");