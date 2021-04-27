<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}

function countMouvements($degustation) {
    $nb_mvmts = 0;

    foreach ($degustation->mouvements_lots as $ope) {
        foreach ($ope as $m) {
            $nb_mvmts++;
        }
    }

    return $nb_mvmts;
}

$t = new lime_test();

$campagne = (date('Y')-1)."";
$degust1_date_fr = '09/09/'.$campagne;
$degust2_date_fr = '11/11/'.$campagne;
$degust1_time_fr = '09:09';
$degust2_time_fr = '11:11';
$degust1_date = $campagne.'-09-01 '.$degust1_time_fr;
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$degust =  CompteTagsView::getInstance()->findOneCompteByTag('automatique', 'degustateur_porteur_de_memoire');

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

foreach(DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation1 = DegustationClient::getInstance()->find($k);
    $degustation1->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
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
$commissions = DegustationClient::getInstance()->getHistoryLieux();

$t->comment("Prépartion d'une DRev");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();
$produit1 = $drev->addProduit($produitconfig_hash1);
$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$drev->addLot();
$drev->lots[0]->numero_logement_operateur = '1';
$drev->lots[0]->volume = 1;
$drev->lots[1] = clone $drev->lots[0];
$drev->lots[1]->numero_logement_operateur = '2';
$drev->lots[1]->volume = 2;
$drev->validate();
$drev->validateOdg();
$drev->save();

$lotDrev = $drev->lots[0];

$t->comment('Modification du lot');

$lot = LotsClient::getInstance()->find($drev->identifiant, $drev->lots[0]->campagne, $drev->lots[0]->numero_dossier, $drev->lots[0]->numero_archive);

$t->is($lot->unique_id, $drev->lots[0]->unique_id, "Le lot récupéré à le même unique id");
$t->is($lot->id_document, $drev->_id, "Le lot récupéré provient de la drev");
$t->is($lot->document_ordre, "01", "Le lot récupéré a le numéro d'ordre 01");

LotsClient::getInstance()->modifyAndSave($lot);

$drevM01 = $drev->findMaster();
$lotDrevM01 = $drevM01->getLot($lot->unique_id);

$t->is(DRevClient::getInstance()->find($drev->_id)->_rev, $drev->_rev,"La DRev d'origine n'a pas été modifiée");
$t->is($drevM01->_id, $drev->_id.'-M01',"La modification du lot a créé une modificatrice");
$t->is($lotDrevM01->volume, $lot->volume, "Le lot de la modificatrice a le nouveau volume");
