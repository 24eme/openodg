<?php
abstract class DeclarationLots extends acCouchdbDocument implements InterfaceVersionDocument, InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceArchivageDocument
{
      protected $declarant_document = null;
      protected $version_document = null;
      protected $piece_document = null;
      protected $archivage_document = null;
      protected $etablissement = null;

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

      public function initDoc($identifiant, $date = null) {
          $this->identifiant = $identifiant;
          $this->date = $date;
          if (!$this->date) {
              $this->date = date("Y-m-d");
          }
          $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
          $etablissement = $this->getEtablissementObject();
          $this->constructId();
      }

      public function getConfiguration() {
          $configuration = ConfigurationClient::getInstance()->getConfiguration($this->date);
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

      public function cleanDoc() {
          $this->cleanLots();
          $this->cleanMouvementsLots();
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

      public function cleanMouvementsLots(){
          $this->remove('mouvements_lots');
          $this->add('mouvements_lots');
      }

      public function getLots(){
          if(!$this->exist('lots')) {
              return array();
          }
          $lots = $this->_get('lots')->toArray(1,1);
          if($lots){
              return $this->_get('lots');
          }
          uasort($lots, "DeclarationLots::compareLots");
          return $lots;
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

      public function addLot() {
          $lot = $this->add('lots')->add();
          $lot->id_document = $this->_id;
          $lot->campagne = $this->getCampagne();
          $lot->millesime = preg_replace('/-.*/', '', $this->campagne);
          $lot->declarant_identifiant = $this->identifiant;
          $lot->declarant_nom = $this->declarant->raison_sociale;
          $lot->affectable = true;
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
              $date = date('Y-m-d');
          }

          $this->cleanDoc();
          $this->validation = $date;
      }

      public function validateOdg($date = null, $region = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }
        if(!$region && DrevConfiguration::getInstance()->hasOdgProduits() && DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
            throw new sfException("La validation nécessite une région");
        }
        if(DrevConfiguration::getInstance()->hasOdgProduits() && $region){
            return $this->validateOdgByRegion($date, $region);
        }

        foreach($this->lots as $lot) {
          if($lot->hasBeenEdited()) {
              continue;
          }
          $lot->date = $date;
        }

        $this->cleanDoc();
        $this->validation_odg = $date;
    }

      public function devalidate() {
          $this->validation = null;
          $this->validation_odg = null;
          if($this->exist('etape')) {
              $this->etape = null;
          }
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
          if($this->etablissement) {

              return $this->etablissement;
          }

          $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);

          return $this->etablissement;
      }

      public function isAdresseLogementDifferente() {
          if(!$this->chais->adresse && !$this->chais->commune && !$this->chais->code_postal) {
              return false;
          }
          return ($this->chais->adresse != $this->declarant->adresse || $this->chais->commune != $this->declarant->commune || $this->chais->code_postal != $this->declarant->code_postal);
      }


    	public function getDateValidation($format = 'Y-m-d') {
    		if ($this->validation) {
    			$date = new DateTime($this->validation);
    		} else {
    			$date = new DateTime($this->getDate());
    		}
    		return $date->format($format);
    	}

    	protected function doSave() {
            $this->piece_document->generatePieces();
    	}

        public function save() {
            $this->archiver();
            $this->generateMouvementsLots();

            parent::save();

            $this->saveDocumentsDependants();
        }

        public function saveDocumentsDependants() {
            $mother = $this->getMother();

            if(!$mother) {

                return;
            }

            $mother->save();
        }

        public function archiver() {
            $this->add('type_archive', 'Revendication');
            if (!$this->isArchivageCanBeSet()) {
                return;
            }
            $this->archivage_document->preSave();
            $this->archiverLot($this->numero_archive);
        }

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
            if (!$lot->numero_archive && !$lot->numero_dossier) {
              $num++;
              $lot->numero_archive = sprintf("%05d", $num);
              $lot->numero_dossier = $numeroDossier;
            }
          }
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

      public function generatePieces() {
      	return $this->piece_document->generatePieces();
      }

      public function generateUrlPiece($source = null) {
      	return null;
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
          $doc->cleanMouvementsLots();
          return $doc;
      }

      public function generateNextVersion() {
          throw new sfException("Not implemented");
      }

      public function listenerGenerateVersion($document) {
          $document->constructId();
          $document->clearMouvementsLots();
          $document->clearMouvementsFactures();
          $document->devalidate();
          foreach ($document->getProduitsLots() as $produit) {
            if($produit->exist("validation_odg") && $produit->validation_odg){
              $produit->validation_odg = null;
            }
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

      public function isLectureSeule() {
          return $this->exist('lecture_seule') && $this->get('lecture_seule');
      }

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

      public function getStatutRevendique() {

          return Lot::STATUT_REVENDIQUE;
      }

      public function generateMouvementsLots()
      {
          $this->clearMouvementsLots();

          if (!$this->isValideeOdg()) {
            return;
          }

          foreach ($this->lots as $lot) {
              if($lot->hasBeenEdited()) {
                  continue;
              }

              if(!$this->isMaster() && $this->getMaster()->isValideeOdg() && !$this->getMaster()->getLot($lot->unique_id)) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_REVENDICATION_SUPPRIMEE));
                  continue;
              }

              $lot->updateDocumentDependances();

              $this->addMouvementLot($lot->buildMouvement($this->getStatutRevendique()));

              if ($lot->isAffectable()) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
                  continue;
              }

              if($lot->isAffecte()) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC));
                  continue;
              }

              $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONAFFECTABLE));
          }
      }

      /**** FIN DES MOUVEMENTS LOTS ****/
}
