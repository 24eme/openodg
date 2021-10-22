<?php

class SV11DouaneCsvFile extends DouaneImportCsvFile {

    public function convert() {
        $handler = fopen($this->filePath, 'r');

        $csvFile = new CsvFile($this->filePath);

        $premierChamp  =  $csvFile->getPremierChamp();

        if(isset($premierChamp) && $premierChamp != "Apporteur"){
          return;
        }

        $csv = $csvFile->getCsv();

        $doc = array();
        $produits = array();
        $this->cvi = null;
        $this->raison_sociale = null;
        $this->commune = null;
        $communeTiers = null;
        $libellesLigne = null;
        $tabValues = array(3,4,9,10,11,12,13);
        $cpt = 1;

        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {

        		if (preg_match('/cvi:[\s]*([a-zA-Z-0-9]{10})/i', $values[0], $m)) {
        			$this->cvi = $m[1];
        		}
        		if (preg_match('/commune:[\s]*([a-zA-Z-0-9\s]*)$/i', $values[0], $m)) {
        			$this->commune = $m[1];
        		}
        		if (preg_match('/r.+capitulatif par apporteur pour l\'evv[\s]*(.*)$/i', $values[0], $m)) {
        			$this->raison_sociale = "\"".html_entity_decode($m[1])."\"";
        		}
        		if (isset($values[7]) && !empty($values[7]) && preg_match('/libell.+[\s]*du[\s]*produit/i', $values[7])) {
        			$libellesLigne = $values;
        			continue;
        		}
        		if ((!isset($values[1]) || !$values[1]) && preg_match('/^commune de[\s]*(.*)$/i', $values[0], $m)) {
        			$communeTiers = $m[1];
        			continue;
        		}
                $known_produit = array();
        		if (isset($values[7]) && !empty($values[7]) && !preg_match('/libell.+[\s]*du[\s]*produit/i', $values[7])) {
        			foreach ($tabValues as $v) {
        				if (!$values[$v]) {
        					continue;
        				}

                        if (!isset($known_produit[$values[6]])) {
        				    $p = $this->configuration->findProductByCodeDouane($values[6]);
        				    if (!$p) {
        					    $produit = array(null, null, null, null, null, null, null);
        				    } else {
        					    $produit = array($p->getCertification()->getKey(), $p->getGenre()->getKey(), $p->getAppellation()->getKey(), $p->getMention()->getKey(), $p->getLieu()->getKey(), $p->getCouleur()->getKey(), $p->getCepage()->getKey());
        				    }
                            $known_produit[$values[6]] = $produit;
                        }else{
                            $produit = $known_produit[$values[6]];
                        }
	        			$produit[] = $values[6];
	        			$produit[] = $values[7];
	        			$produit[] = $values[8];
	        			$produit[] = sprintf('%02d', ($v+1));
	        			$produit[] = preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v]));
                        if ($v == 3) {
                            $produit[] = self::numerizeVal($values[$v], 4);
                        } else {
                            $produit[] = self::numerizeVal($values[$v], 2);
                        }
	        			$produit[] = $values[1];
	        			$produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[0]));
	        			$produit[] = null;
	        			$produit[] = $communeTiers;
                $produit[] = $cpt;
                $produit[] = Organisme::getCurrentOrganisme();
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($this->doc)? $this->doc->_id : '';
	        			$produits[] = $produit;
        			}
                    $cpt++;
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
