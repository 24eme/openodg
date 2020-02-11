<?php
/**
 * Model for ParcellaireAffectation
 *
 */

class ParcellaireAffectation extends BaseParcellaireAffectation implements InterfaceDeclaration {

  protected $declarant_document = null;
  protected $piece_document = null;

  public function isAdresseLogementDifferente() {
      return false;
  }
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

  public function initDoc($identifiant, $campagne, $date) {
      $this->identifiant = $identifiant;
      $this->campagne = $campagne;
      $this->set('_id', ParcellaireAffectationClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$this->campagne);
      $this->storeDeclarant();
  }

  public function updateParcelles() {
  	$this->addParcellesFromParcellaire(array_keys($this->getDgc()));
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
      	$toDelete = array();
      	$parcelles = array_keys($this->getParcelles());
      	if (count($parcelles) > 0) {
      	    $parcellaireParcelles = array_keys($parcellaire->getParcelles());
      	    foreach ($parcelles as $parcelleLieu) {
      	        $parcelle =  preg_replace('/\/lieux\/[A-Za-z0-9]+\/couleurs\//', '/lieux/'.Configuration::DEFAULT_KEY.'/couleurs/', $parcelleLieu);
      	        if (!in_array($parcelle, $parcellaireParcelles)) {
      	            $toDelete[str_replace('/declaration/', '', $parcelleLieu)] = 1;
      	        }
      	    }
      	}
      	foreach ($this->getParcelles() as $parcelleLieu => $parcelleLieuObject) {
      	    if (preg_match('/\/lieux\/([A-Za-z0-9]+)\/couleurs\//', $parcelleLieu, $m)) {
      	        if (!in_array($m[1], $lieux)) {
      	            $toDelete[str_replace('/declaration/', '', $parcelleLieuObject->getProduit()->getHash())] = 1;
      	        }
      	    }
      	}
      	foreach ($toDelete as $hash => $v) {
      	    if ($this->declaration->exist($hash)) {
      	     $this->declaration->remove($hash);
      	    }
      	}
      	foreach ($parcellaire as $hash => $parcellaireProduit) {
      	    foreach ($parcellaireProduit->detail as $parcelle) {
      	        if (isset($denominations[$parcelle->code_commune])) {
      	            foreach ($denominations[$parcelle->code_commune] as $lieu) {
      	                $hashWithLieu = str_replace('lieux/'.Configuration::DEFAULT_KEY, 'lieux/'.$lieu, $hash);
      	            }
      	            if (!$this->getConfiguration()->declaration->exist($hashWithLieu)) {
      	                continue;
      	            }
      	            if (!isset($libelleProduits[$hashWithLieu])) {
      	                $libelleProduits[$hashWithLieu] = $this->getConfiguration()->declaration->get($hashWithLieu)->getLibelleFormat();
      	            }
      	            if ($this->declaration->exist($hashWithLieu)) {
      	                $item = $this->declaration->get($hashWithLieu);
      	            } else {
      	                $item = $this->declaration->add($hashWithLieu);
      	                $item->libelle = $libelleProduits[$hashWithLieu];
      	            }
      	            if ($item->detail->exist($parcelle->getKey())) {
      	                continue;
      	            }
      	            $subitem = $item->detail->add($parcelle->getKey());
      	            $subitem->superficie = $parcelle->superficie;
      	            $subitem->commune = $parcelle->commune;
      	            $subitem->code_commune = $parcelle->code_commune;
      	            $subitem->section = $parcelle->section;
      	            $subitem->numero_parcelle = $parcelle->numero_parcelle;
      	            $subitem->idu = $parcelle->idu;
      	            $subitem->lieu = $parcelle->lieu;
      	            $subitem->cepage = $parcelle->cepage;
      	            $subitem->active = 1;
      	            $subitem->remove('vtsgn');
      	            if($parcelle->exist('vtsgn')) {
      	                $subitem->add('vtsgn', (int)$parcelle->vtsgn);
      	            }
      	            $subitem->campagne_plantation = ($parcelle->exist('campagne_plantation'))? $parcelle->campagne_plantation : null;
      	            $subitem->affectation = 0;
      	        }
      	    }
      	}
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
    	if ($this->isNew()) {
    		if ($last = ParcellaireAffectationClient::getInstance()->getLast($this->identifiant)) {
    			$last->add('lecture_seule', true);
    			$last->save();
    		}
    	}
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
        $lieu = $configuration->declaration->get($hash)->getLieu();
        $lieux[$lieu->getKey()] = $lieu->getLibelle();
      }
      ksort($lieux);
      return $lieux;
    }
    
    public function getDgcLibelle($dgc) {
        $dgcs = $this->getDgc();
        return (isset($dgcs[$dgc]))? $dgcs[$dgc] : null;
    }
    
    public function getNextDgc($dgc = null) {
        $dgcs = array_keys($this->getDgc());
        $nb = count($dgcs);
        if (!$nb) {
            return null;
        }
        if (!$dgc) {
            return $dgcs[0];
        }
        $key = array_search($dgc, $dgcs);
        if ($key === false) {
            return null;
        }
        if ($key+1 >= $nb) {
            return null;
        }
        return $dgcs[$key+1];
    }
    
    public function getPrevDgc($dgc = null) {
        $dgcs = array_keys($this->getDgc());
        $nb = count($dgcs);
        if (!$nb) {
            return null;
        }
        if (!$dgc) {
            return $dgcs[$nb-1];
        }
        $key = array_search($dgc, $dgcs);
        if ($key === false) {
            return null;
        }
        if ($key-1 <= 0) {
            return null;
        }
        return $dgcs[$key-1];
    }
    
    public function existDgcFromParcellaire($dgc) {
        $parcellaire = $this->getParcellesFromLastParcellaire();
        $communesDenominations = sfConfig::get('app_communes_denominations');
        if (isset($communesDenominations[$dgc])) {
            $codesInsee = $communesDenominations[$dgc];
            foreach ($parcellaire->getParcelles() as $parcelle) {
                if (in_array($parcelle->code_commune, $codesInsee)) {
                    return true;
                }
            }
        }
        return false;
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
            'libelle' => 'Identification des parcelles affectées '.$this->campagne.' '.$complement,
            'mime' => Piece::MIME_PDF,
            'visibilite' => 1,
            'source' => null
        ));
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
