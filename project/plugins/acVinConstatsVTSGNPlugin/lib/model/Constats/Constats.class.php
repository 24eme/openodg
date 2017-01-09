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

    public function getConfiguration() {

        return ConfigurationClient::getConfiguration($this->getDocument()->campagne);
    }

    public function synchroFromRendezVous(RendezVous $rendezvous) {
        $this->identifiant = $rendezvous->identifiant;
        $this->campagne = ConstatsClient::getInstance()->getCampagneByDate($rendezvous->date);

        $this->cvi = $rendezvous->cvi;
        $this->email = $rendezvous->email;
        $this->raison_sociale = $rendezvous->raison_sociale;
        $this->lat = $rendezvous->lat;
        $this->lon = $rendezvous->lon;
        $this->adresse = $rendezvous->adresse;
        $this->commune = $rendezvous->commune;
        $this->code_postal = $rendezvous->code_postal;
    }

    public function getConstatIdNode($rendezvous) {
        $dateStr = str_replace('-', '', $rendezvous->getDate());

        foreach ($this->constats as $constatKey => $constat) {
            if ($rendezvous->isRendezvousRaisin() && $constat->rendezvous_raisin == $rendezvous->_id) {
                return $constatKey;
            }
            if ($rendezvous->isRendezvousVolume() && $constat->rendezvous_volume == $rendezvous->_id) {
                return $constatKey;
            }
        }
        if ($rendezvous->isRendezvousVolume()) {
            throw new sfException("L'identifiant du constat ne peut être créer ou trouvé");
        }
        return sprintf("%s_%s", $dateStr, uniqid());
    }

    public function updateAndSaveConstatNodeFromJson($constatIdNode, $jsonContent) {
        $this->get('constats')->getOrAdd($constatIdNode)->updateConstat($jsonContent);
        $this->save();
    }


}
