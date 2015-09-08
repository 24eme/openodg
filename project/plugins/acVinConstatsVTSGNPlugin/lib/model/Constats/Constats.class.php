<?php

/**
 * Model for Constats
 *
 */
class Constats extends BaseConstats {

    public function constructId() {
        $this->set('_id', sprintf("%s-%s-%s", ConstatsClient::TYPE_COUCHDB, $this->cvi, $this->campagne));
    }

    public function getCompte() {
        return CompteClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function synchroFromRendezVous(RendezVous $rendezvous) {
        $this->identifiant = $rendezvous->identifiant;
        $this->campagne =  substr($rendezvous->date, 0, 4);
        
        $this->cvi = $rendezvous->cvi;
        $this->email = $rendezvous->email;
        $this->raison_sociale = $rendezvous->raison_sociale;
        $this->lat = $rendezvous->lat;
        $this->lon = $rendezvous->lon;
        $this->adresse = $rendezvous->adresse;
        $this->commune = $rendezvous->commune;
        $this->code_postal = $rendezvous->code_postal;
    }
    
}
