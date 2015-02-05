<?php

class parcellaireActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();
        $this->form = new LoginForm();
        
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }

        
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));        
        return $this->redirect('home'); 
    }

    public function executeCreation(sfWebRequest $request) {        
        $this->etablissementIdentifiant = $request->getParameter('identifiant');
        
        if(!$this->etablissementIdentifiant){
            throw new sfException("L'identifiant de l'etablissement est obligatoire pour créer un parcellaire");
        }
        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->etablissementIdentifiant);        
        if(!$this->etablissement){
            throw new sfException("L'etablissement n'a pas été trouvé");
        }
        
        $campagneManager = new CampagneManager('08-01',  CampagneManager::FORMAT_PREMIERE_ANNEE);        
        $this->campagne = $campagneManager->getCurrent();              
        
        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate($this->etablissement,$this->campagne);
        
        if($request->isMethod(sfWebRequest::POST)){
            return $this->redirect('parcellaire_parcelles',array('identifiant' => $request['identifiant'])); 
        }
        
    }

    public function executeParcelles(sfWebRequest $request) {
        
    }
    
    public function executeParcelleAppellation(sfWebRequest $request) {
        $this->appellation = $request->getParameter('appellation');
        return $this->setTemplate('parcelles');
    }

    public function executeAcheteurs(sfWebRequest $request) {
 
    }

    public function executeDegustateurs(sfWebRequest $request) {

    }

    public function executeAgents(sfWebRequest $request) {

    }

    public function executePrelevements(sfWebRequest $request) {

    }

    public function executeValidation(sfWebRequest $request) {
 
    }

    public function executeTournee(sfWebRequest $request) {

    }
}
