<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->degustation = new Degustation();
        $this->form = new DegustationCreationForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_creation', $this->degustation);
    }

    public function executeEdit(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();

        if ($degustation->exist('etape') && $degustation->etape) {

            return $this->redirect('degustation_' . strtolower($degustation->etape), $degustation);
        }

        return $this->redirect('degustation_creation', $degustation);
    }

    public function executeCreation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_CREATION))) {
            $this->degustation->save();
        }

        $this->operateurs = DegustationClient::getInstance()->getPrelevements($this->degustation->date_prelevement_debut, $this->degustation->date_prelevement_fin);

        $this->form = new DegustationCreationFinForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_operateurs', array('sf_subject' => $this->degustation, 'nb_a_prelever' => $this->form->getValue('nombre_operateurs_a_prelever')));
    }

    public function executeOperateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_OPERATEURS))) {
            $this->degustation->save();
        }

        $this->degustation->updateOperateursFromPrevious();
        $this->degustation->updateOperateursFromDRev();

        $this->form = new DegustationOperateursForm($this->degustation);

        $this->nb_a_prelever = $request->getParameter('nb_a_prelever', 0);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->update();
        
        $this->degustation->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        return $this->redirect('degustation_degustateurs', $this->degustation);
    }

    public function executeDegustateurs(sfWebRequest $request) {

        return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getDegustation(), 'type' => CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES));
    }

    public function executeDegustateursType(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_DEGUSTATEURS))) {
            $this->degustation->save();
        }

        $this->types = CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR);

        $this->type = $request->getParameter('type', null);

        if (!array_key_exists($this->type, $this->types)) {

            return $this->forward404(sprintf("Le type de dégustateur \"%s\" est introuvable", $request->getParameter('type', null)));
        }

        $this->noeud = $this->degustation->degustateurs->add($this->type);

        $this->degustateurs = DegustationClient::getInstance()->getDegustateurs($this->type, "-declaration-certification-genre-appellation_ALSACE");

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

        foreach($this->noeud as $degustateur) {
            if(array_key_exists($degustateur->getKey(), $values)) {
               continue; 
            }

            $degustateurs_to_delete[] = $degustateur->getKey();
        }

        foreach($degustateurs_to_delete as $degustateur_key) {
            $this->noeud->remove($degustateur_key);
        }

        $this->degustation->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        return $this->redirect('degustation_degustateurs_type_suivant', array('sf_subject' => $this->degustation, 'type' => $this->type));
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

            return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getDegustation(), 'type' => $prev_key));
        }

        return $this->redirect('degustation_operateurs', $this->getRoute()->getDegustation());
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

            return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->getRoute()->getDegustation(), 'type' => $type_key));
        }

        return $this->redirect('degustation_agents', $this->getRoute()->getDegustation());
    }

    public function executeAgents(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_AGENTS))) {
            $this->degustation->save();
        }

        $this->agents = DegustationClient::getInstance()->getAgents();

        $this->jours = array();
        $date = new DateTime($this->degustation->date);
        $date->modify('-7 days');

        for ($i = 1; $i <= 7; $i++) {
            $this->jours[] = $date->format('Y-m-d');
            $date->modify('+ 1 day');
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("agents", array());

        foreach ($values as $key => $value) {
            $agent = $this->degustation->agents->add($key);
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

        $this->degustation->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        return $this->redirect('degustation_prelevements', $this->degustation);
    }

    public function executePrelevements(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_PRELEVEMENTS))) {
            $this->degustation->save();
        }

        $this->couleurs = array("#91204d", "#fa6900", "#1693a5", "#e05d6f", "#7ab317", "#ffba06", "#907860");
        $this->heures = array();
        for ($i = 8; $i <= 18; $i++) {
            $this->heures[sprintf("%02d:00", $i)] = sprintf("%02d", $i);
        }
        $this->heures["24:00"] = "24";
        $this->operateurs = $this->degustation->getOperateursOrderByHour();
        $this->agents_couleur = array();
        $i = 0;
        foreach ($this->degustation->agents as $agent) {
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
            $operateur = $this->degustation->operateurs->get($key);
            if(!str_replace("-", "", $value["tournee"])) {
                $operateur->agent = null;
                $operateur->date = null;
            } else {
                $operateur->agent = preg_replace("/(COMPTE-[A-Z0-9]+)-([0-9]+-[0-9]+-[0-9]+)/", '\1', $value["tournee"]);
                $operateur->date = preg_replace("/(COMPTE-[A-Z0-9]+)-([0-9]+-[0-9]+-[0-9]+)/", '\2', $value["tournee"]);
            }
            $operateur->heure = $value["heure"];
            $operateur->position = $i++;
        }

        $this->degustation->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }


        return $this->redirect('degustation_validation', $this->degustation);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_VALIDATION))) {
            $this->degustation->save();
        }
        if (!$request->isMethod(sfWebRequest::POST)) {
        $this->validation = new DegustationValidation($this->degustation);
        }
        $this->form = new DegustationValidationForm($this->degustation);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {                
                $this->form->save();

                Email::getInstance()->sendDegustationOperateursMails($this->degustation);
                Email::getInstance()->sendDegustationDegustateursMails($this->degustation);

                $this->getUser()->setFlash("notice", "Les emails d'invitations et d'avis de passage ont bien été envoyés");

                return $this->redirect('degustation_visualisation', $this->degustation);
            }
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
    }

    public function executeTourneesGenerate(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        if($this->degustation->generatePrelevements()) {
            $this->degustation->save();
        }
        
        return $this->redirect('degustation_visualisation', $this->degustation);
    }

    public function executeTournee(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->agent = $this->degustation->agents->get($request->getParameter('agent'));
        $this->date = $request->getParameter('date');
        $this->operateurs = $this->degustation->getTourneeOperateurs($request->getParameter('agent'), $request->getParameter('date'));
        $this->produits = array();
        foreach($this->degustation->getProduits() as $produit) {
            $this->produits[$produit->getHash()] = $produit->getLibelleLong();
        }
        $this->setLayout('layoutResponsive');
    }

    public function executeTourneeJson(sfWebRequest $request) {
        $json = array();

        $this->degustation = $this->getRoute()->getDegustation();
        $this->operateurs = $this->degustation->getTourneeOperateurs($request->getParameter('agent'), $request->getParameter('date'));

        foreach($this->operateurs as $key => $operateur) {
            $json[$key] = $operateur->toJson();
        }

        if(!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());

        foreach($json as $key => $operateur) {
            if(!$this->degustation->operateurs->exist($operateur->cvi)) {
                continue;
            }
            $o = $this->degustation->operateurs->get($operateur->cvi);
            foreach($operateur->prelevements as $prelevement_key => $prelevement) {
                if($o->prelevements->exist($prelevement_key)) {
                    $p = $o->prelevements->get($prelevement_key);
                } else {
                    $p = $o->prelevements->add();
                }
                $p->cuve = $prelevement->cuve;  
                $p->anonymat_prelevement = $prelevement->anonymat_prelevement;                
                $p->hash_produit = $prelevement->hash_produit;                
                $p->libelle = $prelevement->libelle;                
                $p->preleve = $prelevement->preleve;
            }
        }

        $this->degustation->save();

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode(array("success" => true)));
    }

    public function executeAffectationGenerate(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->degustation->cleanPrelevements();
        $this->degustation->generateNumeroDegustation();
        $this->degustation->save();

        return $this->redirect('degustation_visualisation', $this->degustation);
    }

    public function executeAffectation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->setLayout('layoutResponsive');
    }

    public function executeAffectationJson(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->prelevements = $this->degustation->getPrelevementsByNumeroPrelevement();
        $json = new stdClass();

        for($i=1; $i<=$this->degustation->nombre_commissions; $i++) {
            $json->commissions[]=$i;
        }

        $json->prelevements = array();

        foreach($this->prelevements as $key => $prelevement) {
            $p = $json->prelevements[] = new stdClass();
            $p->hash_produit = $prelevement->hash_produit;
            $p->libelle = $prelevement->libelle;
            $p->anonymat_degustation= $prelevement->anonymat_degustation;
            $p->anonymat_prelevement = $prelevement->anonymat_prelevement;
            $p->cuve = $prelevement->cuve;
            $p->commission = $prelevement->commission;
        }


        if(!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());

        foreach($json->prelevements as $prelevement) {
            if(!isset($this->prelevements[$prelevement->anonymat_prelevement])) {
                continue;
            }

            $p = $this->prelevements[$prelevement->anonymat_prelevement];
            $p->commission = $prelevement->commission;
        }

        $this->degustation->save();

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode(array("success" => true)));
    }

    public function executeDegustation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->commission = $request->getParameter('commission');
        $this->setLayout('layoutResponsive');
    }

    public function executeDegustationJson(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->commission = $request->getParameter('commission');

        $json = new stdClass();
        $json->commission = $this->commission;
        $json->prelevements = array();
        $json->notes = DegustationClient::$note_type_libelles;

        $prelevements = $this->degustation->getPrelevementsByNumeroDegustation($this->commission);

        foreach($prelevements as $prelevement) {
            $p = $json->prelevements[] = new stdClass();
            $p->anonymat_degustation = $prelevement->anonymat_degustation;
            $p->hash_produit = $prelevement->hash_produit;
            $p->libelle = $prelevement->libelle;
            $p->notes = $prelevement->notes->toArray(true, false);
            $p->appreciations = $prelevement->appreciations;
        }

        if(!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        $json = json_decode($request->getContent());

        foreach($json->prelevements as $p) {
            $prelevement = $prelevements[$p->anonymat_degustation];
            $prelevement->notes = array();
            foreach($p->notes as $key_note => $note) {
                $n = $prelevement->notes->add($key_note);
                $n->note = $note->note;
                $n->defauts = $note->defauts;
            }
            $prelevement->appreciations = $p->appreciations;
        }

        $this->degustation->save();

        $this->response->setContentType('application/json');

        return $this->renderText(json_encode(array("success" => true)));
    }

    protected function getEtape($doc, $etape) {
        $etapes = DegustationEtapes::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

}
