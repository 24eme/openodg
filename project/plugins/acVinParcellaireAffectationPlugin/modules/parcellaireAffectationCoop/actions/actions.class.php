<?php

class parcellaireAffectationCoopActions extends sfActions {

    public function executeApporteurs(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getObject();

        $sv11 = SV11Client::getInstance()->find("SV11-".$etablissement->identifiant."-".$request->getParameter('periode'));

        if(!$sv11) {
            $sv11 = SV11Client::getInstance()->createDoc($etablissement->identifiant, $request->getParameter('periode'));
        }

        $this->form = new SV11ApporteursForm($sv11);
        $this->apporteurs = $this->form->getApporteurs();
        $this->apporteursSV11 = $this->form->getApporteursSV11();
    }

}
