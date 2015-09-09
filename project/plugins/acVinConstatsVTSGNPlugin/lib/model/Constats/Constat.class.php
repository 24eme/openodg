<?php

/**
 * Model for Constat
 *
 */
class Constat extends BaseConstat {

    public function createFromRendezVous(Rendezvous $rdv) {
        $this->date_raisin = $rdv->getDateHeure();
        $this->statut_raisin = ConstatsClient::STATUT_NONCONSTATE;
        $this->statut_volume = ConstatsClient::STATUT_NONCONSTATE;
    }
    
    public function updateConstat($jsonContent) {
        if($this->determineTypeConstat() == ConstatsClient::CONSTAT_TYPE_RAISIN){
            $this->produit = $jsonContent->produit->hash_produit;
            $this->nb_botiche = $jsonContent->nb_botiche;
            $this->type_botiche = $jsonContent->type_botiche->type_botiche;
            $this->degre_potentiel_raisin = $jsonContent->degre_potentiel_raisin;
            $this->statut_raisin = $jsonContent->statut;
        }
    }
    
    public function determineTypeConstat() {
        if($this->statut_raisin == ConstatsClient::STATUT_NONCONSTATE){
            return ConstatsClient::CONSTAT_TYPE_RAISIN;
        }
        return ConstatsClient::CONSTAT_TYPE_VOLUME;
    }
}
