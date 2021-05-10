<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation implements InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceMouvementFacturesDocument, InterfaceArchivageDocument {

	protected $piece_document = null;
	protected $array_tri = null;
    protected $docToSave = array();
    protected $archivage_document = null;
    protected $mouvement_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function getDateFormat($format = 'Y-m-d') {
        if (!$this->date) {
            return date($format);
        }
        return date($format, strtotime($this->date));
    }

		public function getMaster() {
			return $this;
		}

    public function isLotsEditable(){
      return false;
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->piece_document = new PieceDocument($this);
        $this->mouvement_document = new MouvementFacturesDocument($this);
        $this->archivage_document = new ArchivageDocument($this);
    }

    public function getConfiguration() {
        return ConfigurationClient::getInstance()->getConfiguration($this->getDateFormat());
    }

    public function constructId() {
		$date = new DateTime($this->date);

        $this->set('_id', DegustationClient::TYPE_COUCHDB."-".$date->format('YmdHi'));

        $this->campagne = ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate($date->format('Y-m-d'));
    }

		public function getConfigProduits() {

				return $this->getConfiguration()->declaration->getProduits();
		}

    public function getLieuNom() {

        return preg_replace("/[ ]*—.+/", "", $this->lieu);
    }

    public function getLieuAdresse() {

        return preg_replace("/.+—[ ]*/", "", $this->lieu);
    }

	protected function doSave() {
		$this->piece_document->generatePieces();
	}

    public function save($saveDependants = true) {

        $this->generateMouvementsLots();

        parent::save();

        if($saveDependants) {
            $this->saveDocumentsDependants();
		}
    }

	public function storeEtape($etape) {
	    if ($etape == $this->etape) {

	        return false;
	    }

	    $this->add('etape', $etape);

	    return true;
	}

	public function getVersion() {
			return null;
	}

	public function findLot($origineMouvement) {
		foreach($this->lots as $lot) {
			if($lot->origine_mouvement != $origineMouvement) {
				continue;
			}

			return $lot;
		}

		return null;
	}

    public function updateLotLogement($lot, $logement)
    {
        $lots = $this->getLots();
        $lots[$lot->getKey()]->numero_logement_operateur = $logement;
        // TODO: voir pour les mouvements
    }

    public function updateLot($key, $lot)
    {
        $this->lots[$key] = $lot;
    }

	public function getInfosDegustation(){
		$infos = array();
		$infos["nbLots"] = count($this->getLots());
		$infos["nbLotsLeurre"] = count($this->getLots()) - count($this->getLotsWithoutLeurre());;
		$infos["nbLotsSansLeurre"] = count($this->getLotsWithoutLeurre());
		$infos['nbLotsPrelevable'] = count(DegustationClient::getInstance()->getLotsPrelevables());
		$infos['nbLotsRestantAPrelever'] = $this->getNbLotsRestantAPreleve();
		$infos['nbLotsPreleves'] = $this->getNbLotsPreleves();
		$infos['nbLotsPrelevesSansLeurre'] = $this->getNbLotsPreleves() - $infos["nbLotsLeurre"];
		$infos["nbAdherents"] = count($this->getAdherentsPreleves());
  	$infos["nbAdherentsLotsRestantAPrelever"] = count($this->getAdherentsByLotsWithStatut(Lot::STATUT_ATTENTE_PRELEVEMENT));
		$infos["nbAdherentsPreleves"] = count($this->getAdherentsPreleves());
		$infos["degustateursConfirmes"] = $this->getDegustateursConfirmes();
		$infos["nbDegustateursConfirmes"] = count($infos["degustateursConfirmes"]);
		$infos["nbDegustateursATable"] = count($this->getDegustateursATable());
		$infos["nbDegustateursSansTable"] = $infos["nbDegustateursConfirmes"] -	$infos["nbDegustateursATable"];
		$infos["degustateurs"] = array();
		foreach (DegustationConfiguration::getInstance()->getColleges() as $college_key => $libelle) {
			$collegeVar = ucfirst(str_replace('_','',$college_key));
			$infos["degustateurs"][$libelle] = array();
			$infos["degustateurs"][$libelle]['confirmes'] = $this->getNbDegustateursStatutWithCollege(true,$college_key);
			$infos["degustateurs"][$libelle]['total'] = count($this->degustateurs->getOrAdd($college_key));
			$infos["degustateurs"][$libelle]['key'] = "nb".$collegeVar;
		}
		$tables = $this->getTablesWithFreeLots();
		$infos["nbTables"] = count($tables);
		$infos["nbFreeLots"] = count($this->getFreeLots());
		$infos["nbLotsAnonymises"] = count($this->getLotsAnonymized());
		$infos["nbLotsConformes"] = $this->getNbLotsConformes();
		$infos["nbLotsNonConformes"] = $this->getNbLotsNonConformes();
		return $infos;
	}

    /**** MOUVEMENTS LOTS ****/

    public function clearMouvementsLots(){
        $this->remove('mouvements_lots');
        $this->add('mouvements_lots');
    }

    public function addMouvementLot($mouvement) {

        return $this->mouvements_lots->add($mouvement->declarant_identifiant)->add($mouvement->getUnicityKey(), $mouvement);
    }

	public function fillDocToSaveFromLots() {
		foreach ($this->lots as $lot) {
            if(!$lot->id_document_provenance) {
                continue;
            }
            $this->docToSave[$lot->id_document_provenance] = $lot->id_document_provenance;
        }
	}

    public function saveDocumentsDependants() {
        $this->fillDocToSaveFromLots();
        foreach($this->docToSave as $docId) {
            DeclarationClient::getInstance()->findCache($docId)->save(false);
        }

        $this->docToSave = array();
        DeclarationClient::getInstance()->clearCache();
    }

	public function getLot($uniqueId) {

        foreach($this->lots as $lot) {
            if($lot->getUniqueId() != $uniqueId) {

                continue;
            }

            return $lot;
        }

        return null;
    }

    public function generateMouvementsLots()
    {
        $this->clearMouvementsLots();

        foreach ($this->lots as $lot) {
            if ($lot->isLeurre()) {
                continue;
            }
            $lot->updateDocumentDependances();
            $lot->updateSpecificiteWithDegustationNumber();
			$statut = $lot->statut;
			if ($statut == Lot::STATUT_NONCONFORME_LEVEE) {
				$this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONCONFORME_LEVEE));
				$statut = Lot::STATUT_NONCONFORME;
			}
            if ($lot->conforme_appel) {
                $statut = Lot::STATUT_CONFORME_APPEL;
            }elseif ($lot->recours_oc) {
                $statut = Lot::STATUT_RECOURS_OC;
            }
            switch($statut) {
                case Lot::STATUT_CONFORME_APPEL:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CONFORME_APPEL, null, $lot->conforme_appel));
                case Lot::STATUT_RECOURS_OC:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_RECOURS_OC, null, $lot->recours_oc));
                    $statut = Lot::STATUT_NONCONFORME;
                case Lot::STATUT_CONFORME:
                case Lot::STATUT_NONCONFORME:
                    $detail = $lot->getShortLibelleConformite();
                    if($statut == Lot::STATUT_NONCONFORME){
                        $detail .= ($lot->motif)? " : ".$lot->motif : "";
                    }
                    $this->addMouvementLot($lot->buildMouvement($statut,$detail));
                    if ($lot->isChange()) {
                        $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_SRC, $lot->getLibelle()));
                    }else{
                        $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGEABLE));
                    }

                case Lot::STATUT_DEGUSTE:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_DEGUSTE));

                case Lot::STATUT_ANONYMISE:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ANONYMISE,"N° anon. : ".$lot->numero_anonymat));

                case Lot::STATUT_ATTABLE:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ATTABLE,"Table : ".$lot->getNumeroTableStr()));

                case Lot::STATUT_PRELEVE:
                case Lot::STATUT_ATTENTE_PRELEVEMENT:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT));

                case Lot::STATUT_AFFECTE_DEST:
					$ordre = intval($lot->document_ordre) - 1;
					$detail = sprintf("%dme passage", $ordre);
					if ($ordre == 1) {
						$detail = "1er passage";
					}
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_DEST, $detail));

                default:
                    break;
            }
            if ($lot->isPreleve()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_PRELEVE, '', $lot->preleve));
            }
			if ($lot->isChange()) {
				continue;
			}
			if ($lot->isAffecte()) {
				$this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC, ($lot->getNombrePassage() + 1).'ème passage'));
			}elseif($lot->isAffectable()) {
				$this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE, ($lot->getNombrePassage() + 1).'ème passage'));
			} elseif(in_array($lot->statut, array(Lot::STATUT_NONCONFORME, Lot::STATUT_RECOURS_OC))) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE));
            }
        }
    }

    /**** FIN DES MOUVEMENTS LOTS ****/

    /**** PIECES ****/

    public function getAllPieces() {
    	$pieces = array();

        $base_libelle = 'Résultat de la dégustation du ' . $this->getDate();

        $declarants = [];

        foreach ($this->lots as $lot) {
			if (!$lot->email_envoye) {
				continue;
			}
            if (! $lot->isNonConforme()) {
                if (in_array($lot->declarant_identifiant, $declarants) === true) {
                    continue;
                }

                $declarants[] = $lot->declarant_identifiant;
                $libelle = $base_libelle . ' - Conformités';
            } else {
                $libelle = $base_libelle . ' - Non conformite du lot '. $lot->unique_id;
            }

            $pieces[] = [
                'identifiant' => $lot->declarant_identifiant,
                'date_depot' => $lot->email_envoye,
                'libelle' => $libelle,
                'mime' => Piece::MIME_PDF,
                'visibilite' => 1,
                'source' => (! $lot->isNonConforme()) ? 'conforme'.$lot->declarant_identifiant : $lot->unique_id
            ];
        }

    	return $pieces;
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
        if (strpos($source, 'conforme') === 0) {
            $url =  'degustation_conformite_pdf' ;
            $param = ['id' => $this->_id, 'identifiant' => str_replace('conforme', '', $source)];
        } else {
            $lot = explode('-', $source);
            $url = 'degustation_non_conformite_pdf';
            $param = ['id' => $this->_id, 'lot_dossier' => $lot[2], 'lot_archive' => $lot[3]];
        }
        return sfContext::getInstance()->getRouting()->generate($url, $param);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
        return sfContext::getInstance()->getRouting()->generate('degustation_visualisation', array('id' => $id, 'identifiant' => '1300000401'));
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

    public function addLot($lotOrig, $update = true)
    {
        if (!$this->_id) {
            throw new sfException("Pour ajouter un lot, il faut avoir un id à notre degustation");
        }
        $lotOrig = DegustationClient::getInstance()->cleanLotForDegustation($lotOrig);

        if (property_exists($lotOrig, 'leurre') && ! $lotOrig->leurre) {
            $this->docToSave[$lotOrig->id_document] = $lotOrig->id_document;
        }

        $lot = $this->lots->add(null, $lotOrig);
        $lot->date = $this->date;
        $lot->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
        $lot->id_document_provenance = $lotOrig->id_document;
        $lot->id_document_affectation = null;
        $lot->id_document = $this->_id;
        $lot->affectable = false;
        $lot->numero_anonymat = null;
		$lot->email_envoye = null;
        $lot->preleve = null;
        $lot->motif = null;
        $lot->conformite = null;
        if ((get_class($lotOrig) != 'stdClass' && $lotOrig->document_ordre) ||
                isset($lotOrig->document_ordre)) {
            $lot->document_ordre = sprintf('%02d', intval($lotOrig->document_ordre) + 1 );
        }
        if($update) {
            $lot->updateSpecificiteWithDegustationNumber();
            $lot->updateDocumentDependances();
        }
        return $lot;
    }

    public function setLots($lots)
    {
         $this->fillDocToSaveFromLots();

		 $this->remove('lots');
		 $this->add('lots');

        foreach($lots as $key => $lot) {
            $this->addLot($lot);
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

	public function getAdherentsPreleves(){
		$adherents = array();
		foreach ($this->getLots() as $lot) {
				if($lot->isPreleve()){
					$adherents[$lot->getDeclarantIdentifiant()] = $lot->getDeclarantIdentifiant();
				}
		}
	 return $adherents;
 }

	 public function getNbLotsWithStatut($statut = null, $including_leurre = true){
			return count($this->getLotsWithStatut($statut,$including_leurre));
	 }

	 public function getLotsWithStatut($statut = null, $including_leurre = true){
		 if(!$statut){
			 return array();
		 }
		 $lots = array();
		 foreach ($this->getLots() as $lot) {
				if(!$including_leurre && $lot->isLeurre()){
					continue;
				}
				if($lot->getMouvement($statut)){
					$lots[] = $lot;
				}
			}
			return $lots;
	 }

    public function getLotsByOperateurs($identifiant = null)
    {
        $lots = [];
        foreach ($this->getLots() as $lot) {
            if ($lot->isLeurre()) {
                continue;
            }

            if ($identifiant && $lot->declarant_identifiant !== $identifiant) {
                continue;
            }

            if (array_key_exists($lot->declarant_identifiant, $lots) === false) {
                $lots[$lot->declarant_identifiant] = [];
            }

            $lots[$lot->declarant_identifiant][] = $lot;
        }

        return $lots;
    }

    public function getLotsConformes($identifiant = null)
    {
        $all_lots = $this->getLotsByOperateurs($identifiant);
        $conformes = [];

        foreach ($all_lots as $operateur => $lots) {
            $conformes[$operateur] = [];
            foreach ($lots as $lot) {
                if ($lot->statut === Lot::STATUT_CONFORME) {
                    $conformes[$operateur][] = $lot;
                }
            }
        }

        return $conformes;
    }

    public function getLotsNonConformes($identifiant = null)
    {
        $all_lots = $this->getLotsByOperateurs($identifiant);
        $nonconformes = [];

        foreach ($all_lots as $operateur => $lots) {
            $nonconformes[$operateur] = [];
            foreach ($lots as $lot) {
                if ($lot->statut === Lot::STATUT_NONCONFORME) {
                    $nonconformes[$operateur][] = $lot;
                }
            }
        }

        return $nonconformes;
    }

	 public function getLotsByOperateursAndConformites(){
		 $lotsByAdherents = array();
		 $conformiteArray = array(Lot::STATUT_CONFORME,Lot::STATUT_NONCONFORME);
		 foreach ($conformiteArray as $bool => $conformite) {
			 foreach ($this->getLotsConformesOrNot(!$bool) as $lot) {
				 if($lot->isLeurre()){
					 continue;
				 }
				 if(!array_key_exists($lot->getDeclarantIdentifiant(),$lotsByAdherents)){
					 $lotsByAdherents[$lot->getDeclarantIdentifiant()] = new stdClass();
					 $lotsByAdherents[$lot->getDeclarantIdentifiant()]->declarant_nom = $lot->declarant_nom;
                     $lotsByAdherents[$lot->getDeclarantIdentifiant()]->email_envoye = $lot->email_envoye;
                     if(!$lot->email_envoye){
                         $lotsByAdherents[$lot->getDeclarantIdentifiant()]->email_envoye = false;
                     }
					 $lotsByAdherents[$lot->getDeclarantIdentifiant()]->lots = array();
					}
					if(!array_key_exists($conformite,$lotsByAdherents[$lot->getDeclarantIdentifiant()]->lots)){
						$lotsByAdherents[$lot->getDeclarantIdentifiant()]->lots[$conformite] = array();
 					}
				 $lotsByAdherents[$lot->getDeclarantIdentifiant()]->lots[$conformite][$lot->getUnicityKey()] = $lot;
				 if($lotsByAdherents[$lot->getDeclarantIdentifiant()]->email_envoye === false){
                     $lotsByAdherents[$lot->getDeclarantIdentifiant()]->email_envoye = false;
                 }
			 }
		 }
		return $lotsByAdherents;
	 }

	 public function getLotsConformitesOperateur($identifiant){
		$lotsByAdherents = $this->getLotsByOperateursAndConformites();
	 return $lotsByAdherents[$identifiant];
	}

	 public function getLotByNumArchive($numero_archive){
		 foreach ($this->lots as $lot) {
			 if($lot->numero_archive == $numero_archive){
				 return $lot;
			 }
		 }
		 return null;
	 }

	 public function getNbLotsRestantAPreleve(){
		 return (count($this->getLots()) - $this->getNbLotsPreleves());
	 }

	 public function getLotsDegustes(){
		 return array_merge($this->getLotsWithStatut(Lot::STATUT_CONFORME,false),$this->getLotsWithStatut(Lot::STATUT_NONCONFORME,false));
	 }


	 public function getNbLotsPreleves(){
		 return count($this->getLotsPreleves());
	 }

	 public function getNbLotsConformes(){

			return count($this->getLotsConformesOrNot(true));
	 }

	 public function getNbLotsNonConformes(){

		 return count($this->getLotsConformesOrNot(false));
	 }

	 public function getLotsConformesOrNot($conforme = true){
		 $lots = array();
		 foreach ($this->getLotsDegustes() as $lot) {
			 if($conforme && $lot->exist('conformite') && $lot->conformite == Lot::CONFORMITE_CONFORME){
				 $lots[] = $lot;
			 }
			 if(!$conforme && $lot->isNonConforme()){
				 $lots[] = $lot;
			 }
		 }
		 return $lots;
	 }

    /**** FIN DES PIECES ****/


		/**** Gestion des tables de la degustation ****/

		public function getLotsPreleves() {
	   		$lots = array();
	   		foreach ($this->getLots() as $lot) {
                if ($lot->isLeurre()) {
                    $lots[] = $lot;
                    continue;
                }

                if(! $lot->getMouvement(Lot::STATUT_PRELEVE)) {
                    continue;
                }

                $lots[] = $lot;
	   		}
			return $lots;
		}

		public function getLotsPrelevesCustomSort(array $tri = null) {
			$lots = $this->getLotsPreleves();
			if (!$tri) {
				$tri = explode('|', $this->tri);
			}
			$this->array_tri = $tri;
	   		uasort($lots, array($this, "sortLotsByThisTri"));
	   		return $lots;
   	 	}

		public function getFreeLots(){
			$freeLots = array();
			foreach ($this->getLotsPreleves() as $lot) {
				if(! $lot->exist('numero_table') || (!$lot->numero_table && $lot->isIgnored())){
					$freeLots[] = $lot;
				}
			}
			return $freeLots;
		}

		public function getTablesWithFreeLots(){
			$tables = array();
			$freeLots = $this->getFreeLots();
			foreach ($this->lots as $lot) {
				if($lot->exist('numero_table') && $lot->numero_table && !$lot->isIgnored()){
					if(!isset($tables[$lot->numero_table])){
						$tables[$lot->numero_table] = new stdClass();
						$tables[$lot->numero_table]->lots = array();
						$tables[$lot->numero_table]->freeLots = $freeLots;
					}
					$tables[$lot->numero_table]->lots[] = $lot;
				}
			}
            ksort($tables);
			return $tables;
		}

		public function getLotsWithoutLeurre(){
			$lots = array();
			foreach ($this->lots as $lot) {
					if ($lot->leurre === true) {
							continue;
					}
					$lots[] = $lot;
			}
			return $lots;
		}

        public function getLotsFromProvenance() {
            $lots = array();
            foreach($this->getLots() as $lot) {
                if ($lot->isLeurre()) {
                    continue;
                }
                $lots[$lot->unique_id] = DegustationClient::getInstance()->cleanLotForDegustation($lot->getLotProvenance()->getData());
				$lots[$lot->unique_id]->specificite = $lot->specificite;
				$lots[$lot->unique_id]->statut = $lot->statut;
            }
            return $lots;
        }

		public function getLotsByTable($numero_table){
			$lots = array();
			foreach ($this->getLots() as $lot) {
				if(intval($lot->numero_table) == $numero_table){
					$lots[] = $lot;
				}
			}
			$this->array_tri = ['numero_anonymat'];
			usort($lots, array($this, "sortLotsByThisTri"));
 		 	return $lots;
		}

        public function getLotsNonAnonymisable(){
            $lotsNonAnonymisable = array();
            foreach ($this->getLots() as $lot) {
            if(!$lot->isAnonymisable())
                $lotsNonAnonymisable[$lot->getHash()] = $lot;
            }

            return $lotsNonAnonymisable;
        }

        public function cleanLotsNonAnonymisable(){
			$this->fillDocToSaveFromLots();
            foreach ($this->getLotsNonAnonymisable() as $hashLot => $lot) {
                $this->remove($hashLot);
            }
			$this->generateMouvementsLots();
        }

		public function getTri() {
			$tri = $this->_get('tri');
			if (!$tri) {
				$tri = 'Couleur|Appellation|Cépage';
			}
			return $tri;
		}

		public function anonymize(){
            $this->cleanLotsNonAnonymisable();

			for($table = 1; true ; $table++) {
				$lots = $this->getLotsByTable($table);
				if (!count($lots)) {
					break;
				}
                $this->array_tri = explode('|', $this->tri);
				usort($lots, array($this, 'sortLotsByThisTri'));
				foreach ($lots as $k => $lot){
					if ($lot->numero_anonymat) {
						throw new sfException("L'anonymat a déjà été réalisé");
					}

                    $lot->anonymize($k);
				}
			}

            $this->generateMouvementsLots();
            // MouvementsFacture => On ne génére les mouvements de facture qu'a l'anonymat et la mise en non conformité
			$this->generateMouvementsFactures();
		}

		public function desanonymize(){
			for($table = 1; true ; $table++) {
				$lots = $this->getLotsByTable($table);
				if (!count($lots)) {
					break;
				}
				foreach ($lots as $k => $lot){
					if ($lot->numero_anonymat){
						$lot->numero_anonymat = null;
					}
				}
			}

            $this->generateMouvementsLots();
			$this->clearMouvementsFactures();
		}

		public function isAnonymized(){
			for($table = 1; true ; $table++) {
				$lots = $this->getLotsByTable($table);
				if (!count($lots)) {
					return false;
				}
				foreach ($lots as $k => $lot){
					if ($lot->numero_anonymat) {
					return true;
					}
				}
			}
			return false;
		}

        public function getLotsAnonymized(){
            $lotsAnon = array();
            foreach ($this->getLots() as $k => $lot){
                if (!$lot->leurre && $lot->numero_anonymat) {
                    $lotsAnon[$lot->numero_anonymat] = $lot;
                }
            }
            return $lotsAnon;
        }

		public function getLotsTableOrFreeLots($numero_table, $free = true){
			$lots = array();
			foreach ($this->getLotsPreleves() as $lot) {
				if(($lot->numero_table == $numero_table)){
					$lots[] = $lot;
					continue;
				}

				if($free && !$lot->numero_table)  {
					$lots[] = $lot;
					continue;
				}
			}
			return $lots;
		}

		public function getLotsTableOrFreeLotsCustomSort($numero_table, $free = true){
			$lots = $this->getLotsTableOrFreeLots($numero_table, $free);
			$this->array_tri = $this->getTriArray();
			uasort($lots, array($this, 'sortLotsByThisTri'));
			return $lots;
		}

		public function setTri($t) {
			$this->_set('tri', $t);
			$this->updatePositionLots();
		}

		public function updatePositionLots() {
            $t = 0; $i = 0;
            foreach($this->getTablesWithFreeLots() as $table) {
                $t++;
                foreach($this->getLotsTableOrFreeLotsCustomSort($t) as $lot) {
                    $i++;
                    $lot->position = sprintf("%d0%02d0", $t, $i);
                }
            }
		}

		public function hasFreeLots(){
			foreach ($this->getLotsPreleves() as $lot) {
				if(!$lot->exist("numero_table") || is_null($lot->numero_table)){
					return true;
				}
			}
			return false;
		}

		public function getSyntheseLotsTable($numero_table = null){
			$lots = $this->getLotsPreleves();
			$syntheseLots =  $this->createSynthesFromLots($lots, $numero_table);
			ksort($syntheseLots);
			return $syntheseLots;
		}

        public function getTriArray() {
            return explode('|', strtolower($this->tri));

        }

        public function getSyntheseLotsTableCustomTri($numero_table = null){
            $tri_array = $this->getTriArray();
            if (($key = array_search('manuel', $tri_array)) !== false) {
                unset($tri_array[$key]);
            }
            $lots = $this->getLotsPrelevesCustomSort($tri_array);
            return $this->createSynthesFromLots($lots, $numero_table, $tri_array);
        }

		private function createSynthesFromLots($lots, $numero_table, array $tri = null) {
			$syntheseLots = array();
			foreach ($lots as $lot) {
				if($lot->numero_table == $numero_table || is_null($numero_table) || is_null($lot->numero_table)){
					if(!array_key_exists($lot->getTriHash($tri),$syntheseLots)){
						$synthese = new stdClass();
						$synthese->lotsTable = array();
						$synthese->lotsFree = array();
						$synthese->libelle = $lot->getTriLibelle($tri);
						$synthese->details = '';
						if (!$tri || in_array('Cépage', $tri)) {
							$synthese->details = $lot->getDetails();
						}
						$synthese->millesime = $lot->getMillesime();

						$syntheseLots[$lot->getTriHash($tri)] = $synthese;
					}
					if($lot->numero_table == $numero_table || (is_null($numero_table) && $lot->numero_table)){
						$syntheseLots[$lot->getTriHash($tri)]->lotsTable[] = $lot;
					}else{
						$syntheseLots[$lot->getTriHash($tri)]->lotsFree[] = $lot;
					}
				}
			}
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

    public function sortLotsByThisTri($a, $b){
			$a_data = '';
			$b_data = '';
			foreach($this->array_tri as $t) {
				$a_data .= $a->getValueForTri($t);
				$b_data .= $b->getValueForTri($t);
				if ( $this->array_tri == ['numero_anonymat']){
					$cmp = $a_data-$b_data;
					if ($cmp !=0) {
						return $cmp;
					}
				}
				else{
					$cmp = strcmp($a_data, $b_data);
					if ($cmp) {
					return $cmp;
					}
				}
			}
      return 0;
      }
    public function addLeurre($hash, $cepages, $numero_table)
        {
            if (! $this->exist('lots')) {
                $this->add('lots');
            }
            $leurre = $this->lots->add();
            $leurre->leurre = true;
            $leurre->numero_table = $numero_table;
            $leurre->setProduitHash($hash);
            $leurre->details = $cepages;
            $leurre->declarant_nom = "LEURRE";
            $leurre->statut = Lot::STATUT_NONPRELEVABLE;

            return $leurre;
        }

    public function getLeurres()
    {
        $leurres = [];
        foreach ($this->getLots() as $lot) {
            if ($lot->isLeurre()) {
                $leurres[] = $lot;
            }
        }

        return $leurres;
    }

		public function ignorerLot($lot){
			$lot->numero_table = Lot::TABLE_IGNORE;
			return $lot;
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
						$degustateursByCollege[$college][$compte_id] = ($degustateur->exist('confirmation') && !is_null($degustateur->confirmation) && $degustateur->confirmation);
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

		public function getDegustateursConfirmesTableOrFreeTable($numero_table = null){
			$degustateurs = array();
			foreach ($this->getDegustateursConfirmes() as $id => $degustateur) {
				if(($degustateur->exist('numero_table') && $degustateur->numero_table == $numero_table)
					|| (!$degustateur->exist('numero_table') || is_null($degustateur->numero_table))){
					$degustateurs[$id] = $degustateur;
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

		public function getLotsNonAttables(){
			$non_attables = array();
			foreach ($this->getLotsPreleves() as $lot) {
				if($lot->numero_table && !$lot->isIgnored())
					continue;
				$non_attables[] = $lot;
			}
			return $non_attables;
		}

		public function addDegustateur($compteId, $college, $numTab){
			$this->getOrAdd('degustateurs');
			$compte = CompteClient::getInstance()->find($compteId);
			$degustateur = $this->degustateurs->getOrAdd($college)->getOrAdd($compteId);
			$degustateur->getOrAdd('libelle');
			$degustateur->libelle = $compte->getLibelleWithAdresse();

			if($numTab !== false){
				if($numTab !== null){
					$degustateur->getOrAdd('numero_table');
					$degustateur->numero_table = $numTab;
				}
				$degustateur->getOrAdd('confirmation');
				$degustateur->confirmation = true;
			}

		}

		public function setDateEmailConvocationDegustateur($date, $compteId, $college) {
			$this->getOrAdd('degustateurs');
			$degustateur = $this->degustateurs->getOrAdd($college)->getOrAdd($compteId);
			$degustateur->getOrAdd('email_convocation');
			$degustateur->email_convocation = $date;
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

		/**** Gestion PDF ****/

		public function getEtiquettesFromLots($maxLotsParPlanche){
			$nbLots = 0;
			$planche = 0;
			$etiquettesPlanches = array();
			$etablissements = array();
			$produits = array();
			$lots = array();

			foreach ($this->getLots() as $key => $lot) {
				if($lot->leurre)
					continue;
				$lots[$lot->campagne.$lot->numero_dossier.$lot->declarant_identifiant.$lot->unique_id] = $lot;
			}
			$lotsSorted = $lots;
            ksort($lotsSorted);
			foreach ($lotsSorted as $lot) {
				if($nbLots > $maxLotsParPlanche){
					$planche++;
					$nbLots = 0;
				}
				if(!array_key_exists($planche,$etiquettesPlanches)){
					$etiquettesPlanches[$planche] = array();
				}

				if(!array_key_exists($lot->declarant_identifiant,$etablissements)){
					$etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
				}

				if(!array_key_exists($lot->produit_hash,$produits)){
					$produits[$lot->produit_hash] = $lot->getConfig()->getCouleur()->getLibelle();
				}

				$infosLot = new stdClass();
				$infosLot->lot = $lot;
				$infosLot->etablissement = $etablissements[$lot->declarant_identifiant];
				$infosLot->couleur = $produits[$lot->produit_hash];
				$etiquettesPlanches[$planche][] = $infosLot;
				$nbLots++;
			}
			return $etiquettesPlanches;
		}

		public function getAllLotsTables(){
			$tables = $this->getTablesWithFreeLots();
      $allTablesLots = array();
      foreach ($tables as $key => $value) {
        foreach ($value as $lot) {
          if(!$lot)
            continue;
          $allTablesLots = array_merge($allTablesLots, $lot);
        }

      }
			return $allTablesLots;
		}

		public function getLotTableBySlice($slice){
			$allTablesLots = $this->getAllLotsTables();
			$lotsBySlice = array();
			$cpt = 0;
			$n = intval(count($allTablesLots)/$slice);
			foreach ($allTablesLots as $key => $lot) {
				if($cpt < $slice){
					$cpt++;
				}else {
					$n--;
					$cpt = 1;
				}
				$lotsBySlice[$n][] = $lot;
			}
			return $lotsBySlice;
		}

		public function getLotsByNumDossier(){ //Incompréhensible
			$lots = array();
			foreach ($this->getLotsSortByTables() as $numTab => $lot) {
					$lots[$lot->numero_dossier][$lot->numero_anonymat] = $lot;
			}

			return $lots;
		}

		public function getLotsByNumDossierNumLogementOperateur(){
			$lots = array();
			foreach ($this->getLots() as  $lot) {
			  $lots[$lot->numero_dossier][$lot->numero_logement_operateur] = $lot;
			}

			return $lots;
		}

        public function getLotByNumDossierNumLogementOperateur($numero_dossier, $numero_logement_operateur){
            $allLots = $this->getLotsByNumDossierNumLogementOperateur();
            if(!isset($allLots[$numero_dossier])){
                return null;
            }
            if(!isset($allLots[$numero_dossier][$numero_logement_operateur])){
                return null;
            }
			return $allLots[$numero_dossier][$numero_logement_operateur];
		}

        public function getLotsByNumDossierNumArchive(){
            $lots = array();
            foreach ($this->getLots() as $lot) {
              $lots[$lot->numero_dossier][$lot->numero_archive] = $lot;
            }

            return $lots;
        }

        public function getLotByNumDossierNumArchive($numero_dossier, $numero_archive){
            $allLots = $this->getLotsByNumDossierNumArchive();
            if(!isset($allLots[$numero_dossier])){
                return null;
            }
            if(!isset($allLots[$numero_dossier][$numero_archive])){
                return null;
            }
            return $allLots[$numero_dossier][$numero_archive];
        }

		public function getOdg(){
			return sfConfig::get('sf_app');
		}

		public function getNomOrganisme(){
			return sfConfig::get('app_organisme_nom', array());
		}

		public function getCoordonnees() {

            return FactureConfiguration::getInstance()->getInfs();
        }

		public function getLotsSortByTables(){
			$lots = array();
			for($numTab=1; $numTab <= $this->getLastNumeroTable(); $numTab++) {
				$table = chr($numTab+64);

				foreach ($this->getLotsByTable($numTab) as $key => $lot) {

					$lots[] = $lot;
				}
			}
            $this->array_tri = $this->getTriArray();
            usort($lots, array($this, "sortLotsByThisTri"));
			return $lots;
		}

		public function getComptesDegustateurs(){
			$arrayAssocDegustCompte = array();
			foreach ($this->getDegustateursStatutsParCollege() as $college => $degs) {
				if(count($degs)){
					foreach ($degs as $id_compte => $value) {
						$compte = CompteClient::getInstance()->findByIdentifiant($id_compte);
						$arrayAssocDegustCompte[$college][$id_compte] = $compte;
					}
				}
			}
			return $arrayAssocDegustCompte;
		}

		public function getVolumeLotsConformesOrNot($conforme = true){
			$volume = 0;
			foreach ($this->getLotsDegustes() as $lot) {
				if($conforme && $lot->exist('conformite') && $lot->conformite == Lot::CONFORMITE_CONFORME){
					$volume += $lot->volume;
				}
				if(!$conforme && $lot->isNonConforme()){
					$volume += $lot->volume;
				}
			}
			return $volume;
		}

		public function getEtablissementLotsConformesOrNot($conforme = true){
			$etablissements = array();

			foreach ($this->getLotsDegustes() as $lot) {
				$etablissement = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
				if($conforme && $lot->exist('conformite') && $lot->conformite == Lot::CONFORMITE_CONFORME){
					$etablissements[$lot->declarant_identifiant] = $etablissement;
				}
				if(!$conforme && $lot->isNonConforme()){
					$etablissements[$lot->declarant_identifiant] = $etablissement;
				}
			}
			return $etablissements;
		}

        public function isMailEnvoyeEtablissement($identifiant)
        {
            foreach ($this->getLotsByOperateurs($identifiant) as $operateur => $lots) {
                foreach ($lots as $lot) {
                    if (! $lot->email_envoye) {
                        return false;
                    }
                }
            }
            return true;
		}

		public function setMailEnvoyeEtablissement($identifiant, $date){
            foreach ($this->getLotsByOperateurs($identifiant) as $operateur => $lots) {
                foreach ($lots as $lot) {
                    $lot->email_envoye = $date;
                }
            }
		}

		public function getLotsDegustesByAppelation(){
			$degust = array();
			foreach ($this->getLotsDegustes() as $key => $lot) {
				$degust[$lot->getConfig()->getAppellation()->getLibelle()][] = $lot;
			}

			return $degust;
		}

		public function getNbLotByTypeFilteredByNumDossier($declarant_identifiant, $numDossier){
			$lotsByType = array();
			foreach ($this->getLotsByOperateurs($declarant_identifiant) as $lots) {
                foreach ($lots as $lot) {
                    if($lot->numero_dossier == $numDossier){
                        $lotsByType[$lot->getTypeProvenance()] +=1;
                    }
                }
			}
			return $lotsByType;
		}


        /**** MOUVEMENTS ****/

        public function getTemplateFacture() {

            return TemplateFactureClient::getInstance()->findByCampagne($this->campagne);
        }

        public function getMouvementsFactures() {

            return $this->_get('mouvements');
        }

        public function getMouvementsFacturesCalcule() {
          $templateFacture = $this->getTemplateFacture();

          if(!$templateFacture) {
              return array();
          }
          $cotisations = $templateFacture->generateCotisations($this);
          $mouvements = array();

          foreach($cotisations as $cotisation) {
			  if(!$cotisation->getConfigCallback()){
				  continue;
			  }
              $parameters = array_merge(array($cotisation),$cotisation->getConfigCallbackParameters());
              $mvts = call_user_func_array(array($this, $cotisation->getConfigCallback()), $parameters);
              foreach ($mvts as $identifiant => $mvtsArray) {
                  foreach ($mvtsArray as $key => $value) {
                      $mouvements[$identifiant][$key] = $value;
                  }
              }
          }

          return $mouvements;
        }


        public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {

            return $this->mouvement_document->getMouvementsFacturesCalculeByIdentifiant($identifiant);
        }

        public function generateMouvementsFactures() {
            return $this->mouvement_document->generateMouvementsFactures();
        }

        public function findMouvementFactures($cle, $id = null){
          return $this->mouvement_document->findMouvementFactures($cle, $id);
        }

        public function facturerMouvements() {

            return $this->mouvement_document->facturerMouvements();
        }

        public function isFactures() {

            return $this->mouvement_document->isFactures();
        }

        public function isNonFactures() {

            return $this->mouvement_document->isNonFactures();
        }

        public function clearMouvementsFactures(){
            $this->remove('mouvements');
            $this->add('mouvements');
        }

        /**** FIN DES MOUVEMENTS ****/

        /*** ARCHIVAGE ***/

        public function getNumeroArchive() {

            return $this->_get('numero_archive');
        }

        public function isArchivageCanBeSet() {

            return true;
        }

		protected function preSave() {
			$this->archivage_document->preSave();
		}

        /*** FIN ARCHIVAGE ***/

		/**** Fonctions de facturation ****/

        private function creationMouvementFactureFromLot($cotisation, $lot){

            $mouvement = DegustationMouvementFactures::freeInstance($this);
			$mouvement->detail_identifiant = $lot->numero_dossier;
            $mouvement->createFromCotisationAndDoc($cotisation, $this);
            $mouvement->date = $this->getDateFormat();
            $mouvement->date_version = $this->getDateFormat();

            return $mouvement;
        }

        public function getRedegustationForfait($cotisation,$filters = null){
            return $this->buildMouvementsFacturesRedegustationForfait($cotisation,$filters);
        }
	    public function buildMouvementsFacturesRedegustationForfait($cotisation,$filters = null){
            $mouvements = array();
			foreach ($this->getLots() as $lot) {
                if(!$lot->isSecondPassage()){
                    continue;
                }
                $mouvements[$lot->declarant_identifiant]["NUMERO_PASSAGE_".$lot->getNombrePassage()] = $this->creationMouvementFactureFromLot($cotisation, $lot);
            }
            return $mouvements;
	    }

        public function getFacturationLotRedeguste($cotisation,$filters = null){
            return $this->buildMouvementsFacturesLotRedeguste($cotisation, $filters);
        }
        public function buildMouvementsFacturesLotRedeguste($cotisation,$filters = null){
            $mouvements = array();
            $keyCumul = $cotisation->getDetailKey();
            foreach ($this->getLotsPreleves() as $lot) {
                if(!$lot->isSecondPassage()){
                    continue;
                }
                $mvtFacture = $this->creationMouvementFactureFromLot($cotisation, $lot);
                $mvtFacture->detail_identifiant = $lot->getNumeroDossier();
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey()] = $mvtFacture;
            }

            return $mouvements;
        }

		public function getFacturationVolumeRedeguste($cotisation,$filters = null){
            return $this->buildMouvementsFacturesVolumeRedeguste($cotisation, $filters);
        }
        public function buildMouvementsFacturesVolumeRedeguste($cotisation,$filters = null){
			$mouvements = array();
			$keyCumul = $cotisation->getDetailKey();
			foreach ($this->getLotsPreleves() as $lot) {
				if(!$lot->isSecondPassage()){
					continue;
				}
				$mvtFacture = DegustationMouvementFactures::freeInstance($this);
				$mvtFacture->detail_identifiant = $lot->numero_dossier;
				$mvtFacture->createFromCotisationAndDoc($cotisation, $this);
				$mvtFacture->date = $this->getDateFormat();
				$mvtFacture->date_version = $this->getDateFormat();
				$mvtFacture->quantite = $lot->volume;
				$mouvements[$lot->declarant_identifiant][$lot->getUnicityKey()] = $mvtFacture;
			}

			return $mouvements;
		}

        public function getForfaitConditionnement($cotisation){
            return $this->buildMouvementsFacturesForfaitConditionnement($cotisation);
        }
		public function buildMouvementsFacturesForfaitConditionnement($cotisation){
            $mouvements = array();
            $keyCumul = $cotisation->getDetailKey();
            foreach ($this->getLotsPreleves() as $lot) {
                if(strpos($lot->id_document_provenance, 'CONDITIONNEMENT') !== 0){
                    continue;
                }
                $mvtFacture = $this->creationMouvementFactureFromLot($cotisation, $lot);
                $mvtFacture->detail_identifiant = $lot->getNumeroDossier();
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey()] = $mvtFacture;
            }

            return $mouvements;
        }

        public function getFacturationNonConforme($cotisation,$filters = null) {
            return $this->buildMouvementsFacturesNonConforme($cotisation,$filters);
        }
        public function buildMouvementsFacturesNonConforme($cotisation,$filters = null) {
            $mouvements = array();
            $keyCumul = $cotisation->getDetailKey();
            foreach ($this->getLots() as $lot) {
                if($lot->conformite && ($lot->conformite != Lot::CONFORMITE_CONFORME)){
                    if(isset($mouvements[$lot->declarant_identifiant]) && isset($mouvements[$lot->declarant_identifiant][$keyCumul])){
                        $mouvements[$lot->declarant_identifiant][$keyCumul]->quantite++;
                        continue;
                    }
					$mouvements[$lot->declarant_identifiant] = array();
                    $mouvements[$lot->declarant_identifiant][$keyCumul] = $this->creationMouvementFactureFromLot($cotisation, $lot);
                }
            }

            return $mouvements;
        }


        /** Mis à jour par la degustation du volume d'un lot de DRev **/
		public function modifyVolumeLot($hash_lot,$volume){

			$lot = $this->get($hash_lot);

			// Drev => modificatrice + changement dans Drev
			$lotDrevOriginal = $lot->getLotProvenance();
            $lotDrevOriginalToSave = clone $lotDrevOriginal;

			// $modificatrice
			$modificatrice = $lotDrevOriginal->getDocument()->generateModificative();
			$modificatrice->save();

			$modificatrice = DRevClient::getInstance()->find($modificatrice->_id);


		    $lotModificatrice = $modificatrice->get($lotDrevOriginal->getHash());
            $lotModificatrice->volume = $volume;
            $lotModificatrice->statut = Lot::STATUT_PRELEVABLE;

            $modificatrice->validate();
			$modificatrice->validateOdg();
			$modificatrice->save();

			$lot->volume = $volume;
		}
}
