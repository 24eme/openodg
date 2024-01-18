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
    return $this->redirect('adelphe_repartition_bib', $adelphe);
  }

  public function executeDelete(sfWebRequest $request) {
      $adelphe = $this->getRoute()->getAdelphe();
      $adelphe->delete();
      $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");
      return $this->redirect('declaration_etablissement', array('identifiant' => $adelphe->identifiant));
  }

  public function executeVisualisation(sfWebRequest $request) {
    $adelphe = $this->getRoute()->getAdelphe();
  }

  private function getEtape($doc, $etape) {
    $etapes = AdelpheEtapes::getInstance();
    if (!$doc->exist('etape')) {
      return $etape;
    }
    return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
  }

}
