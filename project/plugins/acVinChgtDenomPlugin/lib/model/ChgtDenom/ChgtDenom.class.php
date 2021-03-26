<?php

class ChgtDenom extends BaseChgtDenom implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceMouvementFacturesDocument {

    const DEFAULT_KEY = 'DEFAUT';

    protected $declarant_document = null;
    protected $mouvement_document = null;
    protected $piece_document = null;
  	protected $cm = null;
    protected $docToSave = array();

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
				$this->cm = new CampagneManager('08-01');
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

		public function getMaster() {
			return $this;
		}

    public function isLotsEditable(){
      return false;
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
        $this->mouvement_document = new MouvementFacturesDocument($this);
        $this->piece_document = new PieceDocument($this);
    }

        public function getDateFormat($format = 'Y-m-d') {
            if (!$this->date) {
                return date($format);
            }
            return date ($format, strtotime($this->date));
        }

        private function getCampagneByDate() {
            return $this->cm->getCampagneByDate($this->getDateFormat());
        }

        public function getPeriode() {
            return preg_replace('/-.*/', '', $this->campagne);
        }

    public function constructId() {
        $date = new DateTime($this->date);

        $id = 'CHGTDENOM-' . $this->identifiant . '-' . $date->format('YmdHis');
        $this->set('_id', $id);
        $this->set('campagne', $this->getCampagneByDate());
    }

    public function getConfiguration() {
        return ConfigurationClient::getInstance()->getConfiguration();
    }

    public function getConfigProduits() {
        return $this->getConfiguration()->declaration->getProduits();
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
        if($this->getEtablissementObject()->famille) {
            $this->declarant->famille = $this->getEtablissementObject()->famille;
        }
    }

    public function storeEtape($etape) {
        $etapeOriginal = ($this->exist('etape')) ? $this->etape : null;
        $this->add('etape', $etape);
        return $etapeOriginal != $this->etape;
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }
        $this->validation = $date;
    }

    public function isPapier() {
        return $this->exist('papier') && $this->get('papier');
    }

    public function isValide() {
      return ($this->validation);
    }

    public function isValidee() {
      return $this->isValide();
    }

    public function isApprouve() {
      return ($this->validation_odg);
    }

    public function validateOdg($date = null) {
        if(is_null($date)) {
            $date = date('c');
        }
        $this->validation_odg = $date;
        $this->generateMouvementsFactures();
    }

    public function getEtablissementObject() {
        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function setChangementType($type) {
        if($type == ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT) {
            $this->changement_produit_hash = null;
        }

        return $this->_set('changement_type', $type);
    }

    public function setChangementProduitHash($hash) {
        $this->changement_produit_libelle = null;
        if($hash) {
            $this->changement_produit_libelle = $this->getConfiguration()->get($hash)->getLibelleComplet();
        }

        return $this->_set('changement_produit_hash', $hash);
    }

    public function setLotOrigine($lot) {
        $this->changement_origine_id_document = $lot->id_document;
        $this->changement_origine_lot_unique_id = $lot->unique_id;
        $this->changement_millesime = $lot->millesime;
        $this->changement_volume = $lot->volume;
    }

    public function getLotOrigine() {
        if(!$this->changement_origine_id_document) {
            return null;
        }

        $doc = acCouchdbManager::getClient()->find($this->changement_origine_id_document);

        if(!$doc) {

            return null;
        }

        return $doc->getLot($this->changement_origine_lot_unique_id);
    }

    public function getLotKey() {

      return $this->changement_origine_id_document.":".$this->changement_origine_lot_unique_id;
    }

	protected function doSave() {
          $this->piece_document->generatePieces();
    	}

    public function saveDocumentsDependants() {
        foreach($this->docToSave as $docId) {
            (acCouchdbManager::getClient()->find($docId))->save();
        }

        $this->docToSave = array();
    }

    public function save() {
        $this->generateMouvementsLots();

        parent::save();

        $this->saveDocumentsDependants();
    }

    public function fillDocToSaveFromLots() {
        foreach ($this->lots as $lot) {
            if(!$lot->id_document_provenance) {
                continue;
            }
            $this->docToSave[$lot->id_document_provenance] = $lot->id_document_provenance;
        }
    }

    public function clearLots(){
      $this->remove('lots');
      $this->add('lots');
    }

    public function isDeclassement() {
      return $this->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT;
    }
    public function isChgtTotal() {
      return ($this->changement_volume == $this->getLotOrigine()->volume);
    }

    public function getPourcentagesCepages() {
      $volume_total = 0;
      $cepages = array();
      foreach($this->changement_cepages as $volume) {
        $volume_total += $volume;
      }
      foreach($this->changement_cepages as $cep => $volume) {
        $cepages[$cep] += round(($volume/$volume_total) * 100);
      }
      return $cepages;
    }

    public function generateLots() {
      $this->clearMouvementsLots();
      $this->clearLots();

      $lots = array();
      $lot = $this->getLotOrigine()->getData();


      $lotDef = ChgtDenomLot::freeInstance($this);
      foreach($lot as $key => $value) {
          if($lotDef->getDefinition()->exist($key)) {
              continue;
          }

          unset($lot->{$key});
      }

      $ordre = sprintf('%02d', intval($lot->document_ordre) + 1 );
      if (!$this->isChgtTotal()) {
        $lotOrig = clone $lot;
        $lotOrig->volume -= $this->changement_volume;
        $lotOrig->numero_archive .= 'a';
        $lotOrig->document_ordre = $ordre;
        $lot->numero_archive .= 'b';
        $lots[] = $lotOrig;
      }
      $lot->document_ordre = $ordre;
      $lot->produit_hash = $this->changement_produit_hash;
      $lot->produit_libelle = $this->changement_produit_libelle;
      $lot->statut = ($this->isDeclassement())? Lot::STATUT_DECLASSE : Lot::STATUT_CONFORME;
      $lot->cepages = $this->changement_cepages;
      if (count($this->changement_cepages->toArray(true, false))) {
          $lot->details = '';
          foreach($this->getPourcentagesCepages() as $cep => $pc) {
              $lot->details .= $cep.' ('.$pc.'%) ';
          }
      }
      $lots[] = $lot;

      foreach($lots as $l) {
        $l->affectable = true;
        $lot = $this->lots->add(null, $l);
        $lot->id_document = $this->_id;
        $lot->updateDocumentDependances();
      }
    }

    public function getCepagesToStr(){
      $cepages = $this->cepages;
      $str ='';
      $k=0;
      $total = 0.0;
      $tabCepages=array();
      foreach ($cepages as $c => $volume){
        $total+=$volume;
      }
      foreach ($cepages as $c => $volume){
        $p = ($total)? round(($volume/$total)*100) : 0.0;
        $tabCepages[$c]=$p;
      }
      arsort($tabCepages);
      foreach ($tabCepages as $c => $p) {
        $k++;
        $str.=" ".$c." (".$p.'%)';
        $str.= ($k < count($cepages))? ',' : '';
      }
      return $str;
    }

  	public function getVersion() {
  			return null;
  	}

    public function addCepage($cepage, $repartition) {
        $this->changement_cepages->add($cepage, $repartition);
    }

    public function getCepagesLibelle() {
        $libelle = null;
        foreach($this->changement_cepages as $cepage => $repartition) {
            if($libelle) {
                $libelle .= ", ";
            }
            $libelle .= $cepage . " (".$repartition."%)";
        }
        return $libelle;
    }

    /**** FIN DES MOUVEMENTS ****/

    /**** MOUVEMENTS LOTS ****/

    public function clearMouvementsLots(){
        $this->remove('mouvements_lots');
        $this->add('mouvements_lots');
    }

    public function addMouvementLot($mouvement) {

        return $this->mouvements_lots->add($mouvement->declarant_identifiant)->add($mouvement->getUnicityKey(), $mouvement);
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
            $lot->updateDocumentDependances();

            if($this->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_DEST));
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_REVENDIQUE));
            }

            if($lot->affectable) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
            }
        }
    }

    /**** FIN DES MOUVEMENTS LOTS ****/

    /**** PIECES ****/
    public function getAllPieces() {
      $lot = $this->getLotOrigine();
      $libelle = ($this->isDeclassement())? 'Déclassement' : 'Changement de dénomination';
      $libelle .= ($this->isChgtTotal())? '' : ' partiel';
      $libelle .= ' lot de '.$lot->produit_libelle.' '.$lot->millesime;
      $libelle .= ' (logement '.$lot->numero_logement_operateur.')';
      $libelle .= ($this->isPapier())? ' (Papier)' : ' (Télédéclaration)';
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => preg_replace('/T.*/', '', $this->validation),
    		'libelle' => $libelle,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return null;
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('chgtdenom_visualisation', array('id' => $id));
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
    	return true;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }
    /**** FIN DES PIECES ****/

    /**** MOUVEMENTS ****/

    public function getTemplateFacture() {
        return TemplateFactureClient::getInstance()->findByCampagne($this->getCampagne());
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

      $identifiantCompte = $this->getIdentifiant();

      $mouvements = array();

      $rienAFacturer = true;

      foreach($cotisations as $cotisation) {
          $mouvement = ChgtDenomMouvementFactures::freeInstance($this);
          $mouvement->fillFromCotisation($cotisation);
          $mouvement->facture = 0;
          $mouvement->facturable = 1;
          $mouvement->date = $this->validation_odg;
          $mouvement->date_version = $this->validation;
          $mouvement->version = $this->version;

          if(!$mouvement->quantite) {
              continue;
          }

          if($mouvement->quantite) {
              $rienAFacturer = false;
          }

          $mouvements[$mouvement->getMD5Key()] = $mouvement;
      }

      if($rienAFacturer) {
          return array();

      }

      return array($identifiantCompte => $mouvements);
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

    public function getVolumeDestFacturable($produitFilter = null){
      if(preg_match("#$produitFilter#",$this->changement_produit_hash)){
          return $this->changement_volume;
      }
      return 0.0;
    }

    public function getFirstChgtDenomFacturable()
    {

        $views = ChgtDenomClient::getInstance()->getHistoryCampagne($this->identifiant,substr($this->campagne,0,4));

        foreach ($views as $id => $view) {
            if($id == $this->_id){
                return 1;
            }
            return 0;
        }
    }

    public function getSecondChgtDenomFacturable()
    {
        $views = ChgtDenomClient::getInstance()->getHistoryCampagne($this->identifiant,substr($this->campagne,0,4));
        $first = true;
        foreach ($views as $id => $view) {
            if($first){
                $first = false;
                continue;
            }
            if($id == $this->_id){
                return 1;
            }
        }
        return 0;
    }


    public function getVolumeFacturable($produitFilter = null)
    {
      $produitFilter = preg_replace("/^NOT /", "", $produitFilter, -1, $produitExclude);
			$produitExclude = (bool) $produitExclude;
			$regexpFilter = "#(".implode("|", explode(",", $produitFilter)).")#";
			if($produitFilter && !$produitExclude && !preg_match($regexpFilter, $this->changement_produit_hash)) {
					return;
			}
			if($produitFilter && $produitExclude && preg_match($regexpFilter, $this->changement_produit_hash)) {
					return;
			}

      return $this->changement_volume;
    }

}
