<?php
abstract class DeclarationLots extends acCouchdbDocument implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceArchivageDocument
{
      protected $declarant_document = null;
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
          $this->piece_document = new PieceDocument($this);
          $this->archivage_document = new ArchivageDocument($this);
      }

      public function initDoc($identifiant, $campagne, $date = null) {
          $this->identifiant = $identifiant;
          $this->date = $date;
          if (!$this->date) {
              $this->date = date("Y-m-d");
          }
          $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($campagne);
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
          $lot->adresse_logement = $this->constructAdresseLogement();

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
          if(!$this->chais->nom && !$this->chais->adresse && !$this->chais->commune && !$this->chais->code_postal) {
              return false;
          }
          return ($this->chais->nom != $this->declarant->nom || $this->chais->adresse != $this->declarant->adresse || $this->chais->commune != $this->declarant->commune || $this->chais->code_postal != $this->declarant->code_postal);
      }


      public function constructAdresseLogement(){
          $completeAdresse = sprintf("%s — %s — %s — %s",$this->declarant->nom,$this->declarant->adresse,$this->declarant->code_postal,$this->declarant->commune);

          if($this->isAdresseLogementDifferente()){
              $completeAdresse = sprintf("%s — %s — %s — %s",$this->chais->nom,$this->chais->adresse,$this->chais->code_postal,$this->chais->commune);
          }

          return trim($completeAdresse);//trim(preg_replace('/\s+/', ' ', $completeAdresse));
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

      public function isTeledeclare() {
          return !$this->isPapier();
      }

      public function isMaster() {

          return true;
      }

      public function isModificative() {

          return false;
      }

      public function getMother() {

          return null;
      }

      public function getVersion() {

          return null;
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

              if($lot->isAffecte()) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC, '1er passage'));
                  continue;
              }
              if ($lot->isAffectable()) {
                  if (!$lot->isChange()) {
                      $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
                  }
              }else{
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONAFFECTABLE));
              }
              if (!$lot->isChange()) {
                  if (!$lot->isAffectable()) {
                      $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGEABLE));
                  }
              }else{
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_SRC, $lot->getLibelle()));
              }
          }
      }

      /**** FIN DES MOUVEMENTS LOTS ****/
}
