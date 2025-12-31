<?php
class controleActions extends sfActions
{

    private function getControlesPlanifies($date = null) {
        $stats = [];
        $this->controles = ControleClient::getInstance()->findAllByStatus();
        $global = [];
        foreach ($this->controles as $statut => $controles) {
          $stats[$statut] = [];
          foreach($controles as $c) {
            if ($date && $date != $c->date_tournee) {
                continue;
            }
            $key = $c->date_tournee;
            if (!isset($stats[$statut][$key])) {
                $stats[$statut][$key] = ['nb_parcelles' => 0, 'operateurs' => [], 'controles' => [], 'geojson' => [], 'date_tournee' => $c->date_tournee, 'type_tournee' => $c->type_tournee];
            }
            $stats[$statut][$key]['nb_parcelles'] += count($c->parcelles);
            $stats[$statut][$key]['operateurs'][] = $c->declarant->nom;
            $stats[$statut][$key]['controles'][$c->_id] = $c->getDataToDump();
            if(!isset($global[$key])) {
                $global[$key] = ['nb_parcelles' => 0, 'operateurs' => [], 'controles' => []];
            }
            $global[$key]['nb_parcelles'] += $stats[$statut][$key]['nb_parcelles'];
            $global[$key]['operateurs'] = array_merge($global[$key]['operateurs'], $stats[$statut][$key]['operateurs']);
            $global[$key]['controles'] = array_merge($global[$key]['controles'], $stats[$statut][$key]['controles']);
          }
        }
        if ($date) {
            return $global;
        }
        return $stats;
    }

    public function executeIndex(sfWebRequest $request)
    {
        $this->stats = $this->getControlesPlanifies();
    }

    public function executeNouveau(sfWebRequest $request)
    {
    	$this->etablissement = $this->getRoute()->getEtablissement();

        if(!$this->getUser()->isAdmin()) {
            throw new sfError403Exception("Accès admin uniquement");
        }
        $this->controle = ControleClient::getInstance()->findOrCreate($this->etablissement->identifiant);
        $this->controle->save();

        return $this->redirect('controle_index');
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
            return $this->redirect('controle_index');
        }
    }

    public function executeAppOrga(sfWebRequest $request)
    {
        $this->date_tournee = $request->getParameter('date');
        $this->controles = $this->getControlesPlanifies($this->date_tournee);
        $this->json = json_encode($this->controles[$this->date_tournee]['controles'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);

        $this->setLayout('appLayout');
    }

    public function executeAppTerrain(sfWebRequest $request)
    {
        $this->date_tournee = $request->getParameter('date');
        $this->controles = $this->getControlesPlanifies($this->date_tournee);
        $this->json = json_encode($this->controles[$this->date_tournee]['controles'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $this->points_de_controle = json_encode(ControleConfiguration::getInstance()->getPointsDeControle(), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);

        $this->setLayout('appLayout');
    }

    public function executeAppOrgaSave(sfWebRequest $request) {
        if (!$request->isMethod(sfWebRequest::POST)) {
            throw new sfError403Exception();
        }
        $date_tournee = $request->getParameter('date');
        $data = json_decode($request->getParameter('data'));
        foreach ($data as $controleId => $parcellesIds) {
            if ($controle = ControleClient::getInstance()->find($controleId)) {

                $controle->updateParcelles($parcellesIds);
                $controle->save();
            }
        }
        return $this->redirect('controle_index');
    }

    public function executeSetDateTournee(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
        if (!$request->getParameter('date_tournee')) {
            return sfView::SUCCESS;
        }
        $this->controle->date_tournee = $request->getParameter('date_tournee');
        $this->controle->type_tournee = $request->getParameter('type_tournee');
        $this->controle->save();
        return $this->redirect('controle_index');
    }

    public function executeVisualisation(sfWebRequest $request)
    {
        $this->controle = $this->getRoute()->getControle();
    }

    public function executeListeOperateursTournee(sfWebRequest $request)
    {
        $this->controles = $this->getControlesPlanifies($request->getParameter('date'))[$request->getParameter('date')]['controles'];
    }

    public function executeListeManquementsControle(sfWebRequest $request)
    {
        $this->manquements = ControleClient::getInstance()->find($request->getParameter('id'))['manquements'];
    }

    public function executeTransmissionData(sfWebRequest $request)
    {
        if ($request->isMethod(sfWebRequest::POST)) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            $controleBase = ControleClient::getInstance()->find($data['controle']['_id']);
            $controleBase->updateParcellePointsControleFromJson($data);
            exit;
        }
    }
}
