<?php

class drapActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $drap = DRaPClient::getInstance()->createDocFromPrevious($etablissement->identifiant, $periode);
        $this->secure(ParcellaireSecurity::EDITION, $drap);

        $drap->save();

        return $this->redirect('drap_edit', $drap);
    }

    public function executeCreatePapier(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $drap = DRaPClient::getInstance()->createDocFromPrevious($etablissement->identifiant, $periode, true);
        $drap->save();

        return $this->redirect('drap_edit', $drap);
    }

    public function executeEdit(sfWebRequest $request) {
        $drap = $this->getRoute()->getDRaP();

        $this->secure(ParcellaireSecurity::EDITION, $drap);

        if ($drap->exist('etape') && $drap->etape) {
            return $this->redirect('drap_' . $drap->etape, $drap);
        }

        if($request->getParameter('coop')) {

            return $this->redirect('drap_parcelles', $drap);
        }

        return $this->redirect('drap_exploitation', $drap);
    }
    public function executeDelete(sfWebRequest $request) {
        $drap = $this->getRoute()->getDRaP();
        $etablissement = $drap->getEtablissementObject();
        $this->secure(ParcellaireSecurity::EDITION, $drap);

        $drap->delete();
        $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $drap->campagne));
    }

    public function executeDevalidation(sfWebRequest $request) {
        $drap = $this->getRoute()->getDRaP();
        if (!$this->getUser()->isAdmin()) {
        $this->secure(ParcellaireSecurity::DEVALIDATION , $drap);
        }

        $drap->validation = null;
        $drap->validation_odg = null;
        $drap->add('etape', null);
        $drap->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('drap_edit', $drap));
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->drap = $this->getRoute()->getDRaP();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::EDITION, $this->drap);

        if($this->drap->storeEtape($this->getEtape($this->drap, DRaPEtapes::ETAPE_EXPLOITATION))) {
        $this->drap->save();
        }

        $this->etablissement = $this->drap->getEtablissementObject();

        $this->form = new EtablissementForm($this->drap->declarant, array("use_email" => !$this->drap->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($this->form->hasUpdatedValues() && !$this->drap->isPapier()) {
            Email::getInstance()->sendNotificationModificationsExploitation($this->drap->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('drap_validation', $this->drap);
        }

        return $this->redirect('drap_parcelles', $this->drap);
    }

    public function executeParcelles(sfWebRequest $request) {
        $this->drap = $this->getRoute()->getDRaP();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::EDITION, $this->drap);

        if($this->drap->storeEtape($this->getEtape($this->drap, DRaPEtapes::ETAPE_PARCELLES))) {
            $this->drap->save();
       	}

        $this->etablissement = $this->drap->getEtablissementObject();

       	if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->drap->setParcellesFromParcellaire($request->getPostParameter('parcelles', array()));
        $this->drap->save();
        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->drap->getEtablissementObject());
        }

        return ($next = $this->getRouteNextEtape(DRaPEtapes::ETAPE_PARCELLES)) ? $this->redirect($next, $this->drap) : $this->redirect('drap_validation', $this->drap);
    }

    public function executeDestinations(sfWebRequest $request) {
        $this->drap = $this->getRoute()->getDRaP();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::EDITION, $this->drap);

        if($this->drap->storeEtape($this->getEtape($this->drap, DRaPEtapes::ETAPE_DESTINATIONS))) {
            $this->drap->save();
        }

        $this->etablissement = $this->drap->getEtablissementObject();

        $this->form = new drapProduitsForm($this->drap);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->drap->getEtablissementObject());
        }

        return $this->redirect('drap_validation', $this->drap);
    }


    public function executeValidation(sfWebRequest $request) {
        $this->drap = $this->getRoute()->getDRaP();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::EDITION, $this->drap);

        if($this->drap->storeEtape($this->getEtape($this->drap, DRaPEtapes::ETAPE_VALIDATION))) {
            $this->drap->save();
        }

        if($this->getUser()->isAdmin()) {
            $this->drap->validateOdg();
        }

        $this->validation = new DRaPValidation($this->drap);
        $this->form = new DRaPValidationForm($this->drap, array('engagements' => $this->validation->getPoints(DRaPValidation::TYPE_ENGAGEMENT)));

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $documents = $this->drap->getOrAdd('documents');

        foreach ($this->validation->getPoints(DRaPValidation::TYPE_ENGAGEMENT) as $engagement) {
            $document = $documents->add($engagement->getCode());
            $document->libelle =DRaPDocuments::getDocumentLibelle($document->getKey());
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "Vos parcelles irrigables ont bien été enregistrées");
            return $this->redirect('drap_visualisation', $this->drap);
    }

    public function executePDF(sfWebRequest $request) {
        set_time_limit(180);
        $this->drap = $this->getRoute()->getDRaP(['allow_habilitation' => true, 'allow_stalker' => true]);
       	$this->secure(ParcellaireSecurity::VISUALISATION, $this->drap);


       	$this->document = new ExportDRaPPDF($this->drap, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

            return $this->renderText($this->document->output());
    }


    public function executeVisualisation(sfWebRequest $request) {
        $this->drap = $this->getRoute()->getDRaP();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->drap);
    }


    protected function getEtape($drap, $etape) {
        $drapEtapes = DRaPEtapes::getInstance();
        if (!$drapEtapes->exist('etape')) {
            return $etape;
        }
        return ($drapEtapes->isLt($drapEtapes->etape, $etape)) ? $etape : $drapEtapes->etape;
    }


    protected function getRouteNextEtape($etape = null, $class = "DRaPEtapes") {
        $etapes = $class::getInstance();
        $routes = $etapes->getRouteLinksHash();
        if (!$etape) {
            $etape = $etapes->getFirst();
        } else {
            $etape = $etapes->getNext($etape);
        }
        return (isset($routes[$etape])) ? $routes[$etape] : null;
    }

    protected function secure($droits, $doc) {
        if (!ParcellaireSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

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
