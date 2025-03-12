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

        if(preg_match('/Recapitulatif de la production/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/Numero CVI/i', $csvFile->getCsv()[1][0]) &&
           preg_match('/Libelle du recoltant/i', $csvFile->getCsv()[1][1])
        ) {
            return "XlsVendanges";
        }

        if(preg_match('/Code produit/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/D?nomination/i', $csvFile->getCsv()[0][1]) &&
           preg_match('/CVI/i', $csvFile->getCsv()[0][3]) &&
           preg_match('/Volume/i', $csvFile->getCsv()[0][5])) {

            return "CsvVendanges";
        }

        if(preg_match('/CODE PRODUIT/i', $csvFile->getCsv()[0][0]) &&
           preg_match('/DENOMINATION/i', $csvFile->getCsv()[0][1]) &&
           preg_match('/MENTION VALORISANTE/i', $csvFile->getCsv()[0][3]) &&
           preg_match('/NUMERO CVI APPORTEUR/i', $csvFile->getCsv()[0][5])) {

            return "CsvVendanges";
        }

        throw new sfException('Format non supporté : '.$this->doc.' '.implode(',', $csvFile->getCsv()[0]));
    }

    public function convertFromCsvVendanges() {
        return $this->convertFromVendanges('csv');
    }

    public function convertFromXlsVendanges() {
        return $this->convertFromVendanges('xls');
    }

    public function convertFromVendanges($type = 'csv') {
        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();
        if(preg_match('/-([0-9]{10})_/', $this->filePath, $matches)) {
            $this->cvi = $matches[1];
        }

        $cpt = 1;
        $index2L = array(
            'SUPERFICIE RECOLTE' => "04",
            'VOLUME APPORT DE RAISIN' => "08",
            'VOLUME ISSU DE MOUTS' => "15",
            'VOLUME APPORT DE MOUTS' => "08",
            'VOLUME ISSU DE RAISINS' => "15",
            'VOLUME A ELIMINER' => "16",
            'EAU ELIMINEE' => "17",
            'VSI' => "18",
            'VCI' => "19",
        );

        $valuename2valueid = array(
            'csv' => array(
                'CODE PRODUIT' => 0,
                'DENOMINATION' => 1,
                'MENTION VALORISANTE' => 2,
                'NUMERO CVI APPORTEUR' => 3,
                'LIBELLE APPORTEUR' => 4,
                'VOLUME APPORT DE RAISIN' => 5,
                'VOLUME APPORT DE MOUTS' => 6,
                'SUPERFICIE RECOLTE' => 7,
                'ZONE DE RECOLTE' => 8,
                'VOLUME ISSU DE RAISINS' => 9,
                'VOLUME ISSU DE MOUTS' => 10,
                'VOLUME A ELIMINER' => 11,
                'VSI' => 12,
                'VCI' => 13,
                'EAU ELIMINEE' => 14,
                'MOUT CONCENTRE' => 15,
                'JUS DE RAISINS' => 16,
                "VOLUME D'ALCOOL AJOUTE" => 17,
            ),
            'xls' => array(
                'CODE PRODUIT' => 2,
                'DENOMINATION' => 3,
                'MENTION VALORISANTE' => 4,
                'NUMERO CVI APPORTEUR' => 0,
                'LIBELLE APPORTEUR' => 1,
                'VOLUME APPORT DE RAISIN' => 999,
                'VOLUME APPORT DE MOUTS' => 10,
                'SUPERFICIE RECOLTE' => 6,
                'ZONE DE RECOLTE' => 5,
                'VOLUME ISSU DE RAISINS' => 999,
                'VOLUME ISSU DE MOUTS' => 17,
                'VOLUME A ELIMINER' => 18,
                'VSI' => 20,
                'VCI' => 21,
                'EAU ELIMINEE' => 19,
                'MOUT CONCENTRE' => 13,
                'JUS DE RAISINS' => 999,
                "VOLUME D'ALCOOL AJOUTE" => 15,
            )
        );

        $known_produit = array();
        $drev_filter = $this->getRelatedDrev();
        $drev = $this->getRelatedDrev(false);
        foreach ($csv as $key => $values) {
            if($key == 0) {
                $libellesLigne = $values;
                continue;
            }
            foreach (array_keys($index2L) as $vname) {
                $v = $valuename2valueid[$type][$vname];
                if (!isset($values[$v])||!VarManipulator::floatize($values[$v])) {
                    continue;
                }
                if (!isset($known_produit[$values[$valuename2valueid[$type]['CODE PRODUIT']]])) {
                    $p = $this->configuration->findProductByCodeDouane($values[$valuename2valueid[$type]['CODE PRODUIT']]);
                    if (!$p) {
                        $produit = array(null, null, null, null, null, null, null);
                    } else {
                        $produit = array($p->getCertification()->getKey(), $p->getGenre()->getKey(), $p->getAppellation()->getKey(), $p->getMention()->getKey(), $p->getLieu()->getKey(), $p->getCouleur()->getKey(), $p->getCepage()->getKey());
                    }
                    $known_produit[$values[$valuename2valueid[$type]['CODE PRODUIT']]] = $produit;
                }

                $produit = $known_produit[$values[$valuename2valueid[$type]['CODE PRODUIT']]];
                $produit[] = $values[$valuename2valueid[$type]['CODE PRODUIT']]; //Code douane
                $produit[] = $values[$valuename2valueid[$type]['DENOMINATION']]; //Libelle produit
                $produit[] = $values[$valuename2valueid[$type]['MENTION VALORISANTE']]; //Mention valorisante
                $produit[] = $index2L[$vname]; //Code categorie
                $produit[] = DouaneCsvFile::getCategorieLibelle('SV11', $index2L[$vname])." - ".preg_replace('/ \(ha\)/i', '', self::cleanStr($libellesLigne[$v]));
                if ($index2L[$vname] == "04") {
                    $produit[] = self::numerizeVal($values[$v], 4);
                } else {
                    $produit[] = self::numerizeVal($values[$v], 2);
                }
                $produit[] = '"'.$values[$valuename2valueid[$type]['NUMERO CVI APPORTEUR']].'"';
                $produit[] = DouaneImportCsvFile::cleanRaisonSociale(html_entity_decode($values[$valuename2valueid[$type]['LIBELLE APPORTEUR']]));
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
                $produit[] = implode('|', DouaneImportCsvFile::extractLabels($values[$valuename2valueid[$type]['MENTION VALORISANTE']]));
                $produit[] = $this->getHabilitationStatus(HabilitationClient::ACTIVITE_VINIFICATEUR, $p);
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
                $produit[] = Organisme::getCurrentOrganisme();
                $produit[] = ($p)? $p->getHash() : '';
                $produit[] = ($drev) ? $drev->_id : '';
                $produit[] = ($drev_filter) ? 'FILTERED'.$drev_filter->_id : '';
                $produit[] = ($this->doc)? $this->doc->_id : '';
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = substr($this->campagne, 0, 4);
                $produit[] = $this->getFamilleCalculeeFromLigneDouane();
                $produit[] = implode('|', DouaneImportCsvFile::extractLabels($values[8]));
                $produit[] = $this->getHabilitationStatus(HabilitationClient::ACTIVITE_VINIFICATEUR, $p);
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
