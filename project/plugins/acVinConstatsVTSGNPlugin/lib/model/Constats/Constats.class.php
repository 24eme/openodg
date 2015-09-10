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
        $this->campagne = substr($rendezvous->date, 0, 4);

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

        if ($rendezvous->isRendezvousRaisin()) {
            $dateStr = str_replace('-', '', $rendezvous->getDate());
            $cpt = 0;
            foreach ($this->constats as $constatKey => $constat) {
                $matches = array();
                if (preg_match('/^' . $dateStr . '([0-9]{3})$/', $constatKey, $matches)) {
                    if ($cpt < $matches[1]) {
                        $cpt = intval($matches[1]);
                    }
                }
            }
            return sprintf("%s%03d", $dateStr, $cpt + 1);
        } else {
            $idRendezvousOrigine = $rendezvous->rendezvous_origine;
            foreach ($this->constats as $constatKey => $constat) {
                if (($constat->rendezvous_origine == $idRendezvousOrigine) && ($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_NONCONSTATE)) {
                    return $constatKey;
                }
            }
        }
        throw new sfException("L'identifiant du rendez vous ne peut être créer ou trouvé");
        return null;
    }

    public function updateConstatNodeFromJson($constatIdNode, $jsonContent) {
        $constatNode = $this->get('constats')->get($constatIdNode)->updateConstat($jsonContent);
    }

}
