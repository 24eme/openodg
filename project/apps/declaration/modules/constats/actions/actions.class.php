<?php

class constatsActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();

        $this->jour = $request->getParameter('jour');

        $this->organisationJournee = RendezvousClient::getInstance()->buildOrganisationNbDays(2, $this->jour);
        $this->form = new LoginForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('rendezvous_declarant', $this->getUser()->getEtablissement()->getCompte());
    }

    public function executePlanificationJour(sfWebRequest $request) {
        $this->jour = $request->getParameter('jour');
        $this->rendezvousJournee = RendezvousClient::getInstance()->buildRendezvousJournee($this->jour);
        $this->tourneesJournee = TourneeClient::getInstance()->buildTourneesJournee($this->jour);
    }

    public function executeTourneeAgentRendezvous(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        $this->tournee = $this->getRoute()->getTournee();
        $rdv0 = RendezvousClient::getInstance()->find("RENDEZVOUS-6823701610-201509081232");
        $rdv1 = RendezvousClient::getInstance()->find("RENDEZVOUS-6823701610-201509081709");
        $rdv2 = RendezvousClient::getInstance()->find("RENDEZVOUS-6701000810-201509081851");

        $this->tournee->addRendezVousAndGenerateConstat($rdv0, "15:20");
        $this->tournee->addRendezVousAndGenerateConstat($rdv1, "16:20");
        $this->tournee->addRendezVousAndGenerateConstat($rdv2, "17:20");
        $this->tournee->save();
        $this->agent = $this->tournee->getFirstAgent();
        $this->date = $this->tournee->getDate();
        $this->lock = (!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_TOURNEES);
        $this->constructProduitsList();
        $this->constructTypesBotiche();
        $this->constats = array();        
        
        $this->setLayout('layoutResponsive');
    }

    public function executeTourneeAgentJsonRendezvous(sfWebRequest $request) {
        $this->tournee = $this->getRoute()->getTournee();

        $json = array();
        $constats = array();

        foreach ($this->tournee->getRendezvous() as $idrendezvous => $rendezvous) {
            foreach ($rendezvous->constats as $constatsDocKey => $constatNode) {
                $constats[$constatsDocKey] = ConstatsClient::getInstance()->find($constatsDocKey);
            }
        }
        foreach ($this->tournee->getRendezvous() as $idrendezvous => $rendezvous) {
            $json[$idrendezvous] = array();
            $json[$idrendezvous]['rendezvous'] = $rendezvous->toJson();
            foreach ($rendezvous->constats as $constatsDocKey => $constatNodes) {
                foreach ($constatNodes as $constatNode) {
                    $json[$idrendezvous]['constats'] = $constats[$constatsDocKey]->constats->get($constatNode)->toJson();
                }
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            $this->response->setContentType('application/json');

            return $this->renderText(json_encode($json));
        }

        if (!$request->getParameter("unlock") && $this->tournee->statut != TourneeClient::STATUT_DEGUSTATIONS) {

            throw new sfException("La dégustation n'est plus éditable");
        }

        $json = json_decode($request->getContent());
        $json_return = array();

        foreach ($json as $json_degustation) {
            if (!$this->tournee->degustations->exist($json_degustation->identifiant)) {
                $json_return[$json_degustation->_id] = false;
                continue;
            }

            $degustation = $this->tournee->getDegustationObject($json_degustation->identifiant);

            /* if($degustation->_rev != $json_degustation->_rev) {
              $json_return[$degustation->_id] = false;
              continue;
              } */

            foreach ($json_degustation->prelevements as $json_prelevement) {
                $prelevement = $degustation->getPrelevementsByAnonymatDegustation($json_prelevement->anonymat_degustation);
                if (!$prelevement) {
                    continue;
                }

                if ($prelevement->commission != $this->commission) {
                    continue;
                }

                $prelevement->notes = array();
                foreach ($json_prelevement->notes as $key_note => $json_note) {
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

    public function executeAjoutAgentTournee(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
        $this->jour = $request->getParameter('jour');
        $this->form = new TourneeAddAgentForm(array('date' => format_date($this->jour, "dd/MM/yyyy", "fr_FR")));
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $compteAgent = CompteClient::getInstance()->find('COMPTE-' . $this->form->getValue('agent'));
        $tournee = TourneeClient::getInstance()->findOrAddByDateAndAgent($this->form->getValue('date'), $compteAgent);
        $this->redirect('constats_planification_jour', array('jour' => $this->jour));
    }

    public function executePlanifications(sfWebRequest $request) {
        $this->jour = $request->getParameter('date');
        $this->couleurs = array("#91204d", "#fa6900", "#1693a5", "#e05d6f", "#7ab317", "#ffba06", "#907860");
        $this->rdvsPris = RendezvousClient::getInstance()->getRendezvousByDateAndStatut($this->jour, RendezvousClient::RENDEZVOUS_STATUT_PRIS);
        $this->tournees = TourneeClient::getInstance()->getTourneesByDate($this->jour);

        $this->heures = array();
        for ($i = 7; $i <= 20; $i++) {
            $this->heures[sprintf("%02d:00", $i)] = sprintf("%02d", $i);
        }

        $this->tourneesCouleur = array();
        $i=0;
        foreach($this->tournees as $tournee) {
                $this->tourneesCouleur[$tournee->_id] = $this->couleurs[$i];
                $i++;
        }

        $this->rdvs = array();
        foreach($this->tournees as $tournee) {
            foreach ($tournee->rendezvous as $id => $rendezvous) {
                $this->rdvs[$rendezvous->heure_reelle][$tournee->_id][$id] = $rendezvous;
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $rdvValues = $request->getParameter("rdvs", array());
        foreach ($rdvValues as $id_rdv => $values) {
            if($values['tournee'] && $values['heure']) {
                $tournee = $this->tournees[$values['tournee']];
                $tournee->addRendezVousAndGenerateConstat($id_rdv, $values['heure']);
                $tournee->save();
            }
        }

        return $this->redirect('constats_planifications', array('date' => $this->jour));
    }

    public function executeRendezvousDeclarant(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->rendezvousDeclarant = RendezvousClient::getInstance()->getRendezvousByCompte($this->compte->cvi);
        $this->formsRendezVous = array();
        $this->form = new LoginForm();

        foreach ($this->compte->getChais() as $chaiKey => $chai) {
            $rendezvous = new Rendezvous();
            $rendezvous->identifiant = $this->compte->identifiant;
            $rendezvous->idchai = $chaiKey;
            $this->formsRendezVous[$chaiKey] = new RendezvousDeclarantForm($rendezvous);
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('rendezvous_declarant', $this->getUser()->getEtablissement()->getCompte());
    }

    public function executeRendezvousModification(sfWebRequest $request) {
        $this->rendezvous = $this->getRoute()->getRendezvous();
        $this->chai = $this->rendezvous->getChai();
        $this->form = new RendezvousDeclarantForm($this->rendezvous);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return $this->getTemplate('rendezvousDeclarant');
        }
        $this->form->save();
        $this->redirect('rendezvous_declarant', $this->rendezvous->getCompte());
    }

    public function executeRendezvousCreation(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->idchai = $request->getParameter('idchai');
        $rendezvous = new Rendezvous();
        $rendezvous->idchai = $this->idchai;
        $this->form = new RendezvousDeclarantForm($rendezvous);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return $this->getTemplate('rendezvousDeclarant');
        }
        $date = $this->form->getValue('date');
        $heure = $this->form->getValue('heure');
        $commentaire = $this->form->getValue('commentaire');
        $rendezvous = RendezvousClient::getInstance()->findOrCreate($this->compte, $this->idchai, $date, $heure, $commentaire);
        $rendezvous->save();
        $this->redirect('rendezvous_declarant', $this->compte);
    }

    private function constructProduitsList() {
        $this->produits = array();
        foreach (ConstatsClient::getInstance()->getProduits() as $produit) {
            $p = new stdClass();
            $p->hash_produit = $produit->getHash();
            $p->libelle = $produit->getLibelleLong();
            $p->libelle_produit = $produit->getParent()->getLibelleComplet();
            $p->libelle_complet = $p->libelle_produit . " " . $p->libelle;
            $this->produits[] = $p;
        }
    }
    
    private function constructTypesBotiche() {        
        $this->types_botiche = array();
        foreach (ConstatsClient::$types_botiche as $type_botiche_key => $type_botiche) {
            $b = new stdClass();
            $b->type_botiche = $type_botiche_key;
            $b->nom = $type_botiche;
            $this->types_botiche[] = $b;
        }
    }

}
