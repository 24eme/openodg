<?php

class habilitationActions extends sfActions {


  public function executeIndex(sfWebRequest $request)
  {
      //$this->lastHabilitations = ;

      $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);

      if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
      }

      $this->form->bind($request->getParameter($this->form->getName()));

      var_dump($this->form->getValue('etablissement')); exit;
      if(!$this->form->isValid()) {

          return sfView::SUCCESS;
      }
      return $this->redirect('habilitation_declarant', $this->form->getValue('etablissement'));
  }


  public function executeEtablissementSelection(sfWebRequest $request) {
      $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
      $form->bind($request->getParameter($form->getName()));
      if (!$form->isValid()) {

          return $this->redirect('habilitation');
      }

      return $this->redirect('habilitation_declarant', $form->getEtablissement());
  }

  public function executeDeclarant(sfWebRequest $request) {
      $this->etablissement = $this->getRoute()->getEtablissement();
      $this->habilitationsHistory = array();
  }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $habilitation = HabilitationClient::getInstance()->createDoc($etablissement->identifiant,date('Y-m-d'));
        $habilitation->save();

        return $this->redirect('habilitation_edition', $habilitation);
    }


    public function executeDelete(sfWebRequest $request) {
        $habilitation = $this->getRoute()->getHabilitation();
        $etablissement = $habilitation->getEtablissementObject();
        $this->secure(HabilitationSecurity::EDITION, $habilitation);

        $habilitation->delete();
        $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', $etablissement);
    }


    public function executeAjoutProduit(sfWebRequest $request) {
        $habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $habilitation);

        $this->ajoutForm = new HabilitationCepageAjoutProduitForm($habilitation);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('habilitation_edition', $habilitation);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('habilitation_edition', $habilitation);
    }

    public function executeHabilitationRecapitulatif(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->isBlocked = count($this->habilitation->getProduits(true)) < 1;
    }

    public function executeEdition(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        if($this->habilitation->storeEtape($this->getEtape($this->habilitation, HabilitationEtapes::ETAPE_EDITION))) {
            $this->habilitation->save();
        }

        $this->editForm = new HabilitationEditionForm($this->habilitation);
        $this->ajoutForm = new HabilitationCepageAjoutProduitForm($this->habilitation);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->editForm->bind($request->getParameter($this->editForm->getName()));

            if (!$this->editForm->isValid() && $request->isXmlHttpRequest()) {
                return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->habilitation->_id, "revision" => $this->habilitation->_rev))));
            }
            if ($this->editForm->isValid()) {
                $this->editForm->save();

                if ($request->isXmlHttpRequest()) {

                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->habilitation->_id, "revision" => $this->habilitation->_rev))));
                }

                if ($request->getParameter('redirect', null)) {
                    return $this->redirect('habilitation_validation', $this->habilitation);
                }
            }
        }
    }

    public function executeHabilitationAjoutProduit(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(habilitationSecurity::EDITION, $this->habilitation);

        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('habilitation_edition', $this->habilitation);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('habilitation_validation', $this->habilitation);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        if($this->habilitation->storeEtape($this->getEtape($this->habilitation, HabilitationEtapes::ETAPE_VALIDATION))) {
            $this->habilitation->save();
        }

        $this->habilitation->cleanDoc();
        $this->validation = new HabilitationValidation($this->habilitation);
        $this->form = new HabilitationValidationForm($this->habilitation);

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


        $this->habilitation->save();

        if($this->getUser()->isAdmin()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            return $this->redirect('Habilitation_visualisation', $this->habilitation);
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::VISUALISATION, $this->habilitation);

        $this->service = $request->getParameter('service');


        if($this->getUser()->isAdmin() && $this->habilitation->validation && !$this->habilitation) {
            $this->validation = new HabilitationValidation($this->habilitation);
        }

        $this->form = null;

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('habilitation_visualisation', $this->habilitation);
    }

    public function executeModificative(sfWebRequest $request) {
        $habilitation = $this->getRoute()->getHabilitation();

        $habilitation_modificative = $habilitation->generateModificative();
        $habilitation_modificative->save();

        return $this->redirect('habilitation_edition', $habilitation_modificative);
    }

    public function executePDF(sfWebRequest $request) {
        $habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::VISUALISATION, $habilitation);

        if (!$habilitation->validation) {
            $habilitation->cleanDoc();
        }

        $this->document = new ExportHabilitationPdf($habilitation, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    protected function getEtape($habilitation, $etape) {
        $habilitationEtapes = HabilitationEtapes::getInstance();
        if (!$habilitation->exist('etape')) {
            return $etape;
        }
        return ($habilitationEtapes->isLt($habilitation->etape, $etape)) ? $etape : $habilitation->etape;
    }

    protected function secure($droits, $doc) {
        if (!HabilitationSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {
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
