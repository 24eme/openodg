<?php
/**
 * Model for ParcellaireIrrigable
 *
 */

class ParcellaireIrrigable extends BaseParcellaireIrrigable implements InterfaceDeclaration {
  protected $declarant_document = null;
  protected $piece_document = null;

  public function __construct() {
      parent::__construct();
      $this->initDocuments();
  }

  public function __clone() {
      parent::__clone();
      $this->initDocuments();
  }

  public function isAdresseLogementDifferente() {
      return false;
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
  public function initDoc($identifiant, $periode) {
      $this->identifiant = $identifiant;
      $this->campagne = $periode.'-'.($periode + 1);
      $this->set('_id', ParcellaireIrrigableClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$this->periode);
      $this->storeDeclarant();
  }

  public function getPeriode() {
      return preg_replace('/-.*/', '', $this->campagne);
  }

  public function getAcheteursByCVI() {
      $acheteursCvi = array();
      foreach ($this->acheteurs as $type => $acheteurs) {
          foreach ($acheteurs as $cvi => $acheteur) {
              $acheteursCvi[$cvi] = $acheteur;
          }
      }

      return $acheteursCvi;
  }

  public function getAcheteursByHash() {
      $acheteurs = array();

      foreach ($this->getDocument()->acheteurs as $achs) {
          foreach ($achs as $acheteur) {
              $acheteurs[$acheteur->getHash()] = sprintf("%s", $acheteur->nom);
          }
      }

      return $acheteurs;
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
        $this->piece_document->generatePieces();
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

    /*** PIECE DOCUMENT ***/

    public function getAllPieces() {
        $complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
        return (!$this->getValidation())? array() : array(array(
            'identifiant' => $this->getIdentifiant(),
            'date_depot' => $this->getValidation(),
            'libelle' => 'Identification des parcelles irrigables '.$complement,
            'mime' => Piece::MIME_PDF,
            'visibilite' => 1,
            'source' => null
        ));
    }

    public function generatePieces() {
        return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
        return sfContext::getInstance()->getRouting()->generate('parcellaireirrigable_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
        return sfContext::getInstance()->getRouting()->generate('parcellaireirrigable_visualisation', array('id' => $id));
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

    /*** FIN PIECE DOCUMENT ***/
}
