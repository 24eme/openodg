<?php

class intentionCremantActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {

        $etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $this->intentionCremant = ParcellaireAffectationClient::getInstance()->findOrCreate($etablissement->cvi, $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()), ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT);
        $this->intentionCremant->initProduitFromLastParcellaire();
        $this->intentionCremant->save();
        return $this->redirect('parcellaire_edit', $this->intentionCremant);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $this->intentionCremant = ParcellaireAffectationClient::getInstance()->findOrCreate($etablissement->cvi, $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()), ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT);
        $this->intentionCremant->add('papier', 1);
        $this->intentionCremant->initProduitsFromCVI();
        $this->intentionCremant->updateIntentionCremantFromLastTwoIntentions();
        $this->intentionCremant->save();

        return $this->redirect('parcellaire_edit', $this->intentionCremant);
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
