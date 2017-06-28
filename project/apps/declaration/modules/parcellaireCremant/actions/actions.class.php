<?php

class parcellaireCremantActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {

        $etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $this->parcellaireCremant = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()), true);
        $this->parcellaireCremant->initProduitFromLastParcellaire();
        $this->parcellaireCremant->save();
        return $this->redirect('parcellaire_edit', $this->parcellaireCremant);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $this->parcellaireCremant = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()), true);
        $this->parcellaireCremant->add('papier', 1);
        $this->parcellaireCremant->initProduitFromLastParcellaire();
        $this->parcellaireCremant->save();

        return $this->redirect('parcellaire_edit', $this->parcellaireCremant);
    }


    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }
}
