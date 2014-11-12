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

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function createDoc($identifiant, $campagne) 
    {  
        $drev = new DRev();
        $drev->initDoc($identifiant, $campagne);

        $etablissement = $drev->getEtablissementObject();

        if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_PRODUCTEUR)) {
            $drev->add('non_recoltant', 1);
        }

        if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_CONDITIONNEUR)) {
            $drev->add('non_conditionneur', 1);
        }

        $drev_previous = $this->find(sprintf("DREV-%s-%s", $identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getPrevious($campagne)));

        if($drev_previous) {
            $drev->updateFromDRev($drev_previous);
        }

        if(count($drev->declaration->getAppellations()) == 0 && $drev->isNonRecoltant()) {
            $drev->initAppellations();
        }

        return $drev;
    }
    
    public function getHistory($cvi, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getPrevious(ConfigurationClient::getInstance()->getCampagneManager()->getCurrent())."";

        return $this->startkey(sprintf("DREV-%s-%s", $cvi, $campagne_from))
                    ->endkey(sprintf("DREV-%s-%s", $cvi, $campagne_to))
                    ->execute($hydrate);
    }
    
    public function getOrdrePrelevements() {
        return array("cuve" => array("cuve_ALSACE", "cuve_GRDCRU", "cuve_VTSGN"), "bouteille" => array("bouteille_ALSACE","bouteille_GRDCRU","bouteille_VTSGN"));
    }
}
