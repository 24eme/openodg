<?php

class ParcellaireAffectationCoopApporteur extends BaseParcellaireAffectationCoopApporteur {

    protected $declarations = [];

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

    public function getDeclarationStatut($type) {
        $doc = $this->getDeclaration($type);

        if($doc && $doc->validation) {

            return self::STATUT_VALIDE;
        }

        if($doc) {

            return self::STATUT_EN_COURS;
        }

        /*if(!$this->getNbParcelles()) {

            return self::STATUT_NON_IDENTIFIEE;
        }*/
        if(!$this->intention) {

            return self::STATUT_DESACTIVE;
        }
        return self::STATUT_A_SAISIR;
    }

    public function getDeclaration($type, $hydrate = acCouchdbClient::HYDRATE_JSON) {
        if(array_key_exists($type, $this->declarations)) {

            return $this->declarations[$type];
        }

        $client = $type."Client";

        $id = $client::TYPE_COUCHDB."-".$this->getEtablissementIdentifiant()."-".substr($this->getDocument()->campagne, 0, 4);
        $this->declarations[$type] = $client::getInstance()->find($id, $hydrate);

        if(!$this->declarations[$type]) {
            $this->declarations[$type] = false;
        }

        return $this->declarations[$type];
    }

    public function createAffectationParcellaire() { // Dépréciée mais encore utilisée dans les tests
        $this->affectationParcellaire = ParcellaireAffectationClient::getInstance()->createDoc($this->getEtablissementIdentifiant(), substr($this->getDocument()->campagne, 0, 4));

        return $this->affectationParcellaire;
    }

    public function updateParcelles() {
        $this->nb_parcelles_identifiees = 0;
        $intention = ParcellaireIntentionClient::getInstance()->getLast($this->getEtablissementIdentifiant(), $this->getDocument()->periode);
        if(!$intention) {
            $intention = ParcellaireIntentionClient::getInstance()->createDoc($this->getEtablissementIdentifiant(), $this->getDocument()->campagne);
        }
        if ($intention) {
            $intention->updateParcelles();
            $this->nb_parcelles_identifiees = count($intention->getParcelles());
        }
    }

    public function getNbParcelles() {
        if (!$this->exist('nb_parcelles_identifiees') || is_null($this->_get('nb_parcelles_identifiees'))) {
            try {
                $this->updateParcelles();
            }catch(sfException $e) {
                return 0;
            }
        }
        return $this->_get('nb_parcelles_identifiees');
    }
}
