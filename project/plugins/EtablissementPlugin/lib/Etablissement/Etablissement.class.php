<?php

/**
 * Model for Etablissement
 *
 */
class Etablissement extends BaseEtablissement {

    public function constructId() {
        $this->_id = sprintf("%s-%s", EtablissementClient::TYPE_COUCHDB, $this->identifiant);
    }

    public function getChaiDefault() {
        if (count($this->chais) < 1) {

            return null;
        }

        return $this->chais->getFirst();
    }

    public function hasFamille($famille) {

        return $this->familles->exist($famille);
    }

    public function getSiret() {

        if (!$this->_get('siret') && $this->siren) {

            return $this->siren;
        }

        return $this->_get('siret');
    }

    public function needEmailConfirmation() {
        return (!$this->exist('date_premiere_connexion') || !$this->get('date_premiere_connexion')) ? true : false;
    }

    public function getCompte() {
        if (!$this->exist('compte_id')) {
            throw new sfException("L'etablissement $this->identifiant ne possède pas de compte");
        }
        return CompteClient::getInstance()->findByIdentifiant($this->compte_id);
    }

}
