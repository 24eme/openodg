<?php

class habilitationActions extends sfActions {


  public function executeIndex(sfWebRequest $request)
  {

      $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);

      if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
      }

      $this->form->bind($request->getParameter($this->form->getName()));

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
      $etablissement = $this->getRoute()->getEtablissement();
      $habilitationsHistory = HabilitationClient::getInstance()->getHistory($etablissement->identifiant);
      if (!count($habilitationsHistory)) {
        return $this->redirect('habilitation_create', array('sf_subject' => $etablissement));
      }
      foreach ($habilitationsHistory as $h) {
      }
      return $this->redirect('habilitation_edition', array('id' => $h->_id));
  }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $habilitation = HabilitationClient::getInstance()->createDoc($etablissement->identifiant,date('Y-m-d'));
        $habilitation->save();

        return $this->redirect('habilitation_edition', $habilitation);
    }

    public function executeAjoutProduit(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->habilitation = HabilitationClient::getInstance()->createOrGetDocFromHistory($this->habilitation);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);
        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
        $newHabilitationDoc = $this->habilitation->isNew();
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));
        if($newHabilitationDoc){
          $this->ajoutForm->getObject()->_rev = null;
        }

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            return $this->redirect('habilitation_edition', $this->habilitation);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect($this->generateUrl('habilitation_edition', $this->habilitation).'#ouvert');
    }

    public function executeHabilitationRecapitulatif(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->isBlocked = count($this->habilitation->getProduits(true)) < 1;
    }

    public function executeEdition(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->editForm = new HabilitationEditionForm($this->habilitation);
        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);

        if ($request->isMethod(sfWebRequest::POST)) {
          $this->habilitation = HabilitationClient::getInstance()->createOrGetDocFromHistory($this->habilitation);
          $newHabilitationDoc = $this->habilitation->isNew();

          $this->editForm = new HabilitationEditionForm($this->habilitation);
          $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
          $this->editForm->bind($request->getParameter($this->editForm->getName()));

            if (!$this->editForm->isValid() && $request->isXmlHttpRequest()) {

                return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->habilitation->_id, "revision" => $this->habilitation->_rev))));
            }
            if ($this->editForm->isValid()) {
                if($newHabilitationDoc){
                  $this->editForm->getObject()->_rev = null;
                }
                $this->editForm->save();
                if ($request->isXmlHttpRequest()) {

                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->habilitation->_id, "revision" => $this->habilitation->_rev))));
                }
                return $this->redirect('habilitation_edition', $this->habilitation);
            }
        }
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
