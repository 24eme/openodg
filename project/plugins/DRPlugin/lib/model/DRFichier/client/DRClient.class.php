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
    
    public function findAll($limit = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$view = $this->startkey(sprintf(self::TYPE_MODEL."-%s-%s", "00000000", "0000"))
    				 ->endkey(sprintf(self::TYPE_MODEL."-%s-%s", "99999999", "9999"));
    	if ($limit) {
    		$view->limit($limit);
    	}
    	return $view->execute($hydrate)->getDatas();
    }
}
