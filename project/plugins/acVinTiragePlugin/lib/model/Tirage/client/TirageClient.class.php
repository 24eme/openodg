<?php

class TirageClient extends acCouchdbClient {

    const TYPE_MODEL = "Tirage"; 
    const TYPE_COUCHDB = "TIRAGE";
    
    const MILLESIME_ASSEMBLE = "ASSEMBLE";

    public static function getInstance()
    {
        
        return acCouchdbManager::getClient("Tirage");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function createDoc($identifiant, $campagne, $papier = false) 
    {  
        $tirage = new Tirage();
        $tirage->initDoc($identifiant, $campagne, "01");

        if($papier) {
            $tirage->add('papier', 1);
        }

        return $tirage;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getPrevious(ConfigurationClient::getInstance()->getCampagneManager()->getCurrent())."";

        return $this->startkey(sprintf("TIRAGE-%s-%s%s", $identifiant, $campagne_from, "00"))
                    ->endkey(sprintf("TIRAGE-%s-%s%s", $identifiant, $campagne_to, "99"))
                    ->execute($hydrate);
    }

    public function getDateOuvertureDebut() {
        $dates = sfConfig::get('app_dates_ouverture_tirage');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_tirage');

        return $dates['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin(); 
    }
}
