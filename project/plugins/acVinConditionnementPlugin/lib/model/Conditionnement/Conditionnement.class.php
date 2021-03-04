<?php

/**
 * Model for Conditionnement
 *
 */
class Conditionnement extends BaseConditionnement implements InterfaceVersionDocument, InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceArchivageDocument {


    protected $declarant_document = null;
    protected $version_document = null;
    protected $piece_document = null;
    protected $archivage_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
        $this->version_document = new VersionDocument($this);
        $this->piece_document = new PieceDocument($this);
        $this->archivage_document = new ArchivageDocument($this);
    }

    public function constructId() {
        if (!$this->date) {
            $this->date = date("Y-m-d");
        }
        $idDate = str_replace('-', '', $this->date).date('Hi');
        $id = 'CONDITIONNEMENT-' . $this->identifiant . '-' . $idDate;
        if($this->version) {
            $id .= "-".$this->version;
        }
        $this->set('_id', $id);
    }

    public function getConfiguration() {
        $configuration = ConfigurationClient::getInstance()->getConfiguration($this->campagne.'-10-01');
        if(ConfigurationConfiguration::getInstance()->hasEffervescentVinbase()){
          $configuration->setEffervescentVindebaseActivate();
        }
        return $configuration;
    }

    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduits();
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isAutomatique() {

        return $this->exist('automatique') && $this->get('automatique');
    }

    public function getValidation() {

        return $this->_get('validation');
    }

    public function getValidationOdg() {

        return $this->_get('validation_odg');
    }

    public function initDoc($identifiant, $campagne, $date = null) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $this->date = $date;
        if (!$this->date) {
            $this->date = date("Y-m-d");
        }
        $etablissement = $this->getEtablissementObject();
    }

    public function addLotFromDegustation($lot) {
        $lot_degustation = clone $lot;

        $lot_degustation->remove('details');
        $lot_degustation->remove('numero_table');
        $lot_degustation->remove('leurre');
        $lot_degustation->remove('conformite');
        $lot_degustation->remove('motif');
        $lot_degustation->remove('observation');
        $lot_degustation->remove('declarant_nom');
        $lot_degustation->remove('declarant_identifiant');
        $lot_degustation->remove('origine_mouvement');
        $lot_degustation->statut = Lot::STATUT_PRELEVABLE;

        $lots = [];
        foreach ($this->lots as $lot) {
            $lots[] = $lot;
        }
        $lots[] = $lot_degustation;

        $this->remove('lots');
        $this->add('lots', $lots);

        return $lot_degustation;
    }

    public function cleanDoc() {
        $this->cleanLots();
        $this->clearMouvementsLots();
    }

    public function cleanLots() {
        if(!$this->exist('lots')) {
            return;
        }
        $lotsToKeep = array();

        foreach($this->lots as $keyLot => $lot) {
            if(!$lot->isCleanable()) {
                $lotsToKeep[] = $lot;
            }
        }
         $this->remove('lots');
         $this->add('lots', $lotsToKeep);
    }

    public function getLots(){
        if(!$this->exist('lots')) {

            return array();
        }
        $lots = $this->_get('lots')->toArray(1,1);
        if($lots){
            return $this->_get('lots');
        }
        uasort($lots, "Conditionnement::compareLots");
        return $lots;
    }

    public function getLotsByCouleur($visualisation = true) {
        $couleurs = array();

        foreach ($this->getLots() as $lot) {
           if($visualisation && !$lot->hasVolumeAndHashProduit()){
             continue;
           }
          $couleur = "vide";
          if($lot->produit_hash){
            $couleur = $lot->getConfigProduit()->getCouleur()->getLibelleComplet();
          }
            if (!isset($couleurs[$couleur])) {
                $couleurs[$couleur] = array();
            }
            $couleurs[$couleur][] = $lot;
        }
        return $couleurs;
    }

    public static function compareLots($lotA, $lotB){
        $dateA = $lotA->getDate();
        $dateB = $lotB->getDate();
        if(empty($dateA)){
            if(!empty($dateB)){
                return $dateB;
            }
        }
        return strcasecmp($dateA, $dateB);
    }

    public function addLot() {
        $lot = $this->add('lots')->add();
        $lot->initDefault();
        
        return $lot;
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

    public function storeLotsDateVersion($date) {
        if($this->exist('lots')){
          foreach($this->lots as $lot) {
              if($lot->hasVolumeAndHashProduit() && (!$lot->exist('id_document') || !$lot->id_document)){
                $lot->add('id_document',$this->_id);
                $lot->add('date',$date);
              }
              foreach ($lot as $key => $field) {
                if($lot->hasVolumeAndHashProduit() && $this->getDocument()->isModifiedMother($lot->getHash(), $key)){
                  $lot->date = $date;
                  $lot->id_document = $this->_id;
                  break;
                }
              }
          }
        }

    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('c');
        }

        $this->storeLotsDateVersion($date);
        $this->cleanDoc();
        $this->validation = $date;
        $this->archiver();
        $this->generateMouvementsLots();
        $this->updateStatutsLotsSupprimes();
    }



    public function delete() {
        $this->updateStatutsLotsSupprimes(false);
        return parent::delete();
    }

    public function devalidate($reinit_version_lot = true) {
        $this->validation = null;
        $this->validation_odg = null;
        if($this->exist('etape')) {
            $this->etape = null;
        }
        if($reinit_version_lot && ConfigurationClient::getCurrent()->declaration->isRevendicationParLots() && $this->exist('lots')){
          foreach($this->lots as $lot) {
              if($lot->exist('date') && $lot->date && ($this->_id == $lot->id_document)){
                $lot->date = null;
                $lot->id_document = null;
              }
          }
        }
        $this->updateStatutsLotsSupprimes(false);
    }

    public function updateStatutsLotsSupprimes($validation = true) {
      if (!$this->hasVersion()) {
        return;
      }
      $mother = $this->getMother();
      $updated = false;
      if ($mother)
      foreach ($mother->getLots() as $lot) {
        if ($validation && $lot->statut == Lot::STATUT_PRELEVABLE && !$this->mouvements_lots->get($this->identifiant)->exist($lot->getUnicityKey())) {
          $lot->statut = Lot::STATUT_NONPRELEVABLE;
          $updated = true;
        }
        if (!$validation && $lot->statut == Lot::STATUT_NONPRELEVABLE && !$this->mouvements_lots->get($this->identifiant)->exist($lot->getUnicityKey())) {
          $lot->statut = Lot::STATUT_PRELEVABLE;
          $updated = true;
        }
      }
      if ($updated) {
        $mother->generateMouvementsLots();
        $mother->save();
      }
    }

    public function validateOdg($date = null, $region = null) {
        if(is_null($date)) {
            $date = date('c');
        }

        if(!$region && DrevConfiguration::getInstance()->hasOdgProduits() && DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
            throw new sfException("La validation nécessite une région");
        }

        if(DrevConfiguration::getInstance()->hasOdgProduits() && $region){
            return $this->validateOdgByRegion($date, $region);
        }

        $this->validation_odg = $date;
    }

    protected function validateOdgByRegion($date = null, $region = null) {
        if($region) {
            foreach ($this->getProduits($region) as $hash => $produit) {
                $produit->validateOdg($date);
            }
        } else {
            foreach (DrevConfiguration::getInstance()->getOdgRegions() as $region) {
                $this->validateOdg($date, $region);
            }
        }

        $allValidate = true;
        foreach ($this->declaration->getProduits() as $key => $produit) {
            if($produit->isValidateOdg()){
               continue;
            }
            $allValidate = false;
            break;
        }

        if($this->isModificative()){
            $this->getMother()->validateOdgByRegion($date, $region);
            $this->getMother()->save();
        }

        if(!$allValidate) {

            return;
        }

        $this->validation_odg = $date;
    }

    public function isValidateOdgByRegion($region){
      if (!$region) {
          return false;
      }
      foreach ($this->getProduits($region) as $hash => $produit) {
        if(!$produit->isValidateOdg()){
          return false;
        }
      }
      return true;
    }

    public function getValidationOdgDateByRegion($region){
      if(!$region){
        return null;
      }
      foreach ($this->getProduits($region) as $hash => $produit) {
        if($produit->isValidateOdg()){
          return $produit->validation_odg;
        }
      }
      return null;
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function isAdresseLogementDifferente() {
        if(!$this->chais->adresse && !$this->chais->commune && !$this->chais->code_postal) {

            return false;
        }

        return ($this->chais->adresse != $this->declarant->adresse || $this->chais->commune != $this->declarant->commune || $this->chais->code_postal != $this->declarant->code_postal);
    }

	protected function doSave() {
        $this->piece_document->generatePieces();
	}

  public function archiver() {
      $this->archivage_document->preSave();
      if ($this->isArchivageCanBeSet()) {
          $this->archiverLot($this->numero_archive);
      }
  }

  /*** ARCHIVAGE ***/

  public function getNumeroArchive() {

      return $this->_get('numero_archive');
  }

  public function isArchivageCanBeSet() {

      return $this->isValidee();
  }

  public function archiverLot($numeroDossier) {
      $lastNum = ArchivageAllView::getInstance()->getLastNumeroArchiveByTypeAndCampagne(Lot::TYPE_ARCHIVE, $this->archivage_document->getCampagne());
      $num = 0;
      if (preg_match("/^([0-9]+).*/", $lastNum, $m)) {
        $num = $m[1];
      }
      foreach($this->lots as $lot) {
        if (empty($lot->numero_archive) && empty($lot->numero_dossier)) {
          $num++;
          $lot->numero_archive = sprintf("%05d", $num);
          $lot->numero_dossier = $numeroDossier;
        }
      }
  }

  /*** FIN ARCHIVAGE ***/


	public function getDateValidation($format = 'Y-m-d')
	{
		if ($this->validation) {
			$date = new DateTime($this->validation);
		} else {
			$date = new DateTime($this->getDate());
		}
		return $date->format($format);
	}

    /**** MOUVEMENTS ****/

    public function hasLotUnicityKey($key) {
        foreach($this->lots as $k => $lot) {
            if ($lot->getUnicityKey() == $key) {
                return true;
            }
        }
        return false;
    }

    public function getPourcentagesCepages($cepages) {
      $total = 0;
      $result = array();
      foreach($cepages as $pc) {
        $total += $pc;
      }
      foreach($cepages as $cep => $pc) {
        if (!isset($result[$cep])) {
          $result[$cep] = 0;
        }
        $result[$cep] += round(($pc/$total) * 100);
      }
      return $result;
    }

    private function generateMouvementLotsFromLot($lot, $key) {
        $mvt = new stdClass();
        $mvt->date = $lot->date;
        $mvt->statut = $lot->statut;
        $mvt->numero_dossier = $lot->numero_dossier;
        $mvt->numero_archive = $lot->numero_archive;
        $mvt->numero_cuve = $lot->numero_cuve;
        $mvt->millesime = $lot->millesime;
        $mvt->volume = $lot->volume;
        $mvt->elevage = $lot->elevage;
        $mvt->produit_hash = $lot->produit_hash;
        $mvt->produit_libelle = $lot->produit_libelle;
        $mvt->produit_couleur = $lot->getCouleurLibelle();
        $mvt->region = '';
        $mvt->version = $this->getVersion();
        $mvt->origine_hash = $lot->getHash();
        $mvt->origine_type = 'conditionnement';
        $mvt->origine_document_id = $this->_id;
        $mvt->id_document = $this->_id;
        $mvt->origine_mouvement = '/mouvements_lots/'.$this->identifiant.'/'.$key;
        $mvt->declarant_identifiant = $this->identifiant;
        $mvt->declarant_nom = $this->declarant->raison_sociale;
        $mvt->destination_type = $lot->destination_type;
        $mvt->destination_date = $lot->destination_date;
        $mvt->details = '';
        $mvt->centilisation = $lot->centilisation;

        $tabCepages=[];

        foreach($this->getPourcentagesCepages($lot->cepages) as $cep => $pc){
          $tabCepages[$cep]=$pc;
        }
        arsort($tabCepages);

        foreach($tabCepages as $cep => $pc) {
          if (strlen($mvt->details)==0){
            $mvt->details .=$cep.' ('.$pc.'%)';
          }
          else{
            $mvt->details .= ' '.$cep.' ('.$pc.'%)';
          }
        }
        $mvt->region = '';
        $mvt->campagne = $this->campagne;
        if($lot->exist('specificite')){
          $mvt->specificite = $lot->specificite;
        }
        return $mvt;
    }

    public function generateAndAddMouvementLotsFromLot($lot, $key) {
        $mvt = $this->generateMouvementLotsFromLot($lot, $key);
        return $this->add('mouvements_lots')->get($this->identifiant)->add($key, $mvt);
    }

    public function generateMouvementsLots() {
        if(!$this->add('mouvements_lots')->exist($this->identifiant)) {
            $this->add('mouvements_lots')->add($this->identifiant);
        }
        foreach($this->lots as $k => $lot) {
            $key = $lot->getUnicityKey();
            $mvt = $this->generateAndAddMouvementLotsFromLot($lot, $key);
        }
    }

    public function clearMouvementsLots(){
        $this->remove('mouvements_lots');
        $this->add('mouvements_lots');
    }

    /**** FIN DES MOUVEMENTS ****/

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
      $date = null;
      if ($this->getValidation()) {
        $dt = new DateTime($this->getValidation());
        $date = $dt->format('Y-m-d');
      }
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $date,
    		'libelle' => 'Déclaration de conditionnement '.$complement,
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
    	return sfContext::getInstance()->getRouting()->generate('conditionnement_visualisation', array('id' => $id));
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

    public function getCategorie(){
      return strtolower($this->type);
    }

    /**** FIN DES PIECES ****/

    /**** VERSION ****/

    public static function buildVersion($rectificative, $modificative) {

        return VersionDocument::buildVersion($rectificative, $modificative);
    }

    public static function buildRectificative($version) {

        return VersionDocument::buildRectificative($version);
    }

    public static function buildModificative($version) {

        return VersionDocument::buildModificative($version);
    }

    public function getVersion() {

        return $this->_get('version');
    }

    public function hasVersion() {

        return $this->version_document->hasVersion();
    }

    public function isVersionnable() {
        if (!$this->validation) {

            return false;
        }

        return $this->version_document->isVersionnable();
    }

    public function getRectificative() {

        return $this->version_document->getRectificative();
    }

    public function isRectificative() {

        return $this->version_document->isRectificative();
    }

    public function isRectifiable() {

        return false;
    }

    public function getModificative() {

        return $this->version_document->getModificative();
    }

    public function isModificative() {

        return $this->version_document->isModificative();
    }

    public function isModifiable() {
        return $this->version_document->isModifiable();
    }

    public function isTeledeclare() {
        return !$this->isPapier();
    }

    public function getPreviousVersion() {

        return $this->version_document->getPreviousVersion();
    }

    public function getMasterVersionOfRectificative() {

        throw new sfException("Not implemented");
    }

    public function needNextVersion() {

        return $this->version_document->needNextVersion() || !$this->isSuivanteCoherente();
    }

    public function getMaster() {

        return $this->version_document->getMaster();
    }

    public function isMaster() {

        return $this->version_document->isMaster();
    }

    public function findMaster() {

        return ConditionnementClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant, $this->campagne);
    }

    public function findDocumentByVersion($version) {
        $tabId = explode('-', $this->_id);
        if (count($tabId) < 3) {
          throw new sfException("Doc id incoherent");
        }
        $id = $tabId[0].'-'.$tabId[1].'-'.$tabId[2];
        if($version) {
            $id .= "-".$version;
        }

        return ConditionnementClient::getInstance()->find($id);
    }

    public function getMother() {

        return $this->version_document->getMother();
    }

    public function motherGet($hash) {

        return $this->version_document->motherGet($hash);
    }

    public function motherExist($hash) {

        return $this->version_document->motherExist($hash);
    }

    public function motherHasChanged() {
        if ($this->declaration->total != $this->getMother()->declaration->total) {

            return true;
        }

        if (count($this->getProduitsDetails($this->teledeclare)) != count($this->getMother()->getProduitsDetails($this->teledeclare))) {

            return true;
        }

        if ($this->droits->douane->getCumul() != $this->getMother()->droits->douane->getCumul()) {

            return true;
        }

        return false;
    }

    public function getDiffWithMother() {

        return $this->version_document->getDiffWithMother();
    }

    public function isModifiedMother($hash_or_object, $key = null) {

        return $this->version_document->isModifiedMother($hash_or_object, $key);
    }

    public function generateRectificative() {

        return $this->version_document->generateRectificative();
    }

    public function generateModificative() {
        $doc = $this->version_document->generateModificative();
        $doc->clearMouvementsLots();
        $doc->clearMouvementsFactures();
        return $doc;
    }

    public function generateNextVersion() {

        throw new sfException("Not implemented");
    }

    public function listenerGenerateVersion($document) {
        $document->devalidate(false);
        foreach ($document->getProduitsLots() as $produit) {
          if($produit->exist("validation_odg") && $produit->validation_odg){
            $produit->validation_odg = null;
          }
        }
        foreach ($document->lots as $lot) {
          $lot->statut = Lot::STATUT_NONPRELEVABLE;
        }
    }

    public function listenerGenerateNextVersion($document) {

    }

    public function getSuivante() {

        throw new sfException("Not implemented");
    }

    public function isValidee() {

        return $this->validation;
    }

    public function isValideeOdg() {

        return boolval($this->getValidationOdg());
    }

    public function isLotsEditable(){
      return $this->isValideeOdg() && $this->isValidee();
    }

    /**** FIN DE VERSION ****/

}
