<?php

class ParcellaireAffectation extends BaseParcellaireAffectation implements InterfaceDeclaration {

  protected $declarant_document = null;
  protected $piece_document = null;
  protected $parcelles_idu = null;

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

      return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
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
  }

  public function getPeriode() {
      return preg_replace('/-.*/', '', $this->campagne);
  }

  public function getParcellaire2Reference() {
      $intention = ParcellaireIntentionClient::getInstance()->getLast($this->identifiant, $this->periode);
      if (!$intention) {
          $intention = ParcellaireIntentionClient::getInstance()->createDoc($this->identifiant, $this->periode);
          if (!count($intention->declaration)) {
              $intention = null;
          }
      }
      return $intention;
  }

  public function updateParcellesAffectation() {
    if($this->validation){
        return;
    }
    $intention = $this->getParcellaire2Reference();
    $previous = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->periode-1);
    if(!$intention) {
        return;
    }
    $intention->updateParcelles();
	foreach ($intention->getParcelles() as $parcelle) {
	    $produit = $parcelle->getProduit();
        $hash = str_replace('/declaration/', '', $produit->getHash());
        if (!$parcelle->affectation) {
            continue;
        }
        if($this->findParcelle($parcelle, true)) {
            continue;
        }
        $item = $this->declaration->add($hash);
        $item->libelle = $produit->libelle;
        $parcelle->origine_doc = $intention->_id;
        unset($parcelle['origine_hash']);
        $detail = $item->detail->add($parcelle->getParcelleId());
        ParcellaireClient::CopyParcelle($detail, $parcelle);
        $detail->origine_doc = $intention->_id;
	}
    if($previous) {
        foreach($previous->getParcelles() as $previousParcelle) {
            if(!$previousParcelle->affectee) {
                continue;
            }
            $pMatch = $this->findParcelle($previousParcelle);

            if($pMatch) {
                $pMatch->affectee = 1;
            }
        }
	}
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
                  if ($detail->affectation) {
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

    public function isMultiApporteur(){
        return count($this->getCaveCooperatives()) > 1;
    }

    public function getCaveCooperatives(){
        return $this->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATIVE);
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

    public function getParcellesByDgc() {
        $parcelles = array();
        foreach($this->getParcellesFromReference() as $p) {
            if (!$p->produit_hash) {
                continue;
            }
            $lieu_hash = preg_replace('/\/lieux\/.*/', '', $p->produit_hash);
            foreach($p->getTheoriticalDgs() as $h => $d) {
                if (strpos($h, $lieu_hash) === false) {
                    continue;
                }
                if (!isset($parcelles[$d])) {
                    $parcelles[$d] = array();
                }
                $p->produit_hash = $h;
                $parcelles[$d][] = $p;
            }
        }
        return $parcelles;
    }

    public function hasParcellaire() {
        return ($this->getParcellaire())? true : false;
    }

    public function getParcelleFromParcellaire($id) {
        $parcellaire = $this->getParcellaire();

        if(!$parcellaire) {

            return null;
        }

        return $parcellaire->getParcelleFromParcellaireId($id);
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

}
