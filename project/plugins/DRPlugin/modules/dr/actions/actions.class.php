<?php

class drActions extends sfActions
{
    public function executeVisualisation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        if (! $this->dr->exist('donnees') || empty($this->dr->donnees)) {
            $this->dr->generateDonnees();
            $this->dr->save();
            $this->dr = $this->getRoute()->getDR();
        }
        $this->configuration = ConfigurationClient::getInstance()->getCurrent();
        $this->validation = new DRValidation($this->dr, ['configuration' => $this->configuration]);
    }

    public function executeApprobation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        if (! $this->getUser()->isAdmin()) {
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

        if (! $this->getUser()->isAdmin()) {
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

        if (! $this->getUser()->isAdmin()) {
            return $this->forwardSecure();
        }

        if ($this->dr->exist('validation_odg') && $this->dr->validation_odg) {
            throw new sfException('La DR doit pas être validée ODG pour permettre la mise en attente');
        }

        $this->dr->switchEnAttente();
        $this->dr->save();

        return $this->redirect('dr_visualisation', $this->dr);
    }

    protected function forwardSecure()
    {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }
}
