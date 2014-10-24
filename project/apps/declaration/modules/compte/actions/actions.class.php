<?php

class compteActions extends sfActions {

    public function executeModification(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();
        
        $this->form = new EtablissementModificationEmailForm($this->etablissement);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->etablissement = $this->form->save();
                $this->getUser()->setFlash('maj', 'Vos identifiants ont bien été mis à jour.');
                $this->redirect('@mon_compte');
            }
        }
    }
    
    
    public function executeRedirectToMonCompteCiva(sfWebRequest $request) {
        $url_compte_civa = sfConfig::get('app_url_compte_civa');
        return $this->redirect($url_compte_civa);        
    }   

}
