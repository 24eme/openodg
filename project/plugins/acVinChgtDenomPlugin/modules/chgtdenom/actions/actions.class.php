<?php

class chgtdenomActions extends sfActions
{
    public function executeAjoutLot(sfWebRequest $request) {

        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->campagne = $request->getParameter('campagne');

        $drev = DRevClient::getInstance()->createDoc($this->etablissement->identifiant, $this->campagne);
        $drev->addLot();
        $drev->lots[0]->numero_archive = sprintf("%05d", "9999");
        $drev->lots[0]->numero_dossier = sprintf("%05d", "9999");
        $this->lot = $drev->lots[0] ;

        $papier = ($this->getUser()->isAdmin()) ? 1 : 0;
        $this->chgtDenom = ChgtDenomClient::getInstance()->createDoc($this->etablissement->identifiant,null, $papier);

        $this->form = new ChgtDenomNewLotForm($drev->lots[0]);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->chgtDenom->origine_millesime = $this->form->getValue('millesime');
        $this->chgtDenom->origine_volume = $this->form->getValue('volume');
        $this->chgtDenom->origine_specificite = $this->form->getValue('specificite');
        $this->chgtDenom->origine_produit_hash = $this->form->getValue('produit_hash');
        $this->chgtDenom->origine_cepages = array($this->form->getValue('cepage_0'),$this->form->getValue('cepage_1'),$this->form->getValue('cepage_2'),$this->form->getValue('cepage_3'),$this->form->getValue('cepage_4'));
        $this->chgtDenom->origine_numero_logement_operateur = $this->form->getValue('numero_logement_operateur');
        $this->chgtDenom->constructId();
        $this->chgtDenom->save();

        $this->form->save();

        return $this->redirect('chgtdenom_edition', array('id' => $this->chgtDenom->_id));
    }

    public function executeCreateFromLot(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $lot = $request->getParameter('lot');
        $this->secureEtablissement(null, $etablissement);

        $papier = ($this->getUser()->isAdmin()) ? 1 : 0;

        $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, null, $papier);

        // Format de $lot : DEGUSTATION-20210101:2020-2021-00001-00001 | document_id:unique_id
        $docid = strtok($lot, ':');
        $unique_id = strtok(':');
        $doc = acCouchdbManager::getClient()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($unique_id);
        $this->forward404Unless($lot);
        $chgtDenom->setLotOrigine($lot);
        $chgtDenom->save();

        return $this->redirect('chgtdenom_edition', array('id' => $chgtDenom->_id));
    }

    public function executeLots(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->campagne = $request->getParameter('campagne');
        $this->lots = ChgtDenomClient::getInstance()->getLotsChangeable($this->etablissement->identifiant);
    }

    public function executeEdition(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($this->chgtDenom);

        if(!$this->chgtDenom->getLotOrigine()) {
            return $this->redirect('chgtdenom_lots', array('sf_subject' => $this->chgtDenom->getEtablissementObject(), 'campagne' => $this->chgtDenom->campagne));
        }

        $this->form = new ChgtDenomForm($this->chgtDenom);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('chgtdenom_validation', $this->chgtDenom);
    }

    public function executeLogement(sfWebRequest $request) {
        $chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($chgtDenom);

        $form = new ChgtDenomLogementForm($chgtDenom);

        $form->bind($request->getParameter($form->getName()));

        if (!$form->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            return $this->redirect('chgtdenom_validation', $chgtDenom);
        }

        $form->save();
        $this->getUser()->setFlash("notice", 'Le logement a été modifié avec succès.');
        return $this->redirect('chgtdenom_validation', $chgtDenom);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($this->chgtDenom);
        $this->isAdmin = $this->getUser()->isAdmin();

        if (! $this->chgtDenom->isValide()) {
            $this->formLogement = new ChgtDenomLogementForm($this->chgtDenom);
        }

        $this->validation = new ChgtDenomValidation($this->chgtDenom);

        $this->form = new ChgtDenomValidationForm($this->chgtDenom, array(), array('isAdmin' => $this->isAdmin));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin) {
            $this->chgtDenom->validateOdg();
            $this->chgtDenom->save();
            $this->getUser()->setFlash("notice", "Le changement dénommination a été validé et approuvé");

            return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
        }

        return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->isAdmin = $this->getUser()->isAdmin();

        $this->form = null;
        if ($this->isAdmin && !$this->chgtDenom->isApprouve()) {
          $this->form = new ChgtDenomValidationForm($this->chgtDenom, array(), array('isAdmin' => $this->isAdmin));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin) {
            $this->chgtDenom->validateOdg();
            $this->chgtDenom->save();
            $this->getUser()->setFlash("notice", "Le changement dénomination a été approuvé");
        }

        return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $chgtDenom = $this->getRoute()->getChgtDenom();
        if (!$this->getUser()->isAdmin()) {
          $this->secure(ChgtDenomSecurity::DEVALIDATION , $chgtDenom);
        }

        if($chgtDenom->hasLotsUtilises()) {
            throw new Exception("Dévalidation impossible car des lots dans cette déclaration sont utilisés");
        }

        $chgtDenom->devalidate();
        $chgtDenom->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('chgtdenom_edition', $chgtDenom));
    }

    public function executeSuppression(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($this->chgtDenom);
        $identifiant = $this->chgtDenom->identifiant;
        $this->chgtDenom->delete();
        return $this->redirect('declaration_etablissement', array('identifiant' => $identifiant));
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    public function executeChgtDenomPDF(sfWebRequest $request)
    {
        $chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureEtablissement(null, $chgtDenom->getEtablissementObject());

        $this->document = new ExportChgtDenomPDF($chgtDenom, $request->getParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generate();
        $this->document->addHeaders($this->getResponse());
        return $this->renderText($this->document->output());
    }

    protected function secureIsValide($chgtDenom) {
      if ($chgtDenom->isValide()) {
        return $this->redirect('chgtdenom_visualisation', $chgtDenom);
      }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}
