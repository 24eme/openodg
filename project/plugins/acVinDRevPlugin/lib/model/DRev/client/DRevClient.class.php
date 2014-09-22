<?php

class DRevClient extends acCouchdbClient {

    const TYPE_MODEL = "DRev"; 
    const TYPE_COUCHDB = "DREV";

    public static function getInstance()
    {
      return acCouchdbManager::getClient("DRev");
    } 

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $doc;
    }

    public function createDoc($identifiant, $campagne) 
    {  
        $drev = new DRev();
        $drev->initDeclaration($identifiant, $campagne);

        $drev_previous = $this->find(sprintf("DREV-%s-%s", $identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getPrevious($campagne)));

        if($drev_previous) {
            $drev->updateFromDRev($drev_previous);
        }

        return $drev;
    }
    
    public function getHistory($hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->startkey('DREV-0000000000-0000-0000')->endkey('DREV-9999999999-9999-9999')->execute($hydrate);
        }
}
