<?php

class compteActions extends sfActions {

    public function executeChoiceCreationAdmin(sfWebRequest $request) {

        $this->form = new CompteChoiceCreationForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $type_compte = $this->form->getValue("type_compte");
                $this->redirect('compte_creation_admin', array("type_compte" => $type_compte));
            }
        }
    }

    public function executeCreationAdmin(sfWebRequest $request) {
        $this->type_compte = $request->getParameter('type_compte');
        if (!$this->type_compte) {
            throw sfException("La création de compte doit avoir un type");
        }
        $this->compte = new Compte($this->type_compte);
        $this->form = $this->getCompteModificationForm();

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('compte_visualisation_admin', array('id' => $this->compte->identifiant));
            }
        }
    }

    public function executeVisualisationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
    }

    public function executeModificationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();

        $this->form = $this->getCompteModificationForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('compte_visualisation_admin', array('id' => $this->compte->identifiant));
            }
        }
    }

    public function executeModificationEtablissementAdmin(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->compte = $this->etablissement->getCompte();
        if (!$this->compte) {
            throw new sfException("L'etablissement " . $this->etablissement->identifiant . " n'a pas de compte");
        }
        $this->redirect('compte_modification_admin', array('id' => $this->compte->identifiant));
    }

    public function executeCreation(sfWebRequest $request) {
        
    }

    public function executeCreationConfirmation(sfWebRequest $request) {
        
    }

    public function executeMotDePasseOublie(sfWebRequest $request) {
        
    }

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
        $url_compte_civa = sfConfig::get('app_url_compte_mot_de_passe');
        return $this->redirect($url_compte_civa);
    }

    public function executeAllTagsManuels() {

        $qm = new acElasticaQueryMatchAll();
        $q = new acElasticaQuery();
        $q->setQuery($qm);
        $elasticaFacet = new acElasticaFacetTerms('manuels');
        $elasticaFacet->setField('tags.manuels');
        $elasticaFacet->setSize(200);
        $elasticaFacet->setOrder('count');
        $q->addFacet($elasticaFacet);
        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $this->facets = $resset->getFacets();

        $results = array();

        foreach ($this->facets['manuels']['terms'] as $terms) {
            $result = new stdClass();
            $result->id = $terms['term'];
            $result->text = $terms['term'];
            $results[] = $result;
        }
        return $this->renderText(json_encode($results));
    }

    public function executeRecherche(sfWebRequest $request) {
        $this->form = new CompteRechercheForm();
        $q = $this->initSearch($request);
        $res_by_page = 20;
        $page = $request->getParameter('page', 1);
        $from = $res_by_page * ($page - 1);
        $q->setLimit($res_by_page);
        $q->setFrom($from);
        $facets = array('attributs' => 'tags.attributs', 'produits' => 'tags.produits', 'manuels' => 'tags.manuels');
        foreach ($facets as $nom => $f) {
            $elasticaFacet = new acElasticaFacetTerms($nom);
            $elasticaFacet->setField($f);
            $elasticaFacet->setSize(200);
            $elasticaFacet->setOrder('count');
            $q->addFacet($elasticaFacet);
        }

        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $this->results = $resset->getResults();
        $this->nb_results = $resset->getTotalHits();
        $this->facets = $resset->getFacets();

        $this->last_page = ceil($this->nb_results / $res_by_page);
        $this->current_page = $page;
    }

    private function initSearch(sfWebRequest $request) {
        $this->q = $query = $request->getParameter('q', '*');
        if (!$this->q) {
            $this->q = $query = '*';
        }
        $this->tags = $request->getParameter('tags', array());
        $this->all = $request->getParameter('all', 0);
        if (!$this->all) {
            //$query .= " statut:ACTIF";
        }
        foreach ($this->tags as $tag) {
            $explodeTag = explode(':', $tag);
            $query .= ' tags.' . $explodeTag[0] . ':"' . html_entity_decode($explodeTag[1], ENT_QUOTES) . '"';
        }
        $qs = new acElasticaQueryQueryString($query);
        $q = new acElasticaQuery();
        $q->setQuery($qs);
        $this->args = array('q' => $this->q, 'all' => $this->all, 'tags' => $this->tags);
        return $q;
    }

    private function getCompteModificationForm() {
        switch ($this->compte->getTypeCompte()) {
            case CompteClient::TYPE_COMPTE_CONTACT:
                return new CompteContactModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_ETABLISSEMENT:
                return new CompteEtablissementModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_DEGUSTATEUR:
                return new CompteDegustateurModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_AGENT_PRELEVEMENT:
                return new CompteAgentPrelevementModificationForm($this->compte);
        }
    }

}
