<?php

class SV12DouaneCsvFile extends DouaneImportCsvFile {

    protected $etablissement = null;

    public function convert() {
        $handler = fopen($this->filePath, 'r');

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
        $this->identifiant = ($this->etablissement)? $this->etablissement->identifiant : null;
        $drev = $this->getRelatedDrev();

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
	        			$produit[] = $values[1];
	        			$produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[0]));
	        			$produit[] = null;
	        			$produit[] = $communeTiers;
                $produit[] = $cpt;
                $produit[] = Organisme::getCurrentOrganisme();
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($drev) ? $drev->_id : '';
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
