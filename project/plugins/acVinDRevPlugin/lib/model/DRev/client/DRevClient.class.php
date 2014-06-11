<?php

class DRevClient extends acCouchdbClient {

    const TYPE_MODEL = "DRev"; 
    const TYPE_COUCHDB = "DREV";

    public static function getInstance()
    {
      return acCouchdbManager::getClient("DRev");
    } 

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $drev = parent::find($id, $hydrate, $force_return_ls);

        if($drev && $drev->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $drev;
    }
    
    public function createDrev($identifiant, $campagne) 
    {        
        $drev = new DRev();
        $drev->initDrev($identifiant, $campagne);
        $drev->initProduits();
        $drev->initLots();
        return $drev;
    } 
}
