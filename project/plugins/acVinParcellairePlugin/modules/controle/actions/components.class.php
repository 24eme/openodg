<?php
class controleComponents extends sfComponents
{
    public function executeMonEspace(sfWebRequest $request)
    {
        if (!$this->getUser()->isAdmin()) {
            return;
        }
        if (! ($this->etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR) || $this->etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR))) {
            return;
        }
        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->controle = ControleClient::getInstance()->getLast($this->etablissement->identifiant);
    }

}
