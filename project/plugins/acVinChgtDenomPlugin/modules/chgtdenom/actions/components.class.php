<?php

class chgtdenomComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = $request->getParameter('campagne',ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->enCours = null;
        $chgts = ChgtDenomClient::getInstance()->getHistory($this->etablissement->identifiant);
        foreach($chgts as $chgt) {
            if (!$chgt->isValide() && $chgt->campagne == $this->campagne) {
                $this->enCours = $chgt;
                break;
            }
        }
    }

}
