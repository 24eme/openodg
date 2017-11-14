<?php

class habilitationActions extends sfActions {


  public function executeIndex(sfWebRequest $request)
  {
      $this->buildSearch($request);
      $nbResultatsParPage = 30;
      $this->nbResultats = count($this->docs);
      $this->page = $request->getParameter('page', 1);
      $this->nbPage = ceil($this->nbResultats / $nbResultatsParPage);
      $this->docs = array_slice($this->docs, ($this->page - 1) * $nbResultatsParPage, $nbResultatsParPage);

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

        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
        $this->editForm = new HabilitationEditionForm($this->habilitation);

        $this->setTemplate('habilitation');
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::VISUALISATION, $this->habilitation);

        $this->setTemplate('habilitation');
    }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $habilitation = HabilitationClient::getInstance()->createDoc($etablissement->identifiant,date('Y-m-d'));
        $habilitation->save();

        return $this->redirect('habilitation_edition', $habilitation);
    }

    public function executeAjout(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->habilitation = HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->etablissement->identifiant);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);


        if (!$request->isMethod(sfWebRequest::POST)) {

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

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

        $this->editForm->bind($request->getParameter($this->editForm->getName()));

        if (!$this->editForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('habilitation_declarant', $this->etablissement);
        }

        $this->editForm->save();

        return $this->redirect('habilitation_declarant', $this->etablissement);
    }

    public function executeExport(sfWebRequest $request) {
        $this->buildSearch($request, array(HabilitationActiviteView::KEY_IDENTIFIANT, HabilitationActiviteView::KEY_PRODUIT_LIBELLE, HabilitationActiviteView::KEY_ACTIVITE));

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

    protected function buildSearch(sfWebRequest $request, $sortKeys = array(HabilitationActiviteView::KEY_DATE, HabilitationActiviteView::KEY_IDENTIFIANT, HabilitationActiviteView::KEY_PRODUIT_LIBELLE, HabilitationActiviteView::KEY_ACTIVITE)) {
        $rows = acCouchdbManager::getClient()
                    ->group(true)
                    ->group_level(3)
                    ->getView('habilitation', 'activites')->rows;

        $this->facets = array(
            "Statut" => array(),
            "Activité" => array(),
            "Produit" => array(),
        );

        $facetToRowKey = array("Statut" => HabilitationActiviteView::KEY_STATUT, "Activité" => HabilitationActiviteView::KEY_ACTIVITE, "Produit" => HabilitationActiviteView::KEY_PRODUIT_LIBELLE);

        $this->query = $request->getParameter('query', array("Statut" => HabilitationClient::STATUT_DEMANDE_HABILITATION));
        $this->docs = array();

        if(!$this->query || !count($this->query)) {
            $this->docs = acCouchdbManager::getClient()
            ->reduce(false)
            ->getView('habilitation', 'activites')->rows;
        }

        foreach($rows as $row) {
            $addition = 0;
            foreach($this->facets as $facetNom => $items) {
                $find = true;
                if($this->query) {
                    foreach($this->query as $queryKey => $queryValue) {
                        if($queryValue != $row->key[$facetToRowKey[$queryKey]]) {
                            $find = false;
                            break;
                        }
                    }
                }
                if(!$find) {
                    continue;
                }
                $facetKey = $facetToRowKey[$facetNom];
                if(!array_key_exists($row->key[$facetKey], $this->facets[$facetNom])) {
                    $this->facets[$facetNom][$row->key[$facetKey]] = 0;
                }
                $this->facets[$facetNom][$row->key[$facetKey]] += $row->value;
                $addition += $row->value;

            }
            if($addition > 0 && $this->query && count($this->query)) {
                $keys = array($row->key[HabilitationActiviteView::KEY_STATUT], $row->key[HabilitationActiviteView::KEY_ACTIVITE], $row->key[HabilitationActiviteView::KEY_PRODUIT_LIBELLE]);
                $this->docs = array_merge($this->docs, acCouchdbManager::getClient()
                ->startkey($keys)
                ->endkey(array_merge($keys, array(array())))
                ->reduce(false)
                ->getView('habilitation', 'activites')->rows);
            }
        }

        krsort($this->facets["Statut"]);
        ksort($this->facets["Activité"]);
        ksort($this->facets["Produit"]);

        uasort($this->docs, function($a, $b) use ($sortKeys) {
            foreach($sortKeys as $sortKey) {
                if($a->key[$sortKey] < $b->key[$sortKey]) {
                    return true;
                }
            }
            return false;
        });
    }

}
