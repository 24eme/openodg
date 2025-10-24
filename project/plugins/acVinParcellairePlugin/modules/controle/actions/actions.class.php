<?php
class controleActions extends sfActions
{

    private function getControlesPlanifies($date = null) {
        $stats = [];
        foreach($this->controles[ControleClient::CONTROLE_STATUT_PLANIFIE] as $c) {
            if ($date && $date != $c->date_tournee) {
                continue;
            }
            if (!isset($stats[$c->date_tournee])) {
                $stats[$c->date_tournee] = ['nb_parcelles' => 0, 'operateurs' => [], 'controles' => []];
            }
            $stats[$c->date_tournee]['nb_parcelles'] += count($c->parcelles);
            $stats[$c->date_tournee]['operateurs'][] = $c->declarant->nom;
            $stats[$c->date_tournee]['controles'][] = $c;
        }
        return $stats;
    }

    public function executeIndex(sfWebRequest $request)
    {
        $this->controles = ControleClient::getInstance()->findAllByStatus();
        $this->stats = $this->getControlesPlanifies();
    }

    public function executeNouveau(sfWebRequest $request)
    {
    	$this->etablissement = $this->getRoute()->getEtablissement();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }
		$this->periode = $request->getParameter('periode');
        $this->controle = ControleClient::getInstance()->findOrCreate($this->etablissement->identifiant);
        $this->controle->save();

        return $this->redirect('controle_parcelles', array('id' => $this->controle->_id));
    }

    public function executeParcelles(sfWebRequest $request)
    {
    	$this->controle = $this->getRoute()->getControle();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }

        $this->parcellaire = $this->controle->getParcellaire();

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->controle->updateParcelles($request->getPostParameter('parcelles', []));
            $this->controle->save();
            return $this->redirect('controle_saveinlocalstorage', array('id' => $this->controle->_id));
        }
    }

    public function executeSaveInLocalStorage(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
        $this->json = json_encode($this->controle->getData(), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    }

    public function executeAppTerrain(sfWebRequest $request)
    {
        $this->date_tournee = $request->getParameter('date');
        $this->controles = $this->getControlesPlanifies($this->date_tournee);
        $this->setLayout('appLayout');
    }

    public function executeSetDateTournee(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
        if (!$request->getParameter('date')) {
            return sfView::SUCCESS;
        }
        $this->controle->date_tournee = $request->getParameter('date');
        $this->controle->save();
        return $this->redirect('controle_index');
    }


}
