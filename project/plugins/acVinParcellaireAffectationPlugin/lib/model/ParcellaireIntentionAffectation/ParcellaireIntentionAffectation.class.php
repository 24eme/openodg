<?php
/**
 * Model for ParcellaireIntentionAffectation
 *
 */

class ParcellaireIntentionAffectation extends ParcellaireAffectation {

  protected function getTheoriticalId() {
    $date = str_ireplace("-","",$this->date);
    return ParcellaireIntentionAffectationClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$date;
  }

  public function constructId() {
      $id = $this->getTheoriticalId();
      $this->set('_id', $id);
  }
  public function initDoc($identifiant, $campagne, $date) {
      $this->identifiant = $identifiant;
      $this->campagne = $campagne;
      if ($this->exist('date')) {
        $this->date = $date;
        $this->updateValidationDoc();
      }
      $this->constructId();
      $this->storeDeclarant();
      $this->storeParcelles();
  }
  
  public function updateValidationDoc() {
      $this->validation = $this->date;
      $this->validation_odg = $this->date;
  }

  public function storeParcelles() {
    $lieux = $this->getConfiguration()->getLieux();
    $lieuxArr = array();
    foreach ($lieux as $cLieu => $lLieu) {
        if ($this->existDgcFromParcellaire($cLieu)) {
            $lieuxArr[] = $cLieu;
        }
    }
    $this->addParcellesFromParcellaire($lieuxArr);
  	if ($parcellaireIntentionAffectation = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->identifiant, $this->campagne)) {
 		foreach ($this->getParcelles() as $hash => $parcelle) {
			            $hashWithoutNumeroOrdre = preg_replace("/(-[0-9]+)-[0-9]{2}(-?)/", '\1\2', $hash);
			                   $parcellesMatch = array();
				                   foreach($parcellaireIntentionAffectation->getParcelles() as $h => $p) {
					                if($hashWithoutNumeroOrdre != preg_replace("/(-[0-9]+)-[0-9]{2}/", '\1', $h)) {
					                         continue;
					                 }
			                       $parcellesMatch[] = $h;
			            }
                
            if(count($parcellesMatch) == 1) {
                $hash = $parcellesMatch[0];
            }		
			if ($parcellaireIntentionAffectation->exist($hash) && $parcellaireIntentionAffectation->get($hash)->affectation) {
  		        $parcelle->affectation = 1;
  		        $parcelle->date_affectation = $parcellaireIntentionAffectation->get($hash)->date_affectation;
  		        $parcelle->superficie_affectation = $parcellaireIntentionAffectation->get($hash)->superficie_affectation;
  		    }
  		}
  	}
  }

  public function updateParcelles() {
      $this->addParcellesFromParcellaire(array_keys($this->getDgc()));
  }

  public function addParcellesFromParcellaire(array $lieux) {
      $parcellaire = $this->getParcellesFromLastParcellaire();
      if (!$parcellaire) {
          return;
      }
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
      $affectees = array();
      $parcelles = $this->getParcelles();
      if (count($parcelles) > 0) {
          foreach ($parcelles as $parcelle) {
              if ($parcelle->affectation) {
                  $affectees[$parcelle->getKey()] = array('date' => $parcelle->date_affectation, 'superficie' => $parcelle->superficie_affectation);
              }
          }
      }
      $this->remove('declaration');
      $this->add('declaration');
      foreach ($parcellaire as $hash => $parcellaireProduit) {
          foreach ($parcellaireProduit->detail as $parcelle) {
              if (isset($denominations[$parcelle->code_commune])) {
                foreach ($denominations[$parcelle->code_commune] as $lieu) {
                  $hashWithLieu = preg_replace('/lieux\/[a-zA-Z0-9]+\/couleurs\/[a-zA-Z0-9]+\/cepages\/[a-zA-Z0-9]+$/', 'lieux/'.$lieu, $hash);
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
                  if (isset($affectees[$parcelle->getKey()]) && $affectees[$parcelle->getKey()]) {
                      $subitem->affectation = 1;
                      $subitem->date_affectation = $affectees[$parcelle->getKey()]['date'];
                      $subitem->superficie_affectation  = $affectees[$parcelle->getKey()]['superficie'];
                  } else {
                    $subitem->affectation = 0;
                    $subitem->superficie_affectation = $parcelle->superficie;
                  }
                  $subitem->origine_doc = $parcelle->getDocument()->_id;
                  $subitem->origine_hash = $parcelle->getHash();
                }
             }
          }
      }
  }

  public function getParcellesFromLastParcellaire() {
      $parcellaireCurrent = $this->getParcellaireCurrent();
      if (!$parcellaireCurrent) {
          return;
      }
  
      return $parcellaireCurrent->declaration;
  }
  
  public function getParcellaireCurrent() {
      return ParcellaireClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, date('Y-m-d'));
  }
  
  public function hasParcellaire() {
      return ($this->getParcellaireCurrent())? true : false;
  }

  public function existDgcFromParcellaire($dgc) {
      $parcellaire = $this->getParcellesFromLastParcellaire();
      if (!$parcellaire) {
          return;
      }
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
  
  public function getDocumentDefinitionModel() {
      return 'ParcellaireIntentionAffectation';
  }

    public function getDateFr() {
        $date = new DateTime($this->date);

        return $date->format('d/m/Y');
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }
    
    protected function doSave() {
    	if ($this->isNew()) {
    		if ($last = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->identifiant)) {
    			$last->add('lecture_seule', true);
    			$last->save();
    		}
    	}
    }

}
