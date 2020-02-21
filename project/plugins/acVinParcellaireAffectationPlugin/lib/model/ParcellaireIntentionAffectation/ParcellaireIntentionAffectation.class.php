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

  public function initDoc($identifiant, $campagne, $date) {
      $this->identifiant = $identifiant;
      $this->campagne = $campagne;
      $this->date = $date;
      $this->constructId();
      $this->storeDeclarant();
      $this->storeParcelles();
  }

  public function storeParcelles() {
    $lieux = $this->getConfiguration()->getLieux();
    foreach ($lieux as $cLieu => $lLieu) {
        if ($this->existDgcFromParcellaire($cLieu)) {
            $this->addParcellesFromParcellaire(array($cLieu));
        }
    }
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
