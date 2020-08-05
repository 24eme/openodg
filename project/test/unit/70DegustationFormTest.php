<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "pass AOC");
    return;
}

$t = new lime_test(5);

$campagne = (date('Y')-1)."";
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}
$doc = acCouchdbManager::getClient()->find("DEGUSTATION-".date("Ymd")."-SYNDICAT-VIGNERONS-ARLES");
if ($doc) {
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
$commissions = DegustationConfiguration::getInstance()->getCommissions();

$t->comment("prépartion avec une DRev");
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();
$produit1 = $drev->addProduit($produitconfig_hash1);
$produit1->superficie_revendique = 200;
$produit1->recolte->superficie_total = 200;
$produit1->volume_revendique_issu_recolte = 80;
$lot = $drev->lots[0];
$lot->numero = '1';
$lot->volume = 10;
$drev->validate();
$drev->save();
$t->comment($drev->_id);
$res = MouvementLotView::getInstance()->getByPrelevablePreleveRegionDateIdentifiantDocumentId(1, 0, '', $drev->lots[0]->date, $drev->identifiant, $drev->_id);
$t->is(count($res->rows), 1, 'on a au moins un mouvement de lot prélevable');

$degustation = new Degustation();
$form = new DegustationCreationForm($degustation);
$values = array('date' => date('d/m/Y'), 'lieu' => $commissions[0]);
$form->bind($values);
$t->ok($form->isValid(), "Le formulaire de création est valide");
$form->save();
$t->ok($degustation->_id, "la création donne un id à la degustation");
$t->is($degustation->date, date('Y-m-d'), "La date de la degustation est la bonne");
$t->is($degustation->lieu, $commissions[0], "La commission de la degustation est la bonne");
