<?php

class parcellaireIrrigueComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if (! ($this->etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR) || $this->etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR)))
        {
            return;
        }

        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($this->etablissement->identifiant, $this->periode);
        if(!$this->parcellaireIrrigable->_rev) {
            $this->parcellaireIrrigable = null;
        }
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->getLast($this->etablissement->identifiant, $this->periode);
        if(!$this->parcellaireIrrigable) {
            $this->parcellaireIrrigue = null;
        }
        $this->campagne = sprintf("%d-%d", $this->periode, $this->periode + 1);
        $this->needAffectation = ParcellaireAffectationClient::getInstance()->needAffectation($this->etablissement->identifiant, $this->periode);
    }

}
