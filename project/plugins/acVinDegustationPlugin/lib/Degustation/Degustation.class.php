<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation {
    
    public function constructId() {
        $this->identifiant = sprintf("%s-%s", str_replace("-", "", $this->date), $this->appellation);
        $this->set('_id', sprintf("%s-%s", DegustationClient::TYPE_COUCHDB, $this->identifiant));
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration("2014");
    }

    public function setDate($date) {
        $this->date_prelevement_fin = $date;
        
        return $this->_set('date', $date);
    }

    public function getProduits() {

        return $this->getConfiguration()->declaration->certification->genre->appellation_ALSACE->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function getPrelevementsByTournee($agent_id, $date) {

    }

    public function getPrelevementsOrderByHour() {
        $prelevements = array();
        foreach($this->prelevements as $prelevement) {
            $heure = $prelevement->heure;

            if(!$prelevement->heure) {
                $heure = "24:00"; 
            }
            $prelevements[$heure][sprintf('%05d', $prelevement->position)] = $prelevement;
        }

        return $prelevements;
    }

    public function getTournees() {
        $tournees = array();
        foreach($this->prelevements as $prelevement) {
            if(!$prelevement->date) {
                continue;
            }

            if(!$prelevement->agent) {
                continue;
            }

            $tournees[$prelevement->date.$prelevement->agent] = $prelevement->agent;
        }

        return $tournees;
    }

    public function getTourneePrelevements($agent, $date) {
        $prelevements = array();
        foreach($this->prelevements as $prelevement) {
            if($prelevement->agent != $agent) {

                continue;
            }

            if($prelevement->date != $date) {

                continue;
            }

            $prelevements[$prelevement->getKey()] = $prelevement;
        }

        return $prelevements;
    }

    public function storeEtape($etape) {
        if($etape == $this->etape) {
            
            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

}