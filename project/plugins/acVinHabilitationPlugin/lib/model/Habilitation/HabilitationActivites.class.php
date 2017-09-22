<?php
/**
 * Model for HabilitationActivites
 *
 */

class HabilitationActivites extends BaseHabilitationActivites {

    public function getOrAddDefaultActivities(){
        foreach (HabilitationClient::$activites_libelles as $activite_key => $libelle) {
          $this->getOrAdd($activite_key);
        }
    }

}
