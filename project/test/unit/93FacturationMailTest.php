<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$emetteurs["IGP13"] = array(
    "adresse" => "rue",
    "code_postal" => "cp",
    "ville" => "ville cedex 1",
    "service_facturation" => "Syndicat des vins",
    "telephone" => "00 00 00 00 00 - 00 00 00 00 00",
    "email" => "bonjour@email.fr",
    "responsable" => "responsable",
    "iban" => "iban",
    "tva_intracom" => "tva_intracom",
    "siret" => "siret"
);
sfConfig::set('app_facture_emetteur' , $emetteurs);

$t = new lime_test();

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$societeViti = $viti->getSociete();
$socVitiCompte = $viti->getSociete()->getMasterCompte();

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

//Suppression des factures précédentes
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}

//Suppression des generation précédentes
foreach(GenerationClient::getInstance()->findHistory() as $k => $g) {
    $generation = GenerationClient::getInstance()->find($g->id);
    $generation->delete(false);
}

$config = ConfigurationClient::getCurrent();
$produit_hash = null;
foreach($config->getProduits() as $hash => $produit) {
    $produit_hash = $produit->getHash();
    break;
}

$periode = (date('Y')-1)."";

$t->comment("Création de la drev à facturer");

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();

$lot = $drev->addLot();
$lot->numero_logement_operateur = 'CUVE';
$lot->produit_hash = $produit_hash;
$lot->volume = 100;
$drev->save();

$drev->validate();
$drev->validateOdg();
$drev->save();

$t->ok(count($drev->mouvements),"La drev a des mouvements facturables");

$t->comment("Création de la génération de facture");

$templateFacture = TemplateFactureClient::getInstance()->findByCampagne($drev->campagne);
$generation = new Generation();
$generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
$generation->arguments->add('date_facturation', date('Y-m-d'));
$generation->arguments->add('date_facturation', date('Y-m-d'));
$generation->arguments->add('date_mouvement', date('Y-m-d'));
$generation->arguments->add('type_document', DRevClient::TYPE_MODEL);
$generation->arguments->add('region', strtoupper($application));
$generation->arguments->add('modele', $templateFacture->_id);
$generation->save();

if(count(GenerationConfiguration::getInstance()->getSousGeneration($generation->type_document))) {
$t->is(count($generation->sous_generation_types->toArray(true, false)), 2, "Les types de sous générations possibles sont enregistrés dans le doc");
}

$genarator = GenerationClient::getInstance()->getGenerator($generation, $configuration, array());
$genarator->generate();

$facture = null;
foreach($generation->documents as $id_facture) {
    $facture = FactureClient::getInstance()->find($id_facture);
    break;
}

$t->comment("Envoi des factures par mail avec une génération");

$generationMail = $generation->getOrCreateSubGeneration(GenerationClient::TYPE_DOCUMENT_FACTURES_MAILS);
$t->is($generationMail->type_document, GenerationClient::TYPE_DOCUMENT_FACTURES_MAILS, "Le type de la génération est facture mail");
$t->like($generationMail->_id, '/GENERATION-FACTURE-[0-9]{14}-FACTUREMAIL/', "L'id généré est bon");

$mailGenerator = GenerationClient::getInstance()->getGenerator($generationMail, $configuration, array());
$t->is(get_class($mailGenerator), "GenerationFactureMail", "classe d'éxécution de la génération de mail");

$mail = $mailGenerator->generateMailForADocumentId($facture->_id);
$t->ok(get_class($mail), "Génération du mail d'une facture");
$t->ok(strpos($mail, "https"), "Le mail contient une url");
$t->ok(!strpos($mail, "symfony"), "L'url n'a pas symfony");
$t->like($mail->getSubject(), "/^Facture n°".$facture->getNumeroOdg()." - /", "Sujet du mail");

$mailGenerator->generate();

$t->is($generationMail->statut, GenerationClient::GENERATION_STATUT_GENERE, "Statut généré");
$t->is($mailGenerator->getLogFilname(), $generationMail->date_emission."-facture-envoi-mails.csv", "Nom du fichier csv de log d'envoi de mails");
$t->is($mailGenerator->getLogPath(), sfConfig::get('sf_web_dir')."/generation/".$mailGenerator->getLogFilname(), "Chemin complet vers le fichier de log");
$t->is($mailGenerator->getPublishFile(), "%2Fgeneration%2F".$mailGenerator->getLogFilname(), "Chemin complet relatif encodé");
$logdate = date("Y-m-d H:i:s");
$t->is($mailGenerator->getLog($facture->_id, "ENVOYÉ", $logdate), array($logdate, $facture->getNumeroOdg(), $facture->identifiant, $facture->declarant->raison_sociale, $societeViti->getEmail(), "ENVOYÉ", $facture->_id), "La ligne de log contient les informations");
$t->ok(file_exists($mailGenerator->getLogPath()), "Le fichier de log existe");
$t->is(count(file($mailGenerator->getLogPath())), 2, "Le fichier de log contient 2 lignes");
$mailGenerator->addLog($facture->_id, "ERROR", $logdate);
$t->is(count(file($mailGenerator->getLogPath())), 3, "Le fichier de log contient 2 lignes");
$t->is(count($generationMail->documents->toArray()), 1, "Mail envoyé");
$t->is(count($generationMail->fichiers->toArray()), 1, "Fichier de log généré");

$t->comment("Création des pdfs des factures non téléchargées");
$generationPapier = $generation->getOrCreateSubGeneration(GenerationClient::TYPE_DOCUMENT_FACTURES_PAPIER);
$t->is($generationPapier->type_document, GenerationClient::TYPE_DOCUMENT_FACTURES_PAPIER, "Le type de la génération est facture papier");
$t->like($generationPapier->_id, '/GENERATION-FACTURE-[0-9]{14}-FACTUREPAPIER/', "L'id généré est bon");

$papierGenerator = GenerationClient::getInstance()->getGenerator($generationPapier, $configuration, []);
$t->is(get_class($papierGenerator), 'GenerationFacturePapier', "Classe d'exécution de la génération de facture papier");

$facturePapier = $papierGenerator->generatePDFForADocumentId($facture->_id);
$t->ok(get_class($facturePapier), "FactureLatex", "Génération d'un PDF d'une facture");
$papierGenerator->generate();
