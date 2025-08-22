<?php

class parcellaireIrrigueActions extends sfActions {

    public function executeIrrigation(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $this->etablissement);

        if(!$this->getUser()->isAdminODG() && !ParcellaireIrrigueConfiguration::getInstance()->isOpen()) {
            throw new sfError403Exception("La téléclaration n'est pas encore ouverte");
        }

		$this->papier = $request->getParameter('papier', false);
		$this->periode = $request->getParameter('periode');

        $errors = array();
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->createDoc($this->etablissement->identifiant, $this->periode, $this->papier, null, $errors);

        if (count($errors)) {
            foreach($errors as $err => $details) {
                $this->getUser()->setFlash('warning', "$err : $details");
            }
        }

        $this->form = new ParcellaireIrrigueProduitsForm($this->parcellaireIrrigue);

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

        	return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "Vos parcelles irriguées ont bien été enregistrées");

        return $this->redirect('parcellaireirrigue_edit', array('sf_subject' => $this->etablissement, 'periode' => $this->periode, 'papier' => $this->papier));
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->parcellaireIrrigue = $this->getRoute()->getParcellaireIrrigue();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireIrrigue);

        return $this->redirect('parcellaireirrigue_edit', ['identifiant' => $this->parcellaireIrrigue->identifiant, 'periode' => $this->parcellaireIrrigue->periode]);
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

    public function executePDF(sfWebRequest $request) {
        set_time_limit(180);
        $this->parcellaireIrrigue = $this->getRoute()->getParcellaireIrrigue(['allow_habilitation' => true, 'allow_stalker' => true]);
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireIrrigue);


        $this->document = new ExportParcellaireIrriguePDF($this->parcellaireIrrigue, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executePDFLast(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->getLast($this->etablissement->identifiant, $request->getParameter('periode'));

        return $this->redirect('parcellaireirrigue_export_pdf', $this->parcellaireIrrigue);
    }
}
