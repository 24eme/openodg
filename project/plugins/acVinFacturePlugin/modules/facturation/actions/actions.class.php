<?php

class facturationActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $this->generations = GenerationClient::getInstance()->findHistoryWithType(array(
            GenerationClient::TYPE_DOCUMENT_FACTURES,
            GenerationClient::TYPE_DOCUMENT_EXPORT_SAGE,
            GenerationClient::TYPE_DOCUMENT_EXPORT_XML_SEPA,
            GenerationClient::TYPE_DOCUMENT_EXPORT_COMPTABLE
        ), 10);

        $this->form = new LoginForm();

        if(class_exists("SocieteChoiceForm")) {
            $this->formSociete = new SocieteChoiceForm('INTERPRO-declaration', array('identifiant' => ""), true);

            $this->generation = new Generation();
            $this->generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
            $this->generation->somme = 0;
        }
        $this->formFacturationMassive = new FactureGenerationMasseForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
         $this->formSociete->bind($request->getParameter($this->formSociete->getName()));
         $this->formFacturationMassive->bind($request->getParameter($this->formFacturationMassive->getName()));
         if($this->formSociete->isValid()) {
              $soc = SocieteClient::getInstance()->find($this->formSociete->getValue('identifiant'));
              return $this->redirect('facturation_declarant', array('id' => $soc->getMasterCompte()->_id));
          }
          if($this->formFacturationMassive->isValid()) {

              $generation = $this->formFacturationMassive->save();
              $generation->arguments->add('modele', FactureClient::getInstance()->getTemplateFactureFromConfigurationAndCampagne());
              $generation->save();

              return $this->redirect('generation_view', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission));
          }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('facturation_declarant', $this->form->getValue('etablissement')->getCompte());
    }

    public function executeAttente(sfWebRequest $request)
    {
        $this->mouvements = [];
        $etablissements = [];

        $mouvements_en_attente = MouvementFactureView::getInstance()->getMouvementsFacturesEnAttente();

        foreach ($mouvements_en_attente as $m) {
            if (empty($m->key[MouvementFactureView::KEY_ETB_ID])) {
                continue;
            }

            $this->mouvements[$m->key[MouvementFactureView::KEY_ETB_ID]][] = $m;
        }


        $this->withDetails = $request->getParameter('details', false);
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

            $this->forwardCompteSecure();
            $identifiant = $this->compte->identifiant;
            if($this->compte->exist('id_societe')){
              $identifiant = $this->compte->getSociete()->identifiant;
            }

            if(!$request->getParameter('campagne')) {
                foreach(FactureClient::getInstance()->getFacturesByCompte($identifiant, acCouchdbClient::HYDRATE_JSON, null, 1) as $facture) {

                    return $this->redirect('facturation_declarant', array('id' => $this->compte->_id, 'campagne' => $facture->campagne));
                }
            }

            $this->societe = $this->compte->getSociete();
            $this->form = new FactureGenerationForm();

            if(class_exists("SocieteChoiceForm")) {
                $this->formSociete = new SocieteChoiceForm('INTERPRO-declaration', array('identifiant' => $this->compte->getSociete()->identifiant), true);
            }

            $this->identifiant = $request->getParameter('identifiant');

            $this->campagnes = [];
            $campagne_actuelle = date('Y');
            for ($i = $campagne_actuelle; $i > $campagne_actuelle - 5; $i--) {
                $this->campagnes[] = $i;
            }

            $this->campagne = $request->getParameter('campagne', null);

            if($this->campagne == "tous") {
                $this->campagne = null;
            }

            $this->factures = FactureClient::getInstance()->getFacturesByCompte($identifiant, acCouchdbClient::HYDRATE_DOCUMENT, $this->campagne);

            $this->mouvements = MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($this->societe);

            usort($this->mouvements, function ($a, $b) { return $a->value->date < $b->value->date; });

            $this->templatesFactures = TemplateFactureClient::getInstance()->findAll();

            $this->setTemplate('declarant');

            if (!$request->isMethod(sfWebRequest::POST)) {

                return sfView::SUCCESS;
            }

            $this->form->bind($request->getParameter($this->form->getName()));

            if(!$this->form->isValid()) {

                return sfView::SUCCESS;
            }

            $generation = $this->form->save();
            $generation->arguments->add('modele', FactureClient::getInstance()->getTemplateFactureFromConfigurationAndCampagne());

            $mouvementsBySoc = array($this->societe->identifiant => $this->mouvements);
            $mouvementsBySoc = FactureClient::getInstance()->filterWithParameters($mouvementsBySoc,$generation->arguments->toArray(0,1));
            if($mouvementsBySoc)
            {
                $date_facturation = Date::getIsoDateFromFrenchDate($generation->arguments->get('date_facturation'));
                $message_communication = $generation->arguments->get('message_communication');
                $generation = FactureClient::getInstance()->createFacturesBySoc($mouvementsBySoc,$date_facturation, $message_communication,$generation);
                $generation->libelle = $this->compte->nom_a_afficher;
                $generation->save();
            }

            return $this->redirect('facturation_declarant', array('id' => $this->compte->_id));

        }

    public function executeAvaEdition(sfWebRequest $request) {
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

            return $this->redirect('facturation_ava_edition', $this->facture);
        }

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant));
    }

    public function executeAvoirdefacturant(sfWebRequest $request){
      $this->baseFacture = FactureClient::getInstance()->find($request->getParameter('id'));

      if(!$this->baseFacture) {

          return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
      }
      $date = $request->getParameter('date');
      if ($date && ($date > date('Y-m-d') || count(explode('-', $date)) != 3)) {
          throw sfException('wrong date format '+$date);
      }
      $this->facture = FactureClient::getInstance()->defactureCreateAvoirAndSaveThem($this->baseFacture, $date);
      return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->baseFacture->getSociete()->getEtablissementPrincipal()->identifiant));
    }

    public function executeAvaAvoirForm(sfWebRequest $request) {
        $this->baseFacture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$this->baseFacture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $this->facture = FactureClient::getInstance()->createAvoir($this->baseFacture);

        $this->form = new FactureEditionForm($this->facture);

        $this->setTemplate('avaEdition');

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

            return $this->redirect('facturation_ava_edition', $this->facture);
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

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant, "campagne" => $this->facture->campagne));
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

        if (!UrlSecurity::verifyAuthKey($authKey, $id)) {
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

    public function executeSousGenerationFacture(sfWebRequest $request)
    {
        $generationMaitre = $request->getParameter('generation');
        $type = $request->getParameter('type');

        $generationMaitre = GenerationClient::getInstance()->find($generationMaitre);

        if (! $generationMaitre) {
            $this->redirect404();
        }

        $generation = $generationMaitre->getOrCreateSubGeneration($type);

        $generationMaitre->save();
        $generation->save();

        return $this->redirect('generation_view', [
          'type_document' => $generationMaitre->type_document,
          'date_emission' => $generationMaitre->date_emission.'-'.$generation->type_document
        ]);
    }

    public function executeTemplate(sfWebRequest $request) {
        $this->template = TemplateFactureClient::getInstance()->find($request->getParameter('id'));
    }

    public function executeRedirectTemplate(sfWebRequest $request) {
        $template = FactureClient::getInstance()->getTemplateFactureFromConfigurationAndCampagne();
        return $this->redirect('facturation_template', array('id' => $template));
    }

    private function getLatexTmpPath() {
            return "/tmp/";
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
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

    public function executeXml(sfWebRequest $request){
      $facture = FactureClient::getInstance()->find($request->getParameter('id'));
      $this->getResponse()->setContentType('text/xml');
      $this->setLayout(false);
      $this->xml = $facture->getXml();

    }
}
