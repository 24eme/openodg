<?php

class FichierClient extends acCouchdbClient {
    public static function getInstance()
    {
      return acCouchdbManager::getClient("Fichier");
    }  

    public function createDoc($identifiant, $papier = false)
    {
        $fichier = new Fichier();
        $fichier->initDoc($identifiant);
        
        if($papier) {
            $fichier->add('papier', 1);
        }

        return $fichier;
    }
}
