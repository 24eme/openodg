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
}
