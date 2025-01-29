<?php

class ChgtDenom extends BaseChgtDenom implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceMouvementFacturesDocument, InterfaceArchivageDocument {

    const DEFAULT_KEY = 'DEFAUT';

    protected $declarant_document = null;
    protected $mouvement_document = null;
    protected $piece_document = null;
    protected $archivage_document = null;
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
        $this->archivage_document = new ArchivageDocument($this);
    }

        public function getDateFormat($format = 'Y-m-d') {
            if ($this->validation) {
                return explode('T', $this->validation)[0];
            }
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
        $this->getCampagne();
    }

    public function getConfiguration() {
        return ConfigurationClient::getInstance()->getConfiguration();
    }

    public function getConfigProduitOrigine() {

        return $this->getConfiguration()->get($this->origine_produit_hash);
    }

    public function getConfigProduitChangement() {

        return $this->getConfiguration()->get($this->changement_produit_hash);
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

    public function devalidate() {
        if ($this->isFactures()){
            throw new sfException("dévalisation impossible : le document est déjà facturé");
        }
        $this->validation = null;
        $this->validation_odg = null;
        $this->clearLots();
        $this->clearMouvementsFactures();
        $this->clearMouvementsLots();
        $this->fillDocToSaveFromLots();
        if($this->exist('etape')) {
            $this->etape = null;
        }
        if($this->exist("envoi_oi")){
         $this->envoi_oi = null;
        }
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

    public function isExcluExportCsv() {
        return !$this->isApprouve();
    }

    public function validateOdg($date = null, $region = NULL) {
        if(is_null($date)) {
            $date = date('c');
        }
        $this->validation_odg = $date;
        if(!$this->isFactures()){
            $this->save();
            $this->clearMouvementsFactures();
            $this->generateMouvementsFactures();
        }
    }

    public function getEtablissementObject() {
        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function setChangementType($type, $external_call = true) {
        if($external_call && ($type == ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT)) {
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
        if (get_class($lot) != 'stdClass' && $lot->id_document_provenance && !$lot->getMouvement(Lot::STATUT_CHANGEABLE)){
            throw new sfException('Lot '.$lot->unique_id.' doit être changeable');
        }
        $this->fillDocToSaveFromLots();

        $this->changement_origine_id_document = $lot->id_document;
        $this->changement_origine_lot_unique_id = $lot->unique_id;

        $this->campagne = null;
        $this->getCampagne();

        $this->changement_millesime = $lot->millesime;
        $this->changement_volume = $lot->volume;
        $this->changement_specificite = preg_replace('/ *\d+ème dégustation/', '', $lot->specificite);
        $this->changement_numero_logement_operateur = $lot->numero_logement_operateur;
        $this->changement_affectable = $lot->affectable;
        $this->changement_cepages = $lot->cepages;
        $this->changement_date_commission = $lot->date_commission;
        $this->origine_millesime = $lot->millesime;
        $this->origine_volume = $lot->volume;
        $this->origine_specificite = $lot->specificite;
        $this->origine_produit_hash = $lot->produit_hash;
        $this->origine_cepages = $lot->cepages;
        $this->origine_produit_libelle = $lot->produit_libelle;
        $this->origine_numero_logement_operateur = $lot->numero_logement_operateur;
        $this->origine_affectable = $lot->affectable;
        $this->origine_statut = $lot->statut;
    }

    public function updateStatut() {
        $statut = LotsClient::getInstance()->findStatut($this->identifiant, $this->changement_origine_lot_unique_id, $this->changement_origine_id_document);

        $this->lots[0]->affectable = false;
        if ($this->origine_affectable) {
            if ($this->lots[0]->volume) {
                $this->lots[0]->affectable = true;
            }elseif(count($this->lots) > 1){
                $this->lots[1]->affectable = true;
            }
        }
        if($this->origine_statut) {
            $this->origine_statut = null;
            $this->lots[0]->statut = null;
        }

        if(!$statut) {
            return;
        }

        $this->origine_statut = $statut;
        $this->lots[0]->statut = $statut;
        $this->lots[0]->affectable = false;
        $this->origine_affectable = $this->lots[0]->affectable;

    }

    public function getOrigineNumeroLogementOperateur()
    {
        $l = $this->_get('origine_numero_logement_operateur');

        if ($l) {
            return $l;
        }

        $l = $this->getLotOrigine()->numero_logement_operateur;
        $this->setOrigineNumeroLogementOperateur($l);
        return $l;
    }

    public function getChangementNumeroLogementOperateur()
    {
        $l = $this->_get('changement_numero_logement_operateur');

        if ($l) {
            return $l;
        }

        $l = $this->getOrigineNumeroLogementOperateur();
        $this->setOrigineNumeroLogementOperateur($l);
        return $l;
    }

    public function getDateCommission() {

        return null;
    }

    public function getOrigineProduitLibelleAndCepages() {
        $l = $this->getOrigineProduitLibelle();
        if (count($this->origine_cepages)) {
            $l .= ' (';
            $l .= implode(', ', array_keys($this->origine_cepages->toArray()));
            $l .= ')';
        }
        return $l;
    }

    public function getCampagne() {
        if(is_null($this->_get('campagne'))) {
            $firstOrigineLot = $this->getFirstOrigineLot();
            if($firstOrigineLot) {
                $this->campagne = $firstOrigineLot->campagne;
            } else {
                $this->campagne = $this->getCampagneByDate();
            }
        }

        return $this->_get('campagne');
    }

    public function getFirstOrigineLot() {
        if(!$this->changement_origine_lot_unique_id) {
            return null;
        }
        return LotsClient::getInstance()->findByUniqueId($this->identifiant, $this->changement_origine_lot_unique_id, "01");
    }

    public function isFromProduction()
    {
        if (strpos($this->changement_origine_id_document, 'DR-') === 0) {
            return true;
        }

        if (strpos($this->changement_origine_id_document, 'SV1') === 0) {
            return true;
        }

        return false;
    }

    public function getLotOrigine() {
        if(!$this->changement_origine_id_document) {
            return false;
        }

        $doc = acCouchdbManager::getClient()->find($this->changement_origine_id_document);

        if(!$doc) {

            return null;
        }

        if (method_exists($doc, 'getLot') === false || !$doc->getLot($this->changement_origine_lot_unique_id)) {
           $lot = ChgtDenomLot::freeInstance($this);
           $lot->id_document = $this->changement_origine_id_document;
           $lot->unique_id = $this->changement_origine_lot_unique_id;
           $lot->millesime = $this->changement_millesime;
           $lot->volume = $this->changement_volume;
           $lot->numero_logement_operateur = $this->changement_numero_logement_operateur;
           $lot->millesime = $this->origine_millesime;
           $lot->volume = $this->origine_volume;
           $lot->specificite = $this->origine_specificite;
           $lot->produit_hash = $this->origine_produit_hash;
           if(!is_null($this->origine_cepages)) {
               $lot->cepages = $this->origine_cepages;
           }
           $lot->produit_libelle = $this->origine_produit_libelle;
           $lot->numero_logement_operateur = $this->origine_numero_logement_operateur;
           $lot->affectable = $this->origine_affectable;
           $lot->statut = $this->origine_statut;
           return $lot ;
        }

        return $doc->getLot($this->changement_origine_lot_unique_id);

    }

    public function getLotKey() {

      return $this->changement_origine_id_document.":".$this->changement_origine_lot_unique_id;
    }

    public function hasLotsUtilises() {
        foreach($this->lots as $lot) {
            if($lot->hasBeenEdited()) {
                continue;
            }

            if($lot->isAffecte()) {
                return true;
            }

            if($lot->isChange()) {
                return true;
            }
        }

        return false;
    }

    public function hasDocumentOrigine() {

      $doc = acCouchdbManager::getClient()->find($this->changement_origine_id_document);

      if (!$doc->getLot($this->changement_origine_lot_unique_id)) {
        return false;
      }

      return true;
    }

	protected function doSave() {
          $this->piece_document->generatePieces();
    	}

    public function saveDocumentsDependants()
    {
        if ($this->isFromProduction()) {
            return false;
        }

        foreach($this->docToSave as $docId) {
            DeclarationClient::getInstance()->findCache($docId)->save(false);
        }

        $this->docToSave = array();
        DeclarationClient::getInstance()->clearCache();

    }

    public function save($saveDependants = true) {
        $this->archiver();
        $this->piece_document->generatePieces();
        if ($this->isApprouve()) {
            if (!count($this->lots->toArray(true, false))) {
                $this->generateLots();
                $this->archiver();
            }
            if (count($this->lots) && ($this->lots[0]->unique_id == $this->changement_origine_lot_unique_id)) {
                if (($this->lots[0]->volume) && !($this->lots[0]->id_document_affectation)) {
                    $this->lots[0]->affectable = $this->origine_affectable;
                }else{
                    $this->lots[0]->affectable = false;
                }
            }
            if(isset($this->lots[1])) {
                $this->lots[1]->date_commission = null;
            }
            if(isset($this->lots[1]) && $this->changement_date_commission) {
                $this->lots[1]->date_commission = $this->changement_date_commission;
            }
            $this->generateMouvementsLots();
            $this->fillDocToSaveFromLots();
        }
        $saved = parent::save($saveDependants);
        if ($saveDependants) {
            $this->saveDocumentsDependants();
        }

        return $saved;
    }

    public function fillDocToSaveFromLots() {
        if ($this->changement_origine_id_document) {
            $this->docToSave[$this->changement_origine_id_document] = $this->changement_origine_id_document;
        }
    }

    public function clearLots(){
      $this->remove('lots');
      $this->add('lots');
    }

    public function isDeclassement() {
      return ($this->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
    }
    public function isChgtDenomination() {
        return !$this->isDeclassement();
    }

    public function isTotal()
    {
        if ($this->getLotOrigine() == null) {
            return $this->changement_volume == $this->origine_volume;
        }

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
      $lot = $this->getLotOrigine();
      if ($lot === null) {
          return;
      }

      if ($lot !== false) { // Lot d'origine
          $lot = $lot->getData();
          unset($lot->numero_anonymat);

          $lotDef = ChgtDenomLot::freeInstance($this);
          foreach($lot as $key => $value) {
              if($lotDef->getDefinition()->exist($key)) {
                  continue;
              }

              unset($lot->{$key});
          }
      } else { // Lot de négociant créé
        $lot = new stdClass;
        $lot->document_ordre = "00";
        $lot->volume = $this->origine_volume;
        $lot->campagne = $this->campagne;
      }
      $lot->numero_logement_operateur = $this->origine_numero_logement_operateur;
      $lot->millesime = $this->origine_millesime;
      $lot->produit_libelle = $this->origine_produit_libelle;
      $lot->produit_hash = $this->origine_produit_hash;
      $lot->declarant_nom = $this->declarant->raison_sociale;
      $lot->declarant_identifiant = $this->identifiant;
      $lot->statut = $this->origine_statut;
      if($lot->statut) {
          $lot->affectable = false;
      }

      $ordre = sprintf('%02d', intval($lot->document_ordre) + 1 );
      $lot->date = $this->date;
      $lot->document_ordre = $ordre;
      $lot->id_document_provenance = $this->changement_origine_id_document;

      $lot->volume = $this->changement_volume;

      if ($this->isChgtDenomination()) {
          $lotOrig = clone $lot;
          $lotOrig->initial_type = null;
          $lotOrig->volume = $this->origine_volume - $this->changement_volume;
          if (!$lotOrig->volume) {
              $lotOrig->affectable = false;
          }
          if ($this->origine_numero_logement_operateur !== $this->getLotOrigine()->numero_logement_operateur) {
              $lotOrig->numero_logement_operateur = $this->origine_numero_logement_operateur;
          }
          $this->updateCepageCoherencyWithVolume($lotOrig);
          $lots[] = $lotOrig;
          $lot->initial_type = null;
          $lot->campagne = $this->campagne;
          $lot->numero_archive = null;
          $lot->unique_id = null;
          $lot->document_ordre = '01';
          $lot->specificite = $this->changement_specificite;

          $lot->volume = $this->changement_volume;
          $lot->produit_hash = $this->changement_produit_hash;
          $lot->produit_libelle = $this->changement_produit_libelle;
          if($lot instanceof acCouchdbJson) {
              $lot->remove('cepages');
              $lot->add('cepages', $this->changement_cepages->toArray());
          } else {
            $lot->cepages = $this->changement_cepages->toArray();
          }
          $lot->affectable = $this->changement_affectable;
          $lot->statut = null;
          if(!$lot->affectable) {
              $lot->statut = Lot::STATUT_NONAFFECTABLE;
          }

          if ($this->exist('changement_numero_logement_operateur') && $this->changement_numero_logement_operateur) {
              $lot->numero_logement_operateur = $this->changement_numero_logement_operateur;
          }
      } else {
          $lot->volume = $this->origine_volume - $this->changement_volume;
          if (!$lot->volume) {
              $lot->affectable = false;
          }
          $lot->produit_libelle = $this->origine_produit_libelle;
          $lot->cepages = $this->origine_cepages;
      }

      $this->updateCepageCoherencyWithVolume($lot);
      $lots[] = $lot;

      foreach($lots as $l) {
        $lot = $this->lots->add(null, $l);
        $lot->id_document = $this->_id;
        $lot->updateDocumentDependances();
      }
    }

    private function updateCepageCoherencyWithVolume($lot) {
        if (!$lot->cepages || !count((array) $lot->cepages)) {
            return $lot;
        }
        if (!$lot->volume) {
            $lot->cepages = array();
            return $lot;
        }
        $volume_cepages = 0;
        foreach($lot->cepages as $k => $v) {
            $volume_cepages += $v;
        }
        if ($volume_cepages == $lot->volume) {
            return $lot;
        }

        foreach($this->origine_cepages as $k => $v) {
            if(is_array($lot->cepages)) {
                $lot->cepages[$k] = $v * $lot->volume / $this->origine_volume;
                continue;
            }
            $lot->cepages->{$k} = $v * $lot->volume / $this->origine_volume;
        }
        return $lot;
    }

  	public function getVersion() {
  			return null;
  	}

    public function getLotsWithPseudoDeclassement() {
        $lots_res = array();
        if (!$this->isDeclassement()) {
            foreach($this->lots as $lot) {
                if ($lot->volume) {
                    $lots_res[] = $lot;
                }
            }
            return $lots_res;
        }

        if ($this->lots[0]->volume) {
            $lots_res[] = $this->lots[0];
        }
        $decl = clone $this->lots[0];
        $decl->produit_hash = null;
        $decl->produit_libelle = "Vin sans IG";
        $decl->remove('cepages');
        $decl->add('cepages');
        foreach ($this->changement_cepages as $cep => $vol) {
            $decl->cepages->add($cep, $vol);
        }
        $decl->numero_logement_operateur = $this->changement_numero_logement_operateur;
        $decl->millesime = null;
        $decl->volume = $this->changement_volume;
        $lots_res[] = $decl;

        return $lots_res;
    }

    public function addCepage($cepage, $repartition) {
        $this->changement_cepages->add($cepage, $repartition);
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
        if(!count($this->lots->toArray(true, false))) {
            return;
        }

        if($this->isTotal()) {
            if ($this->isDeclassement()) {
                $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_DECLASSE, "Déclassement total"));
            }else{
                $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_CHANGE_DEST, "Changement total : ".$this->lots[0]->getLibelle()));
            }

        }else{
            if ($this->isDeclassement()) {
                $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_CHANGE_DEST, "Partie non déclassée de ".$this->lots[0]->volume." hl"));
                $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_DECLASSE, "Déclassé de ".$this->changement_volume." hl"));
            }else{
                $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_CHANGE_DEST, "Partie non changée de ".$this->lots[0]->volume." hl"));
                $this->addMouvementLot($this->lots[1]->buildMouvement(Lot::STATUT_CHANGE_DEST, "Changé pour : ".$this->lots[1]->getLibelle().", ".$this->lots[1]->volume." hl"));

            }
        }

        foreach ($this->lots as $i => $lot) {
            $lot->updateDocumentDependances();
            if ($this->isDeclassement() && ($this->isTotal() || $i == 1)) {
                continue;
            }
            //On gère l'avenir du lot changé
            if ($lot->isChange()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_SRC, $lot->getLibelle()));
                continue;
            }
            //Si le lot changé n'a pas été lui même de nouveau changé, on peut le changer et le déguster ou non
            if ($lot->volume && !$lot->isAffecte()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGEABLE));
            }

            if($lot->isAffectable()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
            }else{
                if ($lot->isAffecte()) {
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC));
                }elseif(!$lot->statut){
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONAFFECTABLE));
                }
            }

            if($lot->statut) {
                $this->addMouvementLot($lot->buildMouvement($lot->statut));
            }
        }
    }

    /**** FIN DES MOUVEMENTS LOTS ****/

    /**** PIECES ****/
    public function getAllPieces() {
      $lot = $this->getLotOrigine();
      $libelle = ($this->isDeclassement())? 'Déclassement' : 'Changement de dénomination';
      $libelle .= ($this->isTotal())? '' : ' partiel';
      $libelle .= ' n° '.$this->numero_archive.' -';
      $libelle .= ' lot de '.$this->origine_produit_libelle.' '.$this->origine_millesime;
      $libelle .= ' - logement '.$this->origine_numero_logement_operateur.' ';
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
    	return sfContext::getInstance()->getRouting()->generate('chgtdenom_pdf', $this);
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

    public function getTemplateFacture($region = null) {
        return TemplateFactureClient::getInstance()->findByCampagne($this->getCampagneByDate(), $region);
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

      $identifiantCompte = $this->getIdentifiant();

      $mouvements = array();

      $rienAFacturer = true;

      foreach($cotisations as $cotisation) {
          $mouvement = ChgtDenomMouvementFactures::freeInstance($this);
          $mouvement->detail_identifiant = $this->numero_archive;
          $mouvement->createFromCotisationAndDoc($cotisation, $this);

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

    public function getFirstChgtDenomFacturable(TemplateFactureCotisationCallbackParameters $produitFilter)
    {
        if ($this->isDeclassement()) {
          return;
        }

        return $this->getFirstChgtDenomOrDeclassementFacturable($produitFilter);
    }

    public function getNbLots(TemplateFactureCotisationCallbackParameters $produitFilter) {
        if ($this->validation_odg) {
            return 0;
        }
        if (!$this->matchFilter($produitFilter)) {
            return 0;
        }
        return 1;
    }

    public function getFirstChgtDenomOrDeclassementFacturable(TemplateFactureCotisationCallbackParameters $produitFilter)
    {

      $chgtdenoms = ChgtDenomClient::getInstance()->getHistoryCampagne(
          $this->identifiant,
          substr($this->validation, 0, 7)
      );

      $today = [];
      foreach ($chgtdenoms as $chgt) {
          if ($chgt->validation_odg && substr($chgt->validation, 0, 10) === substr($this->validation, 0, 10) && $this->matchFilter($produitFilter, $chgt)) {
              $today[$chgt->_id] = $chgt->_id;
          }
      }
      ksort($today);

      $first = current($today);
      return $first !== false && $first == $this->_id;
    }

    public function getSecondChgtDenomOrDeclassementFacturable(TemplateFactureCotisationCallbackParameters $produitFilter)
    {
        return !$this->getFirstChgtDenomOrDeclassementFacturable($produitFilter);
    }

    public function matchFilter(TemplateFactureCotisationCallbackParameters $produitFilter = null, $chgtdenom = null)
    {
        if ($chgtdenom === null) {
            $chgtdenom = $this;
        }

        if ($produitFilter === null) {
            $produitFilter = [];
        }

        $match = true;

        foreach ($produitFilter->getParameters() as $type => $filter) {
            if ($type === 'appellations') {
                // filtre sur produit
                $match = $match && $this->produitFilter($filter, $chgtdenom);
            } elseif ($type === 'millesime') {
                // filtre sur millesime
                $isMillesimeCourant = ($this->changement_millesime == substr($this->getCampagneByDate(),0, 4));
                if(strpos($filter, 'NOT') !== false) {
                    $isMillesimeCourant = !$isMillesimeCourant;
                }
                $match = $match && $isMillesimeCourant;
            } elseif ($type === 'origine') {
                $match = $match && $this->origineFilter($filter);
            } elseif ($type === 'famille') {
                // filtre sur famille
                $match = $match && $this->isDeclarantFamille($filter);
            } elseif ($type === 'campagne') {
                // filtre campagnes suivantes
                $match = $match && $this->sameCampagneFilter($filter);
            }
        }

        return $match;
    }

    private function produitFilter($produitFilter = null, $chgtdenom = null)
    {
      $produitFilter = preg_replace("/^NOT /", "", $produitFilter, -1, $produitExclude);
			$produitExclude = (bool) $produitExclude;
			$regexpFilter = "#(".implode("|", explode(",", $produitFilter)).")#";
			if($produitFilter && !$produitExclude && !preg_match($regexpFilter, $chgtdenom->changement_produit_hash)) {
					return false;
			}
			if($produitFilter && $produitExclude && preg_match($regexpFilter, $chgtdenom->changement_produit_hash)) {
					return false;
			}

            return true;
    }

    public function getVolumeFacturable(TemplateFactureCotisationCallbackParameters $filter)
    {
        if ($this->matchFilter($filter) === false) {
            return;
        }

        if ($this->isDeclassement()) {
            return;
        }

      return $this->changement_volume;
    }

    public function isDeclarantFamille($familleFilter = null)
    {
        if(!$familleFilter){
            return false;
        }

        if(!$this->declarant->famille){
            return false;
        }

        $familleFilterMatch = preg_replace("/^NOT /", "", $familleFilter, -1, $exclude);
        $exclude = (bool) $exclude;
        $regexpFilter = "#^(".implode("|", explode(",", $familleFilterMatch)).")$#";

        if(!$exclude && preg_match($regexpFilter, $this->declarant->famille)) {

            return true;
        }

        if($exclude && !preg_match($regexpFilter, $this->declarant->famille)) {

            return true;
        }

        return false;
    }

    private function origineFilter($filter)
    {
        $not = strpos($filter, 'NOT') === 0;
        $filter = str_replace(['NOT', ' '], '', $filter);
        $origine_produit_hash = $this->origine_produit_hash;

        $matches = array_filter(explode(',', $filter), function ($item) use ($origine_produit_hash) {
            return strpos($origine_produit_hash, str_replace('/origine/', '', $item)) !== false; // si on trouve l'origine, il ressortira dans $match si true
        });

        $found = count($matches) > 0;

        if ($not) {
            $found = ! $found;
        }

        return $found;
    }

    private function sameCampagneFilter($filter)
    {
        $not = strpos($filter, 'NOT') === 0;

        $c = substr($this->changement_origine_lot_unique_id, 0, 4) + 1;
        $dateLimite = new DateTimeImmutable($c."-07-31");
        $dateValidation = new DateTimeImmutable($this->validation);

        $result = $dateLimite > $dateValidation;

        if ($not) {
            $result = ! $result;
        }

        return $result;
    }

    public function getBigDocumentSize() {

        return -1;
    }

    public function verifyGenerateModificative()
    {
        return false;
    }

    public function getVolumeSeuil($produits)
    {
        foreach ($produits as $produit) {
            if (VIP2C::isHashMatch($produit['hash_regex'], $this->changement_produit_hash)) {
                return $produit['volume_max'];
            }
        }

        return 0;
    }

    public function addDonneesForProduction(DouaneProduction $doc)
    {
        if ($this->isFromProduction() === false) {
            return null;
        }

        $hash = str_replace('/declaration/', '', $this->origine_produit_hash);
        if ($doc->getConfiguration()->declaration->exist($hash) === false) {
            return null;
        }

        $doc->add('donnees');
        $item = $doc->get('donnees')->add();

        $item->produit = $hash;
        $item->produit_libelle = $this->origine_produit_libelle;
        $item->complement = $this->origine_specificite;
        $item->categorie = "15";
        $item->categorie_libelle = "Vol. de vin avec AO/IGP avec/sans cépage dans la limite du rdt autorisé";
        $item->valeur = - $this->origine_volume;
    }
}
