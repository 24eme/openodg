<?php

class chgtdenomComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->hasLots = (count(MouvementLotView::getInstance()->getByDeclarantIdentifiant($this->etablissement->identifiant)->rows) > 0);
    }

}
