<?php

class SV11DouaneCsvFile extends DouaneImportCsvFile {

    public function convert() {
        $format = $this->detectFormat();

        if(!$format) {

            return null;
        }

        return call_user_func(array($this, "convertFrom".$format));
    }

    public function detectFormat() {
        $csvFile = new CsvFile($this->filePath);
        if(preg_match('/DECLARATION DE PRODUCTION SV11/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/Apporteur/i', $csvFile->getCsv()[3][0]) &&
           preg_match('/Nom/i', $csvFile->getCsv()[4][0])) {

            return "XlsSV11";
        }

        if(preg_match('/Code produit/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/D?nomination/i', $csvFile->getCsv()[0][1]) &&
           preg_match('/CVI/i', $csvFile->getCsv()[0][3]) &&
           preg_match('/Volume/i', $csvFile->getCsv()[0][5])) {

            return "CsvVendanges";
        }

        return null;
    }

    public function convertFromCsvVendanges() {
        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();

        if(preg_match('/-([0-9]{10})_/', $this->filePath, $matches)) {
            $this->cvi = $matches[1];
        }

        $cpt = 1;
        $index2L = array(
            7 => "04",
            5 => "08",
            9 => "15",
            11 => "16",
            14 => "17",
            12 => "18",
            13 => "19",
        );

        $known_produit = array();
        foreach ($csv as $key => $values) {
            if($key == 0) {
                $libellesLigne = $values;
                continue;
            }
            foreach (array_keys($index2L) as $v) {
                if (!($values[$v]*1)) {
                    continue;
                }
                if (!isset($known_produit[$values[0]])) {
                    $p = $this->configuration->findProductByCodeDouane($values[0]);
                    if (!$p) {
                        $produit = array(null, null, null, null, null, null, null);
                    } else {
                        $produit = array($p->getCertification()->getKey(), $p->getGenre()->getKey(), $p->getAppellation()->getKey(), $p->getMention()->getKey(), $p->getLieu()->getKey(), $p->getCouleur()->getKey(), $p->getCepage()->getKey());
                    }
                    $known_produit[$values[0]] = $produit;
                }

                $produit = $known_produit[$values[0]];
                $produit[] = $values[0]; //Code douane
                $produit[] = $values[1]; //Libelle produit
                $produit[] = $values[2]; //Mention valorisante
                $produit[] = $index2L[$v]; //Code categorie
                $produit[] = DouaneCsvFile::getCategorieLibelle('SV11', $index2L[$v])." - ".preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v]));
                if ($index2L[$v] == "04") {
                    $produit[] = self::numerizeVal($values[$v], 4);
                } else {
                    $produit[] = self::numerizeVal($values[$v], 2);
                }
                $produit[] = '"'.$values[3].'"';
                $produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[4]));
                $produit[] = null;
                $produit[] = null;
                $produit[] = $cpt;
                $produit[] = "provence";
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($drev) ? $drev->_id : '';
                $produit[] = ($drev_filter) ? 'FILTERED'.$drev_filter->_id : '';
                $produit[] = ($this->doc)? $this->doc->_id : '';
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = substr($this->campagne, 0, 4);
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produits[] = $produit;
            }
            $cpt++;
        }

        $doc = $this->getEtablissementRows();

        $csv = '';
        foreach ($produits as $p) {
            $csv .= implode(';', $doc).';;;'.implode(';', $p)."\n";
        }

        return $csv;
    }

    public function convertFromXlsSV11() {

        $csvFile = new CsvFile($this->filePath);

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

        $index2L = array(
            3 => "04",
            4 => "08",
            9 => "15",
            10 => "16",
            11 => "17",
            12 => "18",
            13 => "19",
        );
        $this->identifiant = ($this->etablissement)? $this->etablissement->identifiant : null;
        $drev_filter = $this->getRelatedDrev();
        $drev = $this->getRelatedDrev(false);

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
	        			$produit[] = $values[6]; //Code douane
	        			$produit[] = $values[7]; //Libelle produit
	        			$produit[] = $values[8]; //Mention valorisante
	        			$produit[] = $index2L[$v]; //Code categorie
	        			$produit[] = DouaneCsvFile::getCategorieLibelle('SV11', $index2L[$v])." - ".preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v]));
                        if ($v == 3) {
                            $produit[] = self::numerizeVal($values[$v], 4);
                        } else {
                            $produit[] = self::numerizeVal($values[$v], 2);
                        }
                        $produit[] = '"'.$values[1].'"';
	        			$produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[0]));
	        			$produit[] = null;
	        			$produit[] = $communeTiers;
                $produit[] = $cpt;
                $produit[] = "provence";
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($drev) ? $drev->_id : '';
                $produit[] = ($drev_filter) ? 'FILTERED'.$drev_filter->_id : '';
                $produit[] = ($this->doc)? $this->doc->_id : '';
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = substr($this->campagne, 0, 4);
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
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
