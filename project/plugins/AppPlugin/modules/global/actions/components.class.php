<?php

class globalComponents extends sfComponents {

    public function executeNav(sfWebRequest $request)
    {
        $this->route = $request->getAttribute('sf_route');
        $this->etablissement = null;
        $this->compte = null;

        if($this->route instanceof EtablissementRoute):
            $this->etablissement = $this->route->getEtablissement();
            $this->campagne = $this->route->getCampagne();
            $this->compte = $this->etablissement->getMasterCompte();
        endif;
        if($this->route instanceof FacturationDeclarantRoute || $this->route instanceof FactureRoute || $this->route instanceof CompteRoute):
            $this->compte = $this->route->getCompte();
            $this->societe = $this->compte->getSociete();
            $this->compte = $this->societe->getMasterCompte();
            $this->etablissement = $this->societe->getEtablissementPrincipal();
        endif;
        if($this->route instanceof SocieteRoute):
            $this->societe = $this->route->getSociete();
            $this->etablissement = $this->route->getEtablissement();
            $this->compte = $this->route->getSociete()->getMasterCompte();
        endif;

        if($this->getUser()->isAuthenticated() && !$this->getUser()->isAdminODG() && !$this->getUser()->isStalker() && !$this->getUser()->hasDrevAdmin() && !$this->getUser()->hasHabilitation() && (!$this->compte || !$this->etablissement)):
            $this->compte = $this->getUser()->getCompte();
            $this->societe = $this->compte->getSociete() ; if ($this->societe) $this->etablissement = $this->societe->getEtablissementPrincipal();
            if(!$this->etablissement) $this->etablissement = $this->compte->getEtablissement();
        endif;
    }

}
