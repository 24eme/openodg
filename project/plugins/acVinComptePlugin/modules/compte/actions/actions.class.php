<?php

class compteActions extends sfCredentialActions {

    public function executeAjout(sfWebRequest $request) {
        $this->societe = $this->getRoute()->getSociete();
        $this->compte = CompteClient::getInstance()->createCompteFromSociete($this->societe);
        $this->applyRights();
        if(!$this->modification && !$this->reduct_rights){

          return $this->forward('acVinCompte','forbidden');
        }
        $this->processFormCompte($request);
        $this->setTemplate('modification');
    }

    public function executeModification(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->societe = $this->compte->getSociete();
        $this->applyRights();
        if(!$this->modification && !$this->reduct_rights){

          return $this->forward('acVinCompte','forbidden');
        }
        $this->processFormCompte($request);
    }

    protected function processFormCompte(sfWebRequest $request) {
        $this->compteForm = new InterlocuteurForm($this->compte);
        if (!$request->isMethod(sfWebRequest::POST)) {
          return;
        }

        $this->compteForm->bind($request->getParameter($this->compteForm->getName()));

        if (!$this->compteForm->isValid()) {
          return;
        }

        $this->compteForm->save();
        return $this->redirect('compte_visualisation', $this->compte);
    }

    public function executeModificationCoordonnee(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->societe = $this->compte->getSociete();
        $this->compteForm = new CompteCoordonneeForm($this->compte);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->compteForm->bind($request->getParameter($this->compteForm->getName()));
            if ($this->compteForm->isValid()) {
                if($this->compte->isNew()){
                    $this->compte->setStatut(EtablissementClient::STATUT_ACTIF);
                }
                $this->compteForm->save();
                $this->redirect('compte_visualisation', $this->compte);
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->societe = $this->compte->getSociete();
        $this->applyRights();
        if(!$this->compte->lat && !$this->compte->lon){
          $this->compte->updateCoordonneesLongLat();
          $this->compte->save();
        }
        if($this->compte->isEtablissementContact()) {
            return $this->redirect('etablissement_visualisation', $this->compte->getEtablissement());
        }
        if($this->compte->isSocieteContact()) {
            return $this->redirect('societe_visualisation',array('identifiant' => $this->societe->identifiant));
        }
    }

    public function executeSwitchStatus(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $newStatus = "";
        if($this->compte->isActif()){
           $newStatus = CompteClient::STATUT_SUSPENDU;
        }
        if($this->compte->isSuspendu()){
           $newStatus = CompteClient::STATUT_ACTIF;
        }

        $this->compte->setStatut($newStatus);
        $this->compte->save();
        return $this->redirect('compte_visualisation', array('identifiant' => $this->compte->identifiant));
    }

    private function initSearch(sfWebRequest $request, $extratag = null, $excludeextratag = false) {
      $query = $request->getParameter('q', '*');
      if($query == ""){
        $query.="*";
      }
      if (! $request->getParameter('contacts_all') ) {
		      $query .= " doc.statut:ACTIF";
      }
      $this->selected_rawtags = array_unique(array_diff(explode(',', $request->getParameter('tags')), array('')));
      $this->selected_typetags = array();
      foreach ($this->selected_rawtags as $t) {
		if (preg_match('/^([^:]+):(.+)$/', $t, $m)) {
	  		if (!isset($this->selected_typetags[$m[1]])) {
	    		$this->selected_typetags[$m[1]] = array();
	  		}
	  		$this->selected_typetags[$m[1]][] = $m[2];
		}
		$query .= ' doc.tags.'.$t;
      }
      $this->real_q = $query;
      if ($extratag) {
		$query .= ($excludeextratag) ? ' -' : ' ';
		$query .= 'doc.tags.manuel:'.$extratag;
      }
      $qs = new acElasticaQueryQueryString($query);
      $q = new acElasticaQuery();
      $q->setQuery($qs);
      $this->contacts_all = $request->getParameter('contacts_all');
      $this->q = $request->getParameter('q');
      $this->args = array('q' => $this->q, 'contacts_all' => $this->contacts_all, 'tags' => implode(',', $this->selected_rawtags));
      return $q;
    }

    public function executeSearchcsv(sfWebRequest $request) {
        ini_set('memory_limit', '1G');
        $index = acElasticaManager::getType('COMPTE');
        $this->selected_rawtags = array_unique(array_diff(explode(',', $request->getParameter('tags')), array('')));
        $this->selected_typetags = array();
        foreach ($this->selected_rawtags as $t) {
              if (preg_match('/^([^:]+):(.+)$/', $t, $m)) {
          		if (!isset($this->selected_typetags[$m[1]])) {
            		$this->selected_typetags[$m[1]] = array();
          		}
          		$this->selected_typetags[$m[1]][] = $m[2];
        	}
        }
        $q = $this->initSearch($request);
        $resset = $index->search($q);
        $nbTotal = $resset->getTotalHits();
        $nbQueries = floor($nbTotal/1000) - 1;
        $this->results = array();
        for($i=0;$i < $nbQueries;$i++) {
            $q = $this->initSearch($request);
            $q->setLimit(1000);
            $q->setFrom($i*1000);
            $resset = $index->search($q);
            $this->results = array_merge($this->results, $resset->getResults());
        }
        $this->setLayout(false);
        $filename = 'export_contacts';

//      $filename.=str_replace(',', '_', $this->q).'_';
//      if(count($this->args['tags'])){
//          $filename.= str_replace(',', '_', $this->args['tags']);
//      }

        $attachement = "attachment; filename=".$filename.".csv";
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    private function addRemoveGroupe(sfWebRequest $request, $remove = false) {
      $compteAjout = $request->getParameter('compte_groupe_ajout');
      $groupe = $request->getParameter('groupeName');
      $retour = $request->getParameter('retour',null);
      $compteId = $compteAjout["id_etablissement"];
      if($request->getParameter('identifiant',null)){
        $compteId = $request->getParameter('identifiant');
      }

      $index = acElasticaManager::getType('COMPTE');
      $qs = new acElasticaQueryQueryString("* doc.tags.groupes:".Compte::transformTag($groupe)." doc.identifiant:".$compteId);
      $q = new acElasticaQuery();
      $q->setQuery($qs);
      $resset = $index->search($q);
      $nbres = $resset->getTotalHits();
      $this->setTemplate('addremovetag');

      if (!$remove && !$nbres) {
        $this->restants = 1;
        return false;
      }
      if ($remove && $nbres) {
        $this->restants = 1;
        return false;
      }
      if($retour){
        return $this->redirect('compte_visualisation', array('identifiant' => $compteId));
      }
      return true;
    }

    private function addremovetag(sfWebRequest $request, $remove = false) {
      $index = acElasticaManager::getType('COMPTE');
      $tag = Compte::transformTag($request->getParameter('tag'));
      $q = $this->initSearch($request, $tag, !$remove);

      //$q->setLimit(1000000);
      $resset = $index->search($q);

      if (!$tag) {
		throw new sfException("Un tag doit être fourni pour pouvoir être ajouté");
      }
      if (!$this->real_q) {
		throw new sfException("Il n'est pas possible d'ajouter un tag sur l'ensemble des contacts");
      }
      $cpt = 0;
      $nbimpactables =  $resset->getTotalHits();
      foreach ($resset->getResults() as $res) {
	$data = $res->getData();
	$doc = CompteClient::getInstance()->findByIdentifiant($data['doc']['identifiant'], acCouchdbClient::HYDRATE_JSON);
	if (!$doc) {
	  continue;
	}
	if (!isset($doc->tags->manuel)) {
	  $doc->tags->manuel = array();
	}else{
	  $doc->tags->manuel = json_decode(json_encode($doc->tags->manuel), true);
	}
	if ($remove && $doc->tags->manuel) {
	  $doc->tags->manuel = array_values(array_diff($doc->tags->manuel, array($tag)));
	}else{
	  $doc->tags->manuel = array_unique(array_merge($doc->tags->manuel, array($tag)));
	}
	CompteClient::getInstance()->storeDoc($doc);
	$cpt++;
	if ($cpt > 200) {
	  break;
	}
      }
      $q = $this->initSearch($request, $tag, !$remove);
      $resset = $index->search($q);

      $nbimpactes = $resset->getTotalHits();

      $this->setTemplate('addremovetag');
      if ($nbimpactes) {
	$this->restants = $nbimpactables;
	return false;
      }

      if (!$remove && $nbimpactes) {
	$this->restants = $nbimpactes;
	return false;
      }
      return true;
    }

    public function executeAddtag(sfWebRequest $request) {
      if (!$this->addremovetag($request, false)) {
		      return ;
      }
      return $this->redirect('compte_search', $this->args);
    }

    public function executeRemovetag(sfWebRequest $request) {
      if (!$this->addremovetag($request, true)) {
		      return ;
      }
      $this->args['tags'] = implode(',', array_diff($this->selected_rawtags, array('manuel:'.$request->getParameter('tag'))));
      return $this->redirect('compte_search', $this->args);
    }

      public function executeGroupe(sfWebRequest $request){
      $request->setParameter('contacts_all',true);
      $index = acElasticaManager::getType('COMPTE');
      $this->groupeName = $request->getParameter('groupeName');
      $this->filtre = "groupes:".Compte::transformTag($this->groupeName);
      $request->addRequestParameters(array('tags' => $this->filtre));
      $q = $this->initSearch($request);
      $q->setLimit(4000);
		  $elasticaFacet 	= new acElasticaFacetTerms('groupes');
		  $elasticaFacet->setField('doc.tags.groupes');
		  $elasticaFacet->setSize(250);
		  $q->addFacet($elasticaFacet);
      $resset = $index->search($q);
      $this->results = $resset->getResults();

      $this->form = new CompteGroupeAjoutForm('INTERPRO-declaration');
      if ($request->isMethod(sfWebRequest::POST)) {
          $this->form->bind($request->getParameter($this->form->getName()));
          if ($this->form->isValid()) {
              $values = $this->form->getValues();

              $etb = EtablissementClient::getInstance()->find($values['id_etablissement']);
              $compte = $etb->getMasterCompte();
              $compte->addInGroupes($this->groupeName,$values['fonction']);
              $compte->save();
              if (!$this->addRemoveGroupe($request, false)) {
                return ;
              }
              $this->redirect('compte_groupe', array('groupeName' => sfOutputEscaper::unescape($this->groupeName)));
          }
      }
    }

    public function executeRemovegroupe(sfWebRequest $request) {
      $groupeName = $request->getParameter('groupeName');
      $identifiant = $request->getParameter('identifiant');
      $compte = CompteClient::getInstance()->find("COMPTE-".$identifiant);
      $compte->removeGroupes($groupeName);
      $compte->save();
      if (!$this->addRemoveGroupe($request, true)) {
                return ;
      }
      $this->redirect('compte_groupe', array('groupeName' => sfOutputEscaper::unescape($groupeName)));
    }

    public function executeTags(sfWebRequest $request) {
      $q = new acElasticaQuery();
      $this->addTagFacetsToQuerry($q);
      $index = acElasticaManager::getType('COMPTE');
      $resset = $index->search($q);
      $this->facets = $resset->getFacets();
    }

    private function addTagFacetsToQuerry($q) {
      $facets = array('manuel' => 'doc.tags.manuel', 'export' => 'doc.tags.export', 'produit' => 'doc.tags.produit', 'statuts' => 'doc.tags.statuts', 'activite' => 'doc.tags.activite', 'groupes' => 'doc.tags.groupes', 'automatique' => 'doc.tags.automatique');
      foreach($facets as $nom => $f) {
        $elasticaFacet 	= new acElasticaFacetTerms($nom);
        $elasticaFacet->setField($f);
        $elasticaFacet->setSize(150);
        $q->addFacet($elasticaFacet);
      }
    }

    public function executeGroupes(sfWebRequest $request){
      $q = new acElasticaQuery();
      $elasticaFacet   = new acElasticaFacetTerms('groupes');
      $elasticaFacet->setField('doc.groupes.nom');
      $elasticaFacet->setSize(250);
      $q->addFacet($elasticaFacet);
      $index = acElasticaManager::getType('COMPTE');
      $resset = $index->search($q);
      $this->facets = $resset->getFacets();

      $this->form = new CompteNewGroupeForm();
      if ($request->isMethod(sfWebRequest::POST)) {
          $this->form->bind($request->getParameter($this->form->getName()));
          if ($this->form->isValid()) {
            $values = $this->form->getValues();
            $this->groupeName = $values['nom_groupe'];
            $this->redirect('compte_groupe', array('groupeName' => $this->groupeName));
          }
      }
    }


    public function executeSearch(sfWebRequest $request) {
      $res_by_page = 30;
      $page = $request->getParameter('page', 1);
      $from = $res_by_page * ($page - 1);

      $this->contacts_all = $request->getParameter('contacts_all');

      $q = $this->initSearch($request);
      $q->setLimit($res_by_page);
      $q->setFrom($from);
      $this->addTagFacetsToQuerry($q);

      $index = acElasticaManager::getType('COMPTE');
      $resset = $index->search($q);

      $this->results = $resset->getResults();
      $this->nb_results = $resset->getTotalHits();
      $this->facets = $resset->getFacets();

      ksort($this->facets);

      $this->last_page = ceil($this->nb_results / $res_by_page);
      $this->current_page = $page;
    }

    public function executeSearchadvanced(sfWebRequest $request) {
    	$this->form = new CompteRechercheAvanceeForm();

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$identifiants = explode("\n", preg_replace("/^\n/", "",  preg_replace("/\n$/", "", preg_replace("/([^0-9\n]+|\n\n)/", "", str_replace("\n", "\n", $this->form->getValue('identifiants'))))));

    	foreach($identifiants as $index => $identifiant) {
    		$identifiants[$index] = trim($identifiant);
    		if(!$identifiants[$index]) {
    			unset($identifiants[$index]);
    		}
    	}

    	return $this->redirect('compte_search', array("q" => "(doc.num_interne:" . implode(" OR doc.num_interne:", $identifiants) . ")", "contacts_all" => 1));
    }
}
