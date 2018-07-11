<?php

class habilitationActions extends sfActions {


  public function executeIndex(sfWebRequest $request)
  {
      $this->buildSearch($request,
                        'habilitation',
                        'demandes',
                        array("Demande" => HabilitationDemandeView::KEY_DEMANDE,
                              "Statut" => HabilitationDemandeView::KEY_STATUT,
                              "Produit" => HabilitationDemandeView::KEY_PRODUIT),
                        array("Les plus récentes" => array(HabilitationDemandeView::KEY_DATE => 1),
                              "Les plus anciennes" => array(HabilitationDemandeView::KEY_DATE_HABILITATION => -1)),
                        30
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

  public function executeSuivi(sfWebRequest $request)
  {
      $this->buildSearch($request,
                        'habilitation',
                        'activites',
                        array("Statut" => HabilitationActiviteView::KEY_STATUT,
                              "Activité" => HabilitationActiviteView::KEY_ACTIVITE,
                              "Produit" => HabilitationActiviteView::KEY_PRODUIT_LIBELLE),
                        array("Défaut" => array(HabilitationActiviteView::KEY_DATE => 1, HabilitationActiviteView::KEY_IDENTIFIANT => 1, HabilitationActiviteView::KEY_PRODUIT_LIBELLE => 1 , HabilitationActiviteView::KEY_ACTIVITE => 1)),
                        30
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

  public function executeEtablissementSelection(sfWebRequest $request) {
      $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
      $form->bind($request->getParameter($form->getName()));
      if (!$form->isValid()) {

          return $this->redirect('habilitation');
      }

      return $this->redirect('habilitation_declarant', $form->getEtablissement());
  }

    public function executeDeclarant(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        //$this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
        //$this->editForm = new HabilitationEditionForm($this->habilitation);
        $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->etablissement->identifiant), true);

        $this->setTemplate('habilitation');
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::VISUALISATION, $this->habilitation);
        $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);

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

    public function executeDemandeCreation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);

        $this->formDemandeCreation = new HabilitationDemandeCreationForm($this->habilitation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeCreation->bind($request->getParameter($this->formDemandeCreation->getName()));

        if (!$this->formDemandeCreation->isValid()) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeCreation->save();

        return $this->redirect('habilitation_declarant', $this->etablissement);
    }

    public function executeDemandeEdition(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);
        $this->historique = $this->habilitation->getFullHistorique();
        $this->demande = $this->habilitation->demandes->get($request->getParameter('demande'));
        $this->formDemandeEdition = new HabilitationDemandeEditionForm($this->demande);
        $this->urlRetour = $request->getParameter('retour', false);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeEdition->bind($request->getParameter($this->formDemandeEdition->getName()));

        if (!$this->formDemandeEdition->isValid()) {

            return $this->executeDeclarant($request);
        }

        $this->formDemandeEdition->save();

        if($this->urlRetour) {

            return $this->redirect($this->urlRetour);
        }

        return $this->redirect('habilitation_declarant', $this->etablissement);
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

    protected function buildSearch($request, $viewCat, $viewName, $facets, $sorts, $nbResultatsParPage) {
        $rows = acCouchdbManager::getClient()
                    ->group(true)
                    ->group_level(count($facets))
                    ->getView($viewCat, $viewName)->rows;

        $this->facets = array();
        foreach($facets as $libelle => $key) {
            $this->facets[$libelle] = array();
        }
        $this->sorts = $sorts;
        $this->sort = $request->getParameter('sort', key($this->sorts));
        $this->query = $request->getParameter('query', array());
        $this->docs = array();

        if(!$this->query || !count($this->query)) {
            $this->docs = acCouchdbManager::getClient()
            ->reduce(false)
            ->getView($viewCat, $viewName)->rows;
        }

        foreach($rows as $row) {
            $addition = 0;
            foreach($this->facets as $facetNom => $items) {
                $find = true;
                if($this->query) {
                    foreach($this->query as $queryKey => $queryValue) {
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

}
