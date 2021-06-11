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
$produitconfig2 = null;
foreach($config->getProduits() as $produitconfig) {
    if(!$produitconfig->getRendement()) {
        continue;
    }
    if(!$produitconfig1) {
        $produitconfig1 = $produitconfig->getCepage();
        continue;
    }
    if(!$produitconfig2) {
        $produitconfig2 = $produitconfig->getCepage();
        break;
    }
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

$lotD = $drev->lots[0];

$t->is(ExportDeclarationLotsCSV::getHeaderCsv(), "Type;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email Operateur;Num dossier;Num lot;Date lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume;Destination;Date de destination;Pays de destination;Centilisation;Elevage;Eleve;Prelevable;Preleve;Changé;Logement Adresse;Mode de declaration;Date de validation;Date de validation ODG;Date de degustation voulue;Date d'envoi OI;Organisme;Doc Id;Lot unique Id;Hash produit\n", "Entête du csv declarations lots");

$export = new ExportDeclarationLotsCSV($drev, false);
$t->is($export->export(),
    $drev->type.";".
    $drev->campagne.";".
    $drev->identifiant.";".
    $drev->declarant->famille.";".
    $drev->declarant->cvi.";".
    $drev->declarant->siret.";".
    '"'.$drev->declarant->nom."\";".
    '"'.$drev->declarant->adresse."\";".
    $drev->declarant->code_postal.";".
    '"'.$drev->declarant->commune."\";".
    $drev->declarant->email.";".
    $lotD->numero_dossier.";".
    $lotD->numero_archive.";".
    $lotD->date.";".
    '"'.$lotD->numero_logement_operateur."\";".
    $lotD->getConfigProduit()->getCertification()->getKey().";".
    $lotD->getConfigProduit()->getGenre()->getKey().";".
    $lotD->getConfigProduit()->getAppellation()->getKey().";".
    $lotD->getConfigProduit()->getMention()->getKey().";".
    $lotD->getConfigProduit()->getLieu()->getKey().";".
    $lotD->getConfigProduit()->getCouleur()->getKey().";".
    $lotD->getConfigProduit()->getCepage()->getKey().";".
    $lotD->getProduitLibelle().";".
    $lotD->getCepagesLibelle().";".
    $lotD->millesime.";".
    $lotD->specificite.";".
    $lotD->volume.";".
    $lotD->destination_type.";".
    $lotD->destination_date.";".
    $lotD->pays.";".
    $lotD->centilisation.";".
    $lotD->elevage.";".
    $lotD->eleve.";".
    $lotD->affectable.";".
    $lotD->isAffecte().";".
    $lotD->isChange().";".
    '"'.$lotD->adresse_logement."\";".
    "PAPIER;".
    $drev->validation.";".
    $drev->validation_odg.";".
    $drev->date_degustation_voulue.";".
    $drev->envoi_oi.";".
    $application.";".
    $drev->_id.";".
    $lotD->unique_id.";".
    $lotD->produit_hash."\n"
    , "Export csv du lot de la $drev");

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
$t->is(count(explode("\n", $export->export())), 2, "Export csv du lot de $conditionnement");

$transaction = TransactionClient::getInstance()->createDoc($viti->identifiant, $campagne, $date);
$lotT = $transaction->addLot();
$lotT->volume = 12;
$lotT->specificite = null;
$lotT->produit_hash = $produitconfig1->getHash();
$lotT->pays = "FR";
$transaction->validate();
$transaction->validateOdg();
$transaction->save();

$export = new ExportDeclarationLotsCSV($transaction, false);
$t->is(count(explode("\n", $export->export())), 2, "Export csv du lot de $transaction");

$t->is(ExportChgtDenomCSV::getHeaderCsv(), "Type;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email Operateur;Origine Num dossier;Origine Num lot;Origine logement Opérateur;Origine Certification;Origine Genre;Origine Appellation;Origine Mention;Origine Lieu;Origine Couleur;Origine Cepage;Origine Produit;Origine Cépages;Origine Millésime;Origine Spécificités;Origine Volume;Type de changement;Num dossier;Num lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume changé;Prelevable;Preleve;Mode de declaration;Date de validation;Date de validation ODG;Organisme;Origine Doc Id;Origin Lot unique Id;Origin Hash produit;Doc Id;Lot unique Id;Hash produit\n", "Entête de csv du changement de denom");

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date, true);
$chgtDenom->setLotOrigine($lotD);
$chgtDenom->changement_produit_hash = $produitconfig2->getHash();
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT;
$chgtDenom->changement_volume = $lotD - 10;
$chgtDenom->constructId();
$chgtDenom->save();
$chgtDenom->validate();
$chgtDenom->validateOdg();
$chgtDenom->save();

$lotOrigine = $chgtDenom->getLotOrigine();
$lotChgt = $chgtDenom->lots[1];

$baseCsv = $chgtDenom->type.";".
$chgtDenom->campagne.";".
$chgtDenom->identifiant.";".
$chgtDenom->declarant->famille.";".
$chgtDenom->declarant->cvi.";".
$chgtDenom->declarant->siret.";".
'"'.$chgtDenom->declarant->nom."\";".
'"'.$chgtDenom->declarant->adresse."\";".
$chgtDenom->declarant->code_postal.";".
'"'.$chgtDenom->declarant->commune."\";".
$chgtDenom->declarant->email.";".
$lotOrigine->numero_dossier.";".
$lotOrigine->numero_archive.";".
$chgtDenom->origine_numero_logement_operateur.";".
$chgtDenom->getConfigProduitOrigine()->getCertification()->getKey().";".
$chgtDenom->getConfigProduitOrigine()->getGenre()->getKey().";".
$chgtDenom->getConfigProduitOrigine()->getAppellation()->getKey().";".
$chgtDenom->getConfigProduitOrigine()->getMention()->getKey().";".
$chgtDenom->getConfigProduitOrigine()->getLieu()->getKey().";".
$chgtDenom->getConfigProduitOrigine()->getCouleur()->getKey().";".
$chgtDenom->getConfigProduitOrigine()->getCepage()->getKey().";".
$chgtDenom->origine_produit_libelle.";".
$lotOrigine->getCepagesLibelle().";".
$chgtDenom->origine_millesime.";".
$chgtDenom->origine_specificite.";".
$chgtDenom->origine_volume.";";

$export = new ExportChgtDenomCSV($chgtDenom, false);
$t->is($export->export(),
    $baseCsv.
    ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT.";".
    $lotChgt->numero_dossier.";".
    $lotChgt->numero_archive.";".
    $chgtDenom->changement_numero_logement_operateur.";".
    $chgtDenom->getConfigProduitChangement()->getCertification()->getKey().";".
    $chgtDenom->getConfigProduitChangement()->getGenre()->getKey().";".
    $chgtDenom->getConfigProduitChangement()->getAppellation()->getKey().";".
    $chgtDenom->getConfigProduitChangement()->getMention()->getKey().";".
    $chgtDenom->getConfigProduitChangement()->getLieu()->getKey().";".
    $chgtDenom->getConfigProduitChangement()->getCouleur()->getKey().";".
    $chgtDenom->getConfigProduitChangement()->getCepage()->getKey().";".
    $chgtDenom->changement_produit_libelle.";".
    $lotChgt->getCepagesLibelle().";".
    $chgtDenom->changement_millesime.";".
    $chgtDenom->changement_specificite.";".
    $chgtDenom->changement_volume.";".
    $chgtDenom->changement_affectable.";".
    $lotChgt->isAffecte().";".
    "PAPIER;".
    $chgtDenom->validation.";".
    $chgtDenom->validation_odg.";".
    $application.";".
    $chgtDenom->changement_origine_id_document.";".
    $chgtDenom->changement_origine_lot_unique_id.";".
    $chgtDenom->origine_produit_hash.";".
    $chgtDenom->_id.";".
    $lotChgt->unique_id.";".
    $chgtDenom->changement_produit_hash."\n"
    , "Export csv des lots du $chgtDenom");

$chgtDenom->delete();

$chgtDenom = ChgtDenomClient::getInstance()->createDoc($viti->identifiant, $date, true);
$chgtDenom->setLotOrigine($lotD);
$chgtDenom->changement_produit_hash = $produitconfig2->getHash();
$chgtDenom->changement_type = ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
$chgtDenom->changement_volume = $lotD->volume;
$chgtDenom->constructId();
$chgtDenom->save();
$chgtDenom->validate();
$chgtDenom->validateOdg();
$chgtDenom->save();

$lotOrigine = $chgtDenom->getLotOrigine();
$lotChgt = $chgtDenom->lots[0];

$export = new ExportChgtDenomCSV($chgtDenom, false);
$t->is($export->export(),
    $baseCsv.
    ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT.";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    ";".
    $chgtDenom->changement_volume.";".
    ";".
    ";".
    "PAPIER;".
    $chgtDenom->validation.";".
    $chgtDenom->validation_odg.";".
    $application.";".
    $chgtDenom->changement_origine_id_document.";".
    $chgtDenom->changement_origine_lot_unique_id.";".
    $chgtDenom->origine_produit_hash.";".
    $chgtDenom->_id.";".
    ";\n"
    , "Export csv des lots du déclassement $chgtDenom");