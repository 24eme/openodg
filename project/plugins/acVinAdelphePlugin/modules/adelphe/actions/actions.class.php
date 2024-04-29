<?php

class adelpheActions extends sfActions {

  public function executeCreate(sfWebRequest $request) {
      $etablissement = $this->getRoute()->getEtablissement();
      $this->secureEtablissement(AdelpheSecurity::DROIT_ADELPHE, $etablissement);
      $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentYearPeriode());
      $adelphe = AdelpheClient::getInstance()->createDoc($etablissement->identifiant, $periode, ($request->getParameter('papier') == 1));
      $adelphe->save();
      return $this->redirect('adelphe_edit', $adelphe);
  }

  public function executeEdit(sfWebRequest $request) {
      $adelphe = $this->getRoute()->getAdelphe();
      $this->secure(AdelpheSecurity::EDITION, $adelphe);
      if ($adelphe->exist('etape') && $adelphe->etape) {
          return $this->redirect('adelphe_' . $adelphe->etape, $adelphe);
      }
      return $this->redirect('adelphe_volume_conditionne', $adelphe);
  }

  public function executeVolumeConditionne(sfWebRequest $request) {
    $this->adelphe = $this->getRoute()->getAdelphe();
    $this->secure(AdelpheSecurity::EDITION, $this->adelphe);
    if($this->adelphe->storeEtape($this->getEtape($this->adelphe, AdelpheEtapes::ETAPE_VOLUME_CONDITIONNE))) {
      $this->adelphe->save();
    }
    $this->adelphe->setRedirect(false);
    $this->adelphe->save();
    $this->form = new AdelpheVolumeForm($this->adelphe);
    if (!$request->isMethod(sfWebRequest::POST)) {
      return sfView::SUCCESS;
    }
    $this->form->bind($request->getParameter($this->form->getName()));
    if (!$this->form->isValid()) {
        return sfView::SUCCESS;
    }
    $this->form->save();

    if ($this->adelphe->volume_conditionne_total >= $this->adelphe->getMaxSeuil()) {
        $this->adelphe->setRedirect(true);
        $this->adelphe->save();
        return $this->redirect('adelphe_validation', $this->adelphe);
    }
    return $this->redirect('adelphe_repartition_bib', $this->adelphe);
  }

  public function executeRepartitionBib(sfWebRequest $request) {
    $this->adelphe = $this->getRoute()->getAdelphe();
    $this->secure(AdelpheSecurity::EDITION, $this->adelphe);
    if($this->adelphe->storeEtape($this->getEtape($this->adelphe, AdelpheEtapes::ETAPE_REPARTITION_BIB))) {
      $this->adelphe->save();
    }
    $this->form = new AdelpheRepartitionForm($this->adelphe);
    if (!$request->isMethod(sfWebRequest::POST)) {
      return sfView::SUCCESS;
    }
    $this->form->bind($request->getParameter($this->form->getName()));
    if (!$this->form->isValid()) {
        return sfView::SUCCESS;
    }
    $this->form->save();

    if ($this->adelphe->volume_conditionne_total >= $this->adelphe->getSeuil()) {
        $this->adelphe->setRedirect(true);
        $this->adelphe->save();
    }
    return $this->redirect('adelphe_validation', $this->adelphe);
  }

  public function executeValidation(sfWebRequest $request) {
    $this->adelphe = $this->getRoute()->getAdelphe();
    $this->secure(AdelpheSecurity::EDITION, $this->adelphe);
    if($this->adelphe->storeEtape($this->getEtape($this->adelphe, AdelpheEtapes::ETAPE_VALIDATION))) {
      $this->adelphe->save();
    }
    if (!$request->isMethod(sfWebRequest::POST)) {
        return sfView::SUCCESS;
    }
    $this->adelphe->validate(date('c'));
    $this->adelphe->save();
    Email::getInstance()->sendAdelpheValidation($this->adelphe);
    if ($this->adelphe->redirect_adelphe) {
        return $this->redirect(AdelpheConfiguration::getInstance()->getUrlAdelphe());
    }
    return $this->redirect('adelphe_visualisation', $this->adelphe);
  }

  public function executeVisualisation(sfWebRequest $request) {
      $this->adelphe = $this->getRoute()->getAdelphe();
  }

  public function executeDelete(sfWebRequest $request) {
      $adelphe = $this->getRoute()->getAdelphe();
      $this->secure(AdelpheSecurity::EDITION, $adelphe);
      $adelphe->delete();
      $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");
      return $this->redirect('declaration_etablissement', array('identifiant' => $adelphe->identifiant));
    }

  public function executeExport(sfWebRequest $request) {
    $this->forward404Unless($this->getUser()->isAdmin());
    $ids = DeclarationClient::getInstance()->getIds(AdelpheClient::TYPE_MODEL);
    $csv = ExportAdelpheCSV::getHeaderCsv();
    foreach($ids as $id) {
      $doc = AdelpheClient::getInstance()->find($id);
      if (!$doc->validation) {
        continue;
      }
      $export = new ExportAdelpheCSV($doc, false);
      $csv .= $export->export();
    }
    $this->response->setContentType('text/csv');
    $this->response->setHttpHeader('Content-Disposition', "attachment; filename=".date('YmdH:i')."_declarations_adelphe.csv");
    return $this->renderText($csv);
  }

  public function executeReouvrir(sfWebRequest $request) {
      $this->forward404Unless($this->getUser()->isAdmin());
      $adelphe = $this->getRoute()->getAdelphe();
      $adelphe->devalidate();
      $adelphe->save();
      return $this->redirect('adelphe_edit',$adelphe);
  }

  private function getEtape($doc, $etape) {
    $etapes = AdelpheEtapes::getInstance();
    if (!$doc->exist('etape')) {
      return $etape;
    }
    return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

    protected function secure($droits, $doc) {
      if ($droits == AdelpheSecurity::EDITION) {
        if ($doc && $doc->validation) {
          return $this->forwardSecure();
        }
      }
      if (!AdelpheSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {
        return $this->forwardSecure();
      }
    }

    protected function secureEtablissement($droit, $etablissement) {
        if (!$etablissement->getMasterCompte()->hasDroit($droit)) {
            throw new sfError403Exception($etablissement->_id." n'a pas les droits pour accéder à la déclaration Adelphe");
            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
      $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
      throw new sfStopException();
    }

}
