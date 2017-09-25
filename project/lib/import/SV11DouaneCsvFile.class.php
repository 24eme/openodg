<?php

class SV11DouaneCsvFile extends DouaneImportCsvFile {

    public function convert() {
        $handler = fopen($this->filePath, 'r');

        $csv = array();

        while (($data = fgetcsv($handler)) !== FALSE) {
            $csv[] = self::clean($data);
        }
        
        $doc = array();
        $cvi = null;
        $rs = null;
        $commune = null;
        $produits = array();
        $communeTiers = null;
        $libellesLigne = null;
        $tabValues = array(3,4,9,10,11,12,13);
        
        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {

        		if (preg_match('/cvi:[\s]*([a-zA-Z-0-9]{10})/i', $values[0], $m)) {
        			$cvi = $m[1];
        		}
        		if (preg_match('/commune:[\s]*([a-zA-Z-0-9\s]*)$/i', $values[0], $m)) {
        			$commune = $m[1];
        		}
        		if (preg_match('/r.+capitulatif par apporteur pour l\'evv[\s]*(.*)$/i', $values[0], $m)) {
        			$rs = $m[1];
        		}
        		if (isset($values[7]) && !empty($values[7]) && preg_match('/libell.+[\s]*du[\s]*produit/i', $values[7])) {
        			$libellesLigne = $values;
        			continue;
        		}
        		if (preg_match('/^commune de[\s]*(.*)$/i', $values[0], $m)) {
        			$communeTiers = $m[1];
        			continue;
        		}
        		if (isset($values[7]) && !empty($values[7]) && !preg_match('/libell.+[\s]*du[\s]*produit/i', $values[7])) {
        			foreach ($tabValues as $v) {
        				if (!$values[$v]) {
        					continue;
        				}
	        			$produit = array(null, null, null, null, null, null, null);
	        			$produit[] = $values[6];
	        			$produit[] = $values[7];
	        			$produit[] = $values[8];
	        			$produit[] = sprintf('%02d', ($v+1));
	        			$produit[] = preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v]));
	        			if ($v == 3) {
	        				$values[$v] = $values[$v] * 100;
	        			}
	        			$produit[] = self::numerizeVal($values[$v]);
	        			$produit[] = $values[1];
	        			$produit[] = $values[0];
	        			$produit[] = null;
	        			$produit[] = $communeTiers;
	        			$produits[] = $produit;
        			}
        		}
        	}
        }
        
        $doc[] = SV11CsvFile::CSV_TYPE_SV11;
        $doc[] = $this->campagne;
        $doc[] = $cvi;
        $doc[] = $rs;
        $doc[] = null;
        $doc[] = $commune;
        
        $csv = '';
        foreach ($produits as $p) {
	    	$csv .= implode(';', $doc).';'.implode(';', $p)."\n";
        }
        return $csv;
    }
}
