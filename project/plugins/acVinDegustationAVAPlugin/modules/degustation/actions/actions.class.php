<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->tournees = TourneeClient::getInstance()->getTourneesByType(TourneeClient::TYPE_TOURNEE_DEGUSTATION);

        $this->tournee = new Tournee();
        $this->tournee->statut = TourneeClient::STATUT_ORGANISATION;

        if($request->getParameter('date_prelevement_debut')) {
            $this->tournee->date_prelevement_debut = $request->getParameter('date_prelevement_debut');
        }
        if($request->getParameter('date')) {
            $this->tournee->date = $request->getParameter('date');
        }
        if($request->getParameter('appellation')) {
            $this->tournee->appellation = $request->getParameter('appellation');
        }

        $this->form = new TourneeCreationForm($this->tournee);

        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();

        $this->graphs = array(
            'ALSACE' => array("name" => "AOC Alsace", "color" => '120,120,220', "data" => array()),
            'CREMANT' => array("name" => "AOC Crémant Alsace", "color" => '220,178,29', "data" => array()),
            'VTSGN' => array("name" => "VT / SGN", "color" => '0,220,220', "data" => array()),
        );

        foreach($this->graphs as $key => $graph) {
            $dateObject = new DateTime($campagne."-10-01");
            for($i = 0; $i <= 12; $i++) {
                $this->graphs[$key]['data'][$dateObject->format('M Y')] = 0;
                $dateObject = $dateObject->modify("+ 1 month");
            }
            $prelevements = DRevPrelevementsView::getInstance()->getPrelevements(1, $key, $campagne."-10-01", ($campagne+1)."-10-01", $campagne);
            $prelevements = array_merge($prelevements, DRevPrelevementsView::getInstance()->getPrelevements(0, $key, $campagne."-10-01", ($campagne+1)."-10-01", $campagne));
            foreach($prelevements as $prelevement) {
                $dateObject = new DateTime($prelevement->date);
                $this->graphs[$key]['data'][$dateObject->format('M Y')] += 1;
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->doUpdateObject($this->form->getValues());

        if($this->form->getValue('action') == "saisir") {

            return $this->redirect('degustation_saisie_creation', array('date' => $this->tournee->date, 'appellation' => $this->tournee->appellation));
        }

        return $this->redirect('degustation_creation', array('date' => $this->tournee->date, 'date_prelevement_debut' => $this->tournee->date_prelevement_debut, 'appellation' => $this->tournee->appellation));
    }

    public function executeDeclarant(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->degustations = DegustationClient::getInstance()->getDegustationsByEtablissement($this->etablissement->identifiant);
    }

    public function executeSaisieCreation(sfWebRequest $request) {
        $this->tournee = TourneeClient::getInstance()->createOrFindForSaisieDegustation($request->getParameter("appellation"), $request->getParameter("date"));

        $this->form = new TourneeSaisieCreationForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_saisie',  $this->tournee);
    }

    public function executeSaisie(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeSaisieEtapes::ETAPE_SAISIE, "TourneeSaisieEtapes"))) {
            $this->tournee->save();
        }

        $this->form = new TourneeSaisieForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->updateDoc();

        return $this->redirect('degustation_saisie_degustateurs', $this->tournee);
    }

    public function executeSaisieDegustateurs(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeSaisieEtapes::ETAPE_SAISIE_DEGUSTATEURS, "TourneeSaisieEtapes"))) {
            $this->tournee->save();
        }

        $this->form = new TourneeSaisieDegustateursForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->updateDoc();

        return $this->redirect('degustation_saisie_validation', $this->tournee);
    }

    public function executeSaisieValidation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeSaisieEtapes::ETAPE_SAISIE_VALIDATION, "TourneeSaisieEtapes"))) {
            $this->tournee->save();
        }

        $this->validation = new TourneeSaisieValidation($this->tournee);

        if ($this->validation->hasErreurs() || !$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->tournee->statut = TourneeClient::STATUT_DEGUSTATIONS;
        $this->tournee->save();

        return $this->redirect('degustation_visualisation', $this->tournee);
    }

    public function executeEdit(sfWebRequest $request) {
        $degustation = $this->getRoute()->getTournee();

        if ($degustation->exist('etape') && $degustation->etape) {

            return $this->redirect('degustation_' . strtolower($degustation->etape), $degustation);
        }

        return $this->redirect('degustation_creation', $degustation);
    }

    public function executeCreation(sfWebRequest $request) {
        if($request->getParameter('id')) {
            $this->tournee = TourneeClient::getInstance()->find($request->getParameter('id'));
            $this->forward404Unless($this->tournee);
        }

        if(!$this->tournee) {
            if ($gap_fin_prelevement = $request->getParameter('gap_fin_prelevement', null)) {
                $this->tournee = TourneeClient::getInstance()->createOrFindForDegustation($request->getParameter('appellation'),  $request->getParameter('date'), $request->getParameter('date_prelevement_debut'), $gap_fin_prelevement);
            } else {
                $this->tournee = TourneeClient::getInstance()->createOrFindForDegustation($request->getParameter('appellation'),  $request->getParameter('date'), $request->getParameter('date_prelevement_debut'));
            }
        }

        $this->operateurs = TourneeClient::getInstance()->getPrelevementsFiltered($this->tournee->appellation, $this->tournee->date_prelevement_debut, $this->tournee->date_prelevement_fin, $this->tournee->getCampagne());
        $this->reportes =  TourneeClient::getInstance()->getReportes($this->tournee->appellation, $this->tournee->getCampagne());
        $this->nb_force = 0;
        foreach($this->operateurs as $op) {
          $this->nb_force += $op->force;
        }
        $this->nb_reports = count($this->reportes);

        $this->form = new TourneeCreationFinForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $nb_a_prelever = $this->form->getValue('nombre_operateurs_a_prelever') + $this->nb_reports + $this->nb_force;

        return $this->redirect('degustation_operateurs', array('sf_subject' => $this->tournee, 'nb_a_prelever' => $nb_a_prelever));
    }

    public function executeSuppression(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if(count($this->tournee->degustations) > 0) {

            throw new sfException("Suppression de la tournée impossible car il est relié à des prélevements");
        }

        $this->tournee->delete();

        $this->redirect('degustation');
    }

    public function executeOperateurs(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        if ($this->tournee->storeEtape($this->getEtape($this->tournee, TourneeEtapes::ETAPE_OPERATEURS))) {
            $this->tournee->save();
        }

        $this->tournee->updateOperateursFromPrevious();
        $this->tournee->updateOperateursFromDRev();
        $this->tournee->updateOperateursFromOthers();

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

        $this->degustateurs = TourneeClient::getInstance()->getDegustateurs($this->type, "-declaration-certification-genre-appellation_".$this->tournee->appellation);

        uasort($this->degustateurs, function ($a, $b) {

            return rand(-1, 1);
        });

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $values = $request->getParameter("degustateurs", array());

        foreach ($values as $key => $value) {
            $this->tournee->addDegustateur($this->type, $this->degustateurs[$key]->_id);
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

        $agents_to_remove = array();
        foreach($this->tournee->agents as $key => $agent) {
            if(!isset($values[$key])) {
                $agents_to_remove[$key] = true;
            }
        }

        foreach($agents_to_remove as $key => $value) {
            $this->tournee->agents->remove($key);
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
        for ($i = 7; $i <= 20; $i++) {
            $this->heures[sprintf("%02d:00", $i)] = sprintf("%02d", $i);
        }
        $this->heures[TourneeClient::HEURE_NON_REPARTI] = "";
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
            if(!str_replace("-", "", $value["tournee"]) || $value["heure"] == TourneeClient::HEURE_NON_REPARTI || !trim($value["heure"])) {
                $degustation->agent = null;
                $degustation->date_prelevement = null;
                $degustation->heure = null;
            } else {
                $degustation->agent = preg_replace("/(COMPTE-[A-Z0-9]+)-([0-9]+-[0-9]+-[0-9]+)/", '\1', $value["tournee"]);
                $degustation->date_prelevement = preg_replace("/(COMPTE-[A-Z0-9]+)-([0-9]+-[0-9]+-[0-9]+)/", '\2', $value["tournee"]);
                $degustation->heure = $value["heure"];
            }
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

        $this->form = new TourneeValidationForm($this->tournee);

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->validation = new TourneeValidation($this->tournee);
            $this->tournee->cleanOperateurs(false);

            return sfView::SUCCESS;
        }


        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            $this->validation = new TourneeValidation($this->tournee);
            $this->tournee->cleanOperateurs(false);

            return sfView::SUCCESS;
        }

        $this->form->updateObject($this->form->getValues());
        $this->tournee->validate();
        $this->tournee->save();
        $this->tournee->saveDegustations();


        Email::getInstance()->sendDegustationOperateursMails($this->tournee);
        Email::getInstance()->sendDegustationDegustateursMails($this->tournee);

        $this->getUser()->setFlash("notice", "Les emails d'invitations et d'avis de passage ont bien été envoyés");

        return $this->redirect('degustation_visualisation', $this->tournee);
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
        $this->lock = (!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_TOURNEES);
        $request->setParameter('modeMobile', true);
        if($this->tournee->appellation == 'VTSGN') {
            foreach($this->tournee->getProduits() as $produit) {
                if(!$produit->hasVtsgn()) {
                    continue;
                }
                $produit_vt = new stdClass();
                $produit_sgn = new stdClass();
                $produit_vt->hash_produit = $produit->getHash();
                $produit_vt->vtsgn = "VT";
                $produit_vt->trackby = $produit->getHash().$produit_vt->vtsgn;
                $produit_vt->libelle = $produit->getLibelleLong() . " VT";
                $produit_vt->libelle_produit = $produit->getParent()->getLibelleComplet();
                $produit_vt->libelle_complet = $produit_vt->libelle_produit." ".$produit_vt->libelle;
                $produit_sgn->hash_produit = $produit->getHash();
                $produit_sgn->vtsgn = "SGN";
                $produit_sgn->trackby = $produit->getHash().$produit_sgn->vtsgn;
                $produit_sgn->libelle = $produit->getLibelleLong() . " SGN";
                $produit_sgn->libelle_produit = $produit->getParent()->getLibelleComplet();
                $produit_sgn->libelle_complet = $produit_sgn->libelle_produit." ".$produit_sgn->libelle;

                $this->produits[] = $produit_vt;
                $this->produits[] = $produit_sgn;
            }
        } else {
            foreach($this->tournee->getProduits() as $p) {
                $produit = new stdClass();
                $produit->hash_produit = $p->getHash();
                $produit->vtsgn = null;
                $produit->trackby = $p->getHash();
                $produit->libelle = $p->getLibelleLong();
                $produit->libelle_produit = $p->getParent()->getLibelleComplet();
                $produit->libelle_complet = $produit->libelle;
                $this->produits[] = $produit;
            }
        }
    }

    public function executeTourneeJson(sfWebRequest $request) {
        $json = array();

        $this->tournee = $this->getRoute()->getTournee();
        $this->degustations = $this->tournee->getTourneeOperateurs($request->getParameter('agent'), $request->getParameter('date'));

        foreach($this->degustations as $degustation) {
            $degustationJson = $degustation->toJson();
            $degustationJson->raison_sociale = Anonymization::hideIfNeeded($degustationJson->raison_sociale);
            $degustationJson->adresse = Anonymization::hideIfNeeded($degustationJson->adresse);
            $degustationJson->telephone_mobile = Anonymization::hideIfNeeded($degustationJson->telephone_mobile);
            $degustationJson->telephone_bureau = Anonymization::hideIfNeeded($degustationJson->telephone_bureau);
            $degustationJson->telephone_prive = Anonymization::hideIfNeeded($degustationJson->telephone_prive);
            $degustationJson->email = Anonymization::hideIfNeeded($degustationJson->email);

            $json[] = $degustationJson;
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        if(!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_TOURNEES) {
            throw new sfException("La tournée n'est plus éditable");
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
                $p->composition = $prelevement->composition;
                $p->remove('fermentation_lactique');
                if(isset($prelevement->fermentation_lactique)) {
                    $p->add('fermentation_lactique', (bool) $prelevement->fermentation_lactique);
                }
                $p->volume_revendique = $prelevement->volume_revendique;
                $p->hash_produit = $prelevement->hash_produit;
                $p->anonymat_prelevement = $prelevement->anonymat_prelevement;
                $p->libelle = $prelevement->libelle;
                $p->libelle_produit = $prelevement->libelle_produit;
                $p->preleve = $prelevement->preleve;
                $p->vtsgn = null;
                if($prelevement->vtsgn) {
                    $p->vtsgn = $prelevement->vtsgn;
                }
                $p->motif_non_prelevement = null;
                if($p->hash_produit) {
                    $p->motif_non_prelevement = $prelevement->motif_non_prelevement;
                }
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
        $request->setParameter('modeMobile', true);

        if($this->tournee->statut == TourneeClient::STATUT_TOURNEES) {

            return $this->redirect('degustation_affectation_generate', $this->tournee);
        }

        $this->reload = $request->getParameter('reload', 0);
        $this->lock = (!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_AFFECTATION);
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

        if(!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_AFFECTATION) {
            throw new sfException("L'affectation n'est plus éditable");
        }

        $json = json_decode($request->getContent());
        $json_return = array();

        foreach($json as $json_degustation) {
            if(!$this->tournee->degustations->exist($json_degustation->identifiant)) {
                $json_return[$json_degustation->_id] = false;
                continue;
            }

            $degustation = $this->tournee->getDegustationObject($json_degustation->identifiant);

            if($degustation->_rev != $json_degustation->_rev) {
                $json_return[$degustation->_id] = false;
                continue;
            }

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
        $request->setParameter('modeMobile', true);

        if($this->tournee->statut == TourneeClient::STATUT_AFFECTATION && $this->tournee->isAffectationTerminee()) {
            $this->tournee->statut = TourneeClient::STATUT_DEGUSTATIONS;
            $this->tournee->save();
        }

        if(!in_array($this->tournee->statut, array(TourneeClient::STATUT_DEGUSTATIONS, TourneeClient::STATUT_COURRIERS, TourneeClient::STATUT_TERMINE))) {

            return $this->forward404("La tournée n'est pas prête à être dégusté");
        }
    }

    public function executeDegustation(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->commission = $request->getParameter('commission');

        $this->lock = (!$request->getParameter("unlock") && !in_array($this->tournee->statut, array(TourneeClient::STATUT_DEGUSTATIONS, TourneeClient::STATUT_COURRIERS)) && !$this->tournee->hasSentCourrier());
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

        if(!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_DEGUSTATIONS) {

            throw new sfException("La dégustation n'est plus éditable");
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
                $prelevement = $degustation->getPrelevementsByAnonymatDegustation($json_prelevement->anonymat_degustation, $json_prelevement->commission, $json_prelevement->hash_produit, $json_prelevement->vtsgn);
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
                    if(isset($json_note->defauts)) {
                        $note->remove('defauts', $json_note->defauts);
                        $note->add('defauts', $json_note->defauts);
                    }
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

    public function executeDegustateursPresenceExport(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();
        $this->setLayout(false);

        $attachement = sprintf("attachment; filename=degustateurs_%s_%s.csv", $this->tournee->_id, date('Ymd'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    public function executeExportCsv()
    {
        $this->tournee = $this->getRoute()->getTournee();
        $csv = sprintf("\xef\xbb\xbf");
        $csv .= ExportDegustationCSV::getHeaderCsv();
        foreach($this->tournee->getDegustationsObject() as $degustation) {
            $export = new ExportDegustationCSV($degustation, false);
            $csv .= $export->export();
        }

        $this->response->setContentType('text/csv');
        $attachement = sprintf("attachment; filename=degustations_resultats_".$this->tournee->_id.".csv");
        $this->response->setHttpHeader('Content-Disposition', $attachement );

        return $this->renderText($csv);
    }

    public function executeExportCsvManquantes()
    {
        $this->tournee = $this->getRoute()->getTournee();
        $csv = sprintf("\xef\xbb\xbf");
        $csv .= ExportDegustationsManquantes::getHeaderCsv();
        $export = new ExportDegustationsManquantes($this->tournee, false);
        $csv .= $export->export();

        $this->response->setContentType('text/csv');
        $attachement = sprintf("attachment; filename=degustations_manquantes_".$this->tournee->_id.".csv");
        $this->response->setHttpHeader('Content-Disposition', $attachement );

        return $this->renderText($csv);
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

        if($tournee->hasAllTypeCourrier()) {
            $tournee->statut = TourneeClient::STATUT_TERMINE;
            $tournee->save();
        }

        $this->getUser()->setFlash("notice", "Les courriers ont bien été envoyés par mail. Vous pouvez télécharger les courriers à envoyer par voie postale.");

        return $this->redirect('degustation_visualisation', $tournee);
    }

    public function executeCourriersPapier(sfWebRequest $request) {
        set_time_limit(180);
        $tournee = $this->getRoute()->getTournee();
        $this->files = array();
        foreach ($tournee->getPrelevementsReadyForCourrier() as $courrier) {
            foreach ($courrier->prelevements as $prelevement) {
                if($prelevement->courrier_envoye) {
                    continue;
                }
                $document = new ExportDegustationPDF($courrier->operateur, $prelevement, $this->getRequestParameter('output', 'pdf'));
                $document->setPartialFunction(array($this, 'getPartial'));
                $document->generate();
                $this->files[] = $document->getFile();
            }
        }

        if(!count($this->files)) {
            $this->getUser()->setFlash("notice", "Aucun courrier n'est à envoyer par voie postale.");

            return $this->redirect('degustation_visualisation', $tournee);
        }

        $file_cache = sfConfig::get('sf_cache_dir')."/pdf/degustation_courriers_papier_" . str_replace("-", "", $tournee->date) . ".pdf";

        shell_exec("pdftk ". implode(" ", $this->files) ." cat output ".$file_cache);

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

        if(!$this->getUser()->isAdmin() && $this->getUser()->getEtablissement() && $degustation->getCompte()->_id != $this->getUser()->getEtablissement()->getCompte()->_id) {

            return $this->forwardSecure();
        }

        $this->document = new ExportDegustationPDF($degustation, $prelevement, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executeCloturer(sfWebRequest $request) {
        $tournee = $this->getRoute()->getTournee();

        if(!$tournee->hasAllTypeCourrier()) {
            throw new sfException("Tous les types de courriers n'ont pas été défini");
        }

        $tournee->statut = TourneeClient::STATUT_TERMINE;
        $tournee->save();

        $this->getUser()->setFlash("notice", "La dégustation a été cloturée.");

        return $this->redirect('degustation_visualisation', $tournee);
    }

    protected function getEtape($doc, $etape, $class = "TourneeEtapes") {
        $etapes = $class::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

}
