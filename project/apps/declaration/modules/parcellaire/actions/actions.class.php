<?php

class parcellaireActions extends sfActions {

    public function executeDelete(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();
        $etablissement = $parcellaire->getEtablissementObject();
        $this->secure(ParcellaireSecurity::EDITION, $parcellaire);

        $parcellaire->delete();
        $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', $etablissement);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::DEVALIDATION, $parcellaire);

        $parcellaire->devalidate();
        $parcellaire->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect('declaration_etablissement', $parcellaire->getEtablissementObject());
    }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext());
        $this->parcellaire->initProduitFromLastParcellaire();
        $this->parcellaire->save();

        return $this->redirect('parcellaire_edit', $this->parcellaire);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext());
        $this->parcellaire->add('papier', 1);
        $this->parcellaire->initProduitFromLastParcellaire();
        $this->parcellaire->save();

        return $this->redirect('parcellaire_edit', $this->parcellaire);
    }

    public function executeEdit(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $parcellaire);

        if ($parcellaire->exist('etape') && $parcellaire->etape) {
            if ($parcellaire->etape == ParcellaireEtapes::ETAPE_PARCELLES) {
                $this->redirect('parcellaire_' . $parcellaire->etape, array('id' => $parcellaire->_id, 'appellation' => ParcellaireClient::getInstance()->getFirstAppellation($parcellaire->isParcellaireCremant())));
            }
            return $this->redirect('parcellaire_' . $parcellaire->etape, $parcellaire);
        }

        return $this->redirect('parcellaire_exploitation', $parcellaire);
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        if ($this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_EXPLOITATION))) {
            $this->parcellaire->save();
        }

        $this->etablissement = $this->parcellaire->getEtablissementObject();
        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->parcellaire->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();
        $this->parcellaire->storeDeclarant();

        $this->parcellaire->save();

        if ($this->form->hasUpdatedValues() && !$this->parcellaire->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->parcellaire->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('parcellaire_validation', $this->parcellaire);
        }

        return $this->redirect('parcellaire_propriete', $this->parcellaire);
    }

    public function executePropriete(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        if ($this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_PROPRIETE))) {
            $this->parcellaire->save();
        }

        $this->form = new ParcellaireDestinationForm($this->parcellaire);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->update();
        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->parcellaire->_id, "revision" => $this->parcellaire->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('parcellaire_validation', $this->parcellaire);
        }

        $this->firstAppellation = ParcellaireClient::getInstance()->getFirstAppellation($this->parcellaire->isParcellaireCremant());
        return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->firstAppellation));
    }

    public function executeParcelles(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        if ($this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_PARCELLES))) {
            $this->parcellaire->save();
        }

        $this->parcellaireAppellations = ParcellaireClient::getInstance()->getAppellationsAndVtSgnKeys($this->parcellaire->isParcellaireCremant());

        $this->appellation = $request->getParameter('appellation');

        $this->ajoutForm = new ParcellaireAjoutParcelleForm($this->parcellaire, $this->appellation);

        $this->appellationNode = $this->parcellaire->getAppellationNodeFromAppellationKey($this->appellation, true);

        $this->parcelles = array();
        if ($this->appellationNode == ParcellaireClient::APPELLATION_VTSGN) {
           $this->parcelles =  $this->parcellaire->getDeclaration()->getProduitsCepageDetails(true, true);
        } else {
            $this->parcelles = $this->appellationNode->getDetailsSortedByParcelle(false);
        }

        $this->erreur = $request->getParameter('erreur', false);
        $this->attention = $request->getParameter('attention', false);

        $this->form = new ParcellaireAppellationEditForm($this->parcellaire, $this->appellation, $this->parcelles);
        if ($request->isMethod(sfWebRequest::POST)) {

            $this->form->bind($request->getParameter($this->form->getName()));

            if ($this->form->isValid()) {
                $this->form->save();

                if ($request->isXmlHttpRequest()) {

                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->parcellaire->_id, "revision" => $this->parcellaire->_rev))));
                }

                if ($request->getParameter('redirect', null)) {

                    return $this->redirect('parcellaire_validation', $this->parcellaire);
                }
                if ($this->appellationNode != ParcellaireClient::APPELLATION_VTSGN &&
                    ($next_appellation = $next_appellation = $this->appellationNode->getNextAppellationKey()))
                {
                    return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $next_appellation));
                } else {
                    return $this->redirect('parcellaire_acheteurs', $this->parcellaire);
                }
            }
        }
    }

    public function executeAjoutParcelle(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->appellation = $request->getParameter('appellation');

        $this->form = new ParcellaireAjoutParcelleForm($this->parcellaire, $this->appellation);
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {


            return sfView::SUCCESS;
        } else {

            $this->form->save();

            $this->getUser()->setFlash("notice", 'La parcelle a été ajoutée avec succès.');

            return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->appellation));
        }
    }

    public function executeModificationParcelle(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->appellation = $request->getParameter('appellation');
        $parcelleKey = $request->getParameter('parcelle');

        preg_match('/^(.*)-detail-(.*)$/', $parcelleKey, $parcelleKeyMatches);
        $detail = $this->parcellaire->get(str_replace('-', '/', $parcelleKeyMatches[1]))->detail->get($parcelleKeyMatches[2]);

        if (!$detail) {

            return $this->forward404(sprintf("Le détail n'existe pas"));
        }

        $this->form = new ParcellaireModificationParcelleForm($detail);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->appellation));
    }

    public function executeDeleteParcelle(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();
        $appellation = $request->getParameter('appellation');
        $parcelleKey = $request->getParameter('parcelle');

        preg_match('/^(.*)-detail-(.*)$/', $parcelleKey, $parcelleKeyMatches);
        $detail = $parcellaire->get(str_replace('-', '/', $parcelleKeyMatches[1]))->detail->get($parcelleKeyMatches[2]);

        $this->getUser()->setFlash("warning", sprintf('La parcelle %s, %.2f ares, %s, %s a bien été supprimée.', $detail->getParcelleIdentifiant(), $detail->superficie, $detail->getLieuLibelle(), $detail->getCepageLibelle()));

        $parcellaire->get(str_replace('-', '/', $parcelleKeyMatches[1]))->detail->remove($parcelleKeyMatches[2]);
        $parcellaire->save();

        return $this->redirect('parcellaire_parcelles', array('id' => $parcellaire->_id, 'appellation' => $appellation));
    }

    public function executeAcheteurs(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        if ($this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_ACHETEURS))) {
            $this->parcellaire->save();
        }

        $this->form = new ParcellaireAcheteursForm($this->parcellaire);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->update();
        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->parcellaire->_id, "revision" => $this->parcellaire->_rev))));
        }

        if ($request->getParameter('redirect', null)) {

            return $this->redirect('parcellaire_validation', $this->parcellaire);
        }

        return $this->redirect('parcellaire_validation', $this->parcellaire);
    }

    public function executeValidation(sfWebRequest $request) {
        set_time_limit(180);
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        if ($this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_VALIDATION))) {
            $this->parcellaire->save();
        }


        $this->form = new ParcellaireValidationForm($this->parcellaire);

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->validation = new ParcellaireValidation($this->parcellaire);

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($this->parcellaire->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            return $this->redirect('parcellaire_visualisation', $this->parcellaire);
        }

        $this->sendParcellaireValidation($this->parcellaire);

        return $this->redirect('parcellaire_confirmation', $this->parcellaire);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaire);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaire);
    }

    public function executePDF(sfWebRequest $request) {
        set_time_limit(180);
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->parcellaire->declaration->cleanNode();

        $this->document = new ExportParcellairePDF($this->parcellaire, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if($request->getParameter('cvi')) {
            $this->document->setCviFilter($request->getParameter('cvi'), $request->getParameter('cvi'));
        }

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executeCSV(sfWebRequest $request) {
        set_time_limit(180);
        $parcellaire = $this->getRoute()->getParcellaire();
        $this->exportCsv = new ExportParcellaireCSV($parcellaire);

        $this->cvi = null;
        if($request->getParameter('cvi')) {
            $this->cvi = $request->getParameter('cvi');
        }
        $filename = $this->exportCsv->getFileName(true, $this->cvi);
        $attachement = "attachment; filename=" . $filename . ".csv";

        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition', $attachement);

        return $this->renderText($this->exportCsv->export($this->cvi));
    }

    public function sendParcellaireValidation($parcellaire) {
        $pdf = new ExportParcellairePdf($parcellaire, 'pdf', true);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->removeCache();
        $pdf->generate();
        Email::getInstance()->sendParcellaireValidation($parcellaire);
    }

    protected function getEtape($parcellaire, $etape) {
        $parcellaireEtapes = ParcellaireEtapes::getInstance();
        if (!$parcellaire->exist('etape')) {
            return $etape;
        }
        return ($parcellaireEtapes->isLt($parcellaire->etape, $etape)) ? $etape : $parcellaire->etape;
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
