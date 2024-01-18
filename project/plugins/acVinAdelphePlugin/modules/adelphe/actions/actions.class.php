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
