<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->form = new DegustationCreationForm(new Degustation());

        $this->degustations = DegustationClient::getInstance()->getHistory();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $degustation = $this->form->save();

        return $this->redirect('degustation_redirect', $degustation);
    }

    public function executePrelevementLots(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_LOTS))) {
            $this->degustation->save();
        }

        $this->form = new DegustationPrelevementLotsForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return ($next = $this->getRouteNextEtape(DegustationEtapes::ETAPE_LOTS))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }

    public function executeSelectionDegustateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_DEGUSTATEURS))) {
            $this->degustation->save();
        }

        $this->form = new DegustationSelectionDegustateursForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return ($next = $this->getRouteNextEtape(DegustationEtapes::ETAPE_DEGUSTATEURS))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }

    public function executePresence(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_PRESENCE))) {
            $this->degustation->save();
        }

        $this->form = new DegustationPresenceForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => '0'));
    }


    public function executeOrganisationTable(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->numero_table = $request->getParameter('numero_table',0);

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_ORGANISATION_TABLE))) {
            $this->degustation->save();
        }

        $this->liste_tables = $this->degustation->getTablesWithFreeLots();
        $this->tableLots = $this->degustation->getLotsTableOrFreeLots($this->numero_table);
        $this->nb_tables = count($this->liste_tables);
        $options = array('tableLots' => $this->tableLots, 'numero_table' => $this->numero_table);

        $this->form = new DegustationOrganisationTableForm($this->degustation, $options);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();

        return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table));
    }



    public function executeDevalidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->degustation->devalidate();
        $this->degustation->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect('degustation_presence', $this->degustation);
    }

    public function executeRedirect(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();

        if ($degustation->isValidee()) {
            return $this->redirect('degustation_presence', $degustation);
        }

        return ($next = $this->getRouteNextEtape($degustation->etape))? $this->redirect($next, $degustation) : $this->redirect('degustation');
    }

    protected function getEtape($doc, $etape, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

    protected function getRouteNextEtape($etape = null, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        $routes = $etapes->getRouteLinksHash();
        if (!$etape) {
            $etape = $etapes->getFirst();
        } else {
            $etape = $etapes->getNext($etape);
        }
        return (isset($routes[$etape]))? $routes[$etape] : null;
    }

    public function executeList(sfWebRequest $request) {
        $etablissement_id = $request->getParameter('id');
        $this->etablissement = EtablissementClient::getInstance()->find($etablissement_id);
        $this->forward404Unless($this->etablissement);

        $this->lots = array();
        foreach (MouvementLotView::getInstance()->getByDeclarantIdentifiant($etablissement_id)->rows as $item) {
            $key = Lot::generateMvtKey($item->value);
            if (!isset($this->lots[$key])) {
                $this->lots[$key] = $item->value;
                $this->lots[$key]->steps = array();
            }
            $this->lots[$key]->steps[] = $item->value;
        }
    }

}
