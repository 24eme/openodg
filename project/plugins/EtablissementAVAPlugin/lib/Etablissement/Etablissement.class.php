<?php

/**
 * AVA
 */
class Etablissement/*** AVA ***/ extends BaseEtablissement {

    public function constructId() {
        $this->_id = sprintf("%s-%s", EtablissementClient::TYPE_COUCHDB, $this->identifiant);
    }

    public function getChaiDefault() {
        if (count($this->chais) < 1) {

            return null;
        }

        return $this->chais->getFirst();
    }

    public function getNom() {
        return Anonymization::hideIfNeeded($this->getRaisonSociale());
    }

    public function hasFamille($famille) {

        return $this->familles->exist($famille);
    }

    public function getSiret() {

        if (!$this->_get('siret') && $this->siren) {

            return Anonymization::hideIfNeeded($this->siren);
        }

        return Anonymization::hideIfNeeded($this->_get('siret'));
    }

    public function getUniqueEmail() {

        return Anonymization::hideIfNeeded($this->email);
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

    public function getSociete() {
        return null;
    }

    public function synchroFromCompte($compte) {
        $this->raison_sociale = $compte->raison_sociale;
        $this->cvi = $compte->cvi;
        $this->siren = $compte->siren;
        $this->siret = $compte->siret;
        $this->email = $compte->email;
        $this->telephone_bureau = $compte->telephone_bureau;
        $this->telephone_mobile = $compte->telephone_mobile;
        $this->telephone_prive = $compte->telephone_prive;
        $this->fax = $compte->fax;
        $this->adresse = $compte->adresse;
        $this->code_postal = $compte->code_postal;
        $this->commune = $compte->commune;
        $this->compte_id = $compte->identifiant;
        $this->familles = $compte->infos->attributs->toArray(true, false);
        $chais = $compte->chais->toArray(true, false);
        foreach($chais as $key => $chai) {
            unset($chais[$key]['lat']);
            unset($chais[$key]['lon']);
        }

        $this->chais = $chais;
    }

    public function updateCompte() {
        $compte = $this->getCompte();

        $compte->raison_sociale = $this->raison_sociale;
        $compte->siret = $this->siret;
        $compte->adresse = $this->adresse;
        $compte->commune = $this->commune;
        $compte->code_postal = $this->code_postal;
        $compte->telephone_bureau = $this->telephone_bureau;
        $compte->telephone_mobile = $this->telephone_mobile;
        $compte->telephone_prive = $this->telephone_prive;
        $compte->fax = $this->fax;
        $compte->email = $this->email;

        if($compte->adresse != $this->adresse || $compte->commune != $this->commune || $compte->code_postal != $this->code_postal) {
        }

        $compte->save(false, true);
    }

    public function getPays() {

        return "France";
    }

}
