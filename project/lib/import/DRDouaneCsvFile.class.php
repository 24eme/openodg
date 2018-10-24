<?php

class DRDouaneCsvFile extends DouaneImportCsvFile {

    public function convert($type = null) {
    	if (!$this->filePath) {
    		throw new sfException("La cible du fichier n'est pas spécifiée.");
    	}
        $handler = fopen($this->filePath, 'r');

        $csv = array();

        while (($data = fgetcsv($handler)) !== FALSE) {
            $csv[] = self::clean($data);
        }
        $this->etablissement = ($this->doc)? $this->doc->getEtablissementObject() : null;
	      if ($this->etablissement && !$this->etablissement->isActif()) {
		        return;
        }
        $doc = array();
        $produits = array();
        $ppm = ($this->etablissement)? $this->etablissement->ppm : null;
        $baillage = array();
        $exploitant = array();
        $bailleur = array();
        $libelleLigne = null;
        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {
        		if (preg_match('/dnr/i', $values[0])) {
        			$this->cvi = (isset($values[1]))? $values[1] : null;
        			$this->raison_sociale = (isset($values[2]))? "\"".html_entity_decode(trim(preg_replace('/^(.+)\(.+\)$/', '\1', $values[2])))."\"" : null;
        			$this->commune = (isset($values[2]))? trim(preg_replace('/^.+\((.+)\)$/', '\1', $values[2])) : null;
        			continue;
        		}
        		if ($values[0] == 1) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$produit = $this->configuration->findProductByCodeDouane($values[$i]);
        					if (!$produit) {
        						$produits[$i] = array(null, null, null, null, null, null, null);
        					} else {
        						$produits[$i] = array($produit->getCertification()->getKey(), $produit->getGenre()->getKey(), $produit->getAppellation()->getKey(), $produit->getMention()->getKey(), $produit->getLieu()->getKey(), $produit->getCouleur()->getKey(), $produit->getCepage()->getKey());
        					}
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
                            $produits[$i][] = ($values[$i])? str_replace(";", "", $values[$i]) : null;
        				}
        			}
        			continue;
        		}
        		if ($values[0] == 4) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$exploitant[$i][] = array(sprintf('%02d', $values[0]), preg_replace('/ \(ha\)/i', '', self::cleanStr($values[1])), self::numerizeVal($values[$i], 4), null, null, null, null);
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
        					$trt = array(sprintf('%02d', preg_replace("/^([0-9]{1})-[1-9]+$/i", '\1', $values[0])), $libelleLigne, self::numerizeVal($values[$i]), preg_replace(array("/^Acheteur n.{1,2}(FR[0-9a-zA-Z]{11}) -.*$/i", "/^Acheteur n.{1,2}([0-9a-zA-Z]{10}) -.*$/i"), '\1', $values[1]), "\"".trim(preg_replace(array("/^Acheteur n.{1,2}FR[0-9a-zA-Z]{11} -(.*)$/i", "/^Acheteur n.{1,2}[0-9a-zA-Z]{10} -(.*)$/i"), '\1', $values[1]))."\"", null, null);
        					if ($i%2) {
        						$exploitant[$i][] = $trt;
        					} else {
        						$bailleur[$i-1][] = $trt;
        					}
        				}
        			}
        			continue;
        		}
        		if (is_numeric($values[0]) && $values[0] > 8 && $values[0] < 20) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$trt = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($values[$i]), null, null, null, null);
        					if ($i%2) {
        						$exploitant[$i][] = $trt;
        					} else {
        						$bailleur[$i-1][] = $trt;
        					}
        				}
        			}
        			continue;
        		}

        		if ($values[0] == 20 || $values[0] == 21) {
        			for ($i = 2; $i < count($values); $i++) {
        				if (isset($values[$i]) && $values[$i]) {
        					$baillage[$i][] = $values[$i];
        				}
        			}
        			continue;
        		}

        		if (is_numeric($values[0]) && $values[0] > 21) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$exploitant[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::cleanStr($values[$i]), null, null, null, null);
        				}
        			}
        			continue;
        		}
        	}
        }
        $bailleurs = array();
        foreach ($baillage as $bail) {
        	if (isset($bail[1])) {
        		$bailleurs[$bail[1]] = $bail[1];
        	}
        }
        if ($ppm && in_array($ppm, $bailleurs)) {
        	return;
        }

        $csv = '';
        $doc = $this->getEtablissementRows();
        foreach ($produits as $k => $p) {
	        foreach ($exploitant[$k] as $sk => $e) {
	        	$csv .= implode(';', $doc).';;;'.implode(';', $p).';'.implode(';', $e)."\n";
	        	if (isset($baillage[$k]) && isset($bailleur[$k]) && isset($bailleur[$k][$sk])) {
	        		$csv .= implode(';', $doc).';'.implode(';', $baillage[$k]).';'.implode(';', $p).';'.implode(';', $bailleur[$k][$sk])."\n";
	        		unset($bailleur[$k][$sk]);
	        	}
	        }
	        if (isset($baillage[$k]) && isset($bailleur[$k])) {
	        	foreach ($bailleur[$k] as $b) {
	        		$csv .= implode(';', $doc).';'.implode(';', $baillage[$k]).';'.implode(';', $p).';'.implode(';', $b)."\n";
	        	}
	        }
        }
        return $csv;
    }

    public static function convertByDonnees($dr) {
    	if (!$dr->exist('donnees') || count($dr->donnees) < 1) {
    		return null;
    	}
    	$csv = '';
    	$configuration = ConfigurationClient::getCurrent();
    	$categories = sfConfig::get('app_dr_categories');
    	$etablissementClient = EtablissementClient::getInstance();
    	$this->etablissement = $etablissementClient->find($dr->identifiant);
      $this->campagne = $dr->campagne;
    	if (!$this->etablissement) {
    		return null;
    	}

    	$produits = array();

    	foreach ($dr->donnees as $donnee) {
    		if ($produit = $configuration->declaration->get($donnee->produit)) {
    			$p = array();
    			if ($donnee->bailleur && $b = $etablissementClient->find($donnee->bailleur)) {
    				$p[] = $b->raison_sociale;
    				$p[] = $b->ppm;
    			} else {
    				$p[] = null;
    				$p[] = null;
    			}
    			$p[] = $produit->getCertification()->getKey();
    			$p[] = $produit->getGenre()->getKey();
    			$p[] = $produit->getAppellation()->getKey();
    			$p[] = $produit->getMention()->getKey();
    			$p[] = $produit->getLieu()->getKey();
    			$p[] = $produit->getCouleur()->getKey();
    			$p[] = $produit->getCepage()->getKey();
    			$p[] = $produit->code_douane;
    			$p[] = $produit->getLibelleFormat();
    			$p[] = $donnee->complement;
    			$p[] = $donnee->categorie;
    			$p[] = (isset($categories[$donnee->categorie]))? preg_replace('/^[0-9]+\./', '', $categories[$donnee->categorie]) : null;
    			$p[] = str_replace('.', ',', $donnee->valeur);
    			if ($donnee->tiers && $t = $etablissementClient->find($donnee->tiers)) {
    				$p[] = $t->cvi;
    				$p[] = DouaneImportCsvFile::cleanRaisonSociale($t->raison_sociale);
    				$p[] = null;
    				$p[] = $t->siege->commune;
    			} else {
    				$p[] = null;
    				$p[] = null;
    				$p[] = null;
    				$p[] = null;
    			}
    			$produits[] = $p;
    		}
    	}
      $drInfos = $this->getEtablissementRows();
    	foreach ($produits as $k => $p) {
    		$csv .= implode(';', $drInfos).';'.implode(';', $p)."\n";
    	}
        return $csv;
    }

}
