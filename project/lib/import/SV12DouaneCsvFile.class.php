<?php

class SV12DouaneCsvFile extends DouaneImportCsvFile {

    public function convert() {
        $format = $this->detectFormat();

        if(!$format) {

            return null;
        }

        return call_user_func(array($this, "convertFrom".$format));
    }

    public function detectFormat() {
        $csvFile = new CsvFile($this->filePath);
        if(preg_match('/DECLARATION DE PRODUCTION SV12/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/Fournisseurs/i', $csvFile->getCsv()[3][0]) &&
           preg_match('/Nom/i', $csvFile->getCsv()[4][0])) {

            return "XlsSV12";
        }
        if(preg_match('/Code produit/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/nomination/i', $csvFile->getCsv()[0][1]) &&
           preg_match('/CVI/i', $csvFile->getCsv()[0][3]) &&
           preg_match('/raisin/i', $csvFile->getCsv()[0][5]) &&
           (preg_match('/MOUTS/i', $csvFile->getCsv()[0][6]) || preg_match('/moûts/i', $csvFile->getCsv()[0][6]))
          ) {

            return "CsvVendanges";
        }

        throw new sfException('Format non supporté : '.$this->doc.' '.implode(',', $csvFile->getCsv()[0]));
    }

    public function convertFromCsvVendanges() {
        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();

        if(preg_match('/-([0-9]{10})_/', $this->filePath, $matches)) {
            $this->cvi = $matches[1];
        }

        $cpt = 1;
        $index2L = array(
            5 => '06kg',
            6 => '07',
            7 => '04',
            9 => '15VF',
           10 => '15M',
           12  => '15'
        );

        $drev_filter = $this->getRelatedDrev();
        $drev = $this->getRelatedDrev(false);

        $known_produit = array();
        foreach ($csv as $key => $values) {
            if($key == 0) {
                $libellesLigne = $values;
                continue;
            }
            $values[12] = VarManipulator::floatize($values[9]) + VarManipulator::floatize($values[10]);
            foreach (array_keys($index2L) as $v) {
                if (!VarManipulator::floatize($values[$v])) {
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
                $lligne = isset($libellesLigne[$v]) ? " - ".preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v])) : '';
                $produit[] = DouaneCsvFile::getCategorieLibelle('SV12', $index2L[$v]).$lligne;
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
                $produit[] = Organisme::getCurrentOrganisme();
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($drev) ? $drev->_id : '';
                $produit[] = ($drev_filter) ? 'FILTERED'.$drev_filter->_id : '';
                $produit[] = ($this->doc)? $this->doc->_id : '';
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = substr($this->campagne, 0, 4);
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = implode('|', DouaneImportCsvFile::extractLabels($values[2]));
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

    public function convertFromXlsSV12() {
        $index2L = array(
             6 => '06kg',
             7 => '07',
             8 => '04',
             9 => '15VF',
            10 => '15M',
            11 => '15'

        );

        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();

        $this->cvi = null;
        $this->raison_sociale = null;
        $this->commune = null;
        $produits = array();
        $communeTiers = null;
        $libellesLigne = null;
        $lies = null;
        $firstPage = true;
        $secondPage = false;
        $cpt = 1;
        $indexCodeProduit = 3;
        $this->identifiant = (isset($this->etablissement) && $this->etablissement)? $this->etablissement->identifiant : null;
        $drev_filter = $this->getRelatedDrev();
        $drev = $this->getRelatedDrev(false);

        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {
        		if (preg_match('/cvi:[\s]*([a-zA-Z-0-9]{10})/i', $values[0], $m)) {
        			if ($this->cvi) {
                        $secondPage = $firstPage;
        				$firstPage = false;
                        if ($secondPage) {
                            $indexCodeProduit = 5;
                        }
        			}
        			$this->cvi = $m[1];
        		}
        		if (preg_match('/commune:[\s]*([a-zA-Z-0-9\s]*)$/i', $values[0], $m)) {
        			$this->commune = $m[1];
        		}
        		if (preg_match('/r.+capitulatif par fournisseur pour l\'evv[\s]*(.*)$/i', $values[0], $m)) {
        			$this->raison_sociale = "\"".html_entity_decode($m[1])."\"";
        		}
        		if (isset($values[$indexCodeProduit + 1]) && !empty($values[$indexCodeProduit + 1]) && preg_match('/libell.+[\s]*du[\s]*produit/i', $values[$indexCodeProduit + 1])) {
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
        		if (($firstPage || $secondPage) && isset($values[$indexCodeProduit + 1]) && !empty($values[$indexCodeProduit + 1]) && !preg_match('/libell.+[\s]*du[\s]*produit/i', $values[$indexCodeProduit + 1])) {
                    if (!$libellesLigne) {
                        break;
                    }
        			for ($v = $indexCodeProduit + 3; $v < $indexCodeProduit + 10; $v++) {
        				if (!isset($values[$v]) || !$values[$v]) {
        					continue;
        				}
        				$p = $this->configuration->findProductByCodeDouane($values[3]);
        				if (!$p) {
        					$produit = array(null, null, null, null, null, null, null);
        				} else {
        					$produit = array($p->getCertification()->getKey(), $p->getGenre()->getKey(), $p->getAppellation()->getKey(), $p->getMention()->getKey(), $p->getLieu()->getKey(), $p->getCouleur()->getKey(), $p->getCepage()->getKey());
        				}
	        			$produit[] = $values[$indexCodeProduit]; //Code Douane
	        			$produit[] = $values[$indexCodeProduit + 1]; //Libellé produit
	        			$produit[] = $values[$indexCodeProduit + 2]; //Mention
                        $produit[] = $index2L[$v]; //categorie de ligne (04, 15, ...)
	        			$produit[] = DouaneCsvFile::getCategorieLibelle('SV12', $index2L[$v])." - ".preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v])); //libelle categorie
	        			if ($v == $indexCodeProduit + 5) {
                            $produit[] = self::numerizeVal($values[$v], 4);
	        			} else {
                            $produit[] = self::numerizeVal($values[$v], 2);
                        }
                        $produit[] = '"'.$values[1].'"';
	        			$produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[0]));
	        			$produit[] = null;
	        			$produit[] = $communeTiers;
                $produit[] = $cpt;
                $produit[] = Organisme::getCurrentOrganisme();
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($drev) ? $drev->_id : '';
                $produit[] = ($drev_filter) ? 'FILTERED'.$drev_filter->_id : '';
                $produit[] = ($this->doc)? $this->doc->_id : '';
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = substr($this->campagne, 0, 4);
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = implode('|', DouaneImportCsvFile::extractLabels($values[7]));
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
