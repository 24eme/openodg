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

    public function setDate($date) {
        $this->date_prelevement_fin = $date;
        
        return $this->_set('date', $date);
    }

    public function getPrelevementsOrderByHour() {
        $prelevements = array();
        foreach($this->prelevements as $prelevement) {
            $heure = $prelevement->heure;

            if(!$prelevement->heure) {
                $heure = "24:00"; 
            }
            $prelevements[$heure] = $prelevement;
        }

        return $prelevements;
    }

}