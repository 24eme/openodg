<?php

class ParcellaireAffectationCoopApporteur extends BaseParcellaireAffectationCoopApporteur {

    protected $affectationParcellaire = null;

    const STATUT_NON_IDENTIFIEE = "NON_IDENTIFIEE";
    const STATUT_DESACTIVE = "DESACTIVE";
    const STATUT_A_SAISIR = "A_SAISIR";
    const STATUT_EN_COURS = "EN_COURS";
    const STATUT_VALIDE = "VALIDE";

    public function getEtablissementId() {

        return $this->getKey();
    }

    public function getEtablissementIdentifiant() {

        return str_replace("ETABLISSEMENT-", "", $this->getEtablissementId());
    }
    
    public function getEtablissementObject() {

          return EtablissementClient::getInstance()->find($this->getEtablissementId());
    }

    public function getStatut() {
        $affectationParcellaire = $this->getAffectationParcellaire();

        if($affectationParcellaire && $affectationParcellaire->validation) {

            return self::STATUT_VALIDE;
        }

        if($affectationParcellaire) {

            return self::STATUT_EN_COURS;
        }

        if(!$this->getNbParcelles()) {

            return self::STATUT_NON_IDENTIFIEE;
        }
        if(!$this->intention) {

            return self::STATUT_DESACTIVE;
        }
        return self::STATUT_A_SAISIR;
    }

    public function getStatutLibelle() {
        $libelles = array(
            self::STATUT_NON_IDENTIFIEE => "Aucune parcelle identifiée",
            self::STATUT_DESACTIVE => "Coopérateur désactivé",
            self::STATUT_A_SAISIR => "À saisir",
            self::STATUT_EN_COURS => "En cours de saisie",
            self::STATUT_VALIDE => "Validé",
        );

        return $libelles[$this->getStatut()];
    }

    public function getAffectationParcellaire($hydrate = acCouchdbClient::HYDRATE_JSON) {
        if($this->affectationParcellaire !== null) {

            return $this->affectationParcellaire;
        }

        $id = ParcellaireAffectationClient::TYPE_COUCHDB."-".$this->getEtablissementIdentifiant()."-".substr($this->getDocument()->campagne, 0, 4);
        $this->affectationParcellaire = ParcellaireAffectationClient::getInstance()->find($id, $hydrate);

        if(!$this->affectationParcellaire) {
            $this->affectationParcellaire = false;
        }

        return $this->affectationParcellaire;
    }

    public function createAffectationParcellaire() { // Dépréciée mais encore utilisée dans les tests
        $this->affectationParcellaire = ParcellaireAffectationClient::getInstance()->createDoc($this->getEtablissementIdentifiant(), substr($this->getDocument()->campagne, 0, 4));

        return $this->affectationParcellaire;
    }

    public function updateParcelles() {
        $this->nb_parcelles_identifiees = 0;
        $intention = ParcellaireIntentionClient::getInstance()->getLast($this->getEtablissementIdentifiant());
        if(!$intention) {
            $intention = ParcellaireIntentionClient::getInstance()->createDoc($this->getEtablissementIdentifiant(), $this->getDocument()->campagne);
        }
        if ($intention) {
            $this->nb_parcelles_identifiees = count($intention->getParcelles());
        }
    }

    public function getNbParcelles() {
        if (!$this->exist('nb_parcelles_identifiees') || is_null($this->_get('nb_parcelles_identifiees'))) {
            $this->updateParcelles();
        }
        return $this->_get('nb_parcelles_identifiees');
    }
}
