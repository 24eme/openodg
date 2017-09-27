<?php

class DRClient extends acCouchdbClient {
	const TYPE_MODEL = 'DR';
    public static function getInstance()
    {
      return acCouchdbManager::getClient("DR");
    }  

    public function findByArgs($identifiant, $annee)
    {
    	$id = 'DR-' . $identifiant . '-' . $annee;
    	return $this->find($id);
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $fichier = new DR();
        $fichier->campagne = $campagne;
        $fichier->initDoc($identifiant);
        
        if($papier) {
            $fichier->add('papier', 1);
        }

        return $fichier;
    }
}
