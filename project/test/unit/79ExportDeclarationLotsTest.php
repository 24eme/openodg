<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$periode = (date('Y')-1)."";
$campagne = $periode."-".($periode + 1);
$date = date('Y-m-d');
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$centilisations = ConditionnementConfiguration::getInstance()->getContenances();
$centilisations_bib_key = key($centilisations["bib"]);

//Suppression des Conditioinnement (et drev et transaction) précédents
foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $cd = ChgtDenomClient::getInstance()->find($k);
    $cd->delete(false);
}
foreach(DrevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DrevClient::getInstance()->find($k);
    $drev->delete(false);
}
foreach(DegustationClient::getInstance()->getHistory(100, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DegustationClient::getInstance()->deleteDoc(DegustationClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
}
foreach(ArchivageAllView::getInstance()->getDocsByTypeAndCampagne('Revendication', $campagne, 0, 99999, "%05d") as $r) {
    $doc = acCouchdbManager::getClient()->find($r->id);
    $doc->delete();
}

$config = ConfigurationClient::getCurrent();
$produitconfig1 = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    $produitconfig1 = $produitconfig->getCepage();
    continue;
}

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();
$drev->addLot();
$drev->lots[0]->produit_hash = $produitconfig1->getHash();
$drev->lots[0]->numero_logement_operateur = 'A1';
$drev->lots[0]->volume = 100;
$drev->validate();
$drev->validateOdg();
$drev->add('envoi_oi', $drev->validation);
$drev->add('date_degustation_voulue', $drev->validation);
$drev->save();

$lot = $drev->lots[0];

$t->is(ExportDeclarationLotsCSV::getHeaderCsv(), "Type;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email Operateur;Num dossier;Num lot;Date lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume;Destination;Date de destination;Centilisation;Elevage;Eleve;Prelevable;Preleve;Changé;Logement Adresse;Mode de declaration;Date de validation;Date de validation ODG;Date de degustation voulue;Date d'envoi OI;Doc Id;Lot unique Id;Hash produit\n", "Entête de csv");

$export = new ExportDeclarationLotsCSV($drev, false);
$t->is($export->export(),
    $drev->type.";".
    $drev->campagne.";".
    $drev->identifiant.";".
    $drev->declarant->cvi.";".
    $drev->declarant->siret.";".
    '"'.$drev->declarant->nom."\";".
    '"'.$drev->declarant->adresse."\";".
    $drev->declarant->code_postal.";".
    '"'.$drev->declarant->commune."\";".
    $drev->declarant->email.";".
    $lot->numero_dossier.";".
    $lot->numero_archive.";".
    $lot->date.";".
    '"'.$lot->numero_logement_operateur."\";".
    $lot->getConfigProduit()->getCertification()->getKey().";".
    $lot->getConfigProduit()->getGenre()->getKey().";".
    $lot->getConfigProduit()->getAppellation()->getKey().";".
    $lot->getConfigProduit()->getMention()->getKey().";".
    $lot->getConfigProduit()->getLieu()->getKey().";".
    $lot->getConfigProduit()->getCouleur()->getKey().";".
    $lot->getConfigProduit()->getCepage()->getKey().";".
    $lot->getProduitLibelle().";".
    $lot->getCepagesToStr().";".
    $lot->millesime.";".
    $lot->specificite.";".
    $lot->volume.";".
    $lot->destination_type.";".
    $lot->destination_date.";".
    $lot->centilisation.";".
    $lot->elevage.";".
    $lot->eleve.";".
    $lot->affectable.";".
    $lot->isAffecte().";".
    $lot->isChange().";".
    '"'.$lot->adresse_logement."\";".
    "PAPIER;".
    $drev->validation.";".
    $drev->validation_odg.";".
    $drev->date_degustation_voulue.";".
    $drev->envoi_oi.";".
    $drev->_id.";".
    $lot->unique_id.";".
    $lot->produit_hash
    , "Export csv du lot de la drev");

$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $campagne, $date);
$lotC = $conditionnement->addLot();
$lotC->produit_hash = $produitconfig1->getHash();
$lotC->volume = 15;
$lotC->numero_logement_operateur = 'C12';
$lotC->centilisation = $centilisations_bib_key;
$conditionnement->validate();
$conditionnement->validateOdg();
$conditionnement->save();

$export = new ExportDeclarationLotsCSV($conditionnement, false);
$t->is(count(explode("\n", $export->export())), 1, "Export csv du lot de conditionnement");

