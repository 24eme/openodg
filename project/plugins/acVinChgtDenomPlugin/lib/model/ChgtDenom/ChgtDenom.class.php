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
        $this->generateLots();
    }

    public function devalidate() {
        $this->validation = null;
        $this->validation_odg = null;
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
        $this->fillDocToSaveFromLots();

        $this->changement_origine_id_document = $lot->id_document;
        $this->changement_origine_lot_unique_id = $lot->unique_id;
        $this->changement_millesime = $lot->millesime;
        $this->changement_volume = $lot->volume;
        $this->changement_specificite = $lot->specificite;
        $this->changement_numero_logement_operateur = $lot->numero_logement_operateur;
        $this->changement_affectable = $lot->affectable;
        $this->origine_millesime = $lot->millesime;
        $this->origine_volume = $lot->volume;
        $this->origine_specificite = $lot->specificite;
        $this->origine_produit_hash = $lot->produit_hash;
        $this->origine_cepages = $lot->cepages;
        $this->origine_produit_libelle = $lot->produit_libelle;
        $this->origine_numero_logement_operateur = $lot->numero_logement_operateur;
        $this->origine_affectable = $lot->affectable;
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

    public function getLotOrigine() {
        if(!$this->changement_origine_id_document) {
            return false;
        }

        $doc = acCouchdbManager::getClient()->find($this->changement_origine_id_document);

        if(!$doc) {

            return null;
        }

        if (!$doc->getLot($this->changement_origine_lot_unique_id)) {
           $lot = ChgtDenomLot::freeInstance($this);
           $lot->id_document = $this->changement_origine_id_document;
           $lot->unique_id = $this->changement_origine_lot_unique_id;
           $lot->millesime = $this->changement_millesime;
           $lot->volume = $this->changement_volume;
           $lot->specificite = $this->changement_specificite;
           $lot->numero_logement_operateur = $this->changement_numero_logement_operateur;
           $lot->affectable = $this->changement_affectable;
           $lot->millesime = $this->origine_millesime;
           $lot->volume = $this->origine_volume;
           $lot->specificite = $this->origine_specificite;
           $lot->produit_hash = $this->origine_produit_hash;
           $lot->cepages = $this->origine_cepages;
           $lot->produit_libelle = $this->origine_produit_libelle;
           $lot->numero_logement_operateur = $this->origine_numero_logement_operateur;
           $lot->affectable = $this->origine_affectable;
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

    public function saveDocumentsDependants() {
        foreach($this->docToSave as $docId) {
            DeclarationClient::getInstance()->findCache($docId)->save(false);
        }

        $this->docToSave = array();
        DeclarationClient::getInstance()->clearCache();

    }

    public function save($saveDependants = true) {
        $this->archiver();
        $this->generateMouvementsLots();

        parent::save();

        if (count($this->lots) && $saveDependants) {
            $this->fillDocToSaveFromLots();
            $this->saveDocumentsDependants();
        }
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
        $lot->numero_logement_operateur = $this->origine_numero_logement_operateur;
        $lot->millesime = $this->origine_millesime;
        $lot->produit_libelle = $this->origine_produit_libelle;
        $lot->produit_hash = $this->origine_produit_hash;
        $lot->date = $this->date;
        $lot->campagne = $this->campagne;
        $lot->declarant_nom = $this->declarant->raison_sociale;
        $lot->declarant_identifiant = $this->identifiant;
      }

      $ordre = sprintf('%02d', intval($lot->document_ordre) + 1 );
      $lot->document_ordre = $ordre;
      $lot->id_document_provenance = $this->changement_origine_id_document;

      if (!$this->isTotal()) {
        $lotOrig = clone $lot;
        $lotOrig->volume -= $this->changement_volume;

        if ($this->origine_numero_logement_operateur !== $this->getLotOrigine()->numero_logement_operateur) {
            $lotOrig->numero_logement_operateur = $this->origine_numero_logement_operateur;
        }

        $lots[] = $lotOrig;
        $lot->numero_archive = null;
        $lot->unique_id = null;
        $lot->document_ordre = '01';
      }

      $lot->volume = $this->changement_volume;
      $lot->specificite = $this->changement_specificite;

      if ($this->isChgtDenomination()) {
          $lot->produit_hash = $this->changement_produit_hash;
          $lot->produit_libelle = $this->changement_produit_libelle;
          $lot->cepages = $this->changement_cepages;
          if (count($this->changement_cepages->toArray(true, false))) {
              $lot->details = '';
              foreach($this->getPourcentagesCepages() as $cep => $pc) {
                  $lot->details .= $cep.' ('.$pc.'%) ';
              }
          }

          if ($this->exist('changement_affectable')) {
              $lot->affectable = $this->changement_affectable;
          }

          if ($this->exist('changement_numero_logement_operateur') && $this->changement_numero_logement_operateur) {
              $lot->numero_logement_operateur = $this->changement_numero_logement_operateur;
          }
      } else {
          $lot->produit_hash = $this->origine_produit_hash;
          $lot->produit_libelle = $this->origine_produit_libelle;
          $lot->cepages = $this->origine_cepages;
          $lot->specificite .= " DECLASSÉ en VSIG";
      }

      $lots[] = $lot;

      foreach($lots as $l) {
        $lot = $this->_get('lots')->add(null, $l);
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
        if(!count($this->lots)) {
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
                $this->addMouvementLot($this->lots[1]->buildMouvement(Lot::STATUT_DECLASSE, "Déclassé pour ".$this->lots[1]->volume." hl"));
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
            $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGEABLE));
            if($lot->isAffectable()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
            }else{
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONAFFECTABLE));
            }
        }
    }

    /**** FIN DES MOUVEMENTS LOTS ****/

    /**** PIECES ****/
    public function getAllPieces() {
      $lot = $this->getLotOrigine();
      $libelle = ($this->isDeclassement())? 'Déclassement' : 'Changement de dénomination';
      $libelle .= ($this->isTotal())? '' : ' partiel';
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
          foreach ($this->lots as $lot) {
              if($this->changement_produit_hash == $lot->produit_hash){
                  $mouvement->detail_identifiant = $lot->numero_archive;
              }
          }
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
        $regexpFilter = "#(".implode("|", explode(",", $familleFilterMatch)).")#";

        if(!$exclude && preg_match($regexpFilter, $this->declarant->famille)) {

            return true;
        }

        if($exclude && !preg_match($regexpFilter, $this->declarant->famille)) {

            return true;
        }

        return false;
    }
}
