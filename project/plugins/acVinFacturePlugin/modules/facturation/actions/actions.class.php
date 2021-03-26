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
            $this->formFacturationMassive = new FacturationMassiveForm($this->generation, array(), array('uniqueTemplateFactureName' => $this->getUniqueTemplateFactureName()));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        if(class_exists("EtablissementChoiceForm")) {
          $this->formEtablissement->bind($request->getParameter($this->formEtablissement->getName()));
          $this->formFacturationMassive->bind($request->getParameter($this->formFacturationMassive->getName()));
          if($this->formEtablissement->isValid()) {
              $etb = EtablissementClient::getInstance()->find($this->formEtablissement->getValue('identifiant'));
              return $this->redirect('facturation_declarant', array('id' => $etb->getMasterCompte()->_id));
          }
          if($this->formFacturationMassive->isValid()) {
              $etb = EtablissementClient::getInstance()->find($this->formEtablissement->getValue('identifiant'));
              return $this->redirect('facturation_declarant', array('id' => $etb->getMasterCompte()->_id));
          }
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

        $this->form = new FacturationMassiveForm($this->generation, $defaults, $options);



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
        $auth = $request->getParameter('auth');
        $id = $request->getParameter('id');

        $key = FactureClient::generateAuthKey($id);

        if (substr($auth,0,24) !== substr($key,0,24)) {
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

    public function executeDeclarant(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();

        $this->forwardCompteSecure();
        $identifiant = $this->compte->identifiant;
        if($this->compte->exist('id_societe')){
          $identifiant = $this->compte->getSociete()->identifiant;
        }
        $this->factures = FactureClient::getInstance()->getFacturesByCompte($identifiant, acCouchdbClient::HYDRATE_DOCUMENT);
        $this->values = array();
        $this->templatesFactures = TemplateFactureClient::getInstance()->findAll();
        $this->uniqueTemplateFactureName = $this->getUniqueTemplateFactureName();
        $this->form = new FacturationDeclarantForm(array(), array('modeles' => $this->templatesFactures,'uniqueTemplateFactureName' => $this->uniqueTemplateFactureName));

        $this->mouvements = array();

        try {
          foreach ($this->templatesFactures as $key => $templateFacture) {
            $this->mouvements = array_merge($templateFacture->getMouvementsFactures($this->compte->identifiant),$this->mouvements);
          }
        } catch (FacturationPassException $e) { }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->values = $this->form->getValues();
        if($this->uniqueTemplateFactureName = $this->getUniqueTemplateFactureName()) {
          $modelName = $this->uniqueTemplateFactureName;
        }else{
          $modelName = $this->values['modele'];
        }
        $templateFacture = TemplateFactureClient::getInstance()->find($modelName);
        try {
           $generation = FactureClient::getInstance()->createFactureByTemplateWithGeneration($templateFacture, $this->compte->_id, $this->value['date_facturation'], null, $templateFacture->arguments->toArray(true, false));
        } catch (Exception $e) {
           $this->getUser()->setFlash("error", $e->getMessage());
           return $this->redirect('facturation_declarant', $this->compte);
        }

        if(!$generation) {
            $this->getUser()->setFlash("error", "Cet opérateur a déjà été facturé pour ce type de facture.");

            return $this->redirect('facturation_declarant', $this->compte);
        }

        $generation->save();

        return $this->redirect('generation_view', array('type_document' => GenerationClient::TYPE_DOCUMENT_FACTURES, 'date_emission' => $generation->date_emission));
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
