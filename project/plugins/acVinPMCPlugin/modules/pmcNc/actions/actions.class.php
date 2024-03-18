<?php

class pmcNcActions extends sfActions {

    public function executeLots(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->lots = [];
        foreach(MouvementLotView::getInstance()->getByIdentifiant($this->etablissement->identifiant, Lot::STATUT_MANQUEMENT_EN_ATTENTE)->rows as $row) {
            $this->lots[] = $row->value;
        }
        krsort($this->lots);
        $this->campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent());
        $this->periode = preg_replace('/-.*/', '', $this->campagne);
    }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $isAdmin = $this->getUser()->isAdmin();
        if (!$isAdmin) {
            $this->secureEtablissement(EtablissementSecurity::DECLARANT_PMC, $etablissement);
        }
        $lot = LotsClient::getInstance()->findByUniqueId($etablissement->identifiant, $request->getParameter('unique_id'));

        $pmcNc = PMCNCClient::getInstance()->createPMCNC($lot, ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent(), $isAdmin);
        $pmcNc->save();

        return $this->redirect('pmc_lots', $pmcNc);
    }

    protected function secure($droits, $doc) {
        if (!PMCSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
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
