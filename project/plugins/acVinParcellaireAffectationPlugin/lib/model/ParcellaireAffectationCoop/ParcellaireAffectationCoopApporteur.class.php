<?php

class ParcellaireAffectationCoopApporteur extends BaseParcellaireAffectationCoopApporteur {

    public function getEtablissementId() {

        return $this->getKey();
    }

    public function getEtablissementIdentifiant() {

        return str_replace("ETABLISSEMENT-", "", $this->getEtablissementId());
    }

    public function getAffectationParcellaire($hydrate = acCouchdbClient::HYDRATE_JSON) {
        $id = ParcellaireAffectationClient::TYPE_COUCHDB."-".$this->getEtablissementIdentifiant()."-".substr($this->getDocument()->campagne, 0, 4);

        return ParcellaireAffectationClient::getInstance()->find($id, acCouchdbClient::HYDRATE_JSON);
    }

    public function createAffectationParcellaire() {

        return ParcellaireAffectationClient::getInstance()->createDoc($this->getEtablissementIdentifiant(), substr($this->getDocument()->campagne, 0, 4));
    }
}
