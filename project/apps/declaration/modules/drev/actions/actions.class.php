<?php

class drevActions extends sfActions
{

    public function executeCreate(sfWebRequest $request)
    {
        $drev = new DRev();
        $drev->identifiant = '7523700100';
        $drev->campagne = '2013-2014';
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeEdit(sfWebRequest $request)
    {
        $drev = $this->getRoute()->getDRev();

        return $this->redirect('drev_revendication', $drev);
    }

    public function executeRevendication(sfWebRequest $request) {
        
    }
}
