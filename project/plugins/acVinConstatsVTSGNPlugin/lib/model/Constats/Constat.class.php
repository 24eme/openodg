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

}
