<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test(23);

$annee = (date('Y')-1)."";
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$degust_date = $annee.'-09-01 13:45:00';
$degustid = "DEGUSTATION-".str_replace(['-', ' ', ':00', ':'], "", $degust_date);
$degust = DegustationClient::getInstance()->find($degustid);
if ($degust) {
    $degust->delete();
}

$degust = DegustationClient::getInstance()->createDoc($degust_date);
$lot1 = $degust->addLot(
    json_decode('{"date":"2020-09-01 12:45:00","id_document":"DEGUSTATION-202009011245","unique_id":"00001-00002",
        "id_document_provenance":"DREV-'.$viti->identifiant.'-2020","id_document_affectation":null,"campagne":"2020-2021",
        "numero_dossier":"00001","numero_archive":"00002","numero_logement_operateur":3,"position":"01000",
        "document_ordre":"02","volume":2,"declarant_identifiant":"'.$viti->identifiant.'","declarant_nom":"SARL ACTUALYS JEAN",
        "produit_hash":"\/declaration\/certifications\/IGP\/genres\/TRANQ\/appellations\/APL\/mentions\/DEFAUT\/lieux\/DEFAUT\/couleurs\/rouge\/cepages\/DEFAUT",
        "produit_libelle":"Alpilles Rouge","statut":"03_PRELEVE"}' )
);
$lot1->preleve = date('Y-m-d');
$degust->generateMouvementsLots();
$degust->save();
$t->ok($lot1->getMouvement(Lot::STATUT_PRELEVE), 'Le lot 1 est bien prelevé');
$t->ok($degust->lots->get(0)->getMouvement(Lot::STATUT_PRELEVE), 'Le lot 1 est bien prelevé');

$t->comment($degust->_id);
$t->is($degust->hasFreeLots(), true, 'Il y a des lots non assignés');

$t->comment('On attribue le lot à la première table');
$lot1->numero_table = 1;
$t->is(count($degust->getLotsTableOrFreeLots(1)), 1, 'La table 1 à un lot');
$t->is($degust->hasFreeLots(), false, "Il n'y a plus de lot non assigné");

$degust->generateMouvementsLots();
$t->is(count($degust->mouvements_lots->{$lot1->declarant_identifiant}), 4, 'La génération de mouvement a généré 4 lots');

$t->comment('On créé un leurre à la table 1');
$produitLeurreHash = $lot1->getProduitHash();
$produitLeurre = $degust->addLeurre($produitLeurreHash, null, date('Y'), 1);

$t->is($produitLeurre->leurre, true, 'Le produit est un leurre');
$t->is($produitLeurre->produit_hash, $produitLeurreHash, "Le hash produit est $produitLeurreHash");
$t->is($produitLeurre->getIntitulePartiel(), 'lot LEURRE de Alpilles Rouge ('.date('Y').')', 'Le libellé est correct');
$t->is($produitLeurre->millesime, date('Y'), "Le millesime est setté à l'année courante");
$t->is($degust->hasFreeLots(), false, "Le leurre est assigné");

$t->is(count($degust->getLotsTableOrFreeLots(1)), 2, "Il est assigné à la table 1");

$t->comment('On ajoute une table');
$t->is($degust->getLastNumeroTable(), 1, 'La table courante est la 1');
$degust->lots->add();
$degust->lots[2] = clone $lot1;
$degust->lots[2]->numero_logement_operateur = $lot1->numero_logement_operateur + 1;
$degust->lots[2]->numero_table = 2;
$t->is($degust->getLastNumeroTable(), 2, 'La dernière table est la 2');

$t->comment('On ajoute un leurre à la table 2');
$leurreTable2 = $degust->addLeurre($produitLeurreHash, 'Cepage leurre', date('Y'), 2);
$t->is($leurreTable2->leurre, true, 'C\'est un leurre');
$t->is($leurreTable2->getProduitHash(), $produitLeurreHash, 'Le hash est le même');
$t->is($leurreTable2->numero_table, 2, 'Le numéro de table est le 2');
$t->is($leurreTable2->details, 'Cepage leurre', 'Le cepage du leurre est "Cepage leurre"');
$t->is($leurreTable2->millesime, date('Y'), "Le millesime est setté à l'année courante");

$t->comment("On ignore le leurre de la table 2");
$t->is(count($degust->getLotsNonAttables()), 0, "Tous les lots sont attablés");
$lotLeurre = $degust->lots[3];
$degust->lots[3] = $degust->ignorerLot($lotLeurre);
$t->is(count($degust->getLotsNonAttables()), 1, "Un lot non attablé");
$t->ok($lotLeurre->isIgnored(), "Le leurre n'est plus dans une table et est ignoré");
$degust->save();

$t->comment('puis on la retire');
$degust->lots->remove(2);
$t->is($degust->getLastNumeroTable(), 1, 'La dernière table est la 1');
