<?php

class authActions extends sfActions {

    public function executeLogin(sfWebRequest $request) {
        if (sfConfig::get("app_auth_mode") != 'NO_CAS') {


            return $this->forward404();
        }

        $this->form = new TeledeclarationCompteLoginForm(null, array('comptes_type' => array('Compte'), false));


        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $idCompte = $this->form->process()->identifiant;
        $idSociete = $this->form->process()->getSociete()->getIdentifiant();
        $this->getUser()->signInOrigin($this->form->getValue("login"));

        return $this->redirect('common_accueil');
    }

    public function executeLogout(sfWebRequest $request) {
        $this->getUser()->signOutOrigin();
        $urlBack = $this->generateUrl('common_accueil', array(), true);

        if (sfConfig::get("app_auth_mode") == 'CAS') {
            acCas::processLogout($urlBack);
        }

          return $this->redirect('common_accueil');
    }

    public function executeUsurpation(sfWebRequest $request) {
        if(!$this->getUser()->isAdminODG()) {
            throw new sfError403Exception();
        }

        $compte = CompteClient::getInstance()->find("COMPTE-".$request->getParameter('identifiant'));
        $identifiant = $compte->getSociete()->identifiant;
        if($compte->compte_type == CompteClient::TYPE_COMPTE_INTERLOCUTEUR) {
            $identifiant = $compte->identifiant;
        }

        $this->getUser()->usurpationOn($identifiant, $request->getReferer());

        return $this->redirect('common_accueil');
    }

    public function executeDeconnexionUsurpation(sfWebRequest $request) {
        $url_back = $this->getUser()->usurpationOff();

        if ($url_back) {

            return $this->redirect($url_back);
        }

        $this->redirect('common_homepage');
    }


    public function executeForbidden(sfWebRequest $request) {

    }

    public function executeState(sfWebRequest $request)
    {
        $this->response->setContentType('application/json');

        return $this->renderText(json_encode(array("authenticated" => $this->getUser()->isAuthenticated())));
    }

}
