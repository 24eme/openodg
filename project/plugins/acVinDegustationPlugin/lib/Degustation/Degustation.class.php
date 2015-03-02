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
        foreach ($this->prelevements as $prelevement) {
            $heure = $prelevement->heure;

            if (!$prelevement->heure) {
                $heure = "24:00";
            }
            $prelevements[$heure][$prelevement->getKey()] = $prelevement;
        }

        return $prelevements;
    }

    public function getTournees() {
        $tournees = array();
        foreach ($this->prelevements as $prelevement) {
            if (!$prelevement->date) {
                continue;
            }

            if (!$prelevement->agent) {
                continue;
            }
            if (!array_key_exists($prelevement->date . $prelevement->agent, $tournees)) {
                $tournees[$prelevement->date . $prelevement->agent] = new stdClass();
                $tournees[$prelevement->date . $prelevement->agent]->prelevements = array();
                $agents = $this->agents->toArray();
                $tournees[$prelevement->date . $prelevement->agent]->id_agent = $prelevement->agent;
                $tournees[$prelevement->date . $prelevement->agent]->nom_agent = $agents[$prelevement->agent]->nom;
                $tournees[$prelevement->date . $prelevement->agent]->date = $prelevement->date;
            }
            $tournees[$prelevement->date . $prelevement->agent]->prelevements[$prelevement->getKey()] = $prelevement;
        }
        ksort($tournees);
        return $tournees;
    }

    public function getTourneePrelevements($agent, $date) {
        $prelevements = array();
        foreach ($this->prelevements as $prelevement) {
            if ($prelevement->agent != $agent) {

                continue;
            }

            if ($prelevement->date != $date) {

                continue;
            }

            $prelevements[$prelevement->getKey()] = $prelevement;
        }

        return $prelevements;
    }

    public function storeEtape($etape) {
        if ($etape == $this->etape) {

            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

}
