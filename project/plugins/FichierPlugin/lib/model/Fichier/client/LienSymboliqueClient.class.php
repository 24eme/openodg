<?php

class LienSymboliqueClient extends acCouchdbClient {
	
    public static function getInstance()
    {
      return acCouchdbManager::getClient("LienSymbolique");
    }
    
    public function findByArgs($type, $identifiant, $annee)
    {
    	$id = $type.'-' . $identifiant . '-' . $annee;
    	return $this->find($id);
    }
    
    public function createDoc($type, $etablissement, $annee, $lien)
    {
    	$lienSymbolique = new LienSymbolique();
    	$lienSymbolique->createDoc($type, $etablissement, $annee);
    	$lienSymbolique->fichier = $lien;
    	return $lienSymbolique;
    }
}
