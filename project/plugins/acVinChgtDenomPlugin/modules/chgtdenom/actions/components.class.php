<?php

class chgtdenomComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->hasLots = (count(MouvementLotView::getInstance()->getByDeclarantIdentifiant($this->etablissement->identifiant)->rows) > 0);
        $this->enCours = null;
        if ($this->hasLots) {
            $chgts = ChgtDenomClient::getInstance()->getHistory($this->etablissement->identifiant);
            foreach($chgts as $chgt) {
                if (!$chgt->isValide()) {
                    $this->enCours = $chgt;
                    break;
                }
            }
        }
    }

}
