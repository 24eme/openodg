<?php

class DegustationClient extends acCouchdbClient {
    
    const TYPE_MODEL = "Degustation"; 
    const TYPE_COUCHDB = "DEGUSTATION";

    public static function getInstance()
    {
        
        return acCouchdbManager::getClient("Degustation");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findOrCreate($identifiant, $date, $appellation) {
        $degustation = $this->find(sprintf("%s-%s-%s-%s", self::TYPE_COUCHDB, $identifiant, str_replace("-", "", $date), $appellation));
        if($degustation) {

            return $degustation;
        }

        $degustation = new Degustation();
        $degustation->identifiant = $identifiant;
        $degustation->date_degustation = $date;
        $degustation->appellation = $appellation;

        return $degustation;
    }
}
