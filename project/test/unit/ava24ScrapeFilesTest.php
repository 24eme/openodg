<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(1);

$dr = current(DRClient::getInstance()->findAll(1));
if ($dr) {
	$etab = EtablissementClient::getInstance()->find($dr->identifiant);
	
	
	$societeviti = SocieteClient::getInstance()->createSociete("Société viti test", SocieteClient::TYPE_OPERATEUR);
	$societeviti->pays = "FR";
	$societeviti->code_postal = "92100";
	$societeviti->commune = "Neuilly sur seine";
	$societeviti->insee = "94512";
	$societeviti->save();
	
	$t->comment('Création société '.$societeviti->_id);
	
	$etablissementviti = $societeviti->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
	$etablissementviti->region = EtablissementClient::REGION_CVO;
	$etablissementviti->nom = "Etablissement viti test";
	$etablissementviti->cvi = $etab->cvi;
	$etablissementviti->save();
	
	$t->comment('Création établissement '.$etablissementviti->_id);
	
	$t->comment('Scrape et création fichier');
	if ($fichier = FichierClient::getInstance()->scrapeAndSaveFiles($etablissementviti, DRCsvFile::CSV_TYPE_DR, $dr->campagne)) {
		$t->is($fichier->_id, DRCsvFile::CSV_TYPE_DR.'-'.$etablissementviti->identifiant.'-'.$dr->campagne);
		$fichier->delete();
	}
	
	$etablissementviti->delete();
	$societeviti->delete();
	
	$t->comment('Données créées supprimées');

}


