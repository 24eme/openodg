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

        $campagne = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent();
        $periode = preg_replace('/-.*/', '', $campagne);
        $date = date('Y-m-d');

        $pmc = PMCClient::getInstance()->createDoc($etablissement->identifiant, $campagne, $date, $isAdmin);
        $lotDef = PMCLot::freeInstance(new PMC());
        foreach($lot->getFields() as $key => $value) {
            if($lotDef->getDefinition()->exist($key)) {
                continue;
            }
            $lot->remove($key);
        }
        $lot = $pmc->lots->add(null, $lot);
        $lot->id_document = $pmc->_id;
        $lot->date = date('Y-m-d');
        $lot->updateDocumentDependances();
        $pmc->save();

        return $this->redirect('pmc_lots', $pmc);
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
