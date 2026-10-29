<?php

class drapComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if (! ($this->etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR) || $this->etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR)))
        {
            return;
        }

        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);

        $this->drap = DRaPClient::getInstance()->find('DRAP-' . $this->etablissement->identifiant . '-' . $this->periode);

        $this->campagne = sprintf("%d-%d", $this->periode, $this->periode + 1);
        $this->needAffectation = ParcellaireAffectationClient::getInstance()->needAffectation($this->etablissement->identifiant, $this->periode);
    }

}
