<?php
/**
 * Model for ParcellaireAffectation
 *
 */

class ParcellaireAffectation extends BaseParcellaireAffectation implements InterfaceDeclaration {

  protected $declarant_document = null;
  protected $piece_document = null;

  public function __construct() {
      parent::__construct();
      $this->initDocuments();
  }

  public function __clone() {
  	if ($this->_id == $this->getTheoriticalId()) {
  		throw new sfException("La date du parcellaire affecté doit être différente de celle du document d'origine");
  	}
  	parent::__clone();
  	$this->initDocuments();
  	$this->constructId();
  }

  private function getTheoriticalId() {
    $date = str_ireplace("-","",$this->date);
    return ParcellaireAffectationClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$date;
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
  }

  

  public function updateParcelles() {
      return;
  	$this->addParcellesFromParcellaire();
  }

  public function getConfiguration() {

      return ConfigurationClient::getInstance()->getConfiguration($this->campagne.'-03-01');
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

    public function addParcellesFromParcellaire(array $lieux) {
      	$parcellaire = $this->getParcellesFromLastParcellaire();
      	$communesDenominations = sfConfig::get('app_communes_denominations');
      	$denominations = array();
      	$libelleProduits = array();
      	foreach ($lieux as $lieu) {
      	     if (isset($communesDenominations[$lieu])) {
      	         foreach ($communesDenominations[$lieu] as $cp) {
      	             if (isset($denominations[$cp])) {
      	                 $denominations[$cp][] = $lieu;
      	             } else {
      	                 $denominations[$cp] = array($lieu);
      	             }
      	         }
      	     }
      	}
      	foreach ($parcellaire as $hash => $parcellaireProduit) {
      	    foreach ($parcellaireProduit->detail as $parcelle) {
      	        if (isset($denominations[$parcelle->code_commune])) {
      	            foreach ($denominations[$parcelle->code_commune] as $lieu) {
      	                $hashWithLieu = str_replace('lieux/DEFAUT', 'lieux/'.$lieu, $hash);
      	            }
      	            if (!$this->getConfiguration()->declaration->exist($hashWithLieu)) {
      	                continue;
      	            }
      	            if (!isset($libelleProduits[$hashWithLieu])) {
      	                $libelleProduits[$hashWithLieu] = $this->getConfiguration()->declaration->get($hashWithLieu)->getLibelleFormat();
      	            }
      	            $item = $this->declaration->add($hashWithLieu);
      	            $item->libelle = $libelleProduits[$hashWithLieu];
      	            $subitem = $item->detail->add($parcelle->getKey());
      	            $subitem->superficie = $parcelle->superficie;
      	            $subitem->commune = $parcelle->commune;
      	            $subitem->code_commune = $parcelle->code_commune;
      	            $subitem->section = $parcelle->section;
      	            $subitem->numero_parcelle = $parcelle->numero_parcelle;
      	            $subitem->idu = $parcelle->idu;
      	            $subitem->lieu = $parcelle->lieu;
      	            $subitem->cepage = $parcelle->cepage;
      	            $subitem->active = 0;
      	            $subitem->remove('vtsgn');
      	            if($parcelle->exist('vtsgn')) {
      	                $subitem->add('vtsgn', (int)$parcelle->vtsgn);
      	            }
      	            $subitem->campagne_plantation = ($parcelle->exist('campagne_plantation'))? $parcelle->campagne_plantation : null;
      	        }
      	    }
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
    		if ($last = ParcellaireAffectationClient::getInstance()->getLast($this->identifiant)) {
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
            'libelle' => 'Identification des parcelles affectées au '.$this->getDateFr().' '.$complement,
            'mime' => Piece::MIME_PDF,
            'visibilite' => 1,
            'source' => null
        ));
    }
    
    public function getDgc(){
      $lieux = array();
      foreach ($this->declaration as $hash => $produit) {
        $lieu = $this->getConfiguration()->declaration->get($hash)->getLieu();
        $lieux[$lieu->getKey()] = $lieu->getLibelle();
      }
      ksort($lieux);
      return $lieux;
    }

    public function generatePieces() {
        return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
        return sfContext::getInstance()->getRouting()->generate('ParcellaireAffectation_export_pdf', $this);
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
