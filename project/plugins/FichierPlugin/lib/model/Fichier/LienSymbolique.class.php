<?php
/**
 * Model for LienSymbolique
 *
 */

class LienSymbolique extends BaseLienSymbolique {
	
	protected $typeLien;
	protected $identifiant;
	protected $annee;
	
    public function createDoc($type, $identifiant, $annee) {
    	$this->typeLien = $type;
    	$this->identifiant = $identifiant;
    	$this->annee = $annee;
        parent::__construct();
    }
    public function constructId() {
        $this->set('_id', $this->typeLien.'-' . $this->identifiant . '-' . $this->annee);
    }
    public function getFichierObject() {
    	if ($this-fichier) {
    		return FichierClient::getInstance()->find($this->fichier);
    	}
    	return null;
    }

}
