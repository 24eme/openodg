<?php

class SV12Client extends acCouchdbClient {
	const TYPE_MODEL = 'SV12';
    public static function getInstance()
    {
      return acCouchdbManager::getClient("SV12");
    }  

    public function findByArgs($identifiant, $annee)
    {
    	$id = 'SV12-' . $identifiant . '-' . $annee;
    	return $this->find($id);
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $fichier = new SV12();
        $fichier->campagne = $campagne;
        $fichier->initDoc($identifiant);
        
        if($papier) {
            $fichier->add('papier', 1);
        }

        return $fichier;
    }
}
