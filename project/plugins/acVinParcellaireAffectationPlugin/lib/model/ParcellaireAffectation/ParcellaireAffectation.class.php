<?php

class ParcellaireAffectation extends BaseParcellaireAffectation implements InterfaceDeclaration {

  protected $declarant_document = null;
  protected $piece_document = null;
  protected $parcelles_idu = null;
  protected $previous_document = null;
  protected $etablissement = null;
  protected $habilitation = null;

  public function isAdresseLogementDifferente() {
      return false;
  }
  public function __construct() {
      parent::__construct();
      $this->initDocuments();
  }

  public function __clone() {
  	parent::__clone();
  	$this->initDocuments();
  	$this->constructId();
  }

  protected function initDocuments() {
      $this->declarant_document = new DeclarantDocument($this);
      $this->piece_document = new PieceDocument($this);
  }

  public function storeDeclarant() {
      $this->declarant_document->storeDeclarant();
  }

    public function getTypeParcellaire() {
    	if ($this->_id) {
    		if (preg_match('/^([A-Z]*)-([A-Z0-9]*)-([0-9]{4})/', $this->_id, $result)) {
    			return $result[1];
    		}
    	}
    	throw new sfException("Impossible de determiner le type de parcellaire");
    }

  public function getEtablissementObject() {
      if(!$this->etablissement) {
          $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
      }
      return $this->etablissement;
  }

  public function constructId() {
      $this->set('_id', ParcellaireAffectationClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$this->periode);
  }

  public function initDoc($identifiant, $periode, $date) {
      $this->identifiant = $identifiant;
      if ($this->exist('date')) {
        $this->date = $date;
      }
      $this->campagne = $periode.'-'.($periode + 1);
      $this->constructId();
      $this->storeDeclarant();
      $this->updateParcellesAffectation();
      $this->recoverPreviousParcelles();
  }

  public function getPeriode() {
      return preg_replace('/-.*/', '', $this->campagne);
  }

  private $cache_parcellaire2ref = null;
  public function getParcellaire2Reference() {
      if (!$this->cache_parcellaire2ref) {
          $intention = ParcellaireIntentionClient::getInstance()->createDoc($this->identifiant, $this->periode + 1);
          if (!$intention) {
              $intention = ParcellaireIntentionClient::getInstance()->createDoc($this->identifiant, $this->periode + 1);
              if (!count($intention->declaration)) {
                  $intention = null;
              }
          }
          $this->cache_parcellaire2ref = $intention;
      }
      return $this->cache_parcellaire2ref;
  }

  public function getPreviousDocument() {
      if($this->previous_document) {
         return $this->previous_document;
      }

      $this->previous_document = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->periode-1);

      return $this->previous_document;
  }

  public function isAllPreviousParcellesExists() {
      if(!$this->getPreviousDocument()) {

          return true;
      }
      $parcellaire2reference = $this->getParcellaire2Reference();
      foreach($this->getPreviousDocument()->getParcelles() as $previousParcelle) {
          if(!$previousParcelle->affectee) {
              continue;
          }
          if(!$parcellaire2reference->findParcelle($previousParcelle)) {
              return false;
          }
      }

      return true;
  }

  public function updateParcellesAffectation() {
    if($this->validation){
        return;
    }
    $intention = $this->getParcellaire2Reference();

    if(!$intention) {
        return;
    }
    $intention->updateParcelles();
    $allready_selected = [];
    foreach ($intention->declaration as $produit) {
        foreach($produit->detail as $parcelle) {
            if (!$parcelle->affectation) {
                continue;
            }
            if($parcelle->isRealParcelleIdFromParcellaire() && $this->findProduitParcelle($parcelle)) {
                continue;
            }
            if(!$parcelle->isRealParcelleIdFromParcellaire() && $this->findParcelle($parcelle, 1, true, $allready_selected)) {
                continue;
            }

            $this->addParcelle($parcelle);
        }
    }
  }

    public function recoverPreviousParcelles() {
        $previous = $this->getPreviousDocument();
        if(!$previous) {
            return;
        }
        $destinataires = $this->getDestinataires();
        foreach($previous->declaration as $produit) {
          foreach($produit->detail as $previousParcelle) {

            if(!$previousParcelle->isAffectee()) {
                continue;
            }

            $pMatch = $this->findProduitParcelle($previousParcelle);
            if($pMatch) {
                $pMatch->affectee = 1;
                $pMatch->superficie = $previousParcelle->superficie;
                if($previousParcelle->exist('destinations')) {
                    $pMatch->remove('destinations');
                    $pMatch->add('destinations');
                    foreach($previousParcelle->destinations as $destinationIdentifiant => $destination) {
                        if(!array_key_exists("ETABLISSEMENT-".$destinationIdentifiant, $destinataires)) {
                            continue;
                        }
                        $pMatch->add('destinations')->add($destinationIdentifiant, $destination);
                    }
                    if(!$pMatch->superficie) {
                        $pMatch->affectee = 0;
                    }
                    $pMatch->updateAffectations();
                } elseif(count($destinataires) && is_object(current($destinataires))) {

                    $pMatch->affecter($previousParcelle->superficie, current($destinataires)->getEtablissement());
                }
            }
          }
        }
    }

    public function getParcellesMultiProduits() {
        $parcelles = [];
        foreach($this->declaration as $produit) {
            foreach($produit->detail as $parcelle) {
                if(!$parcelle->isAffectee()) {
                    continue;
                }
                if(!isset($parcelles[$parcelle->getParcelleId()])) {
                    $parcelles[$parcelle->getParcelleId()] = [];
                }
                $parcelles[$parcelle->getParcelleId()][] = $parcelle;
            }
        }
        return $parcelles;
    }

    public function getParcellesMultiProduitsByParcelleId($parcelleId) {
        $parcelles = $this->getParcellesMultiProduits();

        return isset($parcelles[$parcelleId]) ? $parcelles[$parcelleId] : [];
    }

  public function getConfiguration() {

      return ConfigurationClient::getInstance()->getConfiguration($this->periode.'-03-01');
  }

    public function storeEtape($etape) {
        if ($etape == $this->etape) {

            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

    public function getDeclarantSiret(){
        $siret = "";
        if($this->declarant->siret){
            return $this->declarant->siret;
        }
        if($siret = $this->getEtablissementObject()->siret){
            return $siret;
        }
    }

  public function validate($date = null) {
      if (is_null($date)) {
          $date = date('Y-m-d');
      }
      $this->validation = $date;
      $this->validateOdg();
  }

  public function devalidate() {
      $this->validation = null;
      $this->validation_odg = null;
      $this->etape = null;
  }

  public function validateOdg() {
      $this->validation_odg = date('Y-m-d');
  }

    protected function doSave() {
        $this->piece_document->generatePieces();
        /* $this->checkDestinatairesAreSet(); */
    }


    public function cleanNonAffectee() {
        $todelete = [];
        foreach($this->declaration as $produit) {
            foreach($produit->detail as $p) {
                if ($p->affectee) {
                    continue;
                }
                $todelete[] = $p;
            }
        }
        foreach($todelete as $p) {
            $this->remove($p->getHash());
        }
        foreach($this->declaration as $hash => $produit) {
            if (!count($produit->detail)) {
                $todelete[] = $produit;
            }
        }
        foreach($todelete as $p) {
            $this->remove($p->getHash());
        }
    }

	public function isValidee(){
		return $this->validation;
	}

    public function getDgc($onlyAffectes = false) {
      $lieux = array();
      $configuration = $this->getConfiguration();
      foreach ($this->declaration as $hash => $produit) {
          if ($onlyAffectes) {
              $hasParcelle = false;
              foreach ($produit->detail as $detail) {
                  if ($detail->isAffectee()) {
                      $hasParcelle = true;
                      break;
                  }
              }
              if (!$hasParcelle) {
                  continue;
              }
          }
        $lieu = $configuration->declaration->get($hash);
        $lieux[$lieu->getKey()] = $lieu->getLibelle();
      }
      ksort($lieux);
      return $lieux;
    }

    public function getDgcLibelle($dgc) {
        $dgcs = $this->getDgc();
        return (isset($dgcs[$dgc]))? $dgcs[$dgc] : null;
    }

    public function getParcellesByIduSurface($idu, $surface) {
        $parcelles = $this->getParcelles();
        $find = array();
        foreach ($parcelles as $parcelle) {
            if ($parcelle->idu == $idu && round($parcelle->superficie,4) == round($surface,4)) {
                $find[] = $parcelle;
            }
        }
        return $find;
    }

    public function getDestinataires() {
        $destinataires = [];

        foreach($this->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATIVE) as $liaison) {
            $destinataires[$liaison->id_etablissement] = $liaison;
        }

        foreach($this->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_NEGOCIANT_VINIFICATEUR) as $liaison) {
            $destinataires[$liaison->id_etablissement] = $liaison;
        }

        foreach($this->getParcelles() as $parcelle) {
            if(!$parcelle->exist('destinations')) {
                continue;
            }
            foreach($parcelle->destinations as $destination) {
                if(!isset($destinataires["ETABLISSEMENT-".$destination->identifiant])) {
                    $destinataires["ETABLISSEMENT-".$destination->identifiant] = ['libelle_etablissement' => $destination->nom];
                }
            }
        }

        if(!count($destinataires) || ($this->getHabilitation() && in_array(HabilitationClient::ACTIVITE_VINIFICATEUR, $this->getHabilitation()->getActivitesHabilites()))) {
            $destinataires[$this->getEtablissementObject()->_id] = ['libelle_etablissement' => "Cave particulière"];
        }

        return $destinataires;
    }

    public function getDestinatairesIncomplete() {
        $destinataires = $this->getDestinataires();
        foreach($destinataires as $idDestinataire => $destinataire) {
            $parcellaireCoop = ParcellaireAffectationCoopClient::getInstance()->find(ParcellaireAffectationCoopClient::getInstance()->buildId(str_replace('ETABLISSEMENT-', '', $idDestinataire), $this->getPeriode()));
            if($parcellaireCoop && $parcellaireCoop->exist('apporteurs/ETABLISSEMENT-'.$this->identifiant.'/statuts/'.$this->getType()) && $parcellaireCoop->get('apporteurs/ETABLISSEMENT-'.$this->identifiant.'/statuts/'.$this->getType()) == ParcellaireAffectationCoopApporteur::STATUT_VALIDE_PARTIELLEMENT) {
                unset($destinataires[$idDestinataire]);
            }
        }

        return $destinataires;
    }

    public function checkDestinatairesAreSet()
    {
        $destinataires = $this->getDestinataires();
        $nb_destinataires = count($destinataires);

        foreach ($this->getParcelles() as $parcelle) {
            if ($parcelle->exist('destinations') && count($parcelle->destinations)) {
                continue;
            }

            if ($nb_destinataires === 1) {
                $etablissement = EtablissementClient::getInstance()->find(key($destinataires));
                $parcelle->affecter($parcelle->superficie, $etablissement);
            }

            if ($nb_destinataires > 1 && getenv('DESTINATION_NO_THROW') !== false) {
                $etablissements = [];
                foreach ($destinataires as $id => $destinataire) {
                    $etablissements[] = EtablissementClient::getInstance()->find($id);
                }

                foreach ($etablissements as $etablissement) {
                    $parcelle->affecter($parcelle->superficie, $etablissement);
                }
            } elseif ($nb_destinataires > 1) {
                throw new Exception("Impossible d'ajouter plusieurs destinations dans une parcelle");
            }
        }
    }

    public function getHabilitation() {
        if(is_null($this->habilitation)) {
            $this->habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->getPeriode().'-99-99');
        }

        return $this->habilitation;
    }

  /*** DECLARATION DOCUMENT ***/

  public function isPapier() {

      return $this->exist('papier') && $this->get('papier') === "1";
  }

  public function isAuto() {
      if ($this->exist('papier')) {
          return $this->papier === "1" || $this->papier === 'AUTO';
      }
      return false;
  }

  public function isLectureSeule() {

      return $this->exist('lecture_seule') && $this->get('lecture_seule');
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
    /*** FIN DECLARATION DOCUMENT ***/

    public function getAllPieces() {
        $complement = "(Télédéclaration)";
        if($this->isPapier()) {
            $complement = "(Papier)";
        }

        if($this->isPapier() && $this->exist('signataire') && $this->signataire) {
            $complement = "(".$this->signataire.")";
        }

        return (!$this->getValidation())? array() : array(array(
            'identifiant' => $this->getIdentifiant(),
            'date_depot' => $this->getValidation(),
            'libelle' => 'Identification des parcelles affectées '.$this->periode.' '.$complement,
            'mime' => Piece::MIME_PDF,
            'visibilite' => 1,
            'source' => null
        ));
    }

    public function generatePieces() {
        return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
        return sfContext::getInstance()->getRouting()->generate('parcellaireaffectation_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
        return null;
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
        return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
        return false;
    }

    public static function isPieceEditable($admin = false) {
        return false;
    }

    public function getGroupedParcelles($onlyAffectee = false, $hashproduitFilter = null) {
        if ($this->getDocument()->hasDgc()) {
            return $this->declaration->getParcellesByDgc($onlyAffectee);
        }
        return $this->declaration->getParcellesByCommune($onlyAffectee, $hashproduitFilter);
    }

    public function hasDgc() {
        return (count($this->declaration) >= 1) && !preg_match('/lieux\/DEFAU/', array_keys($this->declaration->toArray())[0]);
    }

    public function hasParcellaire() {
        return ($this->getParcellaire())? true : false;
    }

    public function getGeoJson() {
        $parcellaire = $this->getParcellaire();

        if(!$parcellaire) {

            return "";
        }

        return $parcellaire->getGeoJson();
    }

    public function hasProblemCepageAutorise() {
        foreach($this->getDeclarationParcelles() as $pid => $p) {
            if ($p->hasProblemCepageAutorise()) {
                return true;
            }
        }
        return false;
    }

    public function hasProblemEcartPieds() {
        foreach($this->getDeclarationParcelles() as $pid => $p) {
            if ($p->hasProblemEcartPieds()) {
                return true;
            }
        }
        return false;
    }

    public function hasProblemParcellaire() {
        foreach($this->getDeclarationParcelles() as $pid => $p) {
            if ($p->hasProblemParcellaire()) {
                return true;
            }
        }
        return false;
    }

    public function hasProblemProduitCVI() {
        foreach($this->getDeclarationParcelles() as $pid => $p) {
            if ($p->hasProblemProduitCVI()) {
                return true;
            }
        }
        return false;
    }

    public function getProblemMultiAffectee()
    {
        $ret = [];
        foreach ($this->getParcellesMultiProduits() as $parcelle) {
            $total_superficie_affecte = 0;
            foreach ($parcelle as $parcelleDetail) {
                $total_superficie_affecte += $parcelleDetail->superficie;
                if ($total_superficie_affecte > $parcelleDetail->getSuperficieParcellaire()) {
                    $ret[$parcelleDetail->idu] = ['section' => $parcelleDetail->section, 'numero_parcelle' => $parcelleDetail->numero_parcelle, 'total_superficie_affecte' => $total_superficie_affecte, 'superficie_parcellaire' => $parcelleDetail->getSuperficieParcellaire()];
                    break;
                }
            }
        }
        return $ret;
    }

    public function getProblemPortentiel() {
        $pot = PotentielProduction::cacheCreatePotentielProduction($this->parcellaire, $this, false);
        $ret = [];
        foreach($pot->getProduits() as $prod) {
            if (!$prod->getProduitHash() || !$this->exist($prod->getProduitHash())) {
                continue;
            }
            if ($prod->hasPotentiel() && $prod->hasLimit()) {
                $ret[$prod->getLibelle()] = $prod->getSuperficieMax();
            }
        }
        return $ret;
    }

    public function getTheoriticalPotentielProduction() {
        return PotentielProduction::cacheCreatePotentielProduction($this->parcellaire);
    }

    public function getTheoriticalPotentielProductionProduit($hash) {
        $pot = $this->getTheoriticalPotentielProduction();
        if (!$pot) {
            return null;
        }
        foreach($pot->getProduits() as $prod) {
            if ($prod->getHashProduitAffectation() == $hash) {
                return $prod;
            }
        }
        return null;
    }

    public function getTheoriticalPotentielForHash($hash) {
        $prod = $this->getTheoriticalPotentielProductionProduit($hash);
        if ($prod && $prod->hasPotentiel()) {
            return $prod->getSuperficieMax();
        }
        return null;
    }

    public function addParcelle($parcelle) {
        $this->parcelles_idu = null;
        $produit = $this->declaration->add(str_replace('/declaration/', '', $parcelle->produit_hash));
        $produit->libelle = $produit->getConfig()->getLibelleComplet();
        $pkey = $parcelle->getKey();
        if (strpos($pkey, $parcelle->getParcelleId()) === false) {
            $pkey = $parcelle->getParcelleId();
        }
        if(get_class($parcelle) == "ParcellaireAffectationProduitDetail") {

            return $produit->detail->add($pkey, $parcelle);
        }
        $detail = $produit->detail->add($pkey);
        ParcellaireClient::CopyParcelle($detail, $parcelle, $parcelle->getDocument()->getType() !== 'Parcellaire');
        $detail->origine_doc = $parcelle->getDocument()->_id;
        $detail->add('destinations');
        return $detail;
    }

    public function getSyntheseCepages($filter_produit_hash = null, $filter_insee = null) {
        return ParcellaireClient::getInstance()->getSyntheseCepages($this, $filter_produit_hash, $filter_insee);
    }

    public function getProduits()
    {
        return $this->declaration->getProduits();
    }

}
