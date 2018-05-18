<?php

class abonnementActions extends sfActions {

    public function executeGenerate(sfWebRequest $request) {
        $compte = $this->getRoute()->getCompte();

        $doc = AbonnementClient::getInstance()->findOrCreateDoc($compte->identifiant, $request->getParameter('annee').'-01-01', $request->getParameter('annee').'-12-31');
        $doc->tarif = AbonnementClient::TARIF_MEMBRE;
        $doc->save();

        return $this->redirect('compte_visualisation', $compte);
    }
}
