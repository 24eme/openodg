<?php
/**
 * Model for DR
 *
 */

class DR extends BaseDR {


	public function constructId() {
		$this->set('_id', 'DR-' . $this->identifiant . '-' . $this->campagne);
	}

    public static function isPieceEditable($admin = false) {
    	return ($admin)? true : false;
    }
    
    public function generateDonnees() {
    	$export = new ExportDRCSV($this, false);
    	$csv = explode(PHP_EOL, $export->export());
    	if (!$this->exist('donnees') || count($this->donnees) < 1) {
    		$donnees = $this->add('donnees');
    		$generate = false;
	    	foreach ($csv as $datas) {
	    		$data = str_getcsv($datas, ";");
	    		if ($data && isset($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]) && !empty($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION])) {
	    			$generate = true;
	    			$item = $donnees->add();
	    			$item->produit = "certifications/".$data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[DouaneCsvFile::CSV_PRODUIT_GENRE]."/appellations/".$data[DouaneCsvFile::CSV_PRODUIT_APPELLATION]."/mentions/".$data[DouaneCsvFile::CSV_PRODUIT_MENTION]."/lieux/".$data[DouaneCsvFile::CSV_PRODUIT_LIEU]."/couleurs/".$data[DouaneCsvFile::CSV_PRODUIT_COULEUR]."/cepages/".$data[DouaneCsvFile::CSV_PRODUIT_CEPAGE];
	    			$item->complement = $data[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
	    			$item->categorie = $data[DouaneCsvFile::CSV_LIGNE_CODE];
	    			$item->valeur = $data[DouaneCsvFile::CSV_VALEUR];
	    			if ($data[DouaneCsvFile::CSV_TIERS_CVI]) {
	    				if ($tiers = EtablissementClient::getInstance()->findByCvi($data[DouaneCsvFile::CSV_TIERS_CVI])) {
	    					$item->tiers = $tiers->_id;
	    				}
	    			}
	    			if ($data[DouaneCsvFile::CSV_BAILLEUR_PPM]) {
	    				if ($tiers = EtablissementClient::getInstance()->findByPPM($data[DouaneCsvFile::CSV_BAILLEUR_PPM])) {
	    					$item->bailleur = $tiers->_id;
	    				}
	    			}
	    		}
	    	}
	    	return $generate;
    	}
    	return false;
    }
}