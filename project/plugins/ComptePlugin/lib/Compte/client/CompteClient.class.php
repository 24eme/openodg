<?php

class CompteClient extends acCouchdbClient {
    
    const TYPE_MODEL = "Compte"; 
    const TYPE_COUCHDB = "COMPTE";

    const DROIT_ADMIN = "ADMIN";
    const DROIT_OPERATEUR = "OPERATEUR";

    public static function getInstance()
    {
        return acCouchdbManager::getClient(self::TYPE_MODEL);
    }

    public function findByIdentifiant($identifiant) {

        return $this->find(self::TYPE_COUCHDB.'-'.$identifiant);
    }

    public function getAll($hydrate = self::HYDRATE_DOCUMENT) {

        $query = $this->startkey(sprintf("COMPTE-%s", "aaaaaaaaaaaa"))
                    ->endkey(sprintf("COMPTE-%s", "zzzzzzzzzzzz"));
        
        return $query->execute(acCouchdbClient::HYDRATE_ARRAY);
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $doc;
    }

}
