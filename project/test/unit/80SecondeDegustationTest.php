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

$t = new lime_test(22);

$campagne = (date('Y')-1)."";
$degust_date = $campagne.'-09-01 12:45';
$degust_date_fr = '01/09/'.$campagne;
$degust_time_fr = '12:45';
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
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete(false);
}

foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $chgtdenom = ChgtDenomClient::getInstance()->find($k);
    $chgtdenom->delete(false);
}

$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";

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
$drev->addLot();
$drev->lots[0]->numero_logement_operateur = '1';
$drev->lots[0]->volume = 1;
$drev->lots[1] = clone $drev->lots[0];
$drev->lots[1]->numero_logement_operateur = '2';
$drev->lots[1]->volume = 2;
$drev->validate();
$drev->validateOdg();
$drev->save();
$t->comment($drev->_id);

//Début des tests
$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$lotsEnManquement = DegustationClient::getInstance()->getManquements();

$t->is(count($lotsPrelevables), 2, "Il y a 2 mouvements prélevables");
$t->is(count($lotsEnManquement), 0, "Il y a 0 mouvement en manquement");

$t->comment("Création d'une degustation");
$degustation = new Degustation();
$form = new DegustationCreationForm($degustation);
$values = array('date' => $degust_date_fr, 'time' => $degust_time_fr, 'lieu' => $commissions[0]);
$form->bind($values);
$degustation = $form->save();

$t->comment("Sélection des lots");
$form = new DegustationPrelevementLotsForm($degustation);
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation->_rev,
);
$valuesRev['lots'][$drev->lots[0]->getUnicityKey()]['preleve'] = 1;
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;
$form->bind($valuesRev);
$form->save();

$t->comment("Conformité des lots");
$degustation->lots[0]->statut = Lot::STATUT_CONFORME;
$degustation->lots[1]->statut = Lot::STATUT_NONCONFORME;
$degustation->save();

$lot = $degustation->lots[1];
$t->ok($lot->getMouvement(Lot::STATUT_NONCONFORME), "Le lot est non conforme");
$t->ok($lot->getMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE), 'Le lot à un manquement en attente');
$t->is(MouvementLotView::getInstance()->getNombrePassage($lot), 1, "C'est le premier passage du lot");

$lot->redegustation();
$degustation->save();

$t->ok($lot->getMouvement(Lot::STATUT_NONCONFORME), "Le lot est toujours non conforme");
$t->ok(! $lot->getMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE), "Le lot n'est plus en manquement en attente");
$t->ok($lot->affectable, "Le lot est affectable");
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTABLE), "Le lot a un mouvement affectable");
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTE_SRC), "Le lot a un mouvement affecte source");

$lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
$lotsEnManquement = DegustationClient::getInstance()->getManquements();

$t->is(count($lotsPrelevables), 1, "Il y a 1 mouvement prélevable");
$t->is(count($lotsEnManquement), 0, "Il y a 0 mouvement en manquement");

$t->comment('Deuxième degustation');
$degustation2 = new Degustation();
$form = new DegustationCreationForm($degustation2);
$values = array('date' => $degust_date_fr, 'time' => $degust_time_fr, 'lieu' => $commissions[1]);
$form->bind($values);
$degustation2 = $form->save();

$t->comment("Sélection des lots");
$form = new DegustationPrelevementLotsForm($degustation2);
$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $degustation2->_rev,
);
$valuesRev['lots'][$drev->lots[1]->getUnicityKey()]['preleve'] = 1;
$form->bind($valuesRev);
$form->save();

$t->is(count($degustation2->lots), 1, "La nouvelle degust à 1 lot");
$lot2 = $degustation2->lots[0];

$t->is(count($lot2->getMouvements()), 2, "Le lot a deux mouvements");
$t->ok($lot2->getMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT), "Le lot a le mouvement ".Lot::STATUT_ATTENTE_PRELEVEMENT);
$t->ok($lot2->getMouvement(Lot::STATUT_AFFECTE_DEST), "Le lot a le mouvement ".Lot::STATUT_AFFECTE_DEST);
$t->is(MouvementLotView::getInstance()->getNombrePassage($lot), 2, "Le lot de la première degust sait qu'il a deux passages");
$t->is(MouvementLotView::getInstance()->getNombrePassage($lot2), 2, "Le lot de la deuxième dégust sait que c'est le deuxième passage");
$t->is($lot2->affectable, false, "Le lot n'est plus affectable");

$t->is($lot2->id_document, $degustation2->_id, "L'id du doc du mouvement est la même degustation");
$t->is($lot2->numero_archive, $lot->numero_archive, "Le numero archive n'a pas changé");
$t->is($lot2->numero_dossier, $lot->numero_dossier, "Le numero dossier n'a pas changé");


