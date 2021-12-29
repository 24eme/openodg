<?php

class DRDouaneCsvFile extends DouaneImportCsvFile {

    public function convert() {
    	if (!$this->filePath) {
    		throw new sfException("La cible du fichier n'est pas spécifiée.");
    	}

        $csvFile = new CsvFile($this->filePath);
        $csv = $csvFile->getCsv();

        $has_volume_nego = 0;
        $has_volume_nego_lignes = array();
        $has_volume_coop = 0;
        $has_volume_coop_lignes = array();
        $has_volume_cave = 0;
        $has_volume_cave_lignes = array();
        $familles_lignes = array();
        $max_ligne = 0;
        foreach ($csv as $key => $values) {
            if (substr(trim($values[0]), 0, 1) == "6" || substr(trim($values[0]), 0, 1) == '7') {
                for($i = 2 ; $i < count($values); $i++) {
                    $has_volume_nego_lignes[$i] = boolval($values[$i]);
                    $has_volume_nego += boolval($values[$i]);
                }
            }
            if (substr(trim($values[0]), 0, 1) == '8') {
                for($i = 2 ; $i < count($values); $i++) {
                    $has_volume_coop_lignes[$i] = boolval($values[$i]);
                    $has_volume_coop += boolval($values[$i]);
                }
            }
            if (substr(trim($values[0]), 0, 1) == '9') {
                for($i = 2 ; $i < count($values); $i++) {
                    $has_volume_cave_lignes[$i] = boolval($values[$i]);
                    $has_volume_cave += boolval($values[$i]);
                }
            }
            if ($max_ligne < count($values)) {
                $max_ligne = count($values);
            }
        }
        for ($i = 0 ; $i < $max_ligne ; $i++) {
            $familles_lignes[$i] = $this->getFamilleCalculeeFromLigneDouane(@$has_volume_cave_lignes[$i], @$has_volume_coop_lignes[$i], @$has_volume_nego_lignes[$i]);
        }
        $famille = $this->getFamilleCalculeeFromLigneDouane($has_volume_cave, $has_volume_coop, $has_volume_nego);

        $this->etablissement = ($this->doc)? $this->doc->getEtablissementObject() : null;
	      if ($this->etablissement && !$this->etablissement->isActif()) {
		        return;
        }
        $doc = array();
        $produits = array();
        $hashes = array();
        $ppm = ($this->etablissement)? $this->etablissement->ppm : null;
        $baillage = array();
        $exploitant = array();
        $bailleur = array();
        $libelleLigne = null;
        $achat_fin = 0;
        $achats = array();
        $ratios_bailleur = array();
        $this->identifiant = ($this->etablissement)? $this->etablissement->identifiant : null;
        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant, $this->campagne);
        foreach ($csv as $key => $values) {
        	if (is_array($values) && count($values) > 0) {
                //Cas de fin de tableur avec les achats tolérés
                if (preg_match('/Achats realises dans le cadre de la toler/', $values[0]) || (isset($values[1]) && preg_match('/Identification du vendeur/', $values[1])) ){
                    $achat_fin = 1;
                    continue;
                }
                if ($achat_fin) {
                    if (isset($values[1]) && isset($values[3])) {
                        $commentaire = $values[0];
                        $vendeur = $values[1];
                        $volume = str_replace('.', ',', $values[3]);
                        $achats[] = array(preg_replace('/ - .*/', '', $vendeur), preg_replace('/ *$/', '', preg_replace('/^[0-9]* - */', '', $vendeur)), $volume, $commentaire);
                    }
                    continue;
                }

                //Récupération des infos du déclarant depuis l'entête
        		if (preg_match('/dnr/i', $values[0])) {
        			$this->cvi = (isset($values[1]))? $values[1] : null;
        			$this->raison_sociale = (isset($values[2]))? "\"".html_entity_decode(trim(preg_replace('/^(.+)\(.+\)$/', '\1', $values[2])))."\"" : null;
        			$this->commune = (isset($values[2]))? trim(preg_replace('/^.+\((.+)\)$/', '\1', $values[2])) : null;
        			continue;
        		}
                //Récupération des infos du produit (sur L1 => le code INAO)
        		if ($values[0] == 1) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$produit = $this->configuration->findProductByCodeDouane($values[$i]);
        					if (!$produit) {
        						$produits[$i] = array(null, null, null, null, null, null, null);
                    $hashes[$i] = '';
        					} else {
        						$produits[$i] = array($produit->getCertification()->getKey(), $produit->getGenre()->getKey(), $produit->getAppellation()->getKey(), $produit->getMention()->getKey(), $produit->getLieu()->getKey(), $produit->getCouleur()->getKey(), $produit->getCepage()->getKey());
                    $hashes[$i] = $produit->getHash();
        					}
        					$produits[$i][] = $values[$i];
        				}
        			}
        			continue;
        		}
                //Récupération des infos du libellé du produit
        		if (!$values[0] && preg_match('/libelle produit/i', $values[1])) {
        			for ($i = 2; $i < count($values); $i++) {
        				if (isset($produits[$i])) {
        					$produits[$i][] = ($values[$i])? $values[$i] : null;
        				}
        			}
        			continue;
        		}
                //Récupération de la mention valorisante
        		if ($values[0] == 2) {
        			for ($i = 2; $i < count($values); $i++) {
        				if (isset($produits[$i])) {
                            $produits[$i][] = ($values[$i])? str_replace(";", "", $values[$i]) : null;
        				}
        			}
        			continue;
        		}
                //Récupération de la superficie (qui n'est que les colonne exploitant)
        		if ($values[0] == 4) {
        			for ($i = 2; $i < count($values); $i++) {
        				if ($values[$i]) {
        					$exploitant[$i][] = array(sprintf('%02d', $values[0]), preg_replace('/ \(ha\)/i', '', self::cleanStr($values[1])), self::numerizeVal($values[$i], 4), null, null, null, null);
        				}
        			}
        			continue;
        		}
                // Calcul du ratio du baillages-métayages
                if ($values[0] == 5 || $values[0] == 15) {
                    $keyLigneBailleur = $key;
                    if($values[0] == 5) {
                        $keyLigneBailleur = $key + 1;
                    }
                    for ($i = 2; $i < count($csv[$keyLigneBailleur]); $i++) {
                        if ($i%2) {
                            $volume = 0;
        					if ($csv[$keyLigneBailleur][$i]) {
                                $volume = (float) str_replace(",", ".", $csv[$keyLigneBailleur][$i]);
        					}
        					if (isset($csv[$keyLigneBailleur][$i+1]) && $csv[$keyLigneBailleur][$i+1]) {
                                $volumeBailleur = (float) str_replace(",", ".", $csv[$keyLigneBailleur][$i+1]);
                                $ratios_bailleur[sprintf('%02d', $values[0])][$i] = $volumeBailleur / ($volume + $volumeBailleur);
        					}
        				}
        			}
                }

                //Récupération de la récolte totale - L5 (avec un décalage car il y a un ligne extra pour l'exploitant et le bailleur)
        		if ($values[0] == 5) {
        			for ($i = 2; $i < count($csv[$key+1]); $i++) {
        				if ($i%2) {
        					if ($csv[$key+1][$i]) {
        						$exploitant[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($csv[$key+1][$i]), null, null, null, null);
        					}
        					if (isset($csv[$key+1][$i+1]) && $csv[$key+1][$i+1]) {
        						$bailleur[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), self::numerizeVal($csv[$key+1][$i+1]), null, null, null, null);
        					}
        				}
        			}
                    for ($i = 2; $i < count($csv[$key+1]); $i++) {
                        if ($i%2) {
                            $colonneid[$i]   = intval($i / 2);
                            $colonneid[$i+1] = intval($i / 2);
                        }
                    }
        			continue;
        		}
        		if(preg_match("/[6-8]{1}-0/", $values[0])) {
        			$libelleLigne = self::cleanStr($values[1]);
        			continue;
        		}
                //Livraison en négoce ou coop
        		if (preg_match("/[6-8]{1}-[1-9]+/", $values[0])) {
                    $values[1] = self::cleanStr($values[1]);
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
                //Les volumes
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
                //info bailleur
        		if ($values[0] == 20 || $values[0] == 21) {
        			for ($i = 2; $i < count($values); $i++) {
        				if (isset($values[$i]) && $values[$i]) {
        					$baillage[$i][] = $values[$i];
        				}
        			}
        			continue;
        		}

                //info motif de non récolte
                if ($values[0] == 22) {
                    for ($i = 2; $i < count($values); $i++) {
                        if ($values[$i]) {
                            $exploitant[$i][] = array(sprintf('%02d', $values[0]), self::cleanStr($values[1]), null, null, self::cleanStr($values[$i]), null, null);
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
          $colExtraIds = ';'.Organisme::getCurrentOrganisme();
          $colExtraIds .= (isset($hashes[$k]))? ';'.$hashes[$k] : ';';
          $colExtraIds .= ($drev) ? ';'.$drev->_id : ';';
          $colExtraIds .= ($this->doc)? ';'.$this->doc->_id : ';';
          $colExtraIds .= ';'.$famille;
          $colExtraIds .= ';'.substr($this->campagne, 0, 4);
          $colExtraIds .= ';'.$familles_lignes[$k];

	        foreach ($exploitant[$k] as $sk => $e) {
                $eOrigin = null;
                if($e[0] == 4) {
                    $eOrigin = $e;
                    $eOrigin[0] = "04b";
                    $eOrigin[1] = "Superificie de récolte originale";
                }
                $ratio_bailleur = null;
                if(!$ratio_bailleur && isset($ratios_bailleur["05"][$k]) && $ratios_bailleur["05"][$k]) {
                    $ratio_bailleur = $ratios_bailleur["05"][$k];
                }
                if(($e[0] == 4) && isset($baillage[$k])) {
                    $superficieInitiale = (float) (str_replace(",", ".", $e[2]));
                    $superficiemetayer = $superficieInitiale;
                    if (isset($ratio_bailleur)) {
                        $superficiemetayer = $superficieInitiale*(1 - $ratio_bailleur);
                    }
                    $e[2] = self::numerizeVal($superficiemetayer, 4);
                    if (!isset($bailleur[$k])) {
                        $bailleur[$k] = array();
                    }
                    array_unshift($bailleur[$k], $e);
                    $bailleur[$k][$sk][2] = self::numerizeVal($superficieInitiale - $superficiemetayer, 4);
                }
	        	$csv .= implode(';', $doc).';;;'.implode(';', $p).';'.implode(';', $e).';'.$colonneid[$k].$colExtraIds."\n";
	        	if (isset($baillage[$k]) && isset($bailleur[$k]) && isset($bailleur[$k][$sk])) {
	        		$csv .= implode(';', $doc).';'.implode(';', $baillage[$k]).';'.implode(';', $p).';'.implode(';', $bailleur[$k][$sk]).';'.$colonneid[$k].$colExtraIds."\n";
	        		unset($bailleur[$k][$sk]);
	        	}
                if(isset($eOrigin)) {
                    $csv .= implode(';', $doc).';;;'.implode(';', $p).';'.implode(';', $eOrigin).';'.$colonneid[$k].$colExtraIds."\n";
                }
	        }
	        if (isset($baillage[$k]) && isset($bailleur[$k])) {
	        	foreach ($bailleur[$k] as $b) {
	        		$csv .= implode(';', $doc).';'.implode(';', $baillage[$k]).';'.implode(';', $p).';'.implode(';', $b).';'.$colonneid[$k].$colExtraIds."\n";
	        	}
	        }
        }
        foreach ($achats as $a) {
            $csv .= implode(';', $doc).';;;;;;;;;;;;;99;Achats realises dans le cadre de la tolerance administrative ou de sinistre climatique;'.$a[2].';'.$a[0].';'.$a[1].';'.$a[3].";;9999".$colExtraIds."\n";
        }
        return $csv;
    }
}
