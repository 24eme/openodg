<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "pass AOC");
    return;
}

$t = new lime_test(63);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}

foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
    HabilitationClient::getInstance()->deleteDoc(HabilitationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    if(!$produit1) {
        $produit1 = $produit;
        continue;
    } elseif(!$produit2) {
        $produit2 = $produit;
        continue;
    }

    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').".csv";
file_put_contents($csvTmpFile, str_replace(array("%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));
$t->comment("utilise le fichier test/data/dr_douane.csv");
$t->comment("%libelle_produit_1% = ".$produit1->getLibelleComplet());
$t->comment("%libelle_produit_2% = ".$produit2->getLibelleComplet());

$campagne = (date('Y')-1)."";

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->save();
$t->comment($drev->_id);
$t->comment("Récupération des données à partir de la DR");

$dr = DRClient::getInstance()->createDoc($viti->identifiant, $campagne);
$dr->setLibelle("DR $campagne issue de Prodouane (Papier)");
$dr->setDateDepot("$campagne-12-15");
$dr->save();
$dr->storeFichier($csvTmpFile);
$dr->save();

$drev->importFromDocumentDouanier();
$drev->save();

$t->is(count($drev->getProduits()), 2 + (!DRevConfiguration::getInstance()->hasDenominationAuto()) * 2, "La DRev a repris le bon nombre de produits du csv de la DR");

$i = 0;
$produits2Delete = array();
foreach($drev->getProduits() as $produit) {
    $i++;
    if($i > 2) {
        $produits2Delete[$produit->getHash()] = $produit->getHash();
    }
}

foreach($produits2Delete as $hash) {
    $drev->remove($hash);
}

$produits = $drev->getProduits();


$produit1 = current($produits);
$produit_hash1 = $produit1->getHash();

next($produits);
$produit2 = current($produits);
$produit_hash2 = $produit2->getHash();

$drev->save();

$t->is($produit1->recolte->superficie_total, 2.4786 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "La superficie total de la DR pour le produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_sur_place, 105.18 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Le volume sur place pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->usages_industriels_total, 3.03 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Les usages industriels la DR pour ce produit ".$produit1->getLibelleComplet()." sont OK");
$t->is($produit1->recolte->recolte_nette, 104.1 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "La récolte nette de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");
$t->is($produit1->recolte->volume_total, 105.18 * (1 + (DRevConfiguration::getInstance()->hasDenominationAuto())), "Le volume total de la DR pour ce produit ".$produit1->getLibelleComplet()." est OK");

$t->comment('Formulaire de revendication des superficies');

if($drev->storeEtape(DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE)) {
    $drev->save();
}

$form = new DRevSuperficieForm($drev);

$defaults = $form->getDefaults();

$t->is($form['produits'][$produit_hash1]['recolte']['superficie_total']->getValue(), $produit1->recolte->superficie_total, "La superficie totale de la DR est initialisé dans le form");
$t->is($form['produits'][$produit_hash1]['superficie_revendique']->getValue(), $produit1->superficie_revendique, "La superficie revendique est initialisé dans le form");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['volume_total']), "Le volume total de la DR n'est pas proposé dans le formulaire");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['recolte_nette']), "Le volume de récolte nette de la DR n'est pas proposé dans le formulaire");
$t->ok(!isset($form['produits'][$produit_hash1]['recolte']['volume_sur_place']), "Le volume sur place de la DR n'est pas proposé dans le formulaire");

$valuesRev = array(
    'produits' => $form['produits']->getValue(),
    '_revision' => $drev->_rev,
);

$valuesRev['produits'][$produit_hash1]['superficie_revendique'] = 10;
$valuesRev['produits'][$produit_hash1]['recolte']['superficie_total'] = 10;
$valuesRev['produits'][$produit_hash2]['recolte']['superficie_total'] = 300;

$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is($produit1->recolte->superficie_total, $valuesRev['produits'][$produit_hash1]['recolte']['superficie_total'], "La superficie total de la DR est enregistré");
$t->is($produit1->superficie_revendique, $valuesRev['produits'][$produit_hash1]['superficie_revendique'], "La superficie revendique est enregistré");

$t->comment("Étape lots");

if($drev->storeEtape(DrevEtapes::ETAPE_LOTS)) {
    $drev->save();
}

$form = new DRevLotsForm($drev);
$defaults = $form->getDefaults();

$t->is(count($form['lots']), 2, "autant de lots que de colonnes dans le DR");
$t->is($form['lots']['0']['produit_hash']->getValue(), $produit1->getParent()->getHash(), 'lot 1 : un produit est déjà sélectionné');
$t->is($form['lots']['0']['millesime']->getValue(), $campagne, 'lot 1 : le millesime est prérempli');

$valuesRev = array(
    'lots' => $form['lots']->getValue(),
    '_revision' => $drev->_rev,
);
$valuesRev['lots']['0']['numero'] = "Cuve A";
$valuesRev['lots']['0']['volume'] = 8.2;
$valuesRev['lots']['0']['destination_type'] = DRevClient::LOT_DESTINATION_VRAC_FRANCE;
$valuesRev['lots']['0']['destination_date'] = '30/11/'.$campagne;

$form->bind($valuesRev);
$t->ok($form->isValid(), "Le formulaire est valide");
$form->save();

$t->is(count($drev->lots), 2, "Les deux lots sont conservés dans la DRev");
$t->is($drev->lots[0]->numero, $valuesRev['lots']['0']['numero'], "Le numéro de cuve du lot 1 est bien enregistré");
$t->is($drev->lots[0]->volume, $valuesRev['lots']['0']['volume'], "Le volume du lot 1 est bien enregistré");
$t->is($drev->lots[0]->destination_type, $valuesRev['lots']['0']['destination_type'], "Le type de destination lot 1 est bien enregistré");
$t->is($drev->lots[0]->destination_date, join('-', array_reverse(explode('/', $valuesRev['lots']['0']['destination_date']))), "La date de destination du lot 1 est bien enregistré");
$t->is($drev->lots[0]->produit_hash, $valuesRev['lots']['0']['produit_hash'], "La hash du produit du lot 1 est bien enregistré");
$t->is($drev->lots[0]->produit_libelle, $produit1->getLibelle(), "Le libellé du produit du lot 1 est bien enregistré");
$t->is($drev->lots[0]->millesime, $valuesRev['lots']['0']['millesime'], "Le millesime du lot 1 est bien enregistré");

if($drev->storeEtape(DrevEtapes::ETAPE_VALIDATION)) {
    $drev->save();
}

$t->comment("Étape validation");

$synthese_revendication = $drev->summerizeProduitsLotsByCouleur();
$couleur = $produit1->getConfig()->getCouleur()->getLibelleComplet();
$t->is($synthese_revendication[$couleur]['superficie_totale'], 12.4786, 'la superficite totale pour le produit du lot est bonne dans la synthèse');
$t->is($synthese_revendication[$couleur]['volume_max'], 208.2, 'le volume max pour le produit du lot est bonne dans la synthèse');
$t->is($synthese_revendication[$couleur]['volume_restant'], 200, 'le volume restant pour le produit du lot est bonne dans la synthèse');

$drev->validate();
$drev->save();

$t->comment("DRev validée");

$t->is(count($drev->lots), 1, "La DRev validée ne contient plus que le lot saisi");
$t->is(count($drev->mouvements_lots->{$drev->identifiant}), 1, "La DRev validée contient le mouvement correspondant au lot saisi");
foreach ($drev->mouvements_lots->{$drev->identifiant} as $k => $mvt) {
    break;
}
$t->is($mvt->prelevable, 1, "Le mouvement est prelevable");
$t->is($mvt->preleve, 0, "Le mouvement n'a pas été prelevé");
$t->is($mvt->produit_hash, $drev->lots[0]->produit_hash, 'Le mouvement a la bonne hash');
$t->is($mvt->produit_libelle, $drev->lots[0]->produit_libelle, 'Le mouvement a le bon libellé');
$t->is($mvt->produit_couleur, $drev->lots[0]->getCouleurLibelle(), 'Le mouvement a le bon libellé de couleur');
$t->is($mvt->volume, $drev->lots[0]->volume, 'Le mouvement a le bon volume');
$t->is($mvt->date, $drev->lots[0]->date, 'Le mouvement a la bonne date');
$t->is($mvt->millesime, $drev->lots[0]->millesime, 'Le mouvement a le bon millesime');
$t->is($mvt->region, '', "Le mouvement a la bonne région");
$t->is($mvt->numero, $drev->lots[0]->numero, 'Le mouvement a le bon numero');
$t->is($mvt->version, 0, "Le mouvement a la version 0");
$t->is($mvt->origine_hash, $drev->lots[0]->getHash(), 'Le mouvement a bien comme origine le premier lot');
$t->is($mvt->origine_type, 'drev', 'le mouvement a bien comme origine une drev');
$t->is($mvt->origine_mouvement, $mvt->getHash(), 'le mouvement a bien comme origine de mouvement lui même');
$t->is($mvt->origine_document_id, $drev->_id, 'Le mouvement a la bonne origine de document');
$t->is($mvt->identifiant, $drev->identifiant, 'Le mouvement a le bon identifiant');
$t->is($mvt->declarant_libelle, $drev->declarant->raison_sociale, 'Le mouvement a la bonne raison sociale');
$t->is($mvt->destination_type, $drev->lots[0]->destination_type, 'Le mouvement a le bon type de destination');
$t->is($mvt->destination_date, $drev->lots[0]->destination_date, 'Le mouvement a la bonne date de destination');
$t->is($mvt->details, '', "le mouvement n'a pas de détail car il n'a pas de répartition de cépage");
$t->is($mvt->campagne, $drev->campagne, "le mouvement a la bonne campagne");

$res = MouvementLotView::getInstance()->getByPrelevablePreleveRegionDateIdentifiantDocumentId(1, 0, '', $drev->lots[0]->date, $drev->identifiant, $drev->_id);
$t->is(count($res->rows), 1, 'on retrouve le mouvement dans la vue MouvementLot');
$t->is($res->rows[0]->id, $drev->_id, 'le mouvement correspond bien à notre drev');
$drevres = DRevClient::getInstance()->find($res->rows[0]->value->origine_document_id);
$t->ok($drevres, 'le mouvement pointe bien sur une Drev existante');
$t->ok( ($drevres instanceof InterfaceMouvementLotsDocument) , 'le mouvement pointe bien vers un document de type InterfaceMouvementLotsDocument');
$lotres = $drevres->get($res->rows[0]->value->origine_hash);
$t->ok($lotres, 'le mouvement correspond bien à un lot');
$mvtres = $drevres->get($res->rows[0]->value->origine_mouvement);
$t->ok($mvtres, 'le mouvement a bien un origine mouvement existant');
$t->ok( ($mvtres instanceof MouvementLots) , 'le mouvement correspond bien à un lot de type MouvementLots');
$t->ok( ($mvtres instanceof InterfaceMouvementLots) , 'le mouvement correspond bien à un lot de type InterfaceMouvementLots');
$t->is($mvtres->origine_mouvement, $res->rows[0]->value->origine_mouvement, "le mouvement l'origine mouvement correspond bien au mouvement");

$t->comment("Gestion du prélèvement");
$mvtres->prelever();
$drevres->save();
$t->ok($mvtres->preleve, "Le mouvement prelevé est bien indiqué comme tel");
$res = MouvementLotView::getInstance()->getByPrelevablePreleveRegionDateIdentifiantDocumentId(1, 0, '', $drev->lots[0]->date, $drev->identifiant, $drev->_id);
$t->is(count($res->rows), 0, 'on retrouve plus le mouvement prelevé dans la vue MouvementLot');
