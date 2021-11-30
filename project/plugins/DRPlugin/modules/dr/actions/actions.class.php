<?php

class drActions extends sfActions
{
    public function executeVisualisation(sfWebRequest $request)
    {
        $this->dr = $this->getRoute()->getDR();
    }

    public function executeApprobation(sfWebRequest $request)
    {

    }
}
