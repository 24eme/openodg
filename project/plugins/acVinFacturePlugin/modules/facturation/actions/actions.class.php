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
        ), 20, $this->getCurrentRegion());

        $this->form = new LoginForm();

        if(class_exists("SocieteChoiceForm")) {
            $this->formSociete = new SocieteChoiceForm('INTERPRO-declaration', array('identifiant' => ""), true);

            $this->generation = new Generation();
            $this->generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
            $this->generation->somme = 0;
        }
        $this->formFacturationMassive = new FactureGenerationMasseForm();

        $this->campagnes = $this->getCampagnesList();
        $this->campagne = FactureClient::getInstance()->getCampagneByDate(date('Y-m-d'));
        if ($request->getParameter('campagne')) {
            $this->campagne = $request->getParameter('campagne');
        }
        $this->factures = FactureClient::getInstance()->getLastFactures($this->campagne);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        if(isset($this->formSociete)) {
            $this->formSociete->bind($request->getParameter($this->formSociete->getName()));
            if($this->formSociete->isValid()) {
                $soc = SocieteClient::getInstance()->find($this->formSociete->getValue('identifiant'));

                return $this->redirect('facturation_declarant', array('identifiant' => $soc->getMasterCompte()->_id));
            }
        }

        $this->formFacturationMassive->bind($request->getParameter($this->formFacturationMassive->getName()));

          if($this->formFacturationMassive->isValid()) {

              $generation = $this->formFacturationMassive->save();
              $generation->arguments->add('modele', TemplateFactureClient::getInstance()->getTemplateIdFromCampagne($generation->getPeriode(), $this->getCurrentRegion()));
              $generation->save();

              return $this->redirect('generation_view', ['id' => $generation->_id]);
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

        $mouvements_en_attente = MouvementFactureView::getInstance()->getMouvementsFacturesEnAttente($this->getCurrentRegion());

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

        return $this->redirect('generation_view', ['id' => $this->generation->_id]);
    }


    public function executeDeclarant(sfWebRequest $request){
            $this->compte = $this->getRoute()->getCompte();

            $this->forwardCompteSecure();
            $identifiant = $this->compte->identifiant;
            if($this->compte->exist('id_societe')){
              $identifiant = $this->compte->getSociete()->identifiant;
            }

            if(FactureConfiguration::getInstance()->isListeDernierExercice() && !$request->getParameter('campagne')) {
                foreach(FactureClient::getInstance()->getFacturesByCompte($identifiant, acCouchdbClient::HYDRATE_JSON, null, 1) as $facture) {

                    return $this->redirect('facturation_declarant', array('identifiant' => $this->compte->identifiant, 'campagne' => $facture->campagne));
                }
            }

            $this->form = new FactureGenerationForm();

            if(class_exists("Societe")) {
                $this->societe = $this->compte->getSociete();
                $this->formSociete = new SocieteChoiceForm('INTERPRO-declaration', array('identifiant' => $this->societe->identifiant), true);
            } else {
                $this->societe = $this->compte;
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

            $this->factures = FactureClient::getInstance()->getFacturesByCompte($identifiant, acCouchdbClient::HYDRATE_DOCUMENT, $this->campagne, null, sfConfig::get('app_region', null));

            $this->mouvements = MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($this->societe);
            if (class_exists('RegionConfiguration') && $this->getCurrentRegion()) {
                $this->mouvements = RegionConfiguration::getInstance()->filterMouvementsByRegion($this->mouvements, $this->getCurrentRegion());
            }

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
<<<<<<< HEAD
            $generation->arguments->add('modele', TemplateFactureClient::getInstance()->getTemplateIdFromCampagne($generation->getPeriode()));
=======
            $generation->arguments->add('modele', TemplateFactureClient::getInstance()->getTemplateIdFromCampagne($generation->getPeriode(), strtoupper(sfConfig::get('app_region', sfConfig::get('sf_app')))));
>>>>>>> master

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

            return $this->redirect('facturation_declarant', array('identifiant' => $this->compte->identifiant));

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
      return $this->redirect('facturation_declarant', array("identifiant" => $this->baseFacture->getSociete()->getEtablissementPrincipal()->identifiant));
    }

    public function executeAvaAvoirForm(sfWebRequest $request) {
        $this->baseFacture = FactureClient::getInstance()->find($request->getParameter('id'));

        if(!$this->baseFacture) {

            return $this->forward404(sprintf("La facture %s n'existe pas", $request->getParameter('id')));
        }

        $this->facture = FactureClient::getInstance()->createAvaAvoir($this->baseFacture);

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

        return $this->redirect('facturation_declarant', array("identifiant" => $this->facture->identifiant));
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

        return $this->redirect('facturation_declarant', array("identifiant" => $this->facture->identifiant));
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

        if(FactureConfiguration::getInstance()->isListeDernierExercice()) {

            return $this->redirect('facturation_declarant', array("identifiant" => $this->facture->identifiant, "campagne" => $this->facture->campagne));
        }

        return $this->redirect('facturation_declarant', array("identifiant" => $this->facture->identifiant));
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

        if(!$this->getUser()->hasFactureAdmin()) {
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

        if($generation->isNew()) {
            $generationMaitre->save();
            $generation->save();
        }

        return $this->redirect('generation_view', ['id' => $generation->_id]);
    }

    public function executeTemplate(sfWebRequest $request) {
        $this->template = TemplateFactureClient::getInstance()->find($request->getParameter('id'));

        $this->organisme = Organisme::getInstance(null, Organisme::FACTURE_TYPE);

        $this->lignes = array();

        foreach($this->template->cotisations as $cotisation) {
            foreach($cotisation->details as $detail) {
                $this->lignes[$detail->getHash()] = $detail;
            }
        }

        ksort($this->lignes);
    }

    public function executeRedirectTemplate(sfWebRequest $request) {
        $template = TemplateFactureClient::getInstance()->getTemplateIdFromCampagne(null, $this->getCurrentRegion());
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
      if(!class_exists("Societe") && !$this->getUser()->hasFactureAdmin() && $this->getUser()->getEtablissement() && $this->compte->_id != $this->getUser()->getEtablissement()->getCompte()->_id) { // Pour l'AVA

          return $this->forwardSecure();
      }

      if(!class_exists("Societe")) { // Pour l'AVA
          return;
      }

      if(!$this->getUser()->hasFactureAdmin() && $this->compte->identifiant != $this->getUser()->getCompte()->getSociete()->getEtablissementPrincipal()->identifiant && $this->compte->identifiant != $this->getUser()->getCompte()->getSociete()->identifiant){
          return $this->forwardSecure();
     }
    }

    public function executeXml(sfWebRequest $request){
      $facture = FactureClient::getInstance()->find($request->getParameter('id'));
      $this->getResponse()->setContentType('text/xml');
      $this->setLayout(false);
      $this->xml = $facture->getXml();

    }

    public function executeLibre(sfWebRequest $request) {
        $facturationsLibre = MouvementsFactureClient::getInstance()->startkey('MOUVEMENTSFACTURE-0000000000')->endkey('MOUVEMENTSFACTURE-9999999999')->execute()->getDatas();
        $region = $this->getCurrentRegion();
        $this->facturationsLibre = array_filter($facturationsLibre, function($item) use($region) { return ($item->region == $region); } );
        krsort($this->facturationsLibre);
    }

    public function executeCreationLibre(sfWebRequest $request) {
            $this->factureMouvements = MouvementsFactureClient::getInstance()->createMouvementsFacture();
            $this->factureMouvements->region = $this->getCurrentRegion();
            $this->factureMouvements->save();
            $this->redirect('facturation_libre_edition', array('id' => $this->factureMouvements->identifiant));
    }

    public function executeEditionLibre(sfWebRequest $request) {
        $this->factureMouvements = MouvementsFactureClient::getInstance()->find('MOUVEMENTSFACTURE-' . $request->getParameter('id'));

        $this->form = new FactureMouvementsEditionForm($this->factureMouvements);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if ($this->form->isValid()) {
            $this->form->save();
            $this->redirect('facturation_libre_edition', array('id' => $this->factureMouvements->identifiant));
        }
    }

    public function executeSuppressionLibre(sfWebRequest $request) {
        $this->factureMouvements = MouvementsFactureClient::getInstance()->find('MOUVEMENTSFACTURE-' . $request->getParameter('id'));
        if ($this->factureMouvements->getNbMvtsAFacture()) {
            $this->redirect('facturation_libre_edition', array('id' => $this->factureMouvements->identifiant));
        }
        $this->factureMouvements->delete();
        $this->redirect('facturation_libre');
    }

    public function executeComptabiliteLibre(sfWebRequest $request) {
        $compta = ComptabiliteClient::getInstance()->findCompta();
        $this->form = new ComptabiliteForm($compta);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                return $this->redirect('facturation_libre_comptabilite');
            }
        }
    }

    public function executeEnvoiEmail(sfWebRequest $request) {
        $facture = FactureClient::getInstance()->find($request->getParameter('id'));

        if (!$facture) {
            $this->getUser()->setFlash("error", "Facture non envoyée par email car celle-ci n'a pas pu être récupérée.");
            $this->redirect('facturation_declarant', array("identifiant" => $facture->identifiant));
        }

        if(!$facture->getSociete()->getEmailCompta()) {
            $this->getUser()->setFlash("error", "Facture non envoyée par email car il n'existe aucune adresse e-mail associée à la société ".$facture->getSociete()->raison_sociale);
            $this->redirect('facturation_declarant', array("identifiant" => $facture->identifiant));
        }

        $message = Email::getInstance()->getMessageFacture($facture);

        $sended = sfContext::getInstance()->getMailer()->send($message);

         if(!$sended) {
             $this->getUser()->setFlash("error", "Facture non envoyée par email. Une erreur s'est produite à la constitution du message.");
             $this->redirect('facturation_declarant', array("identifiant" => $facture->identifiant));
         }

         $this->getUser()->setFlash("notice", "La facture a bien été transmise à l'adresse ".$facture->getSociete()->getEmailCompta());
         $this->redirect('facturation_declarant', array("identifiant" => $facture->identifiant));
    }

    public function executeFactureHistorique(sfWebRequest $request) {
        $this->campagnes = $this->getCampagnesList($request);
        $this->campagne = reset($this->campagnes);
        if ($request->getParameter('campagne')) {
            $this->campagne = $request->getParameter('campagne');
        }
        $this->factures = FactureClient::getInstance()->getAllFactures($this->campagne);
    }

    public function getCampagnesList() {
        $listeCampagnes = acCouchdbManager::getClient()
            ->startkey(array("Facture", array()))
            ->endkey(array("Facture"))
            ->reduce(true)
            ->group_level(2)
            ->descending(true)
            ->getView('declaration', 'export')->rows;

        $campagnes = [];
        foreach ($listeCampagnes as $index => $annee) {
            $campagnes[] = $annee->key[1];
        }
        return $campagnes;
    }

    public function getCurrentRegion() {
        return (RegionConfiguration::getInstance()->hasOdgProduits()) ? Organisme::getCurrentOrganisme() : null ;
    }
}
