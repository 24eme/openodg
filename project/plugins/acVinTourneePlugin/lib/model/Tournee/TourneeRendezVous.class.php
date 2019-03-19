<?php
/**
 * Model for TourneeRendezVous
 *
 */

class TourneeRendezVous extends BaseTourneeRendezVous {

    public function getCompteTelephoneBureau(){
        return Anonymization::hideIfNeeded($this->_get('compte_telephone_bureau'));
    }
    public function getCompteTelephoneMobile(){
        return Anonymization::hideIfNeeded($this->_get('compte_telephone_mobile'));
    }
	public function getCompteTelephonePrive(){
		return Anonymization::hideIfNeeded($this->_get('compte_telephone_prive'));
	}
    public function getCompteEmail(){
        return Anonymization::hideIfNeeded($this->_get('compte_email'));
    }
    public function getCompteAdresse() {
        return Anonymization::hideIfNeeded($this->_get('compte_adresse'));
    }
    public function getCompteRaisonSociale() {
        return Anonymization::hideIfNeeded($this->_get('compte_raison_sociale'));
    }
}
