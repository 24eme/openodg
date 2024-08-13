<?php

class drActions extends sfActions
{
    public function executeVisualisation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        $this->configuration = ConfigurationClient::getInstance()->getCurrent();
        $this->validation = new DRValidation($this->dr, ['configuration' => $this->configuration]);
    }

    public function executeApprobation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        if (! $this->getUser()->isAdminODG()) {
            return $this->forwardSecure();
        }
        $this->configuration = ConfigurationClient::getInstance()->getCurrent();
        $this->validation = new DRValidation($this->dr, ['configuration' => $this->configuration]);

        if ($this->validation->isValide() === false) {
            throw new Exception('On ne peut pas valider une DR avec des erreurs');
        }

        $this->dr->validateOdg();
        $this->dr->save();

        return $this->redirect('dr_visualisation', $this->dr);
    }

    public function executeDevalidation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();

        if (! $this->getUser()->isAdminODG()) {
            return $this->forwardSecure();
        }

        if (! $this->dr->exist('validation_odg') || ! $this->dr->validation_odg) {
            throw new Exception('On ne peut pas dévalider un DR non approuvée');
        }

        $this->dr->validation_odg = null;
        $this->dr->save();

        return $this->redirect('dr_visualisation', $this->dr);
    }

    public function executeEnattenteAdmin(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();

        if (! $this->getUser()->isAdminODG()) {
            return $this->forwardSecure();
        }

        if ($this->dr->exist('validation_odg') && $this->dr->validation_odg) {
            throw new sfException('La DR doit pas être validée ODG pour permettre la mise en attente');
        }

        $this->dr->switchEnAttente();
        $this->dr->save();

        return $this->redirect('dr_visualisation', $this->dr);
    }

    public function executeSuppression(sfWebRequest $request)
    {
        $dr = $this->getRoute()->getDR();

        if (! $this->getUser()->isAdminODG()) {
            return $this->forwardSecure();
        }
        if ($dr->exist('validation_odg') && $dr->validation_odg) {
            throw new sfException('Le document a une validation odg');
        }
        if ($dr->exist('statut_odg') && $dr->statut_odg) {
            throw new sfException('La DR doit pas être mise en attente');
        }
        if ($dr->exist('mouvements') && count($dr->mouvements) ) {
            throw new sfException('La DR doit pas avoir de mouvements');
        }
        $dr->delete();
        return $this->redirect('declaration_etablissement', array('identifiant' => $dr->identifiant, 'campagne' => $dr->campagne));
    }

    protected function forwardSecure()
    {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

    public function executeRedirect(sfWebRequest $request) {
        $identifiant = $request->getParameter('identifiant');
        $campagne = $request->getParameter('campagne');
        $dr = DRClient::getInstance()->findByArgs($identifiant, $campagne);
        if ($dr) {
            return $this->redirect('dr_visualisation', $dr);
        }
        $sv = SV11Client::getInstance()->findByArgs($identifiant, $campagne);
        if ($sv) {
            return $this->redirect('dr_visualisation', $sv);
        }
        $sv = SV12Client::getInstance()->findByArgs($identifiant, $campagne);
        if ($sv) {
            return $this->redirect('dr_visualisation', $sv);
        }
        return $this->redirect('declaration_etablissement', array('identifiant' => $identifiant, 'campagne' => $campagne));
    }

    public function executeSvVerify(sfWebRequest $request) {
        $this->dr = $this->getRoute()->getDR();
        if ($this->dr->type == 'SV11') {
            $this->tableau_comparaison = $this->dr->getTableauComparaisonSV11();
        } else if ($this->dr->type == 'SV12') {
            $this->tableau_comparaison = $this->dr->getTableauComparaisonSV12();
        }
    }

    public function executeDrVerify(sfWebRequest $request) {
        $this->dr = $this->getRoute()->getDR();
        $this->tableau_comparaison = $this->dr->getTableauComparaisonDrDap();
    }

}
