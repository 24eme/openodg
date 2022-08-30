<?php
/**
 * Model for ParcellaireIrrigue
 *
 */

class ParcellaireIrrigue extends BaseParcellaireIrrigue implements InterfaceDeclaration {

  protected $declarant_document = null;
  protected $piece_document = null;

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

  public function initDoc($identifiant, $campagne, $date) {
      $this->identifiant = $identifiant;
      $this->campagne = $campagne;
      $this->date = $date;
      $this->constructId();
      $this->storeDeclarant();
      $this->storeParcelles();
  }

  public function storeParcelles() {
  	if ($parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->getLast($this->identifiant, $this->campagne)) {
  		foreach ($parcellaireIrrigable->declaration as $key => $parcelle) {
  			$item = $this->declaration->add($key);
  			$item->libelle = $parcelle->libelle;
  			foreach ($parcelle->detail as $subkey => $detail) {
  				$subitem = $item->detail->add($subkey);
  				$subitem->superficie = $detail->superficie;
  				$subitem->commune = $detail->commune;
  				$subitem->code_commune = $detail->code_commune;
  				$subitem->section = $detail->section;
  				$subitem->numero_parcelle = $detail->numero_parcelle;
  				$subitem->idu = $detail->idu;
  				$subitem->lieu = $detail->lieu;
  				$subitem->cepage = $detail->cepage;
  				$subitem->active = $detail->active;
		  		if($detail->exist('vtsgn')) {
		  			$subitem->add('vtsgn', (int)$detail->vtsgn);
		  		}
  				$subitem->campagne_plantation = $detail->campagne_plantation;
  				$subitem->materiel = $detail->materiel;
  				$subitem->ressource = $detail->ressource;
  			}
  		}
  	}
  }

  public function updateParcelles() {
  	$irrigations = array();
  	foreach ($this->declaration as $key => $parcelle) {
  		foreach ($parcelle->detail as $subkey => $detail) {
  			if ($detail->date_irrigation) {
  				$irrigations[$detail->getHash()] = $detail->date_irrigation;
  			}
  		}
  	}
  	$this->remove('declaration');
  	$this->add('declaration');
  	$this->storeParcelles();
  	foreach ($irrigations as $hash => $date) {
  		if ($this->getDocument()->exist($hash)) {
  			$parcelle = $this->getDocument()->get($hash);
  			$parcelle->date_irrigation = $date;
  			$parcelle->irrigation = 1;
            unset($irrigations[$hash]);
  		}
  	}

    if(count($irrigations) > 0) {
        throw new Exception("Des parcelles déja irrigués disparaissent : ".$this->_id);
    }
  }

  public function getConfiguration() {

      return ConfigurationClient::getInstance()->getConfiguration($this->campagne.'-03-01');
  }


  public function initProduitFromLastParcellaire() {
      if (count($this->declaration) == 0) {
          $this->importProduitsFromLastParcellaire();
      }
  }

  public function getParcellaireCurrent() {

      return ParcellaireClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, date('Y-m-d'));
  }

    public function getParcelles() {

        return $this->declaration->getParcelles();
    }

    public function getParcellesFromLastParcellaire() {
        $parcellaireCurrent = $this->getParcellaireCurrent();
        if (!$parcellaireCurrent) {
          return;
        }

        return $parcellaireCurrent->declaration;
    }

    public function addParcellesFromParcellaire(array $hashes) {
      	$parcellaire = $this->getParcellesFromLastParcellaire();
      	$remove = array();
      	foreach ($this->declaration as $key => $value) {
      		foreach ($value->detail as $subkey => $subvalue) {
      			if (!in_array($subvalue->getHash(), $hashes)) {
      				$remove[] = $subvalue->getHash();
      			}
      		}
      	}
      	foreach ($remove as $r) {
      		$this->declaration->remove(str_replace('/declaration/', '', $r));
      	}
      	foreach ($hashes as $hash) {
      		$hash = str_replace('/declaration/', '', $hash);
    	  	if ($parcellaire->exist($hash) && !$this->declaration->exist($hash)) {
    	  		$detail = $parcellaire->get($hash);
    	  		$produit = $detail->getProduit();
    	  		$item = $this->declaration->add(str_replace('/declaration/', null, $produit->getHash()));
    	  		$item->libelle = $produit->libelle;
    	  		$subitem = $item->detail->add($detail->getKey());

    	  		$subitem->superficie = $detail->superficie;
    	  		$subitem->commune = $detail->commune;
                $subitem->code_commune = $detail->code_commune;
    	  		$subitem->section = $detail->section;
    	  		$subitem->numero_parcelle = $detail->numero_parcelle;
                $subitem->idu = $detail->idu;
    	  		$subitem->lieu = $detail->lieu;
    	  		$subitem->cepage = $detail->cepage;
    	  		$subitem->active = 1;

                $subitem->remove('vtsgn');
                if($detail->exist('vtsgn')) {
                    $subitem->add('vtsgn', (int)$detail->vtsgn);
                }
    	  		$subitem->campagne_plantation = ($detail->exist('campagne_plantation'))? $detail->campagne_plantation : null;
    	  	}
      	}
      	$remove = array();
      	foreach ($this->declaration as $key => $value) {
      		if (!count($value->detail)) {
      			$remove[] = $key;
      		}
      	}
      	foreach ($remove as $r) {
      		$this->declaration->remove($r);
      	}
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

}
