<?php
/**
 * Model for ParcellaireIntentionAffectation
 *
 */

class ParcellaireIntentionAffectation extends ParcellaireAffectation {

  private function getTheoriticalId() {
    $date = str_ireplace("-","",$this->date);
    return ParcellaireIntentionAffectationClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$date;
  }

  public function constructId() {
      $id = $this->getTheoriticalId();
      $this->set('_id', $id);
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
                  $subitem->superficie_affectation = $parcelle->superficie;
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
