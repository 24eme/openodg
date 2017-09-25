<?php

class DRDouaneCsvFile extends DouaneImportCsvFile {

    const EXPLOITANT = 'exploitant';
    const BAILLEUR = 'bailleur';

    public function convert($type = self::EXPLOITANT) {
    	if (!in_array($type, array(self::BAILLEUR, self::EXPLOITANT))) {
    		throw new sfException("Type $type non reconnu.");
    	}
        $handler = fopen($this->filePath, 'r');

        $csv = array();

        while (($data = fgetcsv($handler)) !== FALSE) {
            $csv[] = self::clean($data);
        }

        $dr = array();
        $produits = array();
        $exploitant = array();
        $bailleur = array();
        $libelleLigne = null;

        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {
        		if (preg_match('/dnr/i', $values[0])) {
        			$dr[] = DRCsvFile::CSV_TYPE_DR;
        			$dr[] = $this->campagne;
        			$dr[] = (isset($values[1]))? $values[1] : null;
        			$dr[] = (isset($values[2]))? trim(preg_replace('/^(.+)\(.+\)$/', '\1', $values[2])) : null;
        			$dr[] = null;
        			$dr[] = (isset($values[2]))? trim(preg_replace('/^.+\((.+)\)$/', '\1', $values[2])) : null;
        			continue;
        		}
        		if ($values[0] == 1) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$produits[$i] = array(null, null, null, null, null, null, null);
        					$produits[$i][] = $values[$i];
        				}
        			}
        			continue;
        		}
        		if (!$values[0] && preg_match('/libelle produit/i', $values[1])) {
        			for ($i = 2; $i < count($values); $i++) {
        				if (isset($produits[$i])) {
        					$produits[$i][] = ($values[$i])? $values[$i] : null;
        				}
        			}
        			continue;
        		}
        		if ($values[0] == 2) {
        			for ($i = 2; $i < count($values); $i++) {
        				if (isset($produits[$i])) {
        					$produits[$i][] = ($values[$i])? $values[$i] : null;
        				}
        			}
        			continue;
        		}
        		if ($values[0] == 4) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$exploitant[$i][] = array(sprintf('%04d', $values[0]), preg_replace('/ \(ha\)/i', '', self::cleanStr($values[1])), self::numerizeVal($values[$i]), null, null, null, null);
        					$bailleur[$i][] = array(sprintf('%04d', $values[0]), preg_replace('/ \(ha\)/i', '', self::cleanStr($values[1])), self::numerizeVal($values[$i]), null, null, null, null);
        				}
        			}
        			continue;
        		}
        		if ($values[0] == 5) {
        			for ($i = 2; $i < count($csv[$key+1]); $i++) {
        				if ($i%2) {
        					if ($csv[$key+1][$i]) {
        						$exploitant[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($csv[$key+1][$i]), null, null, null, null);
        					}
        					if ($csv[$key+1][$i+1]) {
        						$bailleur[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($csv[$key+1][$i+1]), null, null, null, null);
        					}
        				}
        			}
        			continue;
        		}
        		if(preg_match("/[6-8]{1}-0/", $values[0])) {
        			$libelleLigne = self::cleanStr($values[1]);
        			continue;
        		}
        		if (preg_match("/[6-8]{1}-[1-9]+/", $values[0])) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					if ($i%2) {
        						$exploitant[$i][] = array(sprintf('%02d', preg_replace("/^([0-9]{1})-[1-9]+$/i", '\1', $values[0])), $libelleLigne, self::numerizeVal($values[$i]), preg_replace("/^acheteur n°([0-9]+) - .+$/i", '\1', $values[1]), preg_replace("/^acheteur n°[0-9]+ - (.+)$/i", '\1', $values[1]), null, null);
        					} else {
        						$bailleur[$i-1][] = array(sprintf('%02d', preg_replace("/^([0-9]{1})-[1-9]+$/i", '\1', $values[0])), $libelleLigne, self::numerizeVal($values[$i]), preg_replace("/^acheteur n°([0-9]+) - .+$/i", '\1', $values[1]), preg_replace("/^acheteur n°[0-9]+ - (.+)$/i", '\1', $values[1]), null, null);
        					}
        				}
        			}
        			continue;
        		}
        		if (is_numeric($values[0]) && $values[0] > 8) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					if ($i%2) {
        						$exploitant[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($values[$i]), null, null, null, null);
        					} else {
        						$bailleur[$i-1][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($values[$i]), null, null, null, null);
        					}
        				}
        			}
        			continue;
        		}
        	}
        }
        $csv = '';
        foreach ($produits as $k => $p) {
        	if (isset(${$type}[$k])) {
	        	foreach (${$type}[$k] as $e) {
	        		$csv .= implode(';', $dr).';'.implode(';', $p).';'.implode(';', $e)."\n";
	        	}
        	}
        }
        return $csv;
    }
}
