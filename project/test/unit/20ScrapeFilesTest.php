<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(0);

if (!sfConfig::get('app_scrapy_documents') || !file_exists(sfConfig::get('app_scrapy_documents'))) {
	return ;
}

$dr = current(DRClient::getInstance()->findAll(1));
if ($dr) {
	$t = new lime_test(1);
	$etab = EtablissementClient::getInstance()->find($dr->identifiant);

	$societeviti = SocieteClient::getInstance()->createSociete("SARL ACTUALYS JEAN", SocieteClient::TYPE_OPERATEUR);
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
	$etablissementviti->siege->commune = "NEUILLY";
	$etablissementviti->save();

	$t->comment('Création établissement '.$etablissementviti->_id);

	$t->comment('Scrape et création fichier');
	$fichier = FichierClient::getInstance()->scrapeAndSaveFiles($etablissementviti, DRCsvFile::CSV_TYPE_DR, $dr->campagne);
	if ($fichier) {
		$t->is($fichier->_id, DRCsvFile::CSV_TYPE_DR.'-'.$etablissementviti->identifiant.'-'.$dr->campagne);
		$fichier->delete();
	}

	$etablissementviti->delete();
	$societeviti->delete();

	$t->comment('Données créées supprimées');

}
