<?php

class pmcActions extends sfActions {


    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $isAdmin = $this->getUser()->isAdminODG();

        if (!$isAdmin) {
            $this->secureEtablissement(EtablissementSecurity::DECLARANT_PMC, $etablissement);
        }

        $date = $request->getParameter("date");
        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent());

        if (PMCClient::getInstance()->findBrouillon($etablissement->identifiant, $periode)) {
            $this->getUser()->setFlash("warning", "Il existe déjà une déclaration de mise en circulation non terminée");
            return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'periode' => $periode));
        }

        $pmc = PMCClient::getInstance()->createDoc($etablissement->identifiant, $periode, $date, $isAdmin);
        $pmc->save();

        return $this->redirect('pmc_edit', $pmc);
    }

    public function executeEdit(sfWebRequest $request) {
        $pmc = $this->getRoute()->getPMC();

        $this->secure(PMCSecurity::EDITION, $pmc);

        if ($pmc->exist('etape') && $pmc->etape) {
            return $this->redirect('pmc_' . $pmc->etape, $pmc);
        }

        return $this->redirect('pmc_exploitation', $pmc);
    }

    public function executeDelete(sfWebRequest $request) {
        $pmc = $this->getRoute()->getPMC();
        $etablissement = $pmc->getEtablissementObject();
        $this->secure(PMCSecurity::EDITION, $pmc);

        $pmc->delete();
        $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $pmc->campagne));
    }

    public function executeDevalidation(sfWebRequest $request) {
        $pmc = $this->getRoute()->getPMC();
        if (!$this->getUser()->isAdminODG()) {
          $this->secure(PMCSecurity::DEVALIDATION , $pmc);
        }

        if($pmc->hasLotsUtilises()) {
            throw new Exception("Dévalidation impossible car des lots dans cette déclaration sont utilisés");
        }

        $pmc->validation = null;
        $pmc->validation_odg = null;
        foreach ($pmc->getLots() as $lot) {
          if($lot->exist('validation_odg') && $lot->validation_odg){
            $lot->validation_odg = null;
          }
        }
        $pmc->remove('mouvements_lots');
        $pmc->add('etape', null);
        $pmc->devalidate();
        $pmc->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('pmc_edit', $pmc));
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::EDITION, $this->pmc);

        if($this->pmc->storeEtape($this->getEtape($this->pmc, PMCEtapes::ETAPE_EXPLOITATION))) {
            $this->pmc->save();
        }

        $this->etablissement = $this->pmc->getEtablissementObject();
        $this->form = new EtablissementForm($this->pmc->declarant, array("use_email" => !$this->pmc->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        if (PMCConfiguration::getInstance()->hasExploitationSave()) {
            $this->form->save();
        }

        if ($this->form->hasUpdatedValues() && !$this->pmc->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->pmc->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('pmc_validation', $this->pmc);
        }

        return $this->redirect('pmc_lots', $this->pmc);
    }

    public function executeLots(sfWebRequest $request)
    {
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::EDITION, $this->pmc);
        $this->isAdmin = $this->getUser()->isAdminODG();

        $has = false;
        if(count($this->pmc->getLots())){
            $has = true;
        }

        if($this->pmc->storeEtape($this->getEtape($this->pmc, PMCEtapes::ETAPE_LOTS))) {
            $this->pmc->save();
        }
        $options = array();
        if (count($this->pmc->getLots()) == 0 || $request->getParameter('submit') == "add") {
            $options['addrequest'] = true;
            $this->pmc->addLot();
        }
        $this->form = new PMCLotsForm($this->pmc, array(), $options);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($request->getParameter('submit') == 'add') {
            return $this->redirect($this->generateUrl('pmc_lots', $this->pmc).'#dernier');
        }

        return $this->redirect('pmc_validation', $this->pmc);
    }

    public function executeDeleteLots(sfWebRequest $request){
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::EDITION, $this->pmc);

        if($this->pmc->getLotByNumArchive($request->getParameter('numArchive')) === null){
          throw new sfException("le lot d'index ".$request->getParameter('numArchive')." n'existe pas ");
        }

        $lot = $this->pmc->getLotByNumArchive($request->getParameter('numArchive'));
        // $lotCheck = MouvementLotView::getInstance()->getDegustationMouvementLot($this->pmc->identifiant, $lot->numero_archive, $this->pmc->campagne);
        // if($lotCheck){
        //   throw new sfException("le lot de numero d'archive ".$request->getParameter('numArchive').
        //   " ne peut pas être supprimé car associé à un document son id :\n".$lotCheck->id_document);
        // }

        if($lot){
            $this->pmc->remove($lot->getHash());
        }

        $this->pmc->save();
        return $this->redirect('pmc_lots', $this->pmc);

    }

    public function executeValidation(sfWebRequest $request) {
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::EDITION, $this->pmc);
        $this->isAdmin = $this->getUser()->hasPMCAdmin();
        if ($this->pmc->validation) {
            return $this->redirect('pmc_visualisation', $this->pmc);
        }

        if($this->pmc->storeEtape($this->getEtape($this->pmc, PMCEtapes::ETAPE_VALIDATION))) {
            $this->pmc->save();
            return $this->redirect('pmc_validation', $this->pmc);
        }

        $this->pmc->cleanDoc();

        $this->validation = new PMCValidation($this->pmc);
        $this->form = new PMCValidationForm($this->pmc, array(), array('isAdmin' => $this->isAdmin, 'engagements' => $this->validation->getPoints(PMCValidation::TYPE_ENGAGEMENT)));

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        if (!$this->validation->isValide() && $this->pmc->isTeledeclare() && !$this->getUser()->hasPMCAdmin()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();
        $dateValidation = date('Y-m-d');

        if($this->form->getValue("date")) {
            $dt = new DateTime($this->form->getValue("date"));
            $dateValidation = $dt->modify('+1 minute')->format('Y-m-d');
        }

        $this->pmc->validate($dateValidation);
        $this->pmc->cleanLots();
        $this->pmc->save();

        if($this->getUser()->hasPMCAdmin() && PMCConfiguration::getInstance()->hasValidationOdgRegion()) {
            $this->getUser()->setFlash("notice", "La déclaration de mise en circulation a été validée, elle devra être approuvée par l'ensemble des ODG concernées");

            return $this->redirect('pmc_visualisation', $this->pmc);
        }

        if($this->getUser()->hasPMCAdmin() && $this->pmc->isPapier()) {
            $this->pmc->validateOdg();
            $this->pmc->cleanLots();
            $this->pmc->save();
            $this->getUser()->setFlash("notice", "La déclaration de mise en circulation papier a été validée et approuvée");

            return $this->redirect('pmc_visualisation', $this->pmc);
        }

        if($this->getUser()->hasPMCAdmin()) {
            $this->pmc->validateOdg();
            $this->pmc->save();
            $this->getUser()->setFlash("notice", "La déclaration de mise en circulation a été validée et approuvée");

            return $this->redirect('pmc_visualisation', $this->pmc);
        }

        if(PMCConfiguration::getInstance()->hasValidationOdgAuto() && !$this->validation->hasPoints()) {
            $this->pmc->validateOdg();
            $this->pmc->save();
        }

        if ($this->getUser()->hasPMCAdmin() && $this->pmc->getType() === PMCNCClient::TYPE_MODEL) {
            $this->pmc->validateOdg();
            $this->pmc->save();
        }

        //Email::getInstance()->sendPMCValidation($this->pmc);

        return $this->redirect('pmc_confirmation', $this->pmc);
    }

    public function executeValidationAdmin(sfWebRequest $request) {
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(array(PMCSecurity::VALIDATION_ADMIN), $this->pmc);
        $this->regionParam = $request->getParameter('region',null);

        $this->pmc->validateOdg(null,$this->regionParam);
        $this->pmc->save();

        if($this->pmc->validation_odg) {
            $this->getUser()->setFlash("notice", "La déclaration a été approuvée.");
        }

        $service = $request->getParameter("service");
        $params = array('sf_subject' => $this->pmc, 'service' => isset($service) ? $service : null);
        if($this->regionParam){
          $params = array_merge($params,array('region' => $this->regionParam));
        }
        return $this->redirect('pmc_visualisation', $params);
    }



    public function executeConfirmation(sfWebRequest $request) {
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::VISUALISATION, $this->pmc);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::VISUALISATION, $this->pmc);
        $this->isAdmin = $this->getUser()->isAdminODG();

        $this->service = $request->getParameter('service');

        $this->regionParam = $request->getParameter('region',null);
        if (!$this->regionParam && $this->getUser()->getCompte() && $this->getUser()->getCompte()->exist('region')) {
            $this->regionParam = $this->getUser()->getCompte()->region;
        }
        $this->form = null;
        if($this->getUser()->hasPMCAdmin() || $this->pmc->validation) {
            $this->validation = new PMCValidation($this->pmc);
            $this->form = new PMCValidationForm($this->pmc, array(), array('isAdmin' => $this->isAdmin, 'engagements' => $this->validation->getPoints(PMCValidation::TYPE_ENGAGEMENT)));
        }


        $this->dr = DRClient::getInstance()->findByArgs($this->pmc->identifiant, $this->pmc->campagne);
        if (!$request->isMethod(sfWebRequest::POST)) {
          return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin && $this->pmc->isValidee() && $this->pmc->isValideeODG() === false){
          return $this->redirect('pmc_validation_admin', $this->pmc);
        }

        return $this->redirect('pmc_visualisation', $this->pmc);
    }



    public function executeModificative(sfWebRequest $request) {
        $pmc = $this->getRoute()->getPMC();

        $pmc_modificative = $pmc->generateModificative();
        $pmc_modificative->save();
        if(ConfigurationClient::getCurrent()->declaration->isRevendicationParLots()){
          return $this->redirect('pmc_lots', $pmc_modificative);
        }

        return $this->redirect('pmc_edit', $pmc_modificative);
    }

    public function executePDF(sfWebRequest $request) {
        $pmc = $this->getRoute()->getPMC();
        $this->secure(PMCSecurity::PDF, $pmc);
        $this->document = new ExportPMCPDF($pmc, $request->getParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generate();
        $this->document->addHeaders($this->getResponse());
        return $this->renderText($this->document->output());
    }

    public function executeMain()
    {
    }

    protected function getEtape($pmc, $etape) {
        $pmcEtapes = PMCEtapes::getInstance();
        if (!$pmc->exist('etape')) {
            return $etape;
        }
        return ($pmcEtapes->isLt($pmc->etape, $etape)) ? $etape : $pmc->etape;
    }

    protected function sendPMCValidation($pmc) {
        $pdf = new ExportPMCPdf($pmc, null, 'pdf', true);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->removeCache();
        $pdf->generate();
        Email::getInstance()->sendPMCValidation($pmc);
    }

    protected function sendPMCConfirmee($pmc) {
        Email::getInstance()->sendPMCConfirmee($pmc);
    }

    protected function secure($droits, $doc) {
        if (!PMCSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

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
