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
      $etablissement = $this->getRoute()->getEtablissement();
      $habilitationsHistory = HabilitationClient::getInstance()->getHistory($etablissement->identifiant);
      if (!count($habilitationsHistory)) {
        return $this->redirect('habilitation_create', array('sf_subject' => $etablissement));
      }
      foreach ($habilitationsHistory as $h) {
      }
      return $this->redirect('habilitation_edition', array('id' => $h->_id));
  }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $habilitation = HabilitationClient::getInstance()->createDoc($etablissement->identifiant,date('Y-m-d'));
        $habilitation->save();

        return $this->redirect('habilitation_edition', $habilitation);
    }

    public function executeAjoutProduit(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->habilitation = HabilitationClient::getInstance()->createOrGetDocFromHistory($this->habilitation);

        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);
        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
        $newHabilitationDoc = $this->habilitation->isNew();
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));
        if($newHabilitationDoc){
          $this->ajoutForm->getObject()->_rev = null;
        }

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            return $this->redirect('habilitation_edition', $this->habilitation);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect($this->generateUrl('habilitation_edition', $this->habilitation).'#ouvert');
    }

    public function executeHabilitationRecapitulatif(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->isBlocked = count($this->habilitation->getProduits(true)) < 1;
    }

    public function executeEdition(sfWebRequest $request) {
        $this->habilitation = $this->getRoute()->getHabilitation();
        $this->secure(HabilitationSecurity::EDITION, $this->habilitation);

        $this->editForm = new HabilitationEditionForm($this->habilitation);
        $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);

        if ($request->isMethod(sfWebRequest::POST)) {
          $this->habilitation = HabilitationClient::getInstance()->createOrGetDocFromHistory($this->habilitation);
          $newHabilitationDoc = $this->habilitation->isNew();

          $this->editForm = new HabilitationEditionForm($this->habilitation);
          $this->ajoutForm = new HabilitationAjoutProduitForm($this->habilitation);
          $this->editForm->bind($request->getParameter($this->editForm->getName()));

            if (!$this->editForm->isValid() && $request->isXmlHttpRequest()) {

                return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->habilitation->_id, "revision" => $this->habilitation->_rev))));
            }
            if ($this->editForm->isValid()) {
                if($newHabilitationDoc){
                  $this->editForm->getObject()->_rev = null;
                }
                $this->editForm->save();
                if ($request->isXmlHttpRequest()) {

                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->habilitation->_id, "revision" => $this->habilitation->_rev))));
                }
                return $this->redirect('habilitation_edition', $this->habilitation);
            }
        }
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
