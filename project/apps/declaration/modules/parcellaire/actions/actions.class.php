<?php

class parcellaireActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();
        $this->form = new LoginForm();
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('home');
    }

    public function executeDelete(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $parcellaire);

        $parcellaire->delete();
        $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect($this->generateUrl('home'));
    }

    public function executeDevalidation(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::DEVALIDATION, $parcellaire);

        $parcellaire->validation = null;
        $parcellaire->validation_odg = null;
        $parcellaire->etape = null;
        $parcellaire->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('home'));
    }

    public function executeCreate(sfWebRequest $request) {

        $etablissement = $this->getRoute()->getEtablissement();
        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->parcellaire->save();

        return $this->redirect('parcellaire_edit', $this->parcellaire);
    }

    public function executeEdit(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        if ($parcellaire->exist('etape') && $parcellaire->etape) {
            return $this->redirect('parcellaire_' . $parcellaire->etape, $parcellaire);
        }

        return $this->redirect('parcellaire_exploitation', $parcellaire);
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        $this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_EXPLOITATION));
        $this->parcellaire->save();

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

        $this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_PROPRIETE));
        $this->parcellaire->save();

        $this->form = new ParcellaireTypeProprietaireForm($this->parcellaire);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->parcellaire->_id, "revision" => $this->parcellaire->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('parcellaire_validation', $this->parcellaire);
        }

        $this->firstAppellation = ParcellaireClient::getInstance()->getFirstAppellation();
        return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->firstAppellation));
    }

    public function executeParcelles(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        $this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_PARCELLES));
        $this->parcellaire->save();

        $this->parcellaire->initProduitFromLastParcellaire();
        $this->parcellaireAppellations = ParcellaireClient::getInstance()->getAppellationsKeys();
        $this->appellation = $request->getParameter('appellation');
        $this->ajoutForm = new ParcellaireAjoutParcelleForm($this->parcellaire, $this->appellation);
        $this->appellationNode = $this->parcellaire->getAppellationNodeFromAppellationKey($this->appellation);

        $allParcellesByAppellations = $this->parcellaire->getAllParcellesByAppellations();
        $this->parcelles = array();
        foreach ($allParcellesByAppellations as $appellation) {
            $appellationKey = str_replace('appellation_', '', $appellation->appellation->getKey());
            if ($this->appellation == $appellationKey) {
                $this->parcelles = $appellation->parcelles;
            }
        }

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
                $next_appellation = $this->appellationNode->getNextAppellationKey();
                if ($next_appellation) {
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


        $this->ajoutForm = new ParcellaireAjoutParcelleForm($this->parcellaire, $this->appellation);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->appellation));
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'La parcelle a été ajoutée avec succès.');

        return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->appellation));
    }

    public function executeAcheteurs(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        $this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_ACHETEURS));

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
        $this->parcellaire = $this->getRoute()->getParcellaire();

        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

        $this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_VALIDATION));
        $this->parcellaire->save();

        $this->validation = new ParcellaireValidation($this->parcellaire);
        $this->parcellesByCommunes = $this->parcellaire->getParcellesByCommunes();
        $this->form = new ParcellaireValidationForm($this->parcellaire);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {

                $this->parcellaire->validate();
                $this->parcellaire->save();
                $this->sendParcellaireValidation($this->parcellaire);

                return $this->redirect('parcellaire_confirmation', $this->parcellaire);
            }
        }
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaire);
    }

    public function executePDF(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        $this->document = new ExportParcellairePDF($parcellaire, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
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
