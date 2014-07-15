<?php

class EtablissementClient extends acCouchdbClient {
    
    const TYPE_MODEL = "Etablissement"; 
    const TYPE_COUCHDB = "ETABLISSEMENT";

    public static function getInstance()
    {
      return acCouchdbManager::getClient(self::TYPE_MODEL);
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $doc;
    }

    public function createOrFind($identifiant) {
        $doc = $this->find('ETABLISSEMENT-'.$identifiant);

        if($doc) {

            return $doc;
        }

        return $this->createDoc($identifiant);
    }

    public function createDoc($identifiant) {
        $doc = new Etablissement();
        $doc->identifiant = $identifiant;

        return $doc;
    }
}
