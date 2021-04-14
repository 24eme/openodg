<?php

class facturationActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $this->generations = GenerationClient::getInstance()->findHistoryWithType(GenerationClient::TYPE_DOCUMENT_FACTURES,200);

        $this->form = new LoginForm();

        if(class_exists("EtablissementChoiceForm")) {
            $this->formEtablissement = new EtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => ""), true);

            $this->generation = new Generation();
            $this->generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
            $this->generation->somme = 0;
        }
        $this->formFacturationMassive = new FactureGenerationForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
         $this->formEtablissement->bind($request->getParameter($this->formEtablissement->getName()));
         $this->formFacturationMassive->bind($request->getParameter($this->formFacturationMassive->getName()));
         $this->uniqueTemplateFactureName = $this->getUniqueTemplateFactureName();
         if($this->formEtablissement->isValid()) {
              $etb = EtablissementClient::getInstance()->find($this->formEtablissement->getValue('identifiant'));
              return $this->redirect('facturation_declarant', array('id' => $etb->getMasterCompte()->_id));
          }
          if($this->formFacturationMassive->isValid()) {

              $generation = $this->formFacturationMassive->save();
              $generation->arguments->add('modele', $this->uniqueTemplateFactureName);
              $generation->save();

              return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission));
          }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('facturation_declarant', $this->form->getValue('etablissement')->getCompte());
    }

    public function executeMassive(sfWebRequest $request)
    {
        $this->generation = new Generation();
        $this->generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
        $this->generation->somme = 0;

        $defaults = array();
        if($request->getParameter('q')) {
          $defaults['requete'] = $request->getParameter('q');
        }

        $options = array('modeles' => TemplateFactureClient::getInstance()->findAll(),'uniqueTemplateFactureName' => $this->getUniqueTemplateFactureName());

        $this->form = new FactureGenerationForm();


        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->updateDocument();
        $this->generation->save();

        return $this->redirect('generation_view', array('type_document' => GenerationClient::TYPE_DOCUMENT_FACTURES, 'date_emission' => $this->generation->date_emission));
    }


    public function executeDeclarant(sfWebRequest $request){
            $this->compte = $this->getRoute()->getCompte();
            $this->force = boolval($request->getParameter('force',false));

            $this->forwardCompteSecure();
            $identifiant = $this->compte->identifiant;
            if($this->compte->exist('id_societe')){
              $identifiant = $this->compte->getSociete()->identifiant;
            }

            $this->form = new FactureGenerationForm();

            $this->identifiant = $request->getParameter('identifiant');

            $this->factures = FactureClient::getInstance()->getFacturesByCompte($identifiant, acCouchdbClient::HYDRATE_DOCUMENT);
            $this->mouvements = MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($this->compte->getSociete());

            $this->templatesFactures = TemplateFactureClient::getInstance()->findAll();
            $this->uniqueTemplateFactureName = $this->getUniqueTemplateFactureName();

            if($this->force){
                try {
                  foreach ($this->templatesFactures as $key => $templateFacture) {
                    $this->mouvements = array_merge($templateFacture->getMouvementsFactures($this->compte->identifiant, $this->force),$this->mouvements);
                  }
                } catch (FacturationPassException $e) { }
            }

            $this->setTemplate('declarant');

            if (!$request->isMethod(sfWebRequest::POST)) {

                return sfView::SUCCESS;
            }

            $this->form->bind($request->getParameter($this->form->getName()));

            if(!$this->form->isValid()) {

                return sfView::SUCCESS;
            }

            $generation = $this->form->save();
            $generation->arguments->add('modele', $this->uniqueTemplateFactureName);
            $generation->arguments->add('compte', $this->compte->_id);
            $generation->save();

            $urlRetour = $this->generateUrl('facturation_declarant', array('id' => $this->compte->_id));
            return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission, 'retour' => $urlRetour));

        }

    public function executeEdition(sfWebRequest $request) {
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$this->facture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $this->form = new FactureEditionForm($this->facture);

        if($this->facture->isPayee()) {

            throw new sfException(sprintf("La factures %s a déjà été payée", $facture->_id));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->facture->isAvoir()) {
            $this->getUser()->setFlash("notice", "L'avoir a été modifiée.");
        } else {
            $this->getUser()->setFlash("notice", "La facture a bien été modifiée.");
        }

        if($request->getParameter("not_redirect")) {

            return $this->redirect('facturation_edition', $this->facture);
        }

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant));
    }

    public function executeAvoirdefacturant(sfWebRequest $request){
      $this->baseFacture = FactureClient::getInstance()->find($request->getParameter('id'));

      if(!$this->baseFacture) {

          return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
      }

      $this->facture = FactureClient::createAvoir($this->baseFacture);

      $this->facture = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($this->baseFacture);
      return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->baseFacture->getSociete()->getEtablissementPrincipal()->identifiant));
    }

    public function executeAvoir(sfWebRequest $request) {
        $this->baseFacture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$this->baseFacture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $this->facture = FactureClient::createAvoir($this->baseFacture);

        $this->form = new FactureEditionForm($this->facture);

        $this->setTemplate('edition');

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "L'avoir a été créé.");

        if($request->getParameter("not_redirect")) {

            return $this->redirect('facturation_edition', $this->facture);
        }

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant));
    }

    public function executePaiement(sfWebRequest $request) {
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$this->facture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $this->form = new FacturePaiementForm($this->facture);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "Le paiement a bien été ajouté");

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant));
    }

    public function executePaiements(sfWebRequest $request) {
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$this->facture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $this->form = new FacturePaiementsMultipleForm($this->facture);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "Les paiements ont bien été enregistrés");

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant));
    }

    public function executeLatex(sfWebRequest $request) {
        $this->setLayout(false);
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));
        $this->compte = $this->getRoute()->getCompte();

        $this->forwardCompteSecure();

        if(!$this->facture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $latex = new FactureLatex($this->facture);
        $latex->echoWithHTTPHeader($request->getParameter('type'));

        if(!$this->getUser()->isAdmin()) {
            $this->facture->setTelechargee();
            $this->facture->save();
        }
        exit;
    }

    public function executeGetFactureWithAuth(sfWebRequest $request) {
        $authKey = $request->getParameter('auth');
        $id = $request->getParameter('id');

        if (UrlSecurity::verifyAuthKey($authKey, $id)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        $facture = FactureClient::getInstance()->find($id);
        $facture->setTelechargee();
        $facture->save();

        $latex = new FactureLatex($facture);
        $latex->echoWithHTTPHeader();

        exit;
    }

    public function executeRegenerate(sfWebRequest $request) {
        $facture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$facture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $f = FactureClient::getInstance()->regenerate($facture);
        $f->save();

        $this->getUser()->setFlash("notice", "La facture a été regénérée.");

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$f->identifiant));
    }

    public function executeGenerer(sfWebRequest $request) {
        $this->redirect403IfIsTeledeclaration();
        $parameters = $request->getParameter('facture_generation');
        $date_facturation = (!isset($parameters['date_facturation']))? null : DATE::getIsoDateFromFrenchDate($parameters['date_facturation']);
        $message_communication = (!isset($parameters['message_communication']))? null : $parameters['message_communication'];
        $parameters['date_mouvement'] = (isset($parameters['date_mouvement']) && $parameters['date_mouvement']!='')?  $parameters['date_mouvement'] : $date_facturation;
        if(!isset($parameters['type_document']) || !$parameters['type_document'] || $parameters['type_document'] == FactureGenerationMasseForm::TYPE_DOCUMENT_TOUS) {
          unset($parameters['type_document']);
        }

        $this->societe = $this->getRoute()->getSociete();

        $mouvementsBySoc = array($this->societe->identifiant => FactureClient::getInstance()->getFacturationForSociete($this->societe));
        $mouvementsBySoc = FactureClient::getInstance()->filterWithParameters($mouvementsBySoc,$parameters);
        if($mouvementsBySoc)
        {
            $generation = FactureClient::getInstance()->createFacturesBySoc($mouvementsBySoc,$date_facturation, $message_communication);
            $generation->save();
        }
        $this->redirect('facture_societe', $this->societe);
    }

    public function executeTemplate(sfWebRequest $request) {
        $this->template = TemplateFactureClient::getInstance()->find($request->getParameter('id'));
    }

    private function getLatexTmpPath() {
            return "/tmp/";
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

    protected function getUniqueTemplateFactureName(){
      $cm = new CampagneManager(date('m-d'),CampagneManager::FORMAT_PREMIERE_ANNEE);
      return FactureConfiguration::getinstance()->getUniqueTemplateFactureName($cm->getCurrentPrevious());
    }

    protected function forwardCompteSecure(){
      if(!method_exists($this->getUser(),"getEtablissement")){
          if(!$this->getUser()->isAdmin() && $this->compte->identifiant != $this->getUser()->getCompte()->getSociete()->getEtablissementPrincipal()->identifiant && $this->compte->identifiant != $this->getUser()->getCompte()->getSociete()->identifiant){
              return $this->forwardSecure();
          }
      }elseif(!$this->getUser()->isAdmin() && $this->getUser()->getEtablissement() && $this->compte->_id != $this->getUser()->getEtablissement()->getCompte()->_id) {

          return $this->forwardSecure();
      }
    }
}
