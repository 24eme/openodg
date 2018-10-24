<?php

class SV12DouaneCsvFile extends DouaneImportCsvFile {

    public function convert() {
        $handler = fopen($this->filePath, 'r');

        $csv = array();

        while (($data = fgetcsv($handler)) !== FALSE) {
            $csv[] = self::clean($data);
        }
        $this->cvi = null;
        $this->raison_sociale = null;
        $this->commune = null;
        $produits = array();
        $communeTiers = null;
        $libellesLigne = null;
        $lies = null;
        $firstPage = true;

        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {

        		if (preg_match('/cvi:[\s]*([a-zA-Z-0-9]{10})/i', $values[0], $m)) {
        			if ($this->cvi) {
        				$firstPage = false;
        			}
        			$this->cvi = $m[1];
        		}
        		if (preg_match('/commune:[\s]*([a-zA-Z-0-9\s]*)$/i', $values[0], $m)) {
        			$this->commune = $m[1];
        		}
        		if (preg_match('/r.+capitulatif par fournisseur pour l\'evv[\s]*(.*)$/i', $values[0], $m)) {
        			$this->raison_sociale = "\"".html_entity_decode($m[1])."\"";
        		}
        		if (isset($values[4]) && !empty($values[4]) && preg_match('/libell.+[\s]*du[\s]*produit/i', $values[4])) {
        			$libellesLigne = $values;
        			continue;
        		}
        		if (preg_match('/^commune de[\s]*(.*)$/i', $values[0], $m)) {
        			$communeTiers = $m[1];
        			continue;
        		}
        		if (preg_match('/lies/i', $values[0])) {
        			$lies = $values[1];
        			continue;
        		}
        		if ($firstPage && isset($values[4]) && !empty($values[4]) && !preg_match('/libell.+[\s]*du[\s]*produit/i', $values[4])) {
        			for ($v = 6; $v < 13; $v++) {
        				if (!isset($values[$v]) || !$values[$v]) {
        					continue;
        				}
        				$p = $this->configuration->findProductByCodeDouane($values[3]);
        				if (!$p) {
        					$produit = array(null, null, null, null, null, null, null);
        				} else {
        					$produit = array($p->getCertification()->getKey(), $p->getGenre()->getKey(), $p->getAppellation()->getKey(), $p->getMention()->getKey(), $p->getLieu()->getKey(), $p->getCouleur()->getKey(), $p->getCepage()->getKey());
        				}
	        			$produit[] = $values[3];
	        			$produit[] = $values[4];
	        			$produit[] = $values[5];
	        			$produit[] = sprintf('%02d', ($v+1));
	        			$produit[] = preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v]));
	        			if ($v == 8) {
                            $produit[] = self::numerizeVal($values[$v], 4);
	        			} else {
                            $produit[] = self::numerizeVal($values[$v], 2);
                        }
	        			$produit[] = $values[1];
	        			$produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[0]));
	        			$produit[] = null;
	        			$produit[] = $communeTiers;
	        			$produits[] = $produit;
        			}
        		}
        	}
        }

        $doc = $this->getEtablissementRows();

        $csv = '';
        foreach ($produits as $p) {
	    	$csv .= implode(';', $doc).';;;'.implode(';', $p)."\n";
        }
        return $csv;
    }
}
