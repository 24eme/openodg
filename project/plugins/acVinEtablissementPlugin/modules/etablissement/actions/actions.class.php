<?php

class etablissementActions extends sfCredentialActions {

    public function executeAjout(sfWebRequest $request) {
        $this->societe = $this->getRoute()->getSociete();
        $this->applyRights();
        if (!$this->modification) {
            $this->forward('acVinCompte', 'forbidden');
        }
        $this->famille = $request->getParameter('famille');
        $this->etablissement = $this->societe->createEtablissement($this->famille);
        $this->processFormEtablissement($request);
        $this->setTemplate('modification');
    }

    public function executeModification(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->societe = $this->etablissement->getSociete();

        $this->applyRights();
        if (!$this->modification) {
            $this->forward('acVinCompte', 'forbidden');
        }
        $this->processFormEtablissement($request);
    }

    protected function processFormEtablissement(sfWebRequest $request) {
        $this->etablissementModificationForm = new EtablissementModificationForm($this->etablissement);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->etablissementModificationForm->bind($request->getParameter($this->etablissementModificationForm->getName()));
            if ($this->etablissementModificationForm->isValid()) {
                $this->etablissementModificationForm->save();
                $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->societe = $this->etablissement->getSociete();
        $this->applyRights();
        $this->compte = $this->etablissement->getMasterCompte();
        if((!$this->compte->lat && !$this->compte->lon) || !$this->compte->hasLatLonChais()){
          $this->compte->updateCoordonneesLongLat(true);
          $this->compte->save();

        }
    }

     public function executeSwitchStatus(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $newStatus = "";
        if($this->etablissement->isActif()){
           $newStatus = SocieteClient::STATUT_SUSPENDU;
        }
        if($this->etablissement->isSuspendu()){
           $newStatus = SocieteClient::STATUT_ACTIF;
        }
        $compte = $this->etablissement->getMasterCompte();
        if($compte && !$this->etablissement->isSameCompteThanSociete()){
            $compte->setStatut($newStatus);
            $compte->save();
        }
        $this->etablissement->setStatut($newStatus);
        $this->etablissement->save();
        return $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
    }

    public function executeChaiModification(sfWebRequest $request) {
      $this->etablissement = $this->getRoute()->getEtablissement();
      $this->societe = $this->etablissement->getSociete();
      $this->num = $request->getParameter('num');
      $this->chai = $this->etablissement->get('chais')->get($this->num);
      $this->form = new EtablissementChaiModificationForm($this->chai);
      if ($request->isMethod(sfWebRequest::POST)) {
          $this->form->bind($request->getParameter($this->form->getName()));
          if ($this->form->isValid()) {
              $this->form->save();
              $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
          }
      }
    }

    public function executeChaiSuppression(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->etablissement->chais->remove($request->getParameter('num'));
        $this->etablissement->save();
        $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
    }

    public function executeChaiAjout(sfWebRequest $request) {
      $this->etablissement = $this->getRoute()->getEtablissement();
      $newChai = $this->etablissement->getOrAdd('chais')->add();
      $this->etablissement->save();
      return $this->redirect('etablissement_edition_chai', array('identifiant' => $this->etablissement->identifiant, 'num' => $newChai->getKey()));
    }

    public function executeRelationAjout(sfWebRequest $request) {
      $this->etablissement = $this->getRoute()->getEtablissement();
      $this->societe = $this->etablissement->getSociete();

      $this->form = new EtablissementRelationForm($this->etablissement);
      if ($request->isMethod(sfWebRequest::POST)) {
          $this->form->bind($request->getParameter($this->form->getName()));
          if ($this->form->isValid()) {
              $this->form->save();
              $this->redirect('etablissement_visualisation', array('identifiant' => $this->etablissement->identifiant));
          }
      }
    }

}
