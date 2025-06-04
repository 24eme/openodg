<?php
abstract class DeclarationLots extends acCouchdbDocument implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceArchivageDocument, InterfaceMouvementFacturesDocument
{
      protected $declarant_document = null;
      protected $piece_document = null;
      protected $archivage_document = null;
      protected $etablissement = null;
      protected $mouvement_document = null;

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
          $this->mouvement_document = new MouvementFacturesDocument($this);
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
          return ConfigurationClient::getInstance()->getConfiguration($this->date);
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

      public function addLot($imported = false, $auto_millesime = true) {
          $lot = $this->add('lots')->add();
          $lot->id_document = $this->_id;
          $lot->campagne = $this->getCampagne();
          if ($auto_millesime) {
              $lot->millesime = preg_replace('/-.*/', '', $this->campagne);
          }
          $lot->declarant_identifiant = $this->identifiant;
          $lot->declarant_nom = $this->declarant->raison_sociale;
          $lot->affectable = true;
          $lot->initDefault();
          return $lot;
      }

      public function getLots(){
          if(!$this->exist('lots')) {
              $this->add('lots');
          }
          return $this->_get('lots');
      }

      public function getCurrentLots() {
        $lots = array();
        foreach($this->getLots() as $lot) {
          if ($lot->numero_dossier != $this->numero_archive) {
            continue;
          }
          $lots[] = $lot;
        }
        return $lots;
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

        if(RegionConfiguration::getInstance()->hasOdgProduits() && $region){
            return $this->validateOdgByRegion($date, $region);
        }

        foreach($this->getLots() as $lot) {
          if($lot->hasBeenEdited()) {
              continue;
          }
          $lot->date = $date;
          if($lot->specificite == Lot::SPECIFICITE_UNDEFINED) {
              $lot->specificite = null;
          }
        }

        $this->cleanDoc();
        $this->validation_odg = $date;

        if(!$this->numero_archive) {
            $this->save();
        }

        if(!$this->isFactures()){
            $this->clearMouvementsFactures();
            $this->generateMouvementsFactures();
        }
    }

    public function getRegions() {
        $regions = [];
        foreach ($this->getLots() as $lot) {
            if ($lot->produit_hash) {
                $regions[] = $lot->getRegion();
            }
        }
        return array_values(array_unique($regions));
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
              foreach (RegionConfiguration::getInstance()->getOdgRegions() as $region) {
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
          if (!$this->exist('chais')) {
              return false;
          }
          if(!$this->chais->nom && !$this->chais->adresse && !$this->chais->commune && !$this->chais->code_postal) {
              return false;
          }
          return ($this->chais->nom != $this->declarant->nom || $this->chais->adresse != $this->declarant->adresse || $this->chais->commune != $this->declarant->commune || $this->chais->code_postal != $this->declarant->code_postal);
      }


      public function constructAdresseLogement(){
          $completeAdresse = sprintf("%s — %s — %s %s",$this->declarant->nom,$this->declarant->adresse,$this->declarant->code_postal,$this->declarant->commune);

          if($this->isAdresseLogementDifferente()){
              $completeAdresse = sprintf("%s — %s — %s %s",$this->chais->nom,$this->chais->adresse,$this->chais->code_postal,$this->chais->commune);
          }

          return trim($completeAdresse);//trim(preg_replace('/\s+/', ' ', $completeAdresse));
       }

       public function getDateCommission() {
           if(!$this->exist('date_commission')) {

               return null;
           }

           return $this->_get('date_commission');
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
            $this->updateAdresseLogementLots();
    	}

        public function saveDeclaration($saveDependants = true) {
            $this->archiver();
            if ($this->isValideeOdg()) {
                $this->generateMouvementsLots();
            }

            $regions = $this->getRegions();
            if (count($regions)) {
                $this->add('region', implode('|', $regions));
            }

            $saved = parent::save();

            if($saveDependants) {
                $this->saveDocumentsDependants();
            }

            return $saved;
        }

        public function saveDocumentsDependants() {
            $mother = $this->getMother();

            if(!$mother) {

                return;
            }

            $mother->save(false);
            DeclarationClient::getInstance()->clearCache();
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
          $lots = array();
          foreach($this->getLots() as $lot) {
            if ($lot->numero_archive) {
                continue;
            }
            $lots[] = $lot;
          }
          if(!count($lots)) {
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

      public function getPourcentagesCepages() {
        $volume_total = 0;
        $cepages = array();
        foreach($this->cepages as $volume) {
          $volume_total += $volume;
        }
        foreach($this->cepages as $cep => $volume) {
          if (!isset($cepages[$cep])) {
              $cepages[$cep] = 0;
          }
          $vol = ($volume_total>0)? round(($volume/$volume_total) * 100) : 0;
          $cepages[$cep] += $vol;
        }
        return $cepages;
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

      public function getVolumeRevendiqueLots(TemplateFactureCotisationCallbackParameters $produitFilter){
        $total = 0;
        foreach($this->lots as $lot) {
            if (DRevClient::getInstance()->matchFilterLot($lot, $produitFilter) === false) {
                continue;
            }

            $total += $lot->volume;
        }
        return $total;
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

          foreach ($this->getLots() as $lot) {
              if($lot->hasBeenEdited()) {
                  continue;
              }

              if(!$this->isMaster() && $this->getMaster()->isValideeOdg() && (!$this->getMaster()->getLot($lot->unique_id) || $this->getMaster()->getLot($lot->unique_id)->id_document != $lot->id_document)) {
                  continue;
              }

              $lot->updateDocumentDependances();
              $lot->updateStatut();

              if($lot->id_document_provenance) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_DEST, "Changé pour : ".$lot->getLibelle().", ".$lot->volume." hl"));
              }

              if ($lot->document_ordre == "01") {
                  if ($lot->getDocument()->type == DRevClient::TYPE_MODEL) {
                      $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_REVENDIQUE));
                  }else{
                      $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_DECLARE));
                  }
              }

              if ($lot->elevage === true) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ELEVAGE_EN_ATTENTE));
                  continue;
              }
              if ($lot->exist('eleve') && $lot->eleve) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ELEVE, '', $lot->eleve));
              }

              if($lot->isChange()) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_SRC, $lot->getLibelle()));
              } elseif(!$lot->isAffecte()) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGEABLE));
              }

              if($lot->isAffecte() && !preg_match('/^(TOURNEE)/', $lot->id_document_affectation)) {
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC,  Lot::generateTextePassageMouvement($lot->getNombrePassage() + 1)));

                  continue;
              }
              if($lot->isAffecte() && preg_match('/^(TOURNEE)/', $lot->id_document_affectation)) {
                  continue;
              }
              if ($lot->isAffectable()) {
                  if (!$lot->isChange()) {
                      $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
                  }
              }else{
                  $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONAFFECTABLE));
              }
          }
      }      /**** FIN DES MOUVEMENTS LOTS ****/

    /** MOUVEMENTS FACTURES **/

    public function getTemplateFacture($region = null) {
        return TemplateFactureClient::getInstance()->findByCampagne($this->getPeriode(), $region);
    }

    public function getPeriode() {
        return substr($this->getCampagne(), 0, 4);
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

      if(!$cotisations) {
          $cotisations = [];
      }

      foreach($cotisations as $cotisation) {
          $mouvement = ConditionnementMouvementFactures::freeInstance($this);
          $mouvement->detail_identifiant = $this->numero_archive;
          $mouvement->createFromCotisationAndDoc($cotisation, $this);

          $cle = str_replace('%detail_identifiant%', $mouvement->detail_identifiant, $cotisation->getHash());
          if(isset($cotisationsPrec[$cle])) {
              $mouvement->quantite = $mouvement->quantite - $cotisationsPrec[$cle]->getQuantite();
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

    /** FIN MOUVEMENTS FACTURES **/

    public function getBigDocumentSize() {

        return -1;
    }

    public function hasDocumentDouanier() {
        return false;
    }

    public function updateAdresseLogementLots() {
        foreach($this->lots as $lot) {
            $lot->adresse_logement = $this->constructAdresseLogement();
            if ($lot->exist('secteur')) {
                $lot->secteur = $this->getSecteur();
            }
        }
    }

    public function getSecteur() {
        return ($this->chais->exist('secteur'))? $this->chais->secteur : null;
    }
}
