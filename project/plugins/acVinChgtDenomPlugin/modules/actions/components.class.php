<?php

class chgtDenomComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->hasLots = (count(MouvementLotView::getInstance()->getByDeclarantIdentifiant($this->etablissement->identifiant)->rows) > 0)
    }

}
