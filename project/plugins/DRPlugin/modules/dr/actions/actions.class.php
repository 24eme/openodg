<?php

class drActions extends sfActions
{
    public function executeVisualisation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
        $this->lignesAAfficher = ['04', '05', '14', '15'];
    }

    public function executeApprobation(sfWebRequest $request)
    {

    }
}
