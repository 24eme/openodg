<?php

class adelpheActions extends sfActions {

  public function executeCreate(sfWebRequest $request) {
      $etablissement = $this->getRoute()->getEtablissement();
      $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentYearPeriode());
      $adelphe = AdelpheClient::getInstance()->createDoc($etablissement->identifiant, $periode, ($request->getParameter('papier') == 1));
      $adelphe->save();
      return $this->redirect('adelphe_edit', $adelphe);
  }

  public function executeEdit(sfWebRequest $request) {
      $adelphe = $this->getRoute()->getAdelphe();
      return $this->redirect('adelphe_volume_conditionne', $adelphe);
  }

  public function executeVolumeConditionne(sfWebRequest $request) {
    $this->adelphe = $this->getRoute()->getAdelphe();
    $this->adelphe->setRedirect(false);
    if($this->adelphe->storeEtape($this->getEtape($this->adelphe, AdelpheEtapes::ETAPE_VOLUME_CONDITIONNE))) {
      $this->adelphe->save();
    }
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
        return $this->redirect('adelphe_validation', $this->adelphe);
    }
    return $this->redirect('adelphe_repartition_bib', $this->adelphe);
  }

  public function executeRepartitionBib(sfWebRequest $request) {
    $this->adelphe = $this->getRoute()->getAdelphe();
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
    }
    return $this->redirect('adelphe_validation', $this->adelphe);
  }

  public function executeValidation(sfWebRequest $request) {
    $this->adelphe = $this->getRoute()->getAdelphe();
    if (!$request->isMethod(sfWebRequest::POST)) {
        return sfView::SUCCESS;
    }
    $this->adelphe->validate(date('c'));
    $this->adelphe->save();
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
      $adelphe->delete();
      $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");
      return $this->redirect('declaration_etablissement', array('identifiant' => $adelphe->identifiant));
  }

  private function getEtape($doc, $etape) {
    $etapes = AdelpheEtapes::getInstance();
    if (!$doc->exist('etape')) {
      return $etape;
    }
    return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
  }

}
