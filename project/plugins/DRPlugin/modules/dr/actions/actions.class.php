<?php

class drActions extends sfActions
{
    public function executeVisualisation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        $this->lignesAAfficher = ['04', '05', '14', '15'];
        $this->configuration = ConfigurationClient::getInstance()->getCurrent();
        $this->validation = new DRValidation($this->dr, ['configuration' => $this->configuration]);
    }

    public function executeApprobation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        $this->dr->validateOdg();

        $this->redirect('dr_visualisation', $this->dr);
    }
}
