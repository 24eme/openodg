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


    public function createDoc($date) {
        $degustation = new Degustation();
        $degustation->date = $date;

        return $degustation;
    }

    public function getPrelevements($date_from, $date_to) {
        
        return DRevPrelevementsView::getInstance()->getPrelevements($date_from, $date_to);
    }

    public function getAgents($attribut = null) {

        $agents = CompteClient::getInstance()->getAllComptesPrefixed("A");
        $agents_result = array();

        foreach($agents as $agent) {
            if($agent->statut == CompteClient::STATUT_INACTIF) {
                continue;
            }

            if($attribut && !isset($agent->infos->attributs->{$attribut})) {
                continue;
            }
            
            $agents_result[$agent->_id] = $agent;
        }

        return $agents_result;
    }

    public function getDegustateurs($attribut = null) {

        $degustateurs = CompteClient::getInstance()->getAllComptesPrefixed("D");
        $degustateurs_result = array();

        foreach($degustateurs as $degustateur) {
            if($degustateur->statut == CompteClient::STATUT_INACTIF) {
                continue;
            }

            if($attribut && !isset($degustateur->infos->attributs->{$attribut})) {
                continue;
            }
            
            $degustateurs_result[$degustateur->_id] = $degustateur;
        }

        return $degustateurs_result;
    }

    public function getDegustations($hydrate = acCouchdbClient::HYDRATE_JSON) {

        return $this->startkey("DEGUSTATION-00000000-AAAAAAAAA")
                    ->endkey("DEGUSTATION-999999999-ZZZZZZZZZZ")
                    ->execute($hydrate);
    }
    
}
