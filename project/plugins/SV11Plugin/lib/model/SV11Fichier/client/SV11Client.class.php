<?php

class SV11Client extends acCouchdbClient {
	const TYPE_MODEL = 'SV11';
    public static function getInstance()
    {
      return acCouchdbManager::getClient("SV11");
    }  

    public function findByArgs($identifiant, $annee)
    {
    	$id = 'SV11-' . $identifiant . '-' . $annee;
    	return $this->find($id);
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $fichier = new SV11();
        $fichier->campagne = $campagne;
        $fichier->initDoc($identifiant);
        
        if($papier) {
            $fichier->add('papier', 1);
        }

        return $fichier;
    }
}
