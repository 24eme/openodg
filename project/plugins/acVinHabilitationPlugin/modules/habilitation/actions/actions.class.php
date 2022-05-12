<?php

class habilitationActions extends sfActions {


  public function executeIndexDemande(sfWebRequest $request)
  {
        $filtres = array();

        $this->voirtout = (bool) $request->getParameter('voirtout');
        if(!$this->voirtout) {
            $filtres["Statut"] = "/^(?!".implode("$|", HabilitationClient::getInstance()->getStatutsFerme())."$)/";
        }

        $this->buildSearch($request,
                        'habilitation',
                        'demandes',
                        array(
                              "Statut" => HabilitationDemandeView::KEY_STATUT,
                              "Demande" => HabilitationDemandeView::KEY_DEMANDE,
                              "Produit" => HabilitationDemandeView::KEY_PRODUIT),
                        array("Plus urgentes" => array(HabilitationDemandeView::KEY_NBJOURS => 1, HabilitationDemandeView::KEY_IDENTIFIANT => -1),
                              "Moins urgentes" => array(HabilitationDemandeView::KEY_NBJOURS => -1, HabilitationDemandeView::KEY_IDENTIFIANT => -1),
                              "Plus récentes" => array(HabilitationDemandeView::KEY_DATE => 1),
                              "Plus anciennes" => array(HabilitationDemandeView::KEY_DATE_HABILITATION => -1)),
                        30,
                        $filtres,
                        function(&$item, $key) {
                            $dateHabilitation = new DateTime($item->key[HabilitationDemandeView::KEY_DATE_HABILITATION]);
                            $item->key[HabilitationDemandeView::KEY_NBJOURS] = $dateHabilitation->diff(new DateTime())->days;
                        }
                        );

      $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);

      if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
      }

      $this->form->bind($request->getParameter($this->form->getName()));

      if(!$this->form->isValid()) {

          return sfView::SUCCESS;
      }

      return $this->redirect('habilitation_declarant', $this->form->getValue('etablissement'));
  }


    public function executeExportHistorique(sfWebRequest $request) {
        $export = new ExportHabilitationDemandesPublipostageCSV($request->getParameter('dateFrom'), $request->getParameter('dateTo'));

        $this->setLayout(false);

        $attachement = sprintf("attachment; filename=export_demandes_%s_%s.csv", $request->getParameter('dateFrom'), $request->getParameter('dateTo'));
        $this->response->setContent(utf8_decode($export->export()));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition', $attachement);

        return sfView::NONE;
    }

  public function executeIndexHabilitation(sfWebRequest $request)
  {
      if(HabilitationConfiguration::getInstance()->isSuiviParDemande()) {

          return $this->redirect('habilitation_demande');
      }

      $this->buildSearch($request,
                        'habilitation',
                        'activites',
                        array("Statut" => HabilitationActiviteView::KEY_STATUT,
                              "Activité" => HabilitationActiviteView::KEY_ACTIVITE,
                              "Produit" => HabilitationActiviteView::KEY_PRODUIT_LIBELLE),
                        array("Défaut" => array(HabilitationActiviteView::KEY_DATE => 1, HabilitationActiviteView::KEY_IDENTIFIANT => 1, HabilitationActiviteView::KEY_PRODUIT_LIBELLE => 1 , HabilitationActiviteView::KEY_ACTIVITE => 1)),
                        30,
                        array(),
                        null,
                        false,
                        array("Statut" => HabilitationClient::STATUT_DEMANDE_HABILITATION)
                        );

      if(class_exists("EtablissementChoiceForm")) {
          $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
      }elseif(class_exists("LoginForm")) {
        $this->form = new LoginForm();
      }

      if(!isset($this->form)) {
          return sfView::SUCCESS;
      }

      if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
      }

      $this->form->bind($request->getParameter($this->form->getName()));

      if(!$this->form->isValid()) {

          return sfView::SUCCESS;
      }
      return $this->redirect('habilitation_declarant', $this->form->getValue('etablissement'));
  }

  public function executeEtablissementSelection(sfWebRequest $request) {
      $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
      $form->bind($request->getParameter($form->getName()));
      if (!$form->isValid()) {

          return (HabilitationConfiguration::getInstance()->isSuiviParDemande()) ? $this->redirect('habilitation_demande') : $this->redirect('habilitation');
      }

      return $this->redirect('habilitation_declarant', $form->getEtablissement());
  }

    public function executeDeclarant(sfWebRequest $request) {
        if(class_exists("SocieteConfiguration") && !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration() && !$this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {

            throw new sfError403Exception();
        }

        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitations = HabilitationClient::getInstance()->getLastHabilitationsOrCreate($this->etablissement->identifiant);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        if($this->getUser()->isAdmin()) {
            $this->filtre = $request->getParameter('filtre');
        } elseif($this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {
            $this->filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        if($this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN) && !HabilitationConfiguration::getInstance()->isSuiviParDemande()) {
          $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitations[0]);
        }

        if($this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
            $this->editForm = new HabilitationEditionForm($this->habilitations[0]);
        }

        if($this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION) && class_exists("EtablissementChoiceForm")) {
            $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->etablissement->identifiant), true);
        }

        $this->setTemplate('habilitation');
    }

    public function executeVisualisation(sfWebRequest $request) {
        if(class_exists("SocieteConfiguration") && !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration() && !$this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {

            throw new sfError403Exception();
        }

        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::VISUALISATION, $this->habilitation);
        if(class_exists("EtablissementChoiceForm") && $this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION)) {
            $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        }

        if($this->getUser()->isAdmin()) {
            $this->filtre = $request->getParameter('filtre');
        } elseif($this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {
            $this->filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        $this->setTemplate('habilitation');
    }

    public function executeAjout(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);


        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $values = $request->getParameter($this->ajoutForm->getName());

        if(!$this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION) && !preg_match('/^DEMANDE_/', $values['statut'])) {
            $this->getUser()->setFlash("erreur", "Vous n'êtes pas autorisé à ajouter une habilitation avec le statut : ".$values['statut']);

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $this->ajoutForm->bind($values);

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect($this->generateUrl('habilitation_declarant', $this->etablissement));
    }

    public function executeEdition(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->editForm = new HabilitationEditionForm($this->habilitation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $values = $request->getParameter($this->editForm->getName());

        if(!$this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION)) {
            foreach($values as $key => $value) {
                if(preg_match('/^statut_/', $key) && !preg_match('/^(DEMANDE_|ANNULÉ)/', $value)) {
                    $this->getUser()->setFlash("erreur", "Vous n'êtes pas autorisé à modifier une habilitation avec le statut : ".$value);

                    return $this->redirect('habilitation_declarant', $this->etablissement);
                }
            }
        }

        $this->editForm->bind($values);

        if (!$this->editForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $this->editForm->save();

        return $this->redirect('habilitation_declarant', $this->etablissement);
    }

    public function executeDemandeGlobale(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);

        if($this->getUser()->isAdmin()) {
            $this->filtre = $request->getParameter('filtre');
        } else {
            $this->filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        if(!count(HabilitationClient::getInstance()->getDemandes($this->filtre))) {

            throw new sfError403Exception();
        }

        $this->formDemandeGlobale = new HabilitationDemandeGlobaleForm($this->habilitation, array(), array('filtre' => $this->filtre));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeGlobale->bind($request->getParameter($this->formDemandeGlobale->getName()));

        if (!$this->formDemandeGlobale->isValid()) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeGlobale->save();

        return $this->redirect('habilitation_declarant', $this->etablissement);
    }

    public function executeDemandeCreation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = $this->getRoute()->getLastHabilitationOrCreate();

        if (!$this->habilitation->_rev) {
            $this->habilitation->save();
            $this->habilitation = HabilitationClient::getInstance()->find($this->habilitation->_id);
        }

        if($this->getUser()->isAdmin()) {
            $this->filtre = $request->getParameter('filtre');
        } else {
            $this->filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        if(!count(HabilitationClient::getInstance()->getDemandes($this->filtre))) {

            throw new sfError403Exception();
        }

        $this->formDemandeCreation = new HabilitationDemandeCreationForm($this->habilitation, array(), array('filtre' => $this->filtre, 'controle_habilitation' => true));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeCreation->bind($request->getParameter($this->formDemandeCreation->getName()));

        if (!$this->formDemandeCreation->isValid()) {

            return $this->executeDeclarant($request);
        }

        try {
            $this->formDemandeCreation->save();
        } catch (Exception $e) {
            $this->getUser()->setFlash('erreur', $e->getMessage());

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        return $this->redirect('habilitation_declarant', $this->etablissement);
    }

    public function executeDemandeEdition(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);
        $this->historique = $this->habilitation->getFullHistorique();
        $this->demande = $this->habilitation->demandes->get($request->getParameter('demande'));

        $this->urlRetour = $request->getParameter('retour', false);
        if($this->getUser()->isAdmin()) {
            $this->filtre = $request->getParameter('filtre');
        } else {
            $this->filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        if(!$this->demande->isOuvert()) {
            $this->formDemandeEdition = false;

            return $this->executeDeclarant($request);
        }

        if($this->filtre && !preg_match("/".$this->filtre."/i", $this->demande->getStatut())) {
            $this->formDemandeEdition = false;

            return $this->executeDeclarant($request);
        }

        $this->formDemandeEdition = new HabilitationDemandeEditionForm($this->demande, array(), array('filtre' => $this->filtre));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeEdition->bind($request->getParameter($this->formDemandeEdition->getName()));

        if (!$this->formDemandeEdition->isValid()) {

            return $this->executeDeclarant($request);
        }

        try {
            $this->formDemandeEdition->save();
        } catch (Exception $e) {
            $this->getUser()->setFlash('erreur', $e->getMessage());

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        if($this->urlRetour) {

            return $this->redirect($this->urlRetour);
        }

        return $this->redirect('habilitation_declarant', $this->etablissement);
    }

    public function executeDemandeVisualisation(sfWebRequest $request) {
        if(!SocieteConfiguration::getInstance()->isVisualisationTeledeclaration() && !$this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {

            throw new sfError403Exception();
        }

        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);
        $this->historique = $this->habilitation->getFullHistorique();
        $this->demande = $this->habilitation->demandes->get($request->getParameter('demande'));
        $this->urlRetour = $request->getParameter('retour', false);

        $this->formDemandeEdition = false;

        return $this->executeDeclarant($request);
    }

    public function executeDemandeSuppressionDerniere(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);
        $this->demande = $this->habilitation->demandes->get($request->getParameter('demande'));

        if($this->demande->date != $request->getParameter('date') || $this->demande->statut != $request->getParameter('statut')) {

            throw new Exception("La date et le statut n'existe pas");
        }

        if($this->getUser()->isAdmin()) {
            $filtre = $request->getParameter('filtre');
        } else {
            $filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        if($filtre && !preg_match("/".$filtre."/i", $request->getParameter('statut'))) {

            throw new sfError403Exception();
        }

        HabilitationClient::getInstance()->deleteDemandeLastStatutAndSave($this->etablissement->identifiant, $request->getParameter('demande'));

        if(HabilitationClient::getInstance()->getDemandeHabilitationsByTypeDemandeAndStatut($this->demande->demande, $this->demande->statut)) {
            $this->getUser()->setFlash('info', "Cette suppression n'a pas fait évoluer le statut de l'habilitation, il faudra le faire manuellement si besoin.");
        }


        return $this->redirect('habilitation_demande_edition', array('identifiant' => $this->etablissement->identifiant, 'demande' => $request->getParameter('demande')));
    }

    public function executeDemandeModificationCommentaire(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($etablissement->identifiant, $request->getParameter('date'));

        if(!$request->getParameter('commentaire')) {

            throw new Exception("Le commentaire est requis");
        }

        if($this->getUser()->isAdmin()) {
            $filtre = $request->getParameter('filtre');
        } else {
            $filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }

        if($filtre && !preg_match("/".$filtre."/i", $request->getParameter('statut'))) {

            throw new sfError403Exception();
        }

        foreach($habilitation->historique as $h) {
            if($h->iddoc != $habilitation->_id.":/demandes/".$request->getParameter('demande')) {
                continue;
            }

            if($h->statut != $request->getParameter('statut')) {
                echo $statut."\n";
                continue;
            }

            if($h->date != $request->getParameter('date')) {
                echo $statut."\n";
                continue;
            }

            $h->commentaire = $request->getParameter('commentaire');
            $habilitation->save();
            break;
        }

        return $this->redirect('habilitation_demande_edition', array('identifiant' => $etablissement->identifiant, 'demande' => $request->getParameter('demande')));
    }

    public function executeExport(sfWebRequest $request) {
        set_time_limit(-1);
        ini_set('memory_limit', '2048M');
        $this->buildSearch($request,
                          'habilitation',
                          'activites',
                          array("Statut" => HabilitationActiviteView::KEY_STATUT,
                                "Activité" => HabilitationActiviteView::KEY_ACTIVITE,
                                "Produit" => HabilitationActiviteView::KEY_PRODUIT_LIBELLE),
                          array("Défaut" => array(HabilitationActiviteView::KEY_DATE => 1, HabilitationActiviteView::KEY_IDENTIFIANT => 1, HabilitationActiviteView::KEY_PRODUIT_LIBELLE => 1 , HabilitationActiviteView::KEY_ACTIVITE => 1)),
                          true
                          );

        $this->setLayout(false);
        $attachement = sprintf("attachment; filename=export_habilitations_%s.csv", date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    protected function secure($droits, $doc) {
        if (!HabilitationSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {
            return $this->forwardSecure();
        }
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {
            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
        throw new sfStopException();
    }

    protected function buildSearch($request, $viewCat, $viewName, $facets, $sorts, $nbResultatsParPage, $filtres = array(), $traitements = null, $without_group_by = false, $defaults = array()) {
        $rows = array();
        if(!$without_group_by){
            $rows = acCouchdbManager::getClient()
                ->group(true)
                ->group_level(max($facets) + 1)
                ->getView($viewCat, $viewName)->rows;
        }

        $this->facets = array();
        foreach($facets as $libelle => $key) {
            $this->facets[$libelle] = array();
        }
        $this->sorts = $sorts;
        $this->sort = $request->getParameter('sort', key($this->sorts));
        $this->query = $request->getParameter('query', $defaults);
        $this->docs = array();

        if(!$this->query || !count($this->query)) {
            $this->docs = acCouchdbManager::getClient()
            ->reduce(false)
            ->getView($viewCat, $viewName)->rows;
        }

        foreach($rows as $row) {
            $exclude = false;
            foreach($filtres as $keyFiltre => $matchFiltre) {
                if(!preg_match($matchFiltre, $row->key[$facets[$keyFiltre]])) {
                    $exclude = true;
                    break;
                }
            }
            if($exclude) {
                continue;
            }
            $addition = 0;
            foreach($this->facets as $facetNom => $items) {
                $find = true;
                if($this->query) {
                    foreach($this->query as $queryKey => $queryValue) {
                        if(!array_key_exists($queryValue, $this->facets[$queryKey])) {
                            $this->facets[$queryKey][$queryValue] = 0;
                        }
                        if($queryValue != $row->key[$facets[$queryKey]]) {
                            $find = false;
                            break;
                        }
                    }
                }
                if(!$find) {
                    continue;
                }
                $facetKey = $facets[$facetNom];
                if(!array_key_exists($row->key[$facetKey], $this->facets[$facetNom])) {
                    $this->facets[$facetNom][$row->key[$facetKey]] = 0;
                }
                $this->facets[$facetNom][$row->key[$facetKey]] += $row->value;
                $addition += $row->value;

            }
            if($addition > 0 && $this->query && count($this->query)) {
                $keys = array();
                foreach($facets as $libelle => $key) {
                    $keys[] = $row->key[$key];
                }
                $this->docs = array_merge($this->docs, acCouchdbManager::getClient()
                ->startkey($keys)
                ->endkey(array_merge($keys, array(array())))
                ->reduce(false)
                ->getView($viewCat, $viewName)->rows);
            }
        }
        foreach($this->facets as $facetNom => $items) {
            arsort($this->facets[$facetNom]);
        }

        if($traitements !== null) {
            array_walk($this->docs, $traitements);
        }

        if(count($filtres)) {
            foreach($this->docs as $key => $doc) {
                foreach($filtres as $keyFiltre => $matchFiltre) {
                    if(!preg_match($matchFiltre, $doc->key[$facets[$keyFiltre]])) {
                        unset($this->docs[$key]);
                        break;
                    }
                }
            }
        }

        $sortsKeyUsed = $this->sorts[$this->sort];

        uasort($this->docs, function($a, $b) use ($sortsKeyUsed) {
            foreach($sortsKeyUsed as $sortKey => $sens) {
                if($a->key[$sortKey] < $b->key[$sortKey]) {
                    return $sens > 0;
                }
                if($a->key[$sortKey] > $b->key[$sortKey]) {
                    return $sens < 0;
                }
            }
            return true;
        });

        if($nbResultatsParPage === true) {
            return;
        }
        $this->nbResultats = count($this->docs);
        $this->page = $request->getParameter('page', 1);
        $this->nbPage = ceil($this->nbResultats / $nbResultatsParPage);
        $this->docs = array_slice($this->docs, ($this->page - 1) * $nbResultatsParPage, $nbResultatsParPage);
    }

    public function executeCertipaqDiff(sfWebRequest $request) {
        if(class_exists("SocieteConfiguration") && !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration() && !$this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {

            throw new sfError403Exception();
        }

        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        if($this->getUser()->isAdmin()) {
            $this->filtre = $request->getParameter('filtre');
        } elseif($this->getUser()->hasCredential(AppUser::CREDENTIAL_HABILITATION)) {
            $this->filtre = $this->getUser()->getCompte()->getDroitValue('habilitation');
        }
        $this->error = '';
        try {
            $this->certipaq_operateur = CertipaqOperateur::getInstance()->findByEtablissement($this->etablissement);
            $this->pseudo_operateur = (object) CertipaqDI::getInstance()->getOperateurFromHabilitation($this->habilitation);
            if (!$this->certipaq_operateur) {
                $this->error = "Opérateur ".$this->etablissement->nom." non trouvé sur Certipaq par une recherche cvi (".$this->etablissement->cvi.") et siret (".$this->etablissement->siret.")";
            }
        }catch(sfException $e) {
            $this->error .= $e->getMessage();
        }
    }

}
