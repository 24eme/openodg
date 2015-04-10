<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->tournee = new Tournee();
        $this->form = new TourneeCreationForm($this->tournee);
        
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_creation', $this->tournee);
    }

    public function executeEdit(sfWebRequest $request) {
        $degustation = $this->getRoute()->getTournee();

        if ($degustation->exist('etape') && $degustation->etape) {

            return $this->redirect('degustation_' . strtolower($degustation->etape), $degustation);
        }

        return $this->redirect('degustation_creation', $degustation);
    }

    public function executeCreation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_CREATION))) {
            $this->tournee->save();
        }

        $this->operateurs = TourneeClient::getInstance()->getPrelevements($this->tournee->date_prelevement_debut, $this->tournee->date_prelevement_fin);


        $this->nb_reports = $this->tournee->getPrevious() ? count($this->tournee->getPrevious()->getOperateursReporte()) : 0;

        $this->form = new TourneeCreationFinForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $nb_a_prelever = $this->form->getValue('nombre_operateurs_a_prelever') + $this->nb_reports;

        return $this->redirect('degustation_operateurs', array('sf_subject' => $this->tournee, 'nb_a_prelever' => $nb_a_prelever));
    }

    public function executeOperateurs(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_OPERATEURS))) {
            $this->tournee->save();
        }

        //$this->tournee->updateOperateursFromPrevious();
        $this->tournee->updateOperateursFromDRev();

        $this->form = new TourneeOperateursForm($this->tournee);

        $this->nb_a_prelever = $request->getParameter('nb_a_prelever', 0);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->update();
        
        $this->tournee->save();
        $this->tournee->saveDegustations();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->tournee->_id, "revision" => $this->tournee->_rev))));
        }

        return $this->redirect('degustation_degustateurs', $this->tournee);
    }

    public function executeDegustateurs(sfWebRequest $request) {

        return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getTournee(), 'type' => CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES));
    }

    public function executeDegustateursType(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_DEGUSTATEURS))) {
            $this->tournee->save();
        }

        $this->types = CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR);

        $this->type = $request->getParameter('type', null);

        if (!array_key_exists($this->type, $this->types)) {

            return $this->forward404(sprintf("Le type de dégustateur \"%s\" est introuvable", $request->getParameter('type', null)));
        }

        $this->noeud = $this->tournee->degustateurs->add($this->type);

        $this->degustateurs = TourneeClient::getInstance()->getDegustateurs($this->type, "-declaration-certification-genre-appellation_ALSACE");

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("degustateurs", array());

        foreach ($values as $key => $value) {
            $d = $this->degustateurs[$key];
            $degustateur = $this->noeud->add($d->_id);
            $degustateur->nom = $d->nom_a_afficher;
            $degustateur->email = $d->email;
            $degustateur->adresse = $d->adresse;
            $degustateur->commune = $d->commune;
            $degustateur->code_postal = $d->code_postal;
        }

        $degustateurs_to_delete = array();

        foreach ($this->noeud as $degustateur) {
            if (array_key_exists($degustateur->getKey(), $values)) {
                continue;
            }

            $degustateurs_to_delete[] = $degustateur->getKey();
        }

        foreach ($degustateurs_to_delete as $degustateur_key) {
            $this->noeud->remove($degustateur_key);
        }

        $this->tournee->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->tournee->_id, "revision" => $this->tournee->_rev))));
        }

        return $this->redirect('degustation_degustateurs_type_suivant', array('sf_subject' => $this->tournee, 'type' => $this->type));
    }

    public function executeDegustateursTypePrecedent(sfWebRequest $request) {
        $prev_key = null;
        foreach (CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR) as $type_key => $type_libelle) {
            if ($type_key != $request->getParameter('type', null)) {
                $prev_key = $type_key;
                continue;
            }
            if (!$prev_key) {
                continue;
            }

            return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getTournee(), 'type' => $prev_key));
        }

        return $this->redirect('degustation_operateurs', $this->getRoute()->getTournee());
    }

    public function executeDegustateursTypeSuivant(sfWebRequest $request) {
        $find = false;
        foreach (CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR) as $type_key => $type_libelle) {
            if (!$find && $type_key != $request->getParameter('type', null)) {
                continue;
            }
            if ($type_key == $request->getParameter('type', null)) {
                $find = true;
                continue;
            }

            return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getTournee(), 'type' => $type_key));
        }

        return $this->redirect('degustation_agents', $this->getRoute()->getTournee());
    }

    public function executeAgents(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_AGENTS))) {
            $this->tournee->save();
        }

        $this->agents = TourneeClient::getInstance()->getAgents();

        $this->jours = array();
        $date = new DateTime($this->tournee->date);
        $date->modify('-7 days');

        for ($i = 1; $i <= 8; $i++) {
            $this->jours[] = $date->format('Y-m-d');
            $date->modify('+ 1 day');
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("agents", array());

        foreach ($values as $key => $value) {
            $agent = $this->tournee->agents->add($key);
            $a = $this->agents[$key];
            $agent->nom = sprintf("%s %s.", $a->prenom, substr($a->nom, 0, 1));
            $agent->email = $a->email;
            $agent->adresse = $a->adresse;
            $agent->commune = $a->commune;
            $agent->code_postal = $a->code_postal;
            $agent->lat = $a->lat;
            $agent->lon = $a->lon;
            $agent->dates = $value;
        }

        $this->tournee->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->tournee->_id, "revision" => $this->tournee->_rev))));
        }

        return $this->redirect('degustation_prelevements', $this->tournee);
    }

    public function executePrelevements(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_PRELEVEMENTS))) {
            $this->tournee->save();
        }

        $result = $this->organisation($request);
        if ($result !== true) {

            return $result;
        }

        return $this->redirect('degustation_validation', $this->tournee);
    }

    public function executeOrganisation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        $result = $this->organisation($request);

        if ($result !== true) {

            return $result;
        }

        return $this->redirect('degustation_visualisation', $this->tournee);
    }

    protected function organisation(sfWebRequest $request) {
        $this->couleurs = array("#91204d", "#fa6900", "#1693a5", "#e05d6f", "#7ab317", "#ffba06", "#907860");
        $this->heures = array();
        for ($i = 8; $i <= 18; $i++) {
            $this->heures[sprintf("%02d:00", $i)] = sprintf("%02d", $i);
        }
        $this->heures["24:00"] = "24";
        $this->operateurs = $this->tournee->getOperateursOrderByHour();
        $this->agents_couleur = array();
        $i = 0;

        foreach ($this->tournee->agents as $agent) {
            foreach($agent->dates as $date) {
                $this->agents_couleur[$agent->getKey().$date] = $this->couleurs[$i];
                $i++;
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("operateurs", array());
        $i = 0;
        foreach ($values as $key => $value) {
            $degustation = $this->tournee->getDegustationObject($key);
            if(!str_replace("-", "", $value["tournee"])) {
                $degustation->agent = null;
                $degustation->date = null;
            } else {
                $degustation->agent = preg_replace("/(COMPTE-[A-Z0-9]+)-([0-9]+-[0-9]+-[0-9]+)/", '\1', $value["tournee"]);
                $degustation->date = preg_replace("/(COMPTE-[A-Z0-9]+)-([0-9]+-[0-9]+-[0-9]+)/", '\2', $value["tournee"]);
            }
            $degustation->heure = $value["heure"];
            $degustation->position = $i++;
        }

        $this->tournee->save();
        $this->tournee->saveDegustations();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->tournee->_id, "revision" => $this->tournee->_rev))));
        }

        return true;
    }

    public function executeValidation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_VALIDATION))) {
            $this->tournee->save();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->validation = new TourneeValidation($this->tournee);
            $this->tournee->cleanOperateurs(false);
        }

        $this->form = new TourneeValidationForm($this->tournee);
        
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {            
                $this->tournee->validate();
                $this->tournee->save();
                $this->tournee->saveDegustations();


                Email::getInstance()->sendDegustationOperateursMails($this->tournee);
                Email::getInstance()->sendDegustationDegustateursMails($this->tournee);

                $this->getUser()->setFlash("notice", "Les emails d'invitations et d'avis de passage ont bien été envoyés");

                return $this->redirect('degustation_visualisation', $this->tournee);
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
    }

    public function executeTourneesGenerate(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        if($this->tournee->generatePrelevements()) {
            $this->tournee->save();
        }
        
        return $this->redirect('degustation_visualisation', $this->tournee);
    }

    public function executeTournee(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->agent = $this->tournee->agents->get($request->getParameter('agent'));
        $this->date = $request->getParameter('date');
        $this->operateurs = $this->tournee->getTourneeOperateurs($request->getParameter('agent'), $request->getParameter('date'));
        $this->reload = $request->getParameter('reload', 0);
        $this->produits = array();
        foreach($this->tournee->getProduits() as $produit) {
            $this->produits[$produit->getHash()] = $produit->getLibelleLong();
        }
        $this->setLayout('layoutResponsive');
    }

    public function executeTourneeJson(sfWebRequest $request) {
        $json = array();

        $this->tournee = $this->getRoute()->getTournee();
        $this->degustations = $this->tournee->getTourneeOperateurs($request->getParameter('agent'), $request->getParameter('date'));

        foreach($this->degustations as $degustation) {
            $json[] = $degustation->toJson();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());
        $json_return = array();

        foreach($json as $json_degustation) {
            if(!$this->tournee->degustations->exist($json_degustation->identifiant)) {
                $json_return[$json_degustation->_id] = false;
                continue;
            }

            $degustation = $this->tournee->getDegustationObject($json_degustation->identifiant);

            /*if($degustation->_rev != $json_degustation->_rev) {
                $json_return[$degustation->_id] = false;
                continue;
            }*/

            $degustation->motif_non_prelevement = ($json_degustation->motif_non_prelevement) ? $json_degustation->motif_non_prelevement : null;

            foreach($json_degustation->prelevements as $prelevement_key => $prelevement) {
                if($degustation->prelevements->exist($prelevement_key)) {
                    $p = $degustation->prelevements->get($prelevement_key);
                } else {
                    $p = $degustation->prelevements->add();
                }

                $p->cuve = $prelevement->cuve;  
                $p->hash_produit = $prelevement->hash_produit;                
                $p->anonymat_prelevement = $prelevement->anonymat_prelevement;                
                $p->libelle = $prelevement->libelle;                
                $p->preleve = $prelevement->preleve;
            }

            $degustation->save();

            $json_return[$degustation->_id] = $degustation->_rev;
        }

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode($json_return));
    }

    public function executeAffectationGenerate(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if($this->tournee->statut != TourneeClient::STATUT_TOURNEES) {

            return $this->forward404("L'affectation a déjà eu lieu");
        }

        if(!$this->tournee->isTourneeTerminee()) {

            return $this->forward404("Les tournées ne sont pas terminées");
        }

        $this->tournee->statut = TourneeClient::STATUT_AFFECTATION;
        $this->tournee->cleanPrelevements();
        $this->tournee->generateNotes();
        $this->tournee->updateNombrePrelevements();
        $this->tournee->save();
        $this->tournee->saveDegustations();

        return $this->redirect('degustation_affectation', $this->tournee);
    }

    public function executeAffectation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if($this->tournee->statut == TourneeClient::STATUT_TOURNEES) {

            return $this->redirect('degustation_affectation_generate', $this->tournee);
        }

        if($this->tournee->statut != TourneeClient::STATUT_AFFECTATION) {

            return $this->forward404("L'affectation est terminée");
        }

        $this->reload = $request->getParameter('reload', 0);

        $this->setLayout('layoutResponsive');
    }

    public function executeAffectationJson(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        $json = array();

        foreach($this->tournee->getDegustationsObject() as $degustation) {
            $json[$degustation->_id] = $degustation->toJson();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());
        $json_return = array();

        foreach($json as $json_degustation) {
            if(!$this->tournee->degustations->exist($json_degustation->identifiant)) {
                $json_return[$json_degustation->_id] = false;
                continue;
            }

            $degustation = $this->tournee->getDegustationObject($json_degustation->identifiant);

            /*if($degustation->_rev != $json_degustation->_rev) {
                $json_return[$degustation->_id] = false;
                continue;
            }*/

            foreach($json_degustation->prelevements as $json_prelevement) {
                $prelevement = $degustation->getPrelevementsByAnonymatPrelevement($json_prelevement->anonymat_prelevement);
                if(!$prelevement) {
                    continue;
                }

                $prelevement->anonymat_degustation = $json_prelevement->anonymat_degustation;
                $prelevement->commission = $json_prelevement->commission;
            }
            
            $degustation->save();

            $json_return[$degustation->_id] = $degustation->_rev;
        }

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode($json_return));
    }

    public function executeDegustations(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if($this->tournee->statut == TourneeClient::STATUT_AFFECTATION && $this->tournee->isAffectationTerminee()) {
            $this->tournee->statut = TourneeClient::STATUT_DEGUSTATIONS;
            $this->tournee->save();
        }

        if(!in_array($this->tournee->statut, array(TourneeClient::STATUT_DEGUSTATIONS, TourneeClient::STATUT_COURRIERS, TourneeClient::STATUT_TERMINE))) {

            return $this->forward404("La tournée n'est pas prête à être dégusté");
        }

        $this->setLayout('layoutResponsive');
    }

    public function executeDegustation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->commission = $request->getParameter('commission');
        $this->setLayout('layoutResponsive');
    }

    public function executeDegustationJson(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->commission = $request->getParameter('commission');

        $json = array();

        foreach($this->tournee->getDegustationsObjectByCommission($this->commission) as $degustation) {
            $json[$degustation->_id] = $degustation->toJson();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());
        $json_return = array();

        foreach($json as $json_degustation) {
            if(!$this->tournee->degustations->exist($json_degustation->identifiant)) {
                $json_return[$json_degustation->_id] = false;
                continue;
            }

            $degustation = $this->tournee->getDegustationObject($json_degustation->identifiant);

            /*if($degustation->_rev != $json_degustation->_rev) {
                $json_return[$degustation->_id] = false;
                continue;
            }*/

            foreach($json_degustation->prelevements as $json_prelevement) {
                $prelevement = $degustation->getPrelevementsByAnonymatDegustation($json_prelevement->anonymat_degustation);
                if(!$prelevement) {
                    continue;
                }

                if($prelevement->commission != $this->commission) {
                    continue;
                }

                $prelevement->notes = array();
                foreach($json_prelevement->notes as $key_note => $json_note) {
                    $note = $prelevement->notes->add($key_note);
                    $note->note = $json_note->note;
                    $note->defauts = $json_note->defauts;
                }
                $prelevement->appreciations = $json_prelevement->appreciations;
            }
            
            $degustation->save();

            $json_return[$degustation->_id] = $degustation->_rev;
        }

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode($json_return));
    }

    public function executeLeverAnonymat(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if($this->tournee->statut == TourneeClient::STATUT_DEGUSTATIONS && $this->tournee->isDegustationTerminee()) {
            $this->tournee->statut = TourneeClient::STATUT_COURRIERS;
            $this->tournee->save();
        }

        return $this->redirect('degustation_visualisation', $this->tournee);
    }

    public function executeDegustateursPresence(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        $this->form = new DegustateursPresenceForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->update();

        $this->tournee->save();

        return $this->redirect('degustation_visualisation', $this->tournee);
    }

    public function executeCourrier(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->form = new DegustationCourrierForm($this->tournee);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if ($this->form->isValid()) {
                $this->form->update();
                $this->form->getObject()->save();
                return $this->redirect('degustation_visualisation', $this->tournee);
            }
        }
    }

    public function executeGenerationCourrier(sfWebRequest $request) {
        set_time_limit(180);
        $tournee = $this->getRoute()->getTournee();         
        foreach ($tournee->getPrelevementsReadyForCourrier() as $courrier) {
            Email::getInstance()->sendDegustationNoteCourrier($courrier);   
        }

        $tournee->statut = TourneeClient::STATUT_TERMINE;
        $tournee->save();

        return $this->redirect('degustation_visualisation', $tournee);
    }

    public function executeCourriersPapier(sfWebRequest $request) {
        set_time_limit(180);
        $tournee = $this->getRoute()->getTournee();
        $this->files = array();
        foreach ($tournee->getPrelevementsReadyForCourrier() as $courrier) {
            foreach ($courrier->prelevements as $prelevement) {
                // if($prelevement->courrier_envoye) {
                //     continue;
                // }
                $document = new ExportDegustationPDF($courrier->operateur, $prelevement, $this->getRequestParameter('output', 'pdf'));
                $document->setPartialFunction(array($this, 'getPartial'));
                $document->generate();
                $this->files[] = $document->getFile();
            }
        }

        if(!count($this->files)) {
            return $this->redirect('degustation_visualisation', $tournee);
        }

        $file_cache = sfConfig::get('sf_cache_dir')."/pdf/degustation_courriers_papier_" . str_replace("-", "", $tournee->date) . ".pdf";

        exec("pdftk ". implode(" ", $this->files) ." cat output ".$file_cache);

        $this->getResponse()->setHttpHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHttpHeader('Content-disposition', 'attachment; filename="courriers_papier_' . str_replace("-", "", $tournee->date) . '.pdf"');
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText(file_get_contents($file_cache));
    }

    public function executeCourrierPrelevement(sfWebRequest $request) {
        $prelevement = $this->getRoute()->getPrelevement();
        $degustation = $this->getRoute()->getDegustation();

        $this->document = new ExportDegustationPDF($degustation, $prelevement, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    protected function getEtape($doc, $etape) {
        $etapes = TourneeEtapes::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

}
