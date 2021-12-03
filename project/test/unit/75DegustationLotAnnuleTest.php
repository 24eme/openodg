<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test(13);

$campagne = (date('Y')-1)."";
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date));
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des docs précédents
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
    if ($nbProduit == 2) {
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
$drev->validateOdg();
$drev->save();

$lots = [];
foreach ($drev->lots as $lotdrev) {
    $lots[$lotdrev->getUniqueId()] = $lotdrev;
}

$degustation = new Degustation();
$degustation->save();
$degustation->setLots($lots);

foreach ($degustation->getLots() as $lot) {
    $lot->setIsPreleve();
}
$degustation->save();

$lot = $degustation->lots[0];
$lot->volume = 0;
LotsClient::getInstance()->modifyAndSave($lot);

$degustation = DegustationClient::getInstance()->find($degustation->_id);

$lotAnnule = $degustation->lots[0];

$t->is($lotAnnule->volume, 0, "Le volume du lot a été modifié à 0 dans la dégustation");
$t->is($lotAnnule->statut, Lot::STATUT_ANNULE, "Le statut du lot est annulé");
$t->is($lotAnnule->preleve, null, "Le lot n'est pas marqué comme prélevé");
$t->ok($lotAnnule->isAnnule(), "Le lot est annulé");
$t->ok($lotAnnule->getMouvement(Lot::STATUT_AFFECTE_DEST), "Le statut du mouvement de lot est affecte dest");
$t->ok(!$lotAnnule->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Le statut du mouvement de lot n'est pas en attente de prélevement");
$t->ok($lotAnnule->getMouvement(Lot::STATUT_ANNULE), "Le statut du mouvement de lot est annulé");

$inLotPrelevable = false;
foreach($degustation->getLotsPrelevables() as $lot) {
    if($lot->unique_id == $lotAnnule->unique_id) {
        $inLotPrelevable = true;
    }
}
$t->ok(! $inLotPrelevable, "Le lot ne se trouve pas dans les lots prelevables");

$inLotDegustable = false;
foreach($degustation->getLotsDegustables() as $lot) {
    if($lot->unique_id == $lotAnnule->unique_id) {
        $inLotDegustable = true;
    }
}
$t->ok(! $inLotDegustable, "Le lot ne se trouve pas dans les lots dégustables");

$degustation->lots[1]->numero_table = 1;

$t->ok(!$lotAnnule->isAnonymisable(), "Le lot annulé n'est pas anonymisable");

$degustation->anonymize();

$t->is($degustation->lots[0]->unique_id, $lotAnnule->unique_id, "Le lot annulé n'a pas été supprimé lors de l'anonimisation");
$t->is($degustation->lots[0]->numero_table, null, "Aucun numéro de table pour le lot annulé");

$inLotTable = false;
foreach($degustation->getLotsSortByTables() as $lot) {
    if($lot->unique_id == $lotAnnule->unique_id) {
        $inLotTable = true;
    }
}
$t->ok(! $inLotTable, "Le lot ne se trouve pas dans les tables");



