<?php

class tirageActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $tirage = TirageClient::getInstance()->createDoc($etablissement->identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $tirage->save();

        return $this->redirect('tirage_edit', $tirage);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $tirage = TirageClient::getInstance()->createDoc($etablissement->identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $tirage->save();

        return $this->redirect('drevmarc_edit', $tirage);
    }

    public function executeEdit(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();

        if ($tirage->exist('etape') && $tirage->etape) {
            return $this->redirect('tirage_' . $tirage->etape, $tirage);
        }

        return $this->redirect('tirage_exploitation', $tirage);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::DEVALIDATION, $tirage);

        $tirage->validation = null;
        $tirage->validation_odg = null;
        $tirage->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('home'));
    }

    public function executeDelete(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $tirage->delete();
        $this->getUser()->setFlash("notice", 'La déclaration de tirage a été supprimé avec succès.');

        return $this->redirect($this->generateUrl('home'));
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, TirageEtapes::ETAPE_EXPLOITATION));

        $this->tirage->save();

        $this->etablissement = $this->tirage->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->tirage->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->tirage->storeDeclarant();
        $this->tirage->save();
        return $this->redirect('tirage_vin', $this->tirage);
    }

    public function executeVin(sfWebRequest $request) {

        $this->tirage = $this->getRoute()->getTirage();
        $this->form = new TirageVinForm($this->tirage);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }


        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();
          return $this->redirect('tirage_lots', $this->tirage);
    }

    public function executeLots(sfWebRequest $request) {
        /* $this->drev = $this->getRoute()->getDRev();
          $this->secure(DRevSecurity::EDITION, $this->drev);

          $this->prelevement = $this->getRoute()->getPrelevement();

          $this->form = new DRevLotsForm($this->prelevement);
          $this->ajoutForm = new DrevLotsAjoutProduitForm($this->prelevement);

          $this->setTemplate(lcfirst(sfInflector::camelize(strtolower(('lots_' . $this->prelevement->getKey())))));

          $this->error_produit = null;
          if ($request->getParameter(('error_produit'))) {
          $type_error = strstr($request->getParameter('error_produit'), '-', true);
          $error_produit = str_replace($type_error, '', $request->getParameter('error_produit'));
          $this->error_produit = str_replace('-', '_', $error_produit);
          if ($type_error == 'erreur') {
          $this->getUser()->setFlash("erreur", "Pour supprimer un lot, il suffit de vider la case.");
          }
          if ($type_error == 'vigilancewithFlash') {
          $this->getUser()->setFlash("warning", "Pour supprimer un lot, il suffit de vider la case.");
          }
          }

          if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
          }

          $this->form->bind($request->getParameter($this->form->getName()));
          if ($request->isXmlHttpRequest() && !$this->form->isValid()) {
          return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
          }

          if (!$this->form->isValid()) {
          return sfView::SUCCESS;
          }

          $this->form->save();

          if ($request->isXmlHttpRequest()) {

          return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
          }


          if ($request->getParameter('redirect', null)) {
          return $this->redirect('drev_validation', $this->drev);
          }

          if ($this->prelevement->getKey() == Drev::CUVE_ALSACE && $this->drev->prelevements->exist(Drev::CUVE_GRDCRU)) {
          return $this->redirect('drev_lots', $this->drev->prelevements->get(Drev::CUVE_GRDCRU));
          }

          if($this->drev->isNonConditionneur()) {

          return $this->redirect('drev_validation', $this->drev);
          }

          return $this->redirect('drev_controle_externe', $this->drev); */
    }

    public function executeValidation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, DrevEtapes::ETAPE_VALIDATION));
        $this->tirage->save();

        $this->validation = new TirageValidation($this->tirage);

        $this->form = new TirageValidationForm($this->tirage);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if (!$this->validation->isValide()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        if ($this->tirage->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            $this->tirage->validate($this->form->getValue("date"));
            $this->tirage->validateOdg();
            $this->tirage->save();

            return $this->redirect('tirage_visualisation', $this->tirage);
        }

        $this->tirage->validate();
        $this->tirage->save();

        //$this->sendDRevMarcValidation($this->tirage);

        return $this->redirect('tirage_confirmation', $this->tirage);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
    }

    public function executePDF(sfWebRequest $request) {
        $tirage = new Tirage();
        /* $tirage = $this->getRoute()->getTirage();
          $this->secure(TirageSecurity::VISUALISATION, $tirage);

          if (!$tirage->validation) {
          $tirage->cleanDoc();
          }
         */

        $this->document = new ExportTiragePdf($tirage, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    protected function getEtape($tirage, $etape) {
        $tirageEtapes = TirageEtapes::getInstance();
        if (!$tirage->exist('etape')) {
            return $etape;
        }
        return ($tirageEtapes->isLt($tirage->etape, $etape)) ? $etape : $tirage->etape;
    }

    protected function secure($droits, $doc) {
        if (!TirageSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {
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
