<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (strpos($application, 'igp') === false) {
    $t = new lime_test(1);
    $t->ok(true, "pass AOC");
    return;
}

$t = new lime_test();

$annee = (date('Y')-1)."";
if ($annee < 8){
    $annee = $annee - 1;
}
$campagne = $annee.'-'.($annee + 1);
$date = $annee.'-10-10';
$drev_date = $annee.'-10-01';
$degust_date = $date.' 12:45';
$degust_date_fr = '10/10/'.$annee;
$degust_time_fr = '12:45';

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

$docid = "DEGUSTATION-".preg_replace("/[:\ -]+/", "", $degust_date);

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
$lieu = "Lieu test — adresse lieu test";

$t->comment("préparation avec une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $annee);
$chais = $drev->add('chais');
$chais->adresse = 'adresse Chai Drev';
$chais->code_postal = 'cp Chai Drev';
$chais->commune = 'commune Chai Drev';
$addrCompleteLgtDrev = $drev->constructAdresseLogement();

$drev->save();

$iddrev = $drev->_id;
$produit1 = $drev->addProduit($produitconfig_hash1);
$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$drev->addLot();
$drev->lots[0]->numero_logement_operateur = '1';
$drev->lots[0]->produit_hash = $produitconfig_hash1;
$drev->lots[0]->volume = 1;
$drev->validate($drev_date);
$drev->validateOdg($drev_date);
$drev->add('date_commission', $drev->getDateValidation('Y-m-d'));
$drev->save();

$drevM01 = $drev->generateModificative();
$drevM01->addLot();
$drevM01->lots[0]->numero_logement_operateur = '1';
$drevM01->lots[0]->produit_hash = $produitconfig_hash1;
$drevM01->lots[0]->volume = 2;
$drevM01->validate($drev_date);
$drevM01->validateOdg($drev_date);
$drevM01->add('date_commission', $drev->getDateValidation('Y-m-d'));
$drevM01->save();

$degustation = DegustationClient::getInstance()->createDoc($degust_date);
$degustation->setLots([$drev->lots[0]]);
$degustation->lots[0]->setIsPreleve($degust_date);
$degustation->save();

$degustation->lots[0]->volume = 100;
LotsClient::getInstance()->modifyAndSave($degustation->lots[0]);

$drev = DRevClient::getInstance()->find($drev->_id);
$drevM01 = DRevClient::getInstance()->find($drevM01->_id);
$drevM02 = DRevClient::getInstance()->find(str_replace('-M01', '-M02', $drevM01->_id));
$degustation = DegustationClient::getInstance()->find($degustation->_id);

$t->is($drev->lots[0]->id_document_affectation, null, "Le lot de la M0 n'est pas affecté");
$t->is($drev->lots[0]->id_document_provenance, null, "Le lot de la M0 n'a pas de provenance");
$t->is($drevM01->lots[0]->id_document_affectation, null, "Le lot de la M01 n'est pas affecté");
$t->is($drevM01->lots[0]->id_document_provenance, null, "Le lot de la M01 n'a pas de provenance");
$t->is($drevM02->lots[0]->id_document_affectation, $degustation->_id, "Le lot de la M02 est affecté à la dégustation");
$t->is($drevM02->lots[0]->id_document_provenance, null, "Le lot de la M02 n'a pas de provenance");
$t->is($degustation->lots[0]->id_document_affectation, null, "Le lot de la degust n'est pas affecté");
$t->is($degustation->lots[0]->id_document_provenance, $drevM02->_id, "Le lot de la dégust provient de la M02");
