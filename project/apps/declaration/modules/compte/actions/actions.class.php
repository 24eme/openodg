<?php

class compteActions extends sfActions {

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
                $this->redirect('compte_visualisation_admin', $this->compte);
            }
        }
    }

    public function executeVisualisationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->abonnements = AbonnementClient::getInstance()->getAbonnementsByCompte($this->compte->identifiant);
    }
    
    public function executeRedirectEspaceEtablissement(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();        
        if(!($etablissement = $this->compte->getEtablissementObj())){
            throw new sfException("L'établissement du compte n'a pas été trouvé");
        }
        $this->getUser()->signIn($etablissement->identifiant);

        return $this->redirect('home');
    }

    public function executeModificationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();

        $this->form = $this->getCompteModificationForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('compte_visualisation_admin', $this->compte);
            }
        }
    }

    public function executeCreation(sfWebRequest $request) {
        
    }

    public function executeCreationConfirmation(sfWebRequest $request) {
        
    }

    public function executeMotDePasseOublie(sfWebRequest $request) {
        
    }

    public function executeArchiver(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        if($this->compte->date_archivage) {
            throw new sfException("Le compte est déjà archivé");
        }

        $this->compte->archiver();
        $this->compte->save();

        $this->redirect('compte_visualisation_admin', $this->compte);
    }

    public function executeDesarchiver(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        if(!$this->compte->date_archivage) {
            throw new sfException("Le compte n'est pas archivé");
        }

        $this->compte->desarchiver();
        $this->compte->save();

        $this->redirect('compte_visualisation_admin', $this->compte);
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
        if($request->getParameter('return_mon_compte')) {
            return $this->redirect(sprintf("%s?%s", sfConfig::get('app_url_compte_mot_de_passe'), http_build_query(array('service' => $this->generateUrl("mon_compte", array(), true)))));
        }

        return $this->redirect(sprintf("%s?%s", sfConfig::get('app_url_compte_mot_de_passe'), http_build_query(array('service' => $this->generateUrl("home", array(), true)))));
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
        $res_by_page = 15;
        $page = $request->getParameter('page', 1);
        $from = $res_by_page * ($page - 1);
        $q->setLimit($res_by_page);
        $q->setFrom($from);
        $facets = array('automatiques' => 'tags.automatiques', 'attributs' => 'tags.attributs', 'manuels' => 'tags.manuels', 'syndicats' => 'tags.syndicats', 'produits' => 'tags.produits');
        $this->facets_libelle = array('automatiques' => 'Par types', 'attributs' => 'Par attributs', 'manuels' => 'Par mots clés', 'syndicats' => 'Par syndicats', 'produits' => 'Par produits');
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

    public function executeRechercheAvancee(sfWebRequest $request) {
        $this->form = new CompteRechercheAvanceeForm();

        if (!$request->isMethod(sfWebRequest::POST)) {
            
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }

        $cvis = explode("\n", preg_replace("/^\n/", "",  preg_replace("/\n$/", "", preg_replace("/([^0-9\n]+|\n\n)/", "", str_replace("\n", "\n", $this->form->getValue('cvis'))))));

        foreach($cvis as $index => $cvi) {
            $cvis[$index] = trim($cvi);
            if(!$cvis[$index]) {
                unset($cvis[$index]);
            }
        }

        return $this->redirect('compte_recherche', array("q" => "(cvi:" . implode(" OR cvi:", $cvis) . ")", "all" => 1));
    }

    public function executeRechercheCsv(sfWebRequest $request) {
        ini_set('memory_limit', '256M');
        $this->setLayout(false);
        $q = $this->initSearch($request);
        $q->setLimit(99999);
        
        $index = acElasticaManager::getType('compte');
        $resset = $index->search($q);
        $this->results = $resset->getResults();

        $attachement = "attachment; filename=export_contacts.csv";
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }
    
    public function executeRechercheJson($request) {
        if($request->getParameter('q')) {
            $request->setParameter('q', "*".$request->getParameter('q')."* type_compte:ETABLISSEMENT");
        }

        $q = $this->initSearch($request);

        $q->setLimit(60);
        $index = acElasticaManager::getType('compte');

        $resset = $index->search($q);
        $results = $resset->getResults();

        $list = array();
        foreach ($results as $res) {
            $data = $res->getData();
            $item = new stdClass();
            $item->nom_a_afficher = $data['nom_a_afficher'];
            $item->commune = $data['commune'];
            $item->code_postal = $data['code_postal'];
            $item->cvi = $data['cvi'];
            $item->siren = $data['siren'];
            $item->siret = $data['siret'];
            $item->text = sprintf("%s (%s) à %s (%s)", $data['nom_a_afficher'], ($data['cvi']) ? $data['cvi'] : $data['siren'], $data['commune'], $data['code_postal']);
            $item->text_html = sprintf("%s <small>(%s)</small> à %s <small>(%s)</small><br /><small>%s</small>", $data['nom_a_afficher'], ($data['cvi']) ? $data['cvi'] : $data['siren'], $data['commune'], $data['code_postal'], implode(", ", $data['tags']['attributs']));
            $item->id = $data['_id'];
            $list[] = $item;
        }

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode($list));
    }

    public static function convertArgumentsToQuery($arguments) {
        $query = isset($arguments['q']) ? $arguments['q'] : array();
        if (!$query) {
            $query = '*';
        }

        $tags = isset($arguments['tags']) ? $arguments['tags'] : array();
        $all = isset($arguments['all']) ? $arguments['all'] : 0;

        if (!$all) {
            $query .= " statut:ACTIF";
        }
        foreach ($tags as $tag) {
            $explodeTag = explode(':', $tag);
            $query .= ' tags.' . $explodeTag[0] . ':"' . html_entity_decode($explodeTag[1], ENT_QUOTES) . '"';
        }

        return $query;
    }

    private function initSearch(sfWebRequest $request) {
        $this->q = $request->getParameter('q', '*');
        if (!$this->q) {
            $this->q = '*';
        }

        $this->tags = $request->getParameter('tags', array());
        $this->all = $request->getParameter('all', 0);

        $this->args = array('q' => str_replace('"', '\"', $this->q), 'all' => $this->all, 'tags' => $this->tags);

        $qs = new acElasticaQueryQueryString(self::convertArgumentsToQuery($this->args));
        $q = new acElasticaQuery();
        $q->setQuery($qs);
        
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
            case CompteClient::TYPE_COMPTE_SYNDICAT:
                return new CompteSyndicatModificationForm($this->compte);
        }
    }

}
