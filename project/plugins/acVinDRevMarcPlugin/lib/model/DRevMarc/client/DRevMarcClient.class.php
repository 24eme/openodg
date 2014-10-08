<?php

class DRevMarcClient extends acCouchdbClient {

    const TYPE_MODEL = "DRevMarc"; 
    const TYPE_COUCHDB = "DREVMARC";

    public static function getInstance()
    {
      return acCouchdbManager::getClient("DRevMarc");
    } 

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }
    
    public function createDoc($identifiant, $campagne) 
    {  
        $drevmarc = new DRevMarc();
        $drevmarc->initDoc($identifiant, $campagne);     

        return $drevmarc;
    }

    public function getHistory($cvi, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getPrevious(ConfigurationClient::getInstance()->getCampagneManager()->getCurrent())."";

        return $this->startkey(sprintf("DREVMARC-%s-%s", $cvi, $campagne_from))
                    ->endkey(sprintf("DREVMARC-%s-%s", $cvi, $campagne_to))
                    ->execute($hydrate);
    }
}
