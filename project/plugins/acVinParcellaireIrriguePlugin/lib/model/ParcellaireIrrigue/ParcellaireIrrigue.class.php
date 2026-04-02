<?php
/**
 * Model for ParcellaireIrrigue
 *
 */

class ParcellaireIrrigue extends BaseParcellaireIrrigue implements InterfaceDeclaration {

  protected $declarant_document = null;
  protected $piece_document = null;
  protected $parcelles_idu = null;

  public function __construct() {
      parent::__construct();
      $this->initDocuments();
  }

  public function __clone() {
  	if ($this->_id == $this->getTheoriticalId()) {
  		throw new sfException("La date du parcellaire irrigué doit être différente de celle du document d'origine");
  	}
  	parent::__clone();
  	$this->initDocuments();
  	$this->constructId();
  }

  public function isAdresseLogementDifferente() {
      return false;
  }

  private function getTheoriticalId() {
    $date = str_ireplace("-","",$this->date);
    return ParcellaireIrrigueClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$date;
  }

  public function constructId() {
      $id = $this->getTheoriticalId();
      $this->set('_id', $id);
  }

  protected function initDocuments() {
      $this->declarant_document = new DeclarantDocument($this);
      $this->piece_document = new PieceDocument($this);
  }

  public function storeDeclarant() {
      $this->declarant_document->storeDeclarant();
  }

  public function getEtablissementObject() {

      return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
  }

  public function getTypeParcellaire() {
    	if ($this->_id) {
    		if (preg_match('/^([A-Z]*)-([A-Z0-9]*)-([0-9]{4})/', $this->_id, $result)) {
    			return $result[1];
    		}
    	}
    	throw new sfException("Impossible de determiner le type de parcellaire");
  }

  public function initDoc($identifiant, $periode, $date) {
      $this->identifiant = $identifiant;
      $this->campagne = $periode.'-'.($periode + 1);
      $this->date = $date;
      $this->constructId();
      $this->storeDeclarant();
      $this->storeParcelles();
  }

  public function getPeriode() {
      return preg_replace('/-.*/', '', $this->campagne);
  }

  public function getParcellaireIrrigue() {
      return ParcellaireIrrigableClient::getInstance()->getLast($this->identifiant, $this->periode);
  }

  public function getParcellaire2Reference() {
      return $this->getParcellaireIrrigue();
  }

  public function storeParcelles() {
    //throw new sfException('storeParcelles');
  	if ($parcellaireIrrigable = $this->getParcellaireIrrigue()) {
  		foreach ($parcellaireIrrigable->declaration as $key => $parcelle) {
  			$item = $this->declaration->add($key);
  			$item->libelle = $parcelle->libelle;
  			foreach ($parcelle->detail as $subkey => $detail) {
  				$subitem = $item->detail->add($subkey);
  				ParcellaireClient::CopyParcelle($subitem, $detail, true);
  				$subitem->active = $detail->active;
		  		if($detail->exist('vtsgn')) {
		  			$subitem->add('vtsgn', (int)$detail->vtsgn);
		  		}
  				$subitem->campagne_plantation = $detail->campagne_plantation;
  				$subitem->materiel = $detail->materiel;
  				$subitem->ressource = $detail->ressource;
  				$subitem->parcelle_id = $detail->getParcelleId();
  				$subitem->produit_hash = $item->getHash();
  			}
  		}
  	}
  }

  public function updateParcelles( & $error_parcelles = null) {
  	$irrigations = array();
  	foreach ($this->getParcelles() as $key => $parcelle) {
		if (!$parcelle->date_irrigation) {
            continue;
        }

        $irrigations[$parcelle->getHash()] = $parcelle;
  	}
  	$this->remove('declaration');
  	$this->add('declaration');
  	$this->storeParcelles();

    foreach($irrigations as $hash => $oldparcelle) {
        if($oldparcelle->isRealParcelleIdFromParcellaire()) {
            $parcelle = $this->findProduitParcelle($oldparcelle);
        } else {
            $parcelle = $this->findParcelle($oldparcelle);
        }
        if(!$parcelle) {
            continue;
        }
		$parcelle->date_irrigation = $oldparcelle->date_irrigation;
		$parcelle->irrigation = 1;

        unset($irrigations[$hash]);
  	}
    if(count($irrigations) > 0 && $error_parcelles !== null) {
        $error_parcelles["Des parcelles déja irrigués n'existent plus dans le parcellaire"] =  implode(", ", array_keys($irrigations));
    }
  }

  public function getConfiguration() {

      return ConfigurationClient::getInstance()->getConfiguration($this->periode.'-03-01');
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
      foreach($this->getAcheteursByCVI() as $acheteur) {
          $acheteur->email_envoye = null;
      }
  }

  public function validateOdg() {
      $this->validation_odg = date('Y-m-d');
  }

    protected function doSave() {
    	if ($this->isNew()) {
    		if ($last = ParcellaireIrrigueClient::getInstance()->getLast($this->identifiant)) {
    			$last->add('lecture_seule', true);
    			$last->save();
    		}
    	}
        $this->piece_document->generatePieces();
    }

    public function save() {
        $regions = $this->getRegions();
        if (count($regions)) {
            $this->add('region', implode('|', $regions));
        }
        return parent::save();
    }

    public function getRegions() {
        $regions = array();
        foreach ($this->declaration as $key => $value) {
            $regions[] = RegionConfiguration::getInstance()->getOdgRegion($value->getHash());
        }
        return array_filter(array_unique($regions));
    }

	public function isValidee(){
		return $this->validation || $this->validation_odg;
	}

    public function getDateFr() {
        $date = new DateTime($this->date);

        return $date->format('d/m/Y');
    }

  /*** DECLARATION DOCUMENT ***/

  public function isPapier() {

      return $this->exist('papier') && $this->get('papier');
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
        $complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
        return (!$this->getValidation())? array() : array(array(
            'identifiant' => $this->getIdentifiant(),
            'date_depot' => $this->getValidation(),
            'libelle' => 'Identification des parcelles irriguées au '.$this->getDateFr().' '.$complement,
            'mime' => Piece::MIME_PDF,
            'visibilite' => 1,
            'source' => null
        ));
    }

    public function generatePieces() {
        return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
        return sfContext::getInstance()->getRouting()->generate('parcellaireirrigue_export_pdf', $this);
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

    public function getDateIrrigationFromIdParcelle($idParcelle)
    {
        if ($parcelle = $this->findParcelleByIdParcelle($idParcelle)) {
            return $parcelle->date_irrigation;
        }
        return null;
    }

}
