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
    public $generateMouvementsFacturesOnNextSave = false;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

	public function getRegion() {
		if(!$this->exist('region')) {

			return '';
		}

		return $this->_get('region');
	}

    public function getRegions()
    {
        if (DegustationConfiguration::getInstance()->hasStaticRegion()) {
            return [DegustationConfiguration::getInstance()->getStaticRegion()];
        }

		return explode('|', $this->getRegion());
    }

    public function getRegionsFromProduits()
    {
		$regions = [];
		foreach($this->lots as $lot) {
			$regions[] = RegionConfiguration::getInstance()->getOdgRegion($lot->produit_hash);
		}

        return array_unique($regions);
	}

    public function getRegionsFromLotsDegustables()
    {
        $regions = [];
        foreach($this->getLotsDegustables() as $lot) {
            $regions[] = RegionConfiguration::getInstance()->getOdgRegion($lot->produit_hash);
        }

        return array_unique($regions);
    }

    public function getDateFormat($format = 'Y-m-d') {
        if (!$this->date) {
            return date($format);
        }
        return date($format, strtotime($this->date));
    }

    public function getDateCommission() {

        return $this->getDocument()->getDateFormat('Y-m-d');
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

    public function isValidated() {
        return $this->exist('validation') && $this->validation;
    }

    public function isValidatedOI() {
        return $this->exist('validation_oi') && $this->validation_oi;
    }

    public function validate($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->add('validation', $date);
    }

    public function validateOI($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->add('validation_oi', $date);
    }

    public function save($saveDependants = true) {
        if($this->numero_archive) {
            $this->archiverLot($this->numero_archive);
        }
        $this->generateMouvementsLots();

        if($this->generateMouvementsFacturesOnNextSave && !$this->isFactures()) {
            $this->clearMouvementsFactures();
            $this->generateMouvementsFactures();
        }
        $this->generateMouvementsFacturesOnNextSave = false;

        if ($this->etape == DegustationEtapes::ETAPE_VISUALISATION && RegionConfiguration::getInstance()->hasOdgProduits()) {
            if (strpos($this->region, '|') === false && $this->region != Organisme::getOIRegion() && RegionConfiguration::getInstance()->hasOC()) {
                $this->region = $this->region.'|'.Organisme::getOIRegion();
                $this->generateMouvementsLots();
            }
            if (!$this->isValidated()) {
                $this->validate();
            }
            if ($this->isValidated() && !$this->isValidatedOI() && !$this->hasMouvementsEnAttente()) {
                $this->validateOI();
            }
        }

        foreach($this->lots as $l) {
            if ($l->exist('prelevement_heure')) {
                $h = $l->_get('prelevement_heure');
                if ($h) {
                    $l->setPrelevementHeure($h);
                }
                $l->remove('prelevement_heure');
            }
        }

        $saved = parent::save();

        if($saveDependants) {
            $this->saveDocumentsDependants();
		}

        return $saved;
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

    public function archiverLot($numeroDossier)
    {
        // À refacto avec DeclarationLots
        $lots = [];
        foreach($this->getLots() as $lot) {
            if(!$lot->produit_hash && !$lot->details) {
                continue;
            }
            if($lot->isLeurre()) {
                continue;
            }
            if ($lot->numero_archive) {
                continue;
            }
            $lots[] = $lot;
        }

        if(! count($lots)) {
            return;
        }

        $lastNum = ArchivageAllView::getInstance()->getLastNumeroArchiveByTypeAndCampagne(Lot::TYPE_ARCHIVE, $this->archivage_document->getCampagne());
        $num = 0;

        if (preg_match("/^([0-9]+).*/", $lastNum, $m)) {
            $num = $m[1];
        }

        foreach($lots as $lot) {
            $num++;
            $lot->numero_archive = sprintf("%05d", $num);
            $lot->numero_dossier = $numeroDossier;
        }

        DeclarationClient::getInstance()->clearCache();
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
		$infos['nbLotsRestantAPrelever'] = $this->getNbLotsRestantAPreleve();
		$infos['nbLotsPreleves'] = $this->getNbLotsPreleves();
		$infos['nbLotsPrelevesSansLeurre'] = $this->getNbLotsPreleves() - $infos["nbLotsLeurre"];
		$infos["nbAdherents"] = count($this->getAdherentsPreleves());
  	$infos["nbAdherentsLotsRestantAPrelever"] = count($this->getAdherentsByLotsWithoutStatut(Lot::STATUT_PRELEVE));
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
		$tables = $this->getTables();
		$infos["nbTables"] = count($tables);
		$infos["nbLotsAnonymises"] = count($this->getLotsAnonymized());
		$infos["nbLotsConformes"] = $this->getNbLotsConformes(true);
		$infos["nbLotsNonConformes"] = $this->getNbLotsNonConformes(true);
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
				$this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONCONFORME_LEVEE, $lot->observation, $lot->nonconformite_levee));
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
                    if ($lot->id_document_provenance && strpos($lot->id_document_provenance, 'TOURNEE') === false) {
                        $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT));
                    }
                case Lot::STATUT_ANNULE:
                case Lot::STATUT_AFFECTE_DEST:
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_DEST, Lot::generateTextePassageMouvement($lot->getNombrePassage())));

                default:
                    break;
            }
            if($lot->isAnnule()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ANNULE));
            }
            if($lot->isPreleve()) {
                if ($lot->id_document_provenance && strpos($lot->id_document_provenance, 'TOURNEE') === false) {
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_PRELEVE, '', $lot->preleve));
                }
            }
			if ($lot->isChange()) {
				continue;
			}
			if ($lot->isAffecte()) {
				$this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC, Lot::generateTextePassageMouvement($lot->getNombrePassage() + 1)));
			}elseif($lot->isAffectable()) {
				$this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE, Lot::generateTextePassageMouvement($lot->getNombrePassage() + 1)));
			} elseif(in_array($lot->statut, array(Lot::STATUT_NONCONFORME, Lot::STATUT_RECOURS_OC)) && !$lot->id_document_affectation) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE));
            }
        }
    }

    /**** FIN DES MOUVEMENTS LOTS ****/

    /**** PIECES ****/

    public function getAllPieces() {
    	$pieces = array();

        $base_libelle = 'Résultat de la dégustation du ' . $this->getDateFormat('d/m/Y H:i');

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
                'date_depot' => $this->date,
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

    public function verifyGenerateModificative() {

        return true;
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

    public function removeLot($lot) {
        $this->fillDocToSaveFromLots();
        $this->remove($lot->getHash());
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
		$lot->getDateCommission();
        $lot->id_document_provenance = ($lotOrig->id_document !== $this->_id) ? $lotOrig->id_document : null;
        $lot->id_document_affectation = null;
        $lot->id_document = $this->_id;
        $lot->affectable = false;
        $lot->conforme_appel = null;
        $lot->conformite = null;
        $lot->email_envoye = null;
        $lot->motif = null;
        $lot->numero_anonymat = null;
        $lot->numero_table = null;
        $lot->observation = null;
        $lot->position = null;
        $lot->recours_oc = null;
        $lot->preleve = null;
        if($this->type == DegustationClient::TYPE_MODEL && strpos($lotOrig->id_document, TourneeClient::TYPE_COUCHDB) !== false) {
            $lot->preleve = $lotOrig->preleve;
        }
        if(!$lot->preleve) {
            $lot->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
        }
        if ($this->type == TourneeClient::TYPE_MODEL
            && strpos($lot->id_document_provenance, 'PMCNC') === false
            && $lot->initial_type == PMCNCClient::TYPE_COUCHDB)
        {
            $lot->initial_type = TourneeClient::TYPE_TOURNEE_LOT_NC_OI;
        }
        if ((get_class($lotOrig) != 'stdClass' && $lotOrig->document_ordre) || isset($lotOrig->document_ordre)) {
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

        $lotsExistants = [];
        foreach($this->lots as $lot) {
            if($lot->unique_id) {
                $lotsExistants[$lot->unique_id] = $lot->unique_id;
            }
        }

        foreach($lots as $key => $lot) {
            if(isset($lotsExistants[$lot->unique_id])) {
                unset($lotsExistants[$lot->unique_id]);
                continue;
            }
            $this->addLot($lot);
        }

        foreach($lotsExistants as $unique_id) {
            $this->removeLot($this->getLot($unique_id));
        }
	 }

	 public function getAdherentsByLotsWithoutStatut($statut){
 			$adherents = array();
 			foreach ($this->getLots() as $lot) {
 							if ($lot->isLeurre()) {
 									continue;
 							}
 							if(!$lot->getMouvement($statut)) {
 									$adherents[$lot->getDeclarantIdentifiant()] = $lot->getDeclarantIdentifiant();
 							}
 			}
 		return $adherents;
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
				if (!$lot->volume) {
					continue;
				}
				//Les leurres n'ont pas de uniqid donc pas de mouvement
				if($including_leurre && $lot->isLeurre() && $lot->statut === $statut){
					$lots[] = $lot;
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
        foreach ($this->getLotsDegustables() as $lot) {
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

	public function areAllLotsSaisis(){
		$etapeInf = array(
			Lot::STATUT_AFFECTE_DEST,
			Lot::STATUT_ATTENTE_PRELEVEMENT,
			Lot::STATUT_PRELEVE,
			Lot::STATUT_ATTABLE,
			Lot::STATUT_ANONYMISE,
			Lot::STATUT_DEGUSTE
		);

		foreach ($this->getLotsAnonymized() as $lot) {

			if (in_array($lot->statut, $etapeInf)) {
					return false;
			}
		}
		return true;
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

	 public function getLotsDegustes($including_leurre = false){
		 return array_merge($this->getLotsWithStatut(Lot::STATUT_CONFORME, $including_leurre),$this->getLotsWithStatut(Lot::STATUT_NONCONFORME, $including_leurre));
	 }


	 public function getNbLotsPreleves(){
		 return count($this->getLotsDegustables()) + count($this->getLotsSansVolume());
	 }

	 public function getNbLotsConformes($including_leurre = false){

			return count($this->getLotsConformesOrNot(true, $including_leurre));
	 }

	 public function getNbLotsNonConformes($including_leurre = false){

		 return count($this->getLotsConformesOrNot(false, $including_leurre));
	 }

    public function getLotsConformesOrNot($conforme = true, $including_leurre = false){
        $lots = array();
        foreach ($this->getLotsDegustes($including_leurre) as $lot) {
            //Sélection des lots en fonction de l'argument conforme
            // (et avoir une meilleur garantie que
            // les lots conforme et non conforme = lots)
            if(!$conforme XOR $lot->isConforme()){
                $lots[] = $lot;
            }
        }
        return $lots;
    }

    /**** FIN DES PIECES ****/


		/**** Gestion des tables de la degustation ****/

        public function getLotsDegustables($tri = false) {
            $lots = array();
            foreach ($this->getLots() as $lot) {
                if(!$lot->isDegustable()) {
                    continue;
                }

                $lots[] = $lot;
            }

            if ($tri) {
                if (! $this->array_tri) {
                    $this->array_tri = $this->getTriArray();
                }

                uasort($lots, [$this, 'sortLotsByThisTri']);
            }

            return $lots;
        }

		public function getLotsPrelevables() {
            $lots = array();
            foreach ($this->getLots() as $lot) {
                if(!$lot->isPrelevable()) {
                    continue;
                }

                $lots[] = $lot;
            }

            return $lots;
		}

        public function getLotsPreleves()
        {
            return array_filter($this->getLots()->toArray(), function ($lot) {
                return $lot->isPreleve() === true;
            });
        }

        public function getLotsSansVolume() {
            $lots = array();
	   		foreach ($this->getLots() as $lot) {
                if (!$lot->volume) {
                    $lots[] = $lot;
                }
            }
            return $lots;
        }

		public function getLotsDegustablesCustomSort(array $tri = null) {
			$lots = $this->getLotsDegustables();
			if (!$tri) {
				$tri = explode('|', $this->tri);
			}
			$this->array_tri = $tri;
	   		uasort($lots, array($this, "sortLotsByThisTri"));
	   		return $lots;
   	 	}

        public function getTables()
        {
            if(count($this->lots) == 0) {
                return [];
            }

            $last_table = max(array_column($this->lots->toArray(true, false), 'numero_table'));
            $tables = array_fill_keys(range(1, $last_table), []);

            foreach ($this->lots as $lot) {
                if(!$lot->exist('numero_table') || !$lot->numero_table){
                    continue;
                }
                $tables[$lot->numero_table][] = $lot;
            }

            ksort($tables);
            return $tables;
        }

		public function getLotsWithoutLeurre(){
			$lots = array();
			foreach ($this->getLotsDegustables() as $lot) {
					if ($lot->leurre === true) {
							continue;
					}
					$lots[] = $lot;
			}
			return $lots;
		}

        public function getLotsFromProvenance($filter_empty = false) {
            $lots = array();
            foreach($this->getLots() as $lot) {
                if ($lot->isLeurre()) {
                    continue;
                }
                $lotProvenance = $lot->getLotProvenance();

                if ($filter_empty && ! $lotProvenance) {
                    continue;
                }

                if(!$lotProvenance) {
                    throw new sfException("Le lot ".$this->getDocument()->_id.$lot->getHash(). " (".$lot->unique_id.") n'a pas de provenance");
                }
                $lots[$lot->unique_id] = DegustationClient::getInstance()->cleanLotForDegustation($lotProvenance->getData());
				$lots[$lot->unique_id]->specificite = $lot->specificite;
				$lots[$lot->unique_id]->statut = $lot->statut;
            }
            return $lots;
        }

        public function hasLotsSansProvenance()
        {
            return count(array_filter($this->getLots()->toArray(), function ($lot) {
                return ! $lot->getLotProvenance();
            })) > 0;
        }

		public function getLotsByTable($numero_table){
			$lots = array();
			foreach ($this->getLotsDegustables() as $lot) {
				if(intval($lot->numero_table) == $numero_table){
					$lots[] = $lot;
				}
			}
			$this->array_tri = [DegustationClient::DEGUSTATION_TRI_NUMERO_ANONYMAT];
			usort($lots, array($this, "sortLotsByThisTri"));
 		 	return $lots;
		}

        public function cleanLotsNonAnonymisable(){
			$this->fillDocToSaveFromLots();
            foreach ($this->lots->toArray() as $lot) {
                if($lot->isAnonymisable()) {
                    continue;
                }
                if($lot->isAnnule()) {
                    continue;
                }
                $this->remove($lot->getHash());
            }
            $this->lots->reindex();
        }

        public function cleanLotsSansProduit()
        {
            foreach ($this->lots->toArray() as $lot) {
                if ($lot->produit_hash) {
                    continue;
                }

                $this->remove($lot->getHash());
            }

            $this->lots->reindex();
        }

        public function getTri() {
            $tri = $this->_get('tri');
            if (!$tri) {
                $tri = 'Genre|Couleur|Appellation|Millesime|Cépage';
                $this->_set('tri', $tri);
            }
            return $tri;
        }

        public function isTriManuel() {
            return strpos($this->getTri(), DegustationClient::DEGUSTATION_TRI_MANUEL) === 0;
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

            $this->generateMouvementsFacturesOnNextSave = true;
		}

        public function desanonymize()
        {
            foreach ($this->getLots() as $k => $lot){
                if ($lot->numero_anonymat){
                    $lot->numero_anonymat = null;
                }
            }

            if(!$this->isFactures()){
                $this->clearMouvementsFactures();
            }
		}

		public function isAnonymized(){
            if (!count($this->lots)) {
			    return false;
            }
            return ($this->lots[0]->numero_anonymat);
		}

        public function isFullyAnonymized()
        {
            return count(array_filter($this->getLotsDegustables(), function ($lot) {
                return $lot->numero_anonymat === null;
            })) === 0;
        }

        public function getLotsAnonymized(){
            $lotsAnon = array();
            foreach ($this->getLotsDegustables() as $k => $lot){
                if (!$lot->leurre && $lot->numero_anonymat) {
                    $lotsAnon[$lot->numero_anonymat] = $lot;
                }
            }
            return $lotsAnon;
        }

        public function getLotsTableOrFreeLots($numero_table, $free_table = true)
        {
            return array_filter($this->getLotsDegustables(), function ($lot) use ($numero_table, $free_table) {
                if (DegustationConfiguration::getInstance()->isAnonymisationManuelle() && ! $lot->numero_anonymat) {
                    return false;
                }

                if ($lot->numero_table == $numero_table) {
                    return true;
                }
                if ($free_table && ! $lot->numero_table) {
                    return true;
                }

                return false;
            });
		}

        public function getLotsTableOrFreeLotsCustomSort($numero_table, $free_table = true, $with_tri_manual = true){
            $lots = $this->getLotsTableOrFreeLots($numero_table, $free_table);
            $this->array_tri = $this->getTriArray();
            if ($with_tri_manual) {
                uasort($lots, array($this, 'sortLotsByThisTri'));
            }else{
                $atri = $this->array_tri;
                if(($key = array_search(DegustationClient::DEGUSTATION_TRI_MANUEL, $this->array_tri)) !== false){
                     unset($this->array_tri[$key]);
                }
                uasort($lots, array($this, 'sortLotsByThisTri'));
                $this->array_tri = $atri;
            }
            return $lots;
        }

        public function getLotsTableCustomSort($numero_table){
            return $this->getLotsTableOrFreeLotsCustomSort($numero_table, false);
        }

		public function setTri($t) {
			$this->_set('tri', $t);
			$this->updatePositionLots();
		}

        public function updatePositionLots() {
            if ($this->isTriManuel()) {
                return;
            }
            $t = 0;
            foreach($this->getTables() as $table) {
                $t++;
                $this->generateAndSetPositionsForTable($t);
            }
        }

        public function getTheoriticalPosition($table, $without_manual = false) {
            $lots_theoritical = $this->getLotsTableOrFreeLotsCustomSort($table, false, false);
            $theoritical_position = array();
            $i = 0;
            foreach ($lots_theoritical as $lot) {
                if ($without_manual && $lot->isPositionManuel()) {
                    continue;
                }
                $i++;
                $theoritical_position[$lot->getKey()] = $i;
            }
            return $theoritical_position;
        }

        public function generateAndSetPositionsForTable($table) {
            $table = ($table) ? $table : 99;
            $i = 0;
            $position = 0;
            $i = 0;
            $theoritical_position = $this->getTheoriticalPosition($table);
            $lots = $this->getLotsTableOrFreeLotsCustomSort($table, ($table == 99));
            foreach($lots as $lot) {
                $i++;
                if ($table == 99) {
                    $lot->position = '999900';
                }else{
                    if (($lot->position % 2) && ($theoritical_position[$lot->getKey()] != $i)) {
                        $lot->position = sprintf("%02d%03d1", $table, $i);
                    }else{
                        $lot->position = sprintf("%02d%03d0", $table, $i);
                    }
                }
            }
        }

		public function hasFreeLots(){
			foreach ($this->getLotsDegustables() as $lot) {
				if(!$lot->exist("numero_table") || is_null($lot->numero_table)){
					return true;
				}
			}
			return false;
		}

		public function getSyntheseLotsTable($numero_table = null){
			$lots = $this->getLotsDegustables();
			$syntheseLots =  $this->createSynthesFromLots($lots, $numero_table);
			ksort($syntheseLots);
			return $syntheseLots;
		}

        public function getTriArray() {
            return explode('|', strtolower($this->tri));

        }

        public function getSyntheseLotsTableCustomTri($numero_table = null){
            $tri_array = $this->getTriArray();
            if (($key = array_search(DegustationClient::DEGUSTATION_TRI_MANUEL, $tri_array)) !== false) {
                unset($tri_array[$key]);
            }
            $lots = $this->getLotsDegustablesCustomSort($tri_array);
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
			$tables = array_keys($this->getTables());
			if(!count($tables)) { return 0; }
			return min($tables);
		}

		public function getLastNumeroTable(){
			$tables = array_keys($this->getTables());
			if(!count($tables)) { return 0; }
			return max($tables);
		}

    public function sortLotsByThisTri($a, $b){
			$a_data = '';
			$b_data = '';
			foreach($this->array_tri as $t) {
				$a_data .= $a->getValueForTri($t);
				$b_data .= $b->getValueForTri($t);
				if ( $t == DegustationClient::DEGUSTATION_TRI_NUMERO_ANONYMAT){
					$cmp = $a_data-$b_data;
					if ($cmp !=0) {
						return $cmp;
					}
				}
                elseif ( $t == DegustationClient::DEGUSTATION_TRI_GENRE || $t == DegustationClient::DEGUSTATION_TRI_MILLESIME) {
                    $cmp = strcmp($a_data, $b_data);
					if ($cmp) {
						return $cmp*-1;
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

    public function addLeurre($hash, $cepages, $millesime, $numero_table)
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
            $leurre->millesime = $millesime;
			$this->updatePositionLots();

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

		/**** Fin Gestion des tables de la degustation ****/


		/**** Gestion dégustateurs ****/

        public function listeDegustateurs($college, $adresse_only = false)
        {
            $degustateurs = [];

            $regions = array_unique(array_merge([$this->region], $this->getRegionsFromProduits()));
            if (!DegustationConfiguration::getInstance()->hasDegustateurParRegion()) {
                $comptes_degustateurs = CompteTagsView::getInstance()->listByTags('automatique', $college);
            } else {
                foreach($regions as $region) {
                    $region_postfix = ($region)  ? '_'.strtolower($region) : '';
                    $comptes_degustateurs = CompteTagsView::getInstance()->listByTags('automatique', $college.$region_postfix );
                }
            }
            if (count($comptes_degustateurs) > 0) {
                foreach ($comptes_degustateurs as $compte) {
                    $degustateur = CompteClient::getInstance()->find($compte->id);

                    if ($degustateur->isSuspendu()) {
                        continue;
                    }

                    $degustateurs[$degustateur->_id] = ($adresse_only) ? $degustateur->getLibelleWithAdresse() : $degustateur;
                }
            }

            uasort($degustateurs, function ($deg1, $deg2) use ($adresse_only) {
                return ($adresse_only)
                     ? strcasecmp($deg1, $deg2)
                     : strcasecmp($deg1->nom, $deg2->nom);
            });

            return $degustateurs;
        }

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
			foreach ($this->getAllDegustateurs() as $college_cmptId => $degustateur) {
				list($college, $compte_id) = explode("|", $college_cmptId);
				if(!array_key_exists($college,$degustateursByCollege)){
					$degustateursByCollege[$college] = array();
				}
				$degustateursByCollege[$college][$compte_id] = ($degustateur->exist('confirmation') && $degustateur->confirmation);
			}
			return $degustateursByCollege;
		}

		public function getDegustateursConfirmes(){
			$degustateurs = array();
			foreach ($this->getAllDegustateurs() as $college_cmptId => $degustateur) {
				list($college, $compte_id) = explode("|", $college_cmptId);
				if($degustateur->exist('confirmation') && $degustateur->confirmation){
					$degustateurs[$compte_id] = $degustateur;
				}
			}
			return $degustateurs;
		}

		public function getDegustateursConfirmesTableOrFreeTable($numero_table = null){
			$degustateurs = array();
			foreach ($this->getDegustateursConfirmes() as $id => $degustateur) {
				if(($degustateur->exist('numero_table') && $degustateur->numero_table == $numero_table)
					|| (!$degustateur->exist('numero_table') || !($degustateur->numero_table))){
					$degustateurs[$id] = $degustateur;
				}
			}
			return $degustateurs;
		}

		public function getDegustateursATable(){
			$degustateurs = array();
			foreach ($this->getAllDegustateurs() as $college_cmptId => $degustateur) {
				list($college, $compte_id) = explode("|", $college_cmptId);
				if($degustateur->exist('numero_table') && !is_null($degustateur->numero_table)){
					$degustateurs[$compte_id] = $degustateur;
				}
			}
			return $degustateurs;
		}

        public function getDegustateursNonATable(){
			$degustateurs = array();
			foreach ($this->degustateurs as $college => $degs) {
				foreach ($degs as $compte_id => $degustateur) {
                    if($degustateur->exist('numero_table') && !is_null($degustateur->numero_table)) {
                        continue;
                    }
					$degustateurs["$college|$compte_id"] = $degustateur;
				}
			}
			return $degustateurs;
		}

		public function getAllDegustateurs(){
			$degustateurs = array();
			foreach ($this->degustateurs as $college => $degs) {
				foreach ($degs as $compte_id => $degustateur) {
					$degustateurs["$college|$compte_id"] = $degustateur;
				}
			}
			return $degustateurs;
		}

		public function getLotsNonAttables(){
			$non_attables = array();
			foreach ($this->getLotsDegustables() as $lot) {
				if($lot->isAnonymisable()) {
					continue;
                }
				$non_attables[] = $lot;
			}
			return $non_attables;
		}

		public function addDegustateur($compteId, $college, $numTab = null){
			$this->getOrAdd('degustateurs');
			$compte = CompteClient::getInstance()->find($compteId);
			$degustateur = $this->degustateurs->getOrAdd($college)->getOrAdd($compteId);
			$degustateur->getOrAdd('libelle');
			$degustateur->libelle = $compte->getLibelleWithAdresse();

			if($numTab === null){

                return $degustateur;
            }

            $degustateur->getOrAdd('numero_table');
            $degustateur->numero_table = $numTab;
            $degustateur->getOrAdd('confirmation');
            $degustateur->confirmation = true;

            return $degustateur;
		}

		public function setDateEmailConvocationDegustateur($date, $compteId, $college) {
			$this->getOrAdd('degustateurs');
			$degustateur = $this->degustateurs->getOrAdd($college)->getOrAdd($compteId);
			$degustateur->getOrAdd('email_convocation');
			$degustateur->email_convocation = $date;
		}

		public function hasAllDegustateursConfirmation(){
			foreach ($this->getAllDegustateurs() as $degustateur) {
				if(!$degustateur->exist('confirmation')){
					return false;
				}
			}
			return true;
		}

		/**** Fin Gestion dégustateurs ****/

		/**** Gestion PDF ****/

		public function getEtiquettesFromLots($maxLotsParPlanche, $identifiant = null, $secteur = null){
			$nbLots = 0;
			$planche = 0;
			$etiquettesPlanches = array();
			$etablissements = array();
			$produits = array();
			$lots = array();

			foreach ($this->getLotsPrelevables() as $lot) {
                if ($secteur && $secteur != $lot->secteur) {
                    continue;
                }
                if($identifiant && $identifiant != $lot->declarant_identifiant) {
                    continue;
                }

                $lot->getUniqueId(); // on génère les numéros dossier / archives pour les lots aléatoire
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
                    $produits[$lot->produit_hash] = $lot->getConfig() ? $lot->getConfig()->getCouleur()->getLibelle() : $lot->details;
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

        public function getLotsBySecteur($withAllSecteurs = true) {

            $secteurs = [];
            if ($withAllSecteurs) {
                $secteurs[DegustationClient::DEGUSTATION_SANS_SECTEUR] = [];

                foreach (EtablissementClient::getInstance()->getSecteurs() as $secteur) {
                    if ($secteur) {
                            $secteurs[$secteur] = [];
                        }
                }
            }

            foreach($this->getLotsPrelevables() as $lot) {
                $secteur = $lot->secteur;
                if(!$secteur) {
                    $secteur = DegustationClient::DEGUSTATION_SANS_SECTEUR;
                }

                $secteurs[$secteur][$lot->getAdresseLogement()."_".$lot->declarant_identifiant][] = $lot;
            }
            return $secteurs;
        }

        public function hasMouvementsEnAttente() {
            foreach($this->mouvements_lots as $id => $mouvements) {
                foreach($mouvements as $mouvement) {
                    if (strpos($mouvement->statut, Lot::STATUT_MANQUEMENT_EN_ATTENTE) !== false) {
                        return true;
                    }
                }
            }
            return false;
        }

        public function hasLotsSansSecteurs()
        {
            return count(array_filter($this->getLotsPrelevables(), function ($lot) {
                return $lot->secteur === null;
            })) > 0;
        }

        /**
         * Les différents etablissements liés aux lots degustables  de la degustation
         * @return array le tableau des Etablissement
         */
		public function getEtablissementsDegustables() {

            if (! isset($this->etablissementsDegustables)) {
                $this->etablissementsDegustables = [];

                foreach ($this->getLotsDegustables() as $lot) {
                    if (!isset($this->etablissementsDegustables[$lot->declarant_identifiant])) {
                        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
                        if (isset($etablissement)) {
                            $this->etablissementsDegustables[$lot->declarant_identifiant] = $etablissement;
                        }
                    }
                }
            }

            return $this->etablissementsDegustables;
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
			foreach ($this->getLotsDegustes(true) as $key => $lot) {
				if (!$lot->getConfig()) {
					throw new sfException("configuration du lot ".$lot->getHash()." non trouvée :(");
				}
				$degust[$lot->getConfig()->getAppellation()->getLibelle()][] = $lot;
			}

			return $degust;
		}

        /**** MOUVEMENTS ****/

        public function getTemplateFacture($region = null) {

            return TemplateFactureClient::getInstance()->findByCampagne($this->getPeriode(), $region);
        }

        public function getMouvementsFactures() {

            return $this->_get('mouvements');
        }

        public function getMouvementsFacturesCalcule($region = null) {
          $templateFacture = $this->getTemplateFacture($region);

          if(!$templateFacture) {
              return array();
          }
          $cotisations = $templateFacture->generateCotisations($this);
          $mouvements = array();

          foreach($cotisations as $cotisation) {
			  if(!$cotisation->getConfigCallback()){
				  continue;
			  }
              $mvts = call_user_func_array(array($this, $cotisation->getConfigCallback()), [
                  $cotisation,
                  $cotisation->getConfigCallbackParameters()
              ]);
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

        public function getFacturationLotRecours($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
			foreach ($this->getLots() as $lot) {
                if(!$lot->isLotEnRecours()){
                    continue;
                }
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey().':'.$detailKey] = $this->creationMouvementFactureFromLot($cotisation, $lot);
            }
            return $mouvements;
        }

        public function getRedegustationForfait($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            return $this->buildMouvementsFacturesRedegustationForfait($cotisation,$filters);
        }

	    public function buildMouvementsFacturesRedegustationForfait($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
			foreach ($this->getLots() as $lot) {
                if(!$lot->isSecondPassage()){
                    continue;
                }
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey().':'.$detailKey] = $this->creationMouvementFactureFromLot($cotisation, $lot);
            }
            return $mouvements;
	    }
		public function buildMouvementsFacturesRedegustationDejaConformeForfait($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
			foreach ($this->getLots() as $lot) {
                if(!$lot->isRedegustationDejaConforme()){
                    continue;
                }
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey().':'.$detailKey] = $this->creationMouvementFactureFromLot($cotisation, $lot);
            }
            return $mouvements;
	    }

        public function getFacturationLotRedeguste($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            return $this->buildMouvementsFacturesLotRedeguste($cotisation, $filters);
        }

		public function buildMouvementsFacturesHasLotsRedeguste($cotisation,$filters = null){
			$mouvements = $this->buildMouvementsFacturesLotRedeguste($cotisation,$filters);
			$mvt_degust = array();
			foreach ($mouvements as $declarant_identifiant => $mvts) {
				foreach ($mvts as $keylot => $mvt) {
					$mvt_degust[$declarant_identifiant] = array('DEGUSTATION_2deDegust' => $mvt);
					break;
				}
			}
			return $mvt_degust;
		}

        public function buildMouvementsFacturesLotRedeguste($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
            foreach ($this->getLotsDegustables() as $lot) {
                if(!$lot->isSecondPassage()){
                    continue;
                }
                if ($filters && DRevClient::getInstance()->matchFilterLot($lot, $filters) === false) {
                    continue;
                }
                $mvtFacture = $this->creationMouvementFactureFromLot($cotisation, $lot);
                $mvtFacture->detail_identifiant = $lot->getNumeroDossier();
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey().':'.$detailKey] = $mvtFacture;
            }

            return $mouvements;
        }

		public function getFacturationVolumeRedeguste($cotisation, TemplateFactureCotisationCallbackParameters $filters = null){
            return $this->buildMouvementsFacturesVolumeRedeguste($cotisation, $filters);
        }
        public function buildMouvementsFacturesVolumeRedeguste($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            return $this->buildMouvementsFacturesVolume($cotisation, $filters, true);
		}

        public function buildMouvementsFacturesVolumeDeguste($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            return $this->buildMouvementsFacturesVolume($cotisation, $filters);
        }

        private function buildMouvementsFacturesVolume($cotisation, TemplateFactureCotisationCallbackParameters $filters = null, $redegustation = false) {
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
            $volumes_operateurs = [];
            $volumes_operateurs_total = [];
            // volume total revendiqué par opérateur
            foreach ($this->getLotsDegustables() as $lot) {
                if (! isset($volumes_operateurs_total[$lot->declarant_identifiant])) {
                    $volumes_operateurs_total[$lot->declarant_identifiant] = 0;
                }
                $volumes_operateurs_total[$lot->declarant_identifiant] += round($lot->volume, 2);
            }

            foreach ($this->getLotsDegustables() as $lot) {
                if (DRevClient::getInstance()->matchFilterLot($lot, $filters) === false) {
                    continue;
                }

                if ($redegustation && !$lot->isSecondPassage()) {
                    continue;
                }

                if ($redegustation === false && $lot->isSecondPassage()) {
                    continue;
                }

				if (!isset($volumes_operateurs[$lot->declarant_identifiant])) {
					$volumes_operateurs[$lot->declarant_identifiant] = 0;
				}
                $volumes_operateurs[$lot->declarant_identifiant] += $lot->volume;
            }
            foreach ($volumes_operateurs as $operateur => $volume) {
                $minimum = null;
                if ($cotisation->getConfigCollection()->exist('minimum') && $cotisation->getConfigCollection()->minimum) {
                    $minimum = $cotisation->getConfigCollection()->minimum;
                }

                $mvtFacture = DegustationMouvementFactures::freeInstance($this);
                $mvtFacture->createFromCotisationAndDoc($cotisation, $this);
                $mvtFacture->date = $this->getDateFormat();
                $mvtFacture->date_version = $this->getDateFormat();
                $mvtFacture->quantite = $volume;
                if ($minimum && $minimum > $volumes_operateurs_total[$operateur] * $cotisation->getPrix()) {
                    $mvtFacture->quantite = 1;
                    $mvtFacture->taux = $minimum;
                    $mvtFacture->unite = null;
                }
                $mouvements[$operateur][$detailKey] = $mvtFacture;
            }

            return $mouvements;
        }

        public function buildMouvementsNbLotsDegustes($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
            $nblots_operateurs = [];
            foreach ($this->getLotsDegustables() as $lot) {
                if (DRevClient::getInstance()->matchFilterLot($lot, $filters) === false) {
                    continue;
                }
                @$nblots_operateurs[$lot->declarant_identifiant] += 1;
            }
            foreach ($nblots_operateurs as $operateur => $quantite) {
                $mvtFacture = DegustationMouvementFactures::freeInstance($this);
                $mvtFacture->createFromCotisationAndDoc($cotisation, $this);
                $mvtFacture->date = $this->getDateFormat();
                $mvtFacture->date_version = $this->getDateFormat();
                $mvtFacture->quantite = $quantite;
                $mouvements[$operateur][$detailKey] = $mvtFacture;
            }
            return $mouvements;
        }

        public function getForfaitConditionnement($cotisation, TemplateFactureCotisationCallbackParameters $filters){
            return $this->buildMouvementsFacturesForfaitConditionnement($cotisation, $filters);
        }
		public function buildMouvementsFacturesForfaitConditionnement($cotisation, TemplateFactureCotisationCallbackParameters $filters = null){
            $mouvements = array();
            $detailKey = $cotisation->getDetailKey();
            foreach ($this->getLotsDegustables() as $lot) {
                if(strpos($lot->id_document_provenance, 'CONDITIONNEMENT') !== 0){
                    continue;
                }
				if (DRevClient::getInstance()->matchFilterLot($lot, $filters) === false) {
                    continue;
                }
                $mvtFacture = $this->creationMouvementFactureFromLot($cotisation, $lot);
                $mvtFacture->detail_identifiant = $lot->getNumeroDossier();
                $mouvements[$lot->declarant_identifiant][$lot->getUnicityKey().':'.$detailKey] = $mvtFacture;
            }

            return $mouvements;
        }

        public function getFacturationNonConforme($cotisation, TemplateFactureCotisationCallbackParameters $filters = null) {
            return $this->buildMouvementsFacturesNonConforme($cotisation,$filters);
        }
        public function buildMouvementsFacturesNonConforme($cotisation, TemplateFactureCotisationCallbackParameters $filters = null) {
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

        public function getBigDocumentSize() {

            return -1;
        }

        public function getPeriode() {
            return substr($this->campagne, 0, 4);
        }

		public function getAppellationsLots() {
			$conf = $this->getConfiguration();
			$appellations= [];
			foreach($this->lots as $lot) {
				$appellations[$conf->get($lot->produit_hash)->getAppellation()->libelle] = $conf->get($lot->produit_hash)->getAppellation()->libelle;
			}
			return $appellations;
		}

        public function isTournee() {
            return strpos($this->_id, 'TOURNEE') !== false;
        }

        public function isLibelleAcceptable()
        {
            if (DegustationConfiguration::getInstance()->hasAcceptabiliteAoc($this->getRegion())) {
                return DegustationConfiguration::getInstance()->getAcceptabiliteAoc($this->getRegion());
            }
            return false;
        }
}
