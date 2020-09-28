<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation implements InterfacePieceDocument, InterfaceMouvementLotsDocument {

	protected $piece_document = null;

    public function __construct() {
        parent::__construct();
		//TODO : supprimer cette goretterie réalisée pour la démo
		$this->campagne = '2019';
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->piece_document = new PieceDocument($this);
    }

    public function getConfiguration() {

        return ConfigurationClient::getInstance()->getConfiguration($this->campagne.'-10-01');
    }

    public function constructId() {
				$dateId = str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$this->date));
        $id = sprintf("%s-%s-%s", DegustationClient::TYPE_COUCHDB, $dateId, $this->getLieuNom(true));

        $this->set('_id', $id);
    }


		public function getConfigProduits() {

				return $this->getConfiguration()->declaration->getProduits();
		}

    public function getLieuNom($slugify = false) {
        return self::getNomByLieu($this->lieu, $slugify);
    }

    public static function getNomByLieu($lieu, $slugify = false) {
        if (strpos($lieu, "—") === false) {
            throw new sfException('Le lieu « '.$lieu.' » n\'est pas correctement formaté dans la configuration. Séparateur « — » non trouvé.');
        }
        $lieuExpld = explode('—', $lieu);
        return ($slugify)? KeyInflector::slugify($lieuExpld[0]) : $lieuExpld[0];
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->find("ETABLISSEMENT-".$this->identifiant);
    }

	protected function doSave() {
		$this->piece_document->generatePieces();
	}

	public function storeEtape($etape) {
	    if ($etape == $this->etape) {

	        return false;
	    }

	    $this->add('etape', $etape);

	    return true;
	}

	public function validate($date = null) {
	    if(is_null($date)) {
	        $date = date('Y-m-d');
	    }
	    $this->validation = $date;
	    $this->updateMouvementsLots();
	    $this->generateMouvementsLots();
	}


	public function devalidate($reinit_version_lot = true) {
	    $this->validation = null;
	    if($this->exist('etape')) {
	        $this->etape = null;
	    }
	    $this->updateMouvementsLots(0);
	}

	public function updateMouvementsLots($preleve = 1) {
	    foreach ($this->lots as $lot) {
            if ($lot->leurre === true) {
                continue;
            }
	        $doc = acCouchdbManager::getClient()->find($lot->id_document);
	        if ($doc instanceof InterfaceMouvementLotsDocument) {
	            if ($doc->exist($lot->origine_mouvement)) {
	               $doc->get($lot->origine_mouvement)->set('preleve', $preleve);
	               $doc->save();
	            }
	        }
	    }
	}

	public function getInfosDegustation(){
		$infos = array();
		$infos["nbLots"] = count($this->getLots());
		$infos['nbLotsRestantAPreleve'] = $this->getNbLotsWithStatut(Lot::STATUT_ATTENTE_PRELEVEMENT);
  	$infos["nbAdherentsLotsRestantAPreleve"] = count($this->getAdherentsByLotsWithStatut(Lot::STATUT_ATTENTE_PRELEVEMENT));
		$infos["degustateursConfirmes"] = $this->getDegustateursConfirmes();
		$infos["nbDegustateursConfirmes"] = count($infos["degustateursConfirmes"]);
		$infos["nbDegustateursATable"] = count($this->getDegustateursATable());
		$infos["nbDegustateursSansTable"] = $infos["nbDegustateursConfirmes"] -	$infos["nbDegustateursATable"];
		$infos["degustateurs"] = array();
		foreach (DegustationConfiguration::getInstance()->getColleges() as $college_key => $libelle) {
			$infos["degustateurs"][$libelle] = array();
			$infos["degustateurs"][$libelle]['confirmes'] = $this->getNbDegustateursStatutWithCollege(true,$college_key);
			$infos["degustateurs"][$libelle]['total'] = count($this->degustateurs->getOrAdd($college_key));
		}
		$tables = $this->getTablesWithFreeLots();
		$infos["nbTables"] = count($tables);
		$infos["nbFreeLots"] = count($this->getFreeLots());
		$infos["nbLotsDegustes"] = $infos["nbLots"] - $infos["nbFreeLots"];
		$infos["nbLotsConformes"] = $this->getNbLotsWithStatut(Lot::STATUT_CONFORME);
		$infos["nbLotsNonConformes"] = $this->getNbLotsWithStatut(Lot::STATUT_NON_CONFORME);
		return $infos;
	}


	public function generateMouvementsLots() {
	    // A implementer lorsque les lots devront etre redegustes
	}

	public function isValidee() {

	    return $this->validation;
	}

    /**** PIECES ****/

    public function getAllPieces() {
    	$pieces = array();
    	return $pieces;
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return null;
    }

    public static function getUrlvisualisationPiece($id, $admin = false) {
    	return null;
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    public static function isvisualisationMasterUrl($admin = false) {
    	return false;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }

	public function getMvtLotsPrelevables() {
         $mvt = array();
         foreach (MouvementLotView::getInstance()->getByPrelevablePreleve($this->campagne, 1,0)->rows as $item) {
			 $mvt[Lot::generateMvtKey($item->value)] = $item->value;
		 }
		 ksort($mvt);
		 return $mvt;
	 }

    public function getLotsPrelevables() {
        $lots = array();
        foreach ($this->getMvtLotsPrelevables() as $key => $mvt) {
            $lot = MouvementLotView::generateLotByMvt($mvt);
            $lots[$key] = $lot;
        }

        uasort($lots, function ($lot1, $lot2) {
            $date1 = DateTime::createFromFormat('Y-m-d', $lot1->date);
            $date2 = DateTime::createFromFormat('Y-m-d', $lot2->date);

            if ($date1 == $date2) {
                return 0;
            }
            return ($date1 < $date2) ? -1 : 1;
        });
        return $lots;
    }

	 public function setLotsFromMvtKeys($keys, $statut){
		 $this->remove('mouvements_lots');
		 $this->remove('lots');
		 $this->add('mouvements_lots');
		 $this->add('lots');
		 $mvts = $this->getMvtLotsPrelevables();
		 foreach($keys as $key => $activated) {
			 $mvt = $mvts[$key];
			 if ($activated) {
				 $lot = MouvementLotView::generateLotByMvt($mvt);
				 $lot->statut = $statut;
				 $this->lots->add(null, $lot);
				 if (!$this->mouvements_lots->exist($mvt->declarant_identifiant)) {
					 $this->mouvements_lots->add($mvt->declarant_identifiant);
				 }
				 $mvt->prelevable = 0;
				 $mvt->id_document = $this->_id;
				 $this->mouvements_lots->{$mvt->declarant_identifiant}->add($key, $mvt);
			 }
		 }
	 }

	 public function getAdherentsByLotsWithStatut($statut = null){
		 $lots = $this->getLotsWithStatut($statut);
		 $lotsByAdherents = array();
		 foreach ($lots as $lot) {
			 if(!array_key_exists($lot->getDeclarantIdentifiant(),$lotsByAdherents)){
				 	$lotsByAdherents[$lot->getDeclarantIdentifiant()] = array();
				}
				$lotsByAdherents[$lot->getDeclarantIdentifiant()][] = $lot;
		 }

 	 	return $lotsByAdherents;
	}

	 public function getNbLotsWithStatut($statut = null){
			return count($this->getLotsWithStatut($statut));
	 }

	 public function getLotsWithStatut($statut = null){
		 if(!$statut){
			 return array();
		 }
		 $lots = array();
		 foreach ($this->getLots() as $lot) {
				if($lot->statut == $statut){
					$lots[] = $lot;
				}
			}
			return $lots;
	 }


    /**** FIN DES PIECES ****/


		/**** Gestion des tables de la degustation ****/

		public function getFreeLots(){
			$freeLots = array();
			foreach ($this->lots as $lot) {
				if(! $lot->exist('numero_table') || !$lot->numero_table){
					$freeLots[] = $lot;
				}
			}
			return $freeLots;
		}


		public function getTablesWithFreeLots($add_default_table = false){
			$tables = array();
			$freeLots = $this->getFreeLots();
			foreach ($this->lots as $lot) {
				if($lot->exist('numero_table') && $lot->numero_table){
					if(!isset($tables[$lot->numero_table])){
						$tables[$lot->numero_table] = new stdClass();
						$tables[$lot->numero_table]->lots = array();
						$tables[$lot->numero_table]->freeLots = $freeLots;
					}
					$tables[$lot->numero_table]->lots[] = $lot;
				}
			}

			if($add_default_table && !count($tables)){
				$table = new stdClass();
				$table->lots = array();
				$table->freeLots = $freeLots;
				$tables[] = $table;
			}
			return $tables;
		}

		public function getLotsTableOrFreeLots($numero_table, $free = true){
			$lots = array();
			foreach ($this->lots as $lot) {
				if(($lot->numero_table == $numero_table)){
					$lots[] = $lot;
					continue;
				}

				if($free && is_null($lot->numero_table))  {
					$lots[] = $lot;
					continue;
				}
			}
			uasort($lots, "Degustation::sortLotsByAppelationCouleurCepage");
			return $lots;
		}

		public function hasFreeLots(){
			foreach ($this->lots as $lot) {
				if(!$lot->exist("numero_table") || is_null($lot->numero_table)){
					return true;
				}
			}
			return false;
		}

		public function getLotsSorted(){
			$allLots = array();
			foreach ($this->getLots() as $lot) {
				$allLots[] = $lot;
			}
			uasort($allLots, "Degustation::sortLotsByAppelationCouleurCepage");
			return $allLots;
		}

		public function getSyntheseLotsTable($numero_table){
			$syntheseLots = array();
			foreach ($this->lots as $lot) {
				if($lot->numero_table == $numero_table){
					if(!array_key_exists($lot->getProduitHash(),$syntheseLots)){
						$synthese = new stdClass();
						$synthese->lots = array();
						$synthese->libelle = $lot->getProduitLibelle();

						$syntheseLots[$lot->getProduitHash()] = $synthese;
					}
					$syntheseLots[$lot->getProduitHash()]->lots[] = $lot;
				}
			}
			ksort($syntheseLots);
			return $syntheseLots;
		}

		public function getFirstNumeroTable(){
			$tables = array_keys($this->getTablesWithFreeLots());
			if(!count($tables)) { return 0; }
			return min($tables);
		}

		public function getLastNumeroTable(){
			$tables = array_keys($this->getTablesWithFreeLots());
			if(!count($tables)) { return 0; }
			return max($tables);
		}

		public static function sortLotsByAppelationCouleurCepage($a, $b){
        $a_data = $a->getProduitLibelle();
        $b_data = $b->getProduitLibelle();
        return strcmp($a_data,$b_data);
    }

    public function addLeurre($hash, $numero_lot, $numero_table)
        {
            if (! $this->exist('lots')) {
                $this->add('lots');
            }

            $leurre = $this->lots->add();
            $leurre->leurre = true;
            $leurre->numero_table = $numero_table;
            $leurre->setProduitHash($hash);
            if ($numero_lot) {
                $leurre->numero = $numero_lot;
            }

            return $leurre;
        }

		/**** Fin Gestion des tables de la degustation ****/


		/**** Gestion dégustateurs ****/

		public function getNbDegustateursStatutWithCollege($confirme = true ,$college = null){
			return count($this->getDegustateursStatutWithCollege($confirme,$college));
		}

		public function getDegustateursStatutWithCollege($confirme = true ,$college = null){
			$degustateurs = array();
			foreach ($this->getDegustateursStatutsParCollege() as $collegeDegs => $degs) {
				if($collegeDegs != $college){
					continue;
				}
				foreach ($degs as $compte_id => $confirmeDeg) {
						if($confirmeDeg == $confirme){
							$degustateurs[] = $compte_id;
						}
					}
				}
			return  $degustateurs;
		}

		public function getDegustateursStatutsParCollege(){
			$degustateursByCollege = array();
			foreach ($this->degustateurs as $college => $degs) {
				if(!array_key_exists($college,$degustateursByCollege)){
					$degustateursByCollege[$college] = array();
				}
				foreach ($degs as $compte_id => $degustateur) {
						$degustateursByCollege[$college][$compte_id] = ($degustateur->exist('confirmation') && !is_null($degustateur->confirmation));
					}
			}
			return $degustateursByCollege;
		}


		public function getDegustateursConfirmes(){
			$degustateurs = array();
			foreach ($this->degustateurs as $college => $degs) {
				foreach ($degs as $compte_id => $degustateur) {
					if($degustateur->exist('confirmation') && !is_null($degustateur->confirmation)){
						$degustateurs[$compte_id] = $degustateur;
					}
				}
			}
			return $degustateurs;
		}

		public function getDegustateursATable(){
			$degustateurs = array();
			foreach ($this->degustateurs as $college => $degs) {
				foreach ($degs as $compte_id => $degustateur) {
					if($degustateur->exist('numero_table') && !is_null($degustateur->numero_table)){
						$degustateurs[$compte_id] = $degustateur;
					}
				}
			}
			return $degustateurs;
		}

		public function hasAllDegustateursConfirmation(){
			$confirmation = true;
			foreach ($this->getDegustateurs() as $collegeKey => $degustateursCollege) {
				foreach ($degustateursCollege as $compte_id => $degustateur) {
					if(!$degustateur->exist('confirmation')){
						$confirmation = false;
						break;
					}
				}
			}
			return $confirmation;
		}

		/**** Fin Gestion dégustateurs ****/

}
