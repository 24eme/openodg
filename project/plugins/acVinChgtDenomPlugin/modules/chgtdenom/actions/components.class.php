<?php

class chgtdenomComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $defaultCampagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent().'-'.(ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()+1);
        $this->campagne = $request->getParameter('campagne',$defaultCampagne);

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
