<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->form = new DegustationCreationForm();
        $this->formCreationTournee = new TourneeCreationForm();

        $this->lotsEnAttenteDegustation = DegustationClient::getInstance()->getLotsEnAttente(Organisme::getInstance()->getCurrentRegion());
        $this->lotsElevages = DegustationClient::getInstance()->getElevages(null, Organisme::getInstance()->getCurrentRegion());
        $this->lotsManquements = DegustationClient::getInstance()->getManquements(null, (Organisme::getInstance()->isOC()) ? null : Organisme::getInstance()->getCurrentRegion());

        $this->lastAnnee = date('Y');
        $this->degustations = DegustationClient::getInstance()->getHistory(10, "", acCouchdbClient::HYDRATE_JSON, Organisme::getCurrentRegion());

        foreach($this->degustations as $d) {
            $this->lastAnnee = explode("-", $d->date)[0];
        }

        $this->tournees = TourneeClient::getInstance()->getHistory(10, "", acCouchdbClient::HYDRATE_JSON, Organisme::getInstance()->getCurrentRegion());

        if(class_exists("EtablissementChoiceForm")) {
            $this->formEtablissement = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $degustation = $this->form->save();

        return $this->redirect('degustation_selection_lots', $degustation);
    }

    public function executeCreateTournee(sfWebRequest $request) {
        if (! $request->isMethod(sfWebRequest::POST)) {
            return $this->redirect('degustation');
        }

        $this->form = new TourneeCreationForm();
        $this->form->bind($request->getParameter($this->form->getName()));

        if (! $this->form->isValid()) {
            return $this->redirect('degustation');
        }

        $tournee = $this->form->save();

        return $this->redirect('degustation_selection_operateurs', $tournee);
    }

    public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {
            $this->redirect('degustation');
        }

        return $this->redirect('degustation_declarant_lots_liste', $form->getEtablissement());
    }

    public function executeListe(sfWebRequest $request)
    {
        $this->annee = $request->getParameter('campagne');
        $this->degustations = DegustationClient::getInstance()->getHistory(9999, $this->annee, acCouchdbClient::HYDRATE_JSON, $this->getUser()->getRegion());
    }

    public function executeAttente(sfWebRequest $request)
    {
        $this->active = $request->getParameter('active', "degustation");
        if(DegustationConfiguration::getInstance()->isTourneeAutonome()) {
            $this->lotsTournee = TourneeClient::getInstance()->getLotsEnAttente(Organisme::getInstance()->getCurrentRegion());
        }

        $this->lotsDegustation = DegustationClient::getInstance()->getLotsEnAttente(Organisme::getInstance()->getCurrentRegion());
    }

    public function executeSelectionLots(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        $this->redirectIfIsAnonymized();

        if (!DegustationConfiguration::getInstance()->isTourneeAutonome() && $this->degustation->getNbLotsPreleves()) {
            return sfView::ALERT;
        }

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_LOTS))) {
            $this->degustation->save(false);
        }

        $this->form = new DegustationSelectionLotsForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return ($next = $this->getRouteNextEtape(DegustationEtapes::ETAPE_LOTS))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }

    public function executeSelectionOperateurs(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_LOTS))) {
            $this->degustation->save(false);
        }

        $this->formLots = new DegustationSelectionLotsForm($this->degustation, ['filter_empty' => true, 'auto_select_lots' => false]);

        if (! $request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->formLots->bind($request->getParameter($this->formLots->getName()));

        if ($request->getParameter($this->formLots->getName())) {
            if(! $this->formLots->isValid()) {
                return sfView::SUCCESS;
            } else {
                $this->formLots->save();
            }
        }

        return $this->redirect(DegustationEtapes::getInstance()->getNextLink(DegustationEtapes::ETAPE_LOTS), $this->degustation);
    }

    public function executeOperateurAdd(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->etablissement_identifiant = null;
        if(isset($request->getParameter('selection_operateur')['identifiant'])) {
            $this->etablissement_identifiant = $request->getParameter('selection_operateur')['identifiant'];
        }
        $this->formOperateurs = new DegustationSelectionOperateursForm($this->degustation, $this->etablissement_identifiant);
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->formOperateurs->bind($request->getParameter($this->formOperateurs->getName()));
        if ($request->getParameter($this->formOperateurs->getName())) {
            if (! $this->formOperateurs->isValid()) {
                return sfView::SUCCESS;
            } else {
                $this->formOperateurs->save();
                return $this->redirect('degustation_selection_operateurs', $this->degustation);
            }
        }

    }

    public function executePreleve(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        $this->differer = null;

        $this->form = new DegustationPreleveLotsForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {
            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        if ($this->degustation->type == TourneeClient::TYPE_MODEL) {
            if (count($this->degustation->getLotsPreleves()) == count($this->degustation->lots) && $this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_VISUALISATION))) {
                $this->degustation->save(false);
            }

            return $this->redirect('degustation_visualisation', $this->degustation);
        }

        if (DegustationConfiguration::getInstance()->isTourneesParSecteur()) {
            return $this->redirect(DegustationEtapes::getInstance()->getNextLink(DegustationEtapes::ETAPE_PRELEVEMENTS), $this->degustation);
        }

        return $this->redirect('degustation_prelevements_etape', $this->degustation);
    }

    public function executeSupprimerLotNonPreleve(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $lot = $this->degustation->lots->get($request->getParameter('lot'));
        $this->degustation->removeLot($lot);
        $this->degustation->save();
        return $this->redirect('degustation_preleve', $this->degustation);

    }

    public function executeUpdateLotLogement(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->lot = $request->getParameter('lot');

        $this->form = new DegustationPreleveUpdateLogementForm($this->degustation, $this->lot);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if ($this->form->isValid()) {
                $this->form->save();
                return $this->redirect('degustation_preleve', $this->degustation);
            }
        }
    }

    public function executeAjoutDegustateurPresence(sfWebRequest $request){
        $this->degustation = $this->getRoute()->getDegustation();
        $this->table = $request->getParameter('table', null);

        $this->form = new DegustationAjoutDegustateurForm($this->degustation, array(), array('table' => $this->table));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if (!$this->table) {
          $this->redirect('degustation_degustateurs_confirmation', array('id' =>$this->degustation->_id));
        }

        return $this->redirect('degustation_presences', array('id' =>$this->degustation->_id, 'numero_table' => $this->table));
    }

    public function executeSelectionDegustateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        $this->colleges = DegustationConfiguration::getInstance()->getColleges();
        $first_college = array_key_first($this->colleges);

        $this->previous_college = null;
        if(!$this->college = $request->getParameter('college')) {

            return $this->redirect('degustation_selection_degustateurs', array('id' => $this->degustation->_id, 'college' => $first_college));
        }

        $colleges_keys = array_keys($this->colleges);
        $currentCollegeKey = array_search($this->college, $colleges_keys);
        $next_college = ($currentCollegeKey+1 >= count($colleges_keys))? null : $colleges_keys[$currentCollegeKey+1];
        $this->previous_college = ($currentCollegeKey-1 < 0 )? null : $colleges_keys[$currentCollegeKey-1];

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_DEGUSTATEURS))) {
            $this->degustation->save(false);
        }

        $this->form = new DegustationSelectionDegustateursForm($this->degustation,array(),array('college' => $this->college));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        if(!$next_college){
          return $this->redirect('degustation_convocations', $this->degustation);
        }

        return $this->redirect('degustation_selection_degustateurs', array('id' => $this->degustation->_id ,'college' => $next_college));
    }


    public function executeConvocations(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_CONVOCATIONS))) {
            $this->degustation->save(false);
          }
    }

    public function executeConvocationsMails(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        Email::getInstance()->sendConfirmationDegustateursMails($this->degustation);
        $this->getUser()->setFlash("notice", "Les mails de convocations ont été envoyés aux dégustateurs.");
        return $this->redirect('degustation_convocations', $this->degustation);
    }

    public function executeConvocationDegustateurMail(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $id_compte = $request->getParameter('id_compte');
        $college_key = $request->getParameter('college_key');
        Email::getInstance()->sendConfirmationDegustateurMail($this->degustation, $id_compte, $college_key);
        $this->getUser()->setFlash("notice", "Le mail de convocation a été envoyé au dégustateur.");
        return $this->redirect('degustation_degustateurs_confirmation', $this->degustation);
    }


    public function executePrelevementsEtape(sfWebRequest $request) {

        $this->degustation = $this->getRoute()->getDegustation();
        $this->active = DegustationEtapes::ETAPE_PRELEVEMENTS;
        $this->redirectIfIsAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, $this->active))) {
            $this->degustation->save(false);
        }
    }

    public function executeAjoutLotSaisie(sfWebRequest $request)
    {
        $degustation = $this->getRoute()->getDegustation();
        $lot = $degustation->lots->add();
        $lot->date = date('Y-m-d');
        $lot->id_document = $degustation->_id;
        $lot->campagne = $degustation->campagne;
        $lot->affectable = false;

        $etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-'.$request->getParameter('operateur'));

        $lot->declarant_identifiant = $etablissement->identifiant;
        $lot->declarant_nom = $etablissement->raison_sociale;
        $lot->adresse_logement = $etablissement->adresse . ' ' . $etablissement->code_postal . ' ' . $etablissement->commune;

        $degustation->save();

        $this->redirect('degustation_saisie_etape', $degustation);
    }

    /**
     * Les tournées par opérateur
     * @param sfWebRequest $request
     * @return string
     */
    public function executeOrganisationEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_ORGANISATION))) {
            $this->degustation->save(false);
        }

        $this->secteur = $request->getParameter('secteur');

        $this->afficher_tous_les_secteurs = $request->getParameter('afficher_tous_les_secteurs', false);

        $this->lots = $this->degustation->getLotsBySecteur();

        if(!$this->secteur) {
            if (!$this->degustation->hasLotsSansSecteurs()) {
                    foreach (array_keys($this->lots) as $region) {
                        if (!count($this->lots[$region])) {
                            continue;
                        }
                        $second_secteur = $region;
                        return $this->redirect('degustation_organisation_etape', array('sf_subject' => $this->degustation, 'secteur' => $second_secteur));
                    }
            }
            return $this->redirect('degustation_organisation_etape', array('sf_subject' => $this->degustation, 'secteur' => current(array_keys($this->lots))));
        }

        $this->form = new DegustationTourneesForm($this->degustation, $this->secteur);

        if (! $request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (! $this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_organisation_etape', array('sf_subject' => $this->degustation, 'secteur' => $this->secteur));
    }

    public function executeTourneesEtape(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();

        if (!DegustationConfiguration::getInstance()->isTourneesParSecteur()) {
            return $this->redirect('degustation_prelevements_etape', array('sf_subject' => $this->degustation));
        }

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_TOURNEES))) {
            $this->degustation->save(false);
        }

        $this->secteur = $request->getParameter('secteur');

        $this->afficher_tous_les_secteurs = $request->getParameter('afficher_tous_les_secteurs', false);

        $this->lots = $this->degustation->getLotsBySecteur();

        if(!$this->secteur) {
            if (!$this->degustation->hasLotsSansSecteurs()) {
                    foreach (array_keys($this->lots) as $region) {
                        if (!count($this->lots[$region])) {
                            continue;
                        }
                        $second_secteur = $region;
                        return $this->redirect('degustation_tournees_etape', array('sf_subject' => $this->degustation, 'secteur' => $second_secteur));
                    }
            }
            return $this->redirect('degustation_tournees_etape', array('sf_subject' => $this->degustation, 'secteur' => current(array_keys($this->lots))));
        }

        return sfView::SUCCESS;
    }

    public function executeSaisieEtape(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->forward403IfLotsAffectes();

        if ($this->degustation->hasLotsSansProvenance() === false) {
            return $this->redirect(DegustationEtapes::getInstance()->getNextLink(TourneeDegustationEtapes::ETAPE_SAISIE), $this->degustation);
        }

        $this->form = new TourneeLotsForm($this->degustation);

        if (! $request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (! $this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect(DegustationEtapes::getInstance()->getNextLink(TourneeDegustationEtapes::ETAPE_SAISIE), $this->degustation);
    }

    public function executeTablesEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();
        if (count($this->degustation->getLotsDegustables()) < 1) {
            return $this->redirect(DegustationEtapes::getInstance()->getPreviousLink(TourneeDegustationEtapes::ETAPE_TABLES), $this->degustation);
        }

        return $this->redirect('degustation_organisation_table', $this->degustation);
    }

    public function executeAnonymatsEtape(sfWebRequest $request) {
        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle()) {
            return $this->forward('degustation', 'anonymisationManuelle');
        }

        $this->degustation = $this->getRoute()->getDegustation();
        if ($this->degustation->getNbLotsRestantAPreleve() > 0) {
            $this->getUser()->setFlash('error', 'Il reste des lots à prélever');
            return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_PRELEVEMENTS), $this->degustation);
        }
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_ANONYMATS))) {
            $this->degustation->save(false);
          }
    }

    public function executeCommissionEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_COMMISSION))) {
            $this->degustation->save(false);
          }
    }

    public function executeResultatsEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_RESULTATS))) {
            $this->degustation->save();
          }
    }

    public function executeNotificationsEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();

        if (!DegustationConfiguration::getInstance()->hasNotification()) {
            return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_VISUALISATION), $this->degustation);
        }

        $this->mail_to_identifiant = $request->getParameter('mail_to_identifiant');

        if (!$this->degustation->areAllLotsSaisis()) {
            $this->getUser()->setFlash('error', "Il reste des lots sans résultats (conformes/non-conformes). Merci de les saisir !");
            return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_RESULTATS), $this->degustation);
        }

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_NOTIFICATIONS))) {
            $this->degustation->save();
        }
    }

    public function executeCloture(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_VISUALISATION))) {
            $this->degustation->validate();
            $this->degustation->save();
        }
        return $this->redirect('degustation_visualisation', $this->degustation);
    }

    public function executeExportCsv(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $export = new ExportDegustationCSV($this->degustation);

        $this->response->setContent(utf8_decode($export->export()));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition', "attachment; filename=".$export->getFileName());

        return sfView::NONE;

        $attachement = "attachment; filename=".$export->getFileName();
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement);

        return $this->renderText($export->export());
    }

    public function executeDegustateursConfirmation(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
      $this->form = new DegustationDegustateursConfirmationForm($this->degustation);

      if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
      }

      $this->form->bind($request->getParameter($this->form->getName()));

      if (!$this->form->isValid()) {
          return sfView::SUCCESS;
      }
      $this->form->save();

      if($this->degustation->isAnonymized()) {

        return $this->redirect('degustation_commission_etape', $this->degustation);
      }

      return $this->redirect('degustation_prelevements_etape', $this->degustation);
    }

    public function executeDegustateurAbsence(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();

      $college = $request->getParameter('college',null);
      $degustateurId = $request->getParameter('degustateurId',null);
      if(!$college || !$degustateurId){
        return $this->redirect('degustation_degustateurs_confirmation', $this->degustation);
      }

      $this->degustation->degustateurs->getOrAdd($college)->getOrAdd($degustateurId)->add('confirmation',false);

      if($this->degustation->degustateurs->get($college)->get($degustateurId)->exist('numero_table') && $this->degustation->degustateurs->get($college)->get($degustateurId)->numero_table != null) {
         throw new sfError403Exception("Vous n'êtes pas autorisé à marquer l'absence de ce dégustateur car il est déjà affecté à une table");
      }

      $this->degustation->save(false);

      return $this->redirect('degustation_degustateurs_confirmation', $this->degustation);

    }

    public function executeOrganisationTable(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->redirectIfIsAnonymized();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_TABLES))) {
            $this->degustation->save(false);
        }

        if(!$request->getParameter('numero_table')) {
            return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => 1));
        }

        $this->numero_table = $request->getParameter('numero_table');

        $this->tri = $this->degustation->tri;
        $this->tri_array = explode('|', strtolower($this->tri));

        $this->syntheseLots = $this->degustation->getSyntheseLotsTableCustomTri($this->numero_table);
        $this->form = new DegustationOrganisationTableForm($this->degustation, $this->numero_table);
        $this->ajoutLeurreForm = new DegustationAjoutLeurreForm($this->degustation, array('table' => $this->numero_table));
        $this->triTableForm = new DegustationTriTableForm($this->degustation->getTriArray(), false);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();

        if ($request->isXmlHttpRequest()) {

          return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        if(!count($this->degustation->getLotsTableOrFreeLots($this->numero_table, false)) && $this->degustation->hasFreeLots()) {

            return $this->redirect('degustation_organisation_table_recap', array('id' => $this->degustation->_id, 'tri' => $this->tri));
        }

        if($this->degustation->hasFreeLots()) {

            return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table + 1, 'tri' => $this->tri));
        }

        return $this->redirect('degustation_organisation_table_recap', array('id' => $this->degustation->_id, 'tri' => $this->tri));
    }

    public function executeChangePositionLot(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();
        $index = $request->getParameter('index');
        $tri = $degustation->tri;
        $sens = $request->getParameter('sens');
        $numero_table = $request->getParameter('numero_table');

        $this->forward404Unless($degustation->lots->exist($index));
        $lot = $degustation->lots->get($index);
        $lot->changePosition($sens);

        $degustation->save(false);
        return $this->redirect($this->generateUrl('degustation_organisation_table', array('id' => $degustation->_id, 'numero_table' => $numero_table, 'tri' => $degustation->tri))."#form-organisation-table");
    }

    public function executeOrganisationTableRecap(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();
        $this->tri = $this->degustation->tri;
        $this->triTableForm = new DegustationTriTableForm($this->degustation->getTriArray(), true);

        $this->syntheseLots = $this->degustation->getSyntheseLotsTable(null);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        return $this->redirect(DegustationEtapes::getInstance()->getNextLink(DegustationEtapes::ETAPE_TABLES), $this->degustation);
    }

    public function executeAnonymisationManuelle(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_ANONYMISATION_MANUELLE))) {
            $this->degustation->save(false);
        }

        $this->form = new DegustationAnonymisationManuelleForm($this->degustation);
        $this->tri = $this->degustation->tri;
        $this->ajoutLeurreForm = new DegustationAjoutLeurreForm($this->degustation);
        $this->triTableForm = new DegustationTriTableForm($this->degustation->getTriArray());

        if (! $request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (! $this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($this->degustation->isFullyAnonymized() === false) {
            $this->getUser()->setFlash("error", "Tous les lots doivent être anonymisés afin de passer à l'étape suivante. Cependant, vos numéros d'anonymats renseignés ont bien été enregistré.");
            return $this->redirect('degustation_anonymats_etape', $this->degustation);
        }

        return $this->redirect(DegustationEtapes::getInstance()->getNextLink(DegustationEtapes::ETAPE_ANONYMISATION_MANUELLE), ['id' => $this->degustation->_id]);
    }

    public function executeAjoutLeurre(sfWebRequest $request){
        $this->degustation = $this->getRoute()->getDegustation();
        $this->ajoutLeurreForm = new DegustationAjoutLeurreForm($this->degustation);
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->ajoutLeurreForm->bind($request->getParameter($this->ajoutLeurreForm->getName()));

        if (!$this->ajoutLeurreForm->isValid())
        {
            $this->getUser()->setFlash('error', 'Formulaire d\'ajout de leurre invalide');

            if ($service = $request->getParameter('service', null)) {
                return $this->redirect($service);
            }

            return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => 0));
        }
        $this->ajoutLeurreForm->save();

        $table = $this->ajoutLeurreForm->getValue('table');
        if ($table == null) {
            $table = 0;
        }

        if ($service = $request->getParameter('service', null)) {
            return $this->redirect($service);
        }

        return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => $table));
    }

      public function executeResultats(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->numero_table = $request->getParameter('numero_table',0);
        $this->popup_validation = $request->getParameter('popup',0);

        if(!$this->numero_table && $this->degustation->getFirstNumeroTable()){
          return $this->redirect('degustation_resultats', array('id' => $this->degustation->_id, 'numero_table' => $this->degustation->getFirstNumeroTable()));
        }

        $this->tableLots = $this->degustation->getLotsByTable($this->numero_table);
        $this->nb_tables = count($this->degustation->getTables());
        $options = array('numero_table' => $this->numero_table);
        $this->form = new DegustationResultatsForm($this->degustation, $options);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->popup_validation){
          return $this->redirect('degustation_resultats', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table));
        }

        if($this->numero_table != $this->nb_tables){
          return $this->redirect('degustation_resultats', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table+1));
        }

        return $this->redirect('degustation_resultats_etape', $this->degustation);
    }


    public function executePresences(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->numero_table = $request->getParameter('numero_table',0);

        if(!$this->numero_table && $this->degustation->getFirstNumeroTable()){
          return $this->redirect('degustation_presences', array('id' => $this->degustation->_id, 'numero_table' => $this->degustation->getFirstNumeroTable()));
        }

        $this->nb_tables = count($this->degustation->getTables());
        $options = array('numero_table' => $this->numero_table);
        $this->form = new DegustationDegustateursTableForm($this->degustation, $options);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->numero_table && ($this->numero_table < $this->degustation->getLastNumeroTable())){
          return $this->redirect('degustation_presences', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table+1));
        }

        return $this->redirect('degustation_resultats_etape', $this->degustation);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->lots = $this->degustation->getLotsPreleves();
        uasort($this->lots, function ($a, $b) { return $a->declarant_nom > $b->declarant_nom; });

        $this->mvts = array_map(function ($lot) {
            $m = LotsClient::getInstance()->getHistory($lot->declarant_identifiant, $lot->unique_id);
            return end($m);
        }, $this->lots);

        if ($this->degustation->etape == TourneeDegustationEtapes::ETAPE_VISUALISATION) {

            return sfView::SUCCESS;
        }

        $etape = $this->getRouteEtape($this->degustation->etape);
        if(!$etape){

            return $this->redirect('degustation_selection_lots', $this->degustation);
        }

        return $this->redirect($etape, $this->degustation);
    }


    protected function getEtape($doc, $etape, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

    protected function getRouteEtape($etape = null, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        $routes = $etapes->getRouteLinksHash();

        return (isset($routes[$etape]))? $routes[$etape] : null;
    }

    protected function getRouteNextEtape($etape = null, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        $routes = $etapes->getRouteLinksHash();
        if (!$etape) {
            $etape = $etapes->getFirst();
        } else {
            $etape = $etapes->getNext($etape);
        }
        return (isset($routes[$etape]))? $routes[$etape] : null;
    }

    public function executeLotHistorique(sfWebRequest $request){
        $this->etablissement = $this->getRoute()->getEtablissement(['allow_stalker' => true]);
        $identifiant = $request->getParameter('identifiant');
        $uniqueId = $request->getParameter('unique_id');

        $this->lot = LotsClient::getInstance()->findByUniqueId($identifiant, $uniqueId);
        $mvts = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($identifiant, $uniqueId, null, null, null, true);
        if (!$this->getUser()->hasDrevAdmin() && (!$mvts->rows[0] || MouvementLotHistoryView::isWaitingLotNotification($mvts->rows[0]->value))) {
            throw new sfError403Exception('Accès impossible');
        }

        if(!$this->lot) {

            throw new sfError404Exception("Lot non trouvé");
        }

        $this->mouvements = LotsClient::getInstance()->getHistory($identifiant, $uniqueId);
    }

    public function executeLotModification(sfWebRequest $request){
        $identifiant = $request->getParameter('identifiant');
        $uniqueId = $request->getParameter('unique_id');
        $this->service = $request->getParameter('service', null);

        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($identifiant);
        $this->lot = LotsClient::getInstance()->findByUniqueId($identifiant, $uniqueId);

        if(!$this->lot) {

            throw new sfError404Exception("Lot non trouvé");
        }

        if (!$this->lot->getDocument()->getMaster()->verifyGenerateModificative()) {
            $this->getUser()->setFlash('error', "Le lot n'est pas modifiable : un document est sans doute en cours d'édition");
            return $this->redirect('degustation_lot_historique', array('identifiant' => $this->etablissement->identifiant, 'unique_id' => $this->lot->unique_id));
        }

        $this->form = new LotModificationForm($this->lot);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }
        try {
            $this->form->save();
        }catch(sfException $e) {
            $this->getUser()->setFlash('error', "Le lot n'est pas modifiable : ".$e->getMessage());
        }

        return $this->redirect('degustation_lot_historique', array('identifiant' => $this->etablissement->identifiant, 'unique_id' => $this->lot->unique_id));
    }

    public function executeLotDelete(sfWebRequest $request) {
        $lot = LotsClient::getInstance()->findByUniqueId($request->getParameter('identifiant'), $request->getParameter('unique_id'));

        if(!$lot) {

            throw new sfError404Exception("Lot non trouvé");
        }

        LotsClient::getInstance()->deleteAndSave($lot->declarant_identifiant, $lot->unique_id);

        return $this->redirect('degustation_declarant_lots_liste',array('identifiant' => $lot->declarant_identifiant, 'campagne' => $lot->campagne));
    }

    public function executeLotsListe(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement(['allow_stalker' => true]);
        $this->forward404Unless($this->etablissement);
        $identifiant = $this->etablissement->identifiant;
        $region = Organisme::getInstance()->getCurrentRegion();

        if(class_exists("EtablissementChoiceForm")) {
            $this->formEtablissement = new EtablissementChoiceForm(sfConfig::get('app_interpro', 'INTERPRO-declaration'), array('identifiant' => $this->etablissement->identifiant), true);
        } elseif(class_exists("LoginForm")) {
            $this->formEtablissement = new LoginForm();
        }

        $this->campagnes = MouvementLotHistoryView::getInstance()->getCampagneFromDeclarantMouvements($identifiant, $region);
        if(!$this->campagnes) {
            $this->campagnes = array(ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate(date('Y-m-d')));
        }
        $this->campagne = $request->getParameter('campagne', $this->campagnes[0]);
        $this->mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByDeclarant($identifiant, $this->campagne, $region)->rows;

        if ($region) {
            $this->mouvements = RegionConfiguration::getInstance()->filterMouvementsByRegion($this->mouvements, $region);
        }

        uasort($this->mouvements, function($a, $b) { if($a->value->date ==  $b->value->date) { return $a->value->numero_archive < $b->value->numero_archive; } return $a->value->date < $b->value->date; });

        $this->syntheseLots = LotsClient::getInstance()->getSyntheseLots($identifiant, array($this->campagne), $region);
    }

    public function executeNonconformites(sfWebRequest $request) {
      $this->chgtDenoms = [];
      $this->campagne = $request->getParameter('campagne', null);
      $this->manquements = DegustationClient::getInstance()->getManquements($this->campagne, (Organisme::getInstance()->isOC()) ? null : Organisme::getInstance()->getCurrentRegion());
    }

    public function executeElevages(sfWebRequest $request) {
      $this->lotsElevages = DegustationClient::getInstance()->getElevages($request->getParameter('campagne'), $this->getUser()->getRegion());
    }

    public function executeRedeguster(sfWebRequest $request) {
        $docid = $request->getParameter('id');
        $lotid = $request->getParameter('lot');
        $doc = DegustationClient::getInstance()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($lotid);
        $this->forward404Unless($lot);

        $lot->redegustation();

        $doc->generateMouvementsLots();
        $doc->save();

        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
    }

    public function executeRecoursOc(sfWebRequest $request) {
        if (RegionConfiguration::getInstance()->hasOC() && Organisme::getInstance()->isOC() === false) {
            throw new sfException('Vous ne pouvez pas faire de recours OC');
        }

        $docid = $request->getParameter('id');
        $lotid = $request->getParameter('lot');
        $doc = DegustationClient::getInstance()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($lotid);
        $this->forward404Unless($lot);

        $lot->recoursOc();

        $doc->generateMouvementsLots();
        $doc->save();

        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
    }

    public function executeLotConformeAppel(sfWebRequest $request) {
        $docid = $request->getParameter('id');
        $lotid = $request->getParameter('lot');
        $doc = acCouchdbManager::getClient()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($lotid);
        $this->forward404Unless($lot);

        $lot->statut = Lot::STATUT_CONFORME_APPEL;
        $lot->conforme_appel = date('Y-m-d');

        $doc->generateMouvementsLots();
        $doc->save();

        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
    }

    public function executeLotLeverNonConformite(sfWebRequest $request) {
        $docid = $request->getParameter('id');
        $lotid = $request->getParameter('lot');
        $doc = DegustationClient::getInstance()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($lotid);
        $this->forward404Unless($lot);

        $lot->leverNonConformite();

        $doc->generateMouvementsLots();
        $doc->save();

        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
    }

    public function executeLotReputeConforme(sfWebRequest $request) {
        $docid = $request->getParameter('id');
        $unique_id = $request->getParameter('unique_id');
        $doc = acCouchdbManager::getClient()->find($docid);
        $lot = $doc->getLot($unique_id);
        if (!$lot->getMouvement(Lot::STATUT_AFFECTABLE)) {
            throw new sfException("Action impossible");
        }
        $lot->affectable = false;
        $doc->save();
        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
    }

    public function executeLotAffectable(sfWebRequest $request) {
        $docid = $request->getParameter('id');
        $unique_id = $request->getParameter('unique_id');
        $doc = acCouchdbManager::getClient()->find($docid);
        $lot = $doc->getLot($unique_id);
        if (!$lot->getMouvement(Lot::STATUT_NONAFFECTABLE) && !$lot->getMouvement(Lot::STATUT_NONAFFECTABLE_EN_ATTENTE)) {
            //Pendant quelques semaine en 2025, cette exception a été désativée
            //et potentiellement affactable n'a pas eu d'impact sur les statuts
            //=> si c'est le cas, il faut intervenir en base et pas toucher ici
           throw new sfException("Action impossible");
        }
        $lot->affectable = true;
        $lot->updateStatut();
        $doc->save();
        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
    }

    public function executeLotAffectation(sfWebRequest $request){
      $identifiant = $request->getParameter('id');
      $unique_id = $request->getParameter('unique_id');
      $this->lot = LotsClient::getInstance()->findByUniqueId($identifiant, $unique_id);
      $this->etablissement = EtablissementClient::getInstance()->find($identifiant);

      $this->form = new DegustationAffectionLotForm($this->lot, !$request->getParameter('plusdedegustations', false));

      if (!$request->isMethod(sfWebRequest::POST)) {

          return sfView::SUCCESS;
      }

      $this->form->bind($request->getParameter($this->form->getName()));

      if (!$this->form->isValid()) {

          return sfView::SUCCESS;

      }

      $this->form->save();

      $degust = $this->form->getDegustation();

      if ($degust->etape == DegustationEtapes::ETAPE_PRELEVEMENTS ) {
        $this->getUser()->setFlash("warning", "La dégustation est à l'étape du prélèvement, votre numéro de table ne sera pas pris en compte.");
      }

      if (in_array($degust->etape,array(DegustationEtapes::ETAPE_CONVOCATIONS,DegustationEtapes::ETAPE_DEGUSTATEURS,DegustationEtapes::ETAPE_LOTS)) ) {
        $this->getUser()->setFlash("warning", "La dégustation est à l'étape de l'enregistrement des lots, votre statut de prélèvement et numéro de table ne sera pas pris en compte.");
      }

       return $this->redirect("degustation_lot_historique",array('identifiant' => $identifiant, 'unique_id'=> $unique_id));

    }

    public function executeAnonymize(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $degustation->anonymize();
      $degustation->save();
      return $this->redirect('degustation_commission_etape', $degustation);
    }

    public function executeDesanonymize(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $degustation->desanonymize();
      $degustation->storeEtape(DegustationEtapes::ETAPE_ANONYMATS);
      $degustation->save();
      return $this->redirect('degustation_anonymats_etape', $degustation);
    }

    public function executeMailPrevisualisation(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->identifiant_operateur = $request->getParameter('identifiant');
      $this->lotsOperateur = $this->degustation->getLotsByOperateurs($this->identifiant_operateur);

      $this->popup = true;

      $this->setTemplate('notificationsEtape');
    }

    public function executeSetEnvoiMail(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();

      $identifiant = $request->getParameter('identifiant');
      $mailto = $identifiant;
      $date = $request->getParameter('envoye', date('Y-m-d H:i:s'));

      if(!boolval($date)) {
          $date = null;
          $mailto = null;
      }

      $this->degustation->setMailEnvoyeEtablissement($identifiant, $date);
      $this->degustation->save(false);

      if ($mailto) {
          return $this->redirect('degustation_notifications_etape', array('id' => $this->degustation->_id, 'mail_to_identifiant' => $identifiant));
      } else {
          return $this->redirect('degustation_notifications_etape', $this->degustation);
      }
    }

    public function executeMailToNotification(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();
        $identifiant = $request->getParameter('identifiant');
        $lots = $degustation->getLotsByOperateurs()[$identifiant];
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date', 'Partial'));

        $lotsConformes = [];
        $lotsNonConformes = [];

        foreach ($lots as $lot) {
            switch ($lot->statut) {
                case Lot::STATUT_CONFORME:
                    $lotsConformes[] = $lot;
                    break;
                case Lot::STATUT_NONCONFORME:
                    $lotsNonConformes[] = $lot;
                    break;
            }
        }

        $email = EtablissementClient::getInstance()->find($identifiant)->getEmail();
        $email = trim($email);

        $cc = Organisme::getInstance(null, 'degustation')->getEmail();
        if ($cc) {
            $cc = "cc=".$cc."&";
        }
        $subject = sprintf("Résultat de la dégustation du %s", $degustation->getDateFormat('d/m/Y'));
        $body = html_entity_decode(str_replace("\n", "%0A", strip_tags(get_partial('degustation/notificationEmail', [
            'degustation' => $degustation,
            'identifiant' => $identifiant,
            'lotsConformes' => $lotsConformes,
            'lotsNonConformes' => $lotsNonConformes
        ]))), ENT_QUOTES | ENT_XML1, 'UTF-8');

        $mailto = "mailto:$email?".$cc."subject=$subject&body=$body";
        $mailto = mb_strcut($mailto, 0, 1559); // Chrome limite les mailto à un certain nombre de caractères 1600 semblent être le max

        $this->getResponse()->clearHttpHeaders();
        $this->getResponse()->setStatusCode(302);
        $this->getResponse()->setHttpHeader('Location', $mailto);
        $this->getResponse()->setContent(sprintf('<html><head><meta http-equiv="refresh" content="%d;url=%s"/></head></html>', 0, $mailto));
        $this->getResponse()->send();

        throw new sfStopException();
    }

    public function executeTriTable(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();
        $numero_table = $request->getParameter('numero_table');
        $service = $request->getParameter('service', null);
        $this->triTableForm = new DegustationTriTableForm(array());

        $this->triTableForm->bind($request->getParameter($this->triTableForm->getName()));
        $recap = $this->triTableForm->getValue('recap');

        if (!$this->triTableForm->isValid()) {
            if ($service) {
                return $this->redirect($service);
            }

            if($recap) {
                return $this->redirect('degustation_organisation_table_recap', array('id' => $degustation->_id));
            }
            return $this->redirect('degustation_organisation_table', array('id' => $degustation->_id, 'numero_table' => $numero_table));
        }

        $values = $this->triTableForm->getValues();
        unset($values['recap']);

        $degustation->tri = join('|', array_filter(array_unique(array_values($values))));
        $degustation->save();

        if ($service) {
            return $this->redirect($service);
        }

        if($recap) {
            return $this->redirect('degustation_organisation_table_recap', array('id' => $degustation->_id));
        }
        return $this->redirect('degustation_organisation_table', array('id' => $degustation->_id, 'numero_table' => $numero_table));
    }

    public function executeEtiquettesPrlvmtCsv(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
      $this->getResponse()->setHttpHeader('Content-Type', 'text/csv; charset=ISO-8859-1');
      $this->setLayout(false);
    }

    public function executeEtiquettesPrlvmtPdf(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
      $this->isEtiquette = true;
      $this->document = new ExportDegustationEtiquettesPrlvmtPDF($this->degustation, $request->getParameter('identifiant', null), $request->getParameter('anonymat4labo', false), $request->getParameter('output', 'pdf'), false, null, null, $request->getParameter('secteur'));
      return $this->mutualExcecutePDF($request);
    }

    public function executeEtiquettesTablesEchantillonsAnonymesPDF(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->isEtiquette = true;
      $this->document = new ExportDegustationEtiquettesTablesEchantillonsParAnonymatOrUniqueidPDF($this->degustation, $request->getParameter('output', 'pdf'), $request->getParameter('tri', 'numero_anonymat'), false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheIndividuellePDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->document = new ExportDegustationFicheIndividuellePDF($this->degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheTablesEchantillonsParDossierPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->document = new ExportDegustationFicheTablesEchantillonsParDossierPDF($this->degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheTablesEchantillonsParRaisonSocialePDF(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->document = new ExportDegustationFicheTablesEchantillonsParRaisonSocialePDF($this->degustation,$request->getParameter('output','pdf'), false);
        return $this->mutualExcecutePDF($request);
    }

    public function executeFicheTablesEchantillonsParTourneePDF(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->document = new ExportDegustationFicheTablesEchantillonsParTourneePDF($this->degustation,$request->getParameter('output','pdf'), false);
        return $this->mutualExcecutePDF($request);
    }

    public function executeFicheTablesEchantillonsParAnonymatPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheTablesEchantillonsParAnonymatPDF($this->degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationAllNotificationsPDF(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsNotAnonymized();
        $this->document = new ExportDegustationAllNotificationsPDF($this->degustation, $request->getParameter('output', 'pdf'), false);
        return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationConformitePDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $etablissement = EtablissementClient::getInstance()->findByIdentifiant($request->getParameter('identifiant'));
      $this->forward404Unless(
            $this->getUser()->isAdmin() ||
            $this->getUser()->isStalker() ||
            $request->getParameter('action') != 'degustationConformitePDF' ||
            $this->getUser()->getCompte()->getSociete()->identifiant == $etablissement->getSociete()->identifiant
      );
      $this->document = new ExportDegustationConformitePDF($this->degustation,$etablissement,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationNonConformitePDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $lot_dossier = $request->getParameter('lot_dossier');
      $lot_archive = $request->getParameter('lot_archive');
      $lot = $this->degustation->getLotByNumDossierNumArchive($lot_dossier, $lot_archive);
      $this->forward404Unless(
          $this->getUser()->isAdmin() ||
          $this->getUser()->isStalker() ||
          $request->getParameter('action') != 'degustationNonConformitePDF' ||
          strpos($lot->declarant_identifiant, $this->getUser()->getCompte()->getSociete()->identifiant) === 0
      );
      $this->document = new ExportDegustationNonConformitePDF($this->degustation,$lot, $request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationRapportInspectionPDF(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $lot_dossier = $request->getParameter('lot_dossier');
        $lot_archive = $request->getParameter('lot_archive');

        $lot = $this->degustation->getLotByNumDossierNumArchive($lot_dossier, $lot_archive);

        $this->forward404Unless(
            $this->getUser()->isAdmin() ||
            $this->getUser()->isStalker() ||
            $request->getParameter('action') != 'degustationRapportInspectionPDF' ||
            strpos($lot->declarant_identifiant, $this->getUser()->getCompte()->getSociete()->identifiant) === 0
        );

        $this->document = new ExportDegustationRapportInspectionPDF($this->degustation, $lot, $request->getParameter('output', 'pdf'), false);

        return $this->mutualExcecutePDF($request);
    }

    public function executeFicheRecapTablesPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->document = new ExportDegustationFicheRecapTablesPDF($this->degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeRetraitNonConformitePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $etablissement = EtablissementClient::getInstance()->findByIdentifiant($request->getParameter('identifiant'));
      $lot_dossier = $request->getParameter('lot_dossier');
      $this->document = new ExportRetraitNonConformitePDF($degustation,$etablissement,$lot_dossier,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheLotsAPreleverPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheLotsAPreleverPDF($this->degustation,$request->getParameter('output','pdf'),false, null, null, $request->getParameter('secteur'));
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheIndividuelleLotsAPreleverPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheIndividuelleLotsAPreleverPDF($this->degustation, $request->getParameter('lotid', null), $request->getParameter('output','pdf'),false, null, null, $request->getParameter('secteur'));
      return $this->mutualExcecutePDF($request);
    }

    public function executeFichePresenceDegustateursPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->document = new ExportDegustationFichePresenceDegustateursPDF($this->degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeProcesVerbalDegustationPDF(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $this->redirectIfIsNotAnonymized();
      $this->document = new ExportDegustationFicheProcesVerbalDegustationPDF($this->degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    private function mutualExcecutePDF(sfWebRequest $request) {
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generate();
        $this->document->addHeaders($this->getResponse());

        if(isset($this->isEtiquette) && sfConfig::get('sf_debug')) {
            $temp = tmpfile();
            $metadata = stream_get_meta_data($temp);

            fwrite($temp, $this->document->output());
            shell_exec(sprintf("pdftk %s background %s output %s", escapeshellarg($metadata['uri']), escapeshellarg(sfConfig::get('sf_web_dir')."/images/pdf/etiquettes_70x36.pdf"), escapeshellarg($metadata['uri'].".pdf")));
            $pdfContent = file_get_contents($metadata['uri'].".pdf");
            $this->getResponse()->setHttpHeader('Content-Length', filesize($metadata['uri'].".pdf"));
            unlink($metadata['uri'].".pdf");
            fclose($temp);

            return $this->renderText($pdfContent);
        }


        return $this->renderText($this->document->output());
    }


    private function forward403IfLotsAffectes() {
        foreach($this->degustation->lots as $l) {
            if ($l->id_document_affectation) {
                throw new sfError403Exception('Modification impossible : Lot '.$l->unique_id.' est affecté à '.$l->id_document_affectation);
            }
        }
    }

    private function redirectIfIsAnonymized()
    {
        $this->forward403IfLotsAffectes();

        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle()) {
            return false;
        }

        if ($this->degustation->isAnonymized()) {
            $etape = $this->getRouteEtape($this->degustation->etape);

            if (DegustationEtapes::$etapes[$this->degustation->etape] < DegustationEtapes::$etapes[DegustationEtapes::ETAPE_ANONYMATS]) {
                return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_ANONYMATS), $this->degustation);
            } else {
                return $this->redirect($etape, $this->degustation);
            }
        }
    }

    private function redirectIfIsNotAnonymized()
    {
        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle()) {
            return false;
        }

        if (! $this->degustation->isAnonymized()) {
            $etape = $this->getRouteEtape($this->degustation->etape);

            if (DegustationEtapes::$etapes[$this->degustation->etape] > DegustationEtapes::$etapes[DegustationEtapes::ETAPE_ANONYMATS]) {
                return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_ANONYMATS), $this->degustation);
            } else {
                return $this->redirect($etape, $this->degustation);
            }
        }
    }

    public function executeGetCourrierWithAuth(sfWebRequest $request) {
        // Gestion du cas ou le mailer ne retire pas le ">" à la fin du lien
        $request->setParameter('lot_archive', str_replace('>', '', $request->getParameter('lot_archive', null)));

        $authKey = $request->getParameter('auth');
        $degustation_id = "DEGUSTATION-".str_replace("DEGUSTATION-", "", $request->getParameter('id'));
        $identifiant = $request->getParameter('identifiant', null);
        $identifiant = str_replace(array('>', '%3E', '%3e'), '', $identifiant);
        $lot_dossier = $request->getParameter('lot_dossier', null);
        $lot_archive = $request->getParameter('lot_archive', null);
        $type = $request->getParameter('type', null);

        if (! $type) {
            throw new sfException('Parametre type n\'est pas défini');
        }

        $descriminant = '';
        switch ($type) {
            case 'NonConformite':
                if (! $lot_archive || ! $lot_dossier) { throw new sfException("Identifiant de lot manquant"); }
                $discriminant = $lot_dossier.$lot_archive;
                break;

            case 'Conformite':
                if (! $identifiant) { throw new sfException("Identifiant de compte manquant"); }
                $discriminant = $identifiant;
                break;

            default:
                break;
        }

        if (empty($discriminant)) {
            throw new sfException('Discriminant vide');
        }

        if (!UrlSecurity::verifyAuthKey($authKey, $degustation_id, $discriminant)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        if($type == "Conformite") {

            return $this->executeDegustationConformitePDF($request);
        }

        if($type == "NonConformite") {

            return $this->executeDegustationNonConformitePDF($request);
        }

        throw new sfError404Exception();
    }

    public function executeConvocationReponse(sfWebRequest $request)
    {
        $this->degustation = $request->getParameter('id');
        $this->college = $request->getParameter('college');
        $this->identifiant = $request->getParameter('identifiant');
        $authkey = $request->getParameter('auth');

        if (! UrlSecurity::verifyAuthKey($authkey, $this->degustation, $this->identifiant)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        $this->degustation = DegustationClient::getInstance()->find($this->degustation);

        if (! $this->degustation->degustateurs->exist($this->college) || ! $this->degustation->degustateurs->get($this->college)->exist($this->identifiant)) {
            throw new sfException("Vous n'êtes pas convoqué dans cette dégustation");
        }

        if ($this->degustation->degustateurs->get($this->college)->get($this->identifiant)->exist('confirmation')) {
            $presence = $this->degustation->degustateurs->get($this->college)->get($this->identifiant)->confirmation;
            return $this->redirect('degustation_convocation_auth', [
                'id' => $this->degustation->_id,
                'college' => $this->college,
                'identifiant' => $this->identifiant,
                'auth' => $authkey,
                'presence' => ($presence) ? 1 : 0
            ]);
        }

        $this->setLayout(false);
    }

    public function executeConvocationWithAuth(sfWebRequest $request) {
        $authKey = $request->getParameter('auth');
        $this->id = $request->getParameter('id');
        $this->identifiant = $request->getParameter('identifiant', null);
        $this->college = $request->getParameter('college', null);
        $this->presence = $request->getParameter('presence', null);

        if (! $this->identifiant) {
            throw new sfException('Parametre identifiant n\'est pas défini');
        }

        if (! $this->college) {
            throw new sfException('Parametre college n\'est pas défini');
        }

        if (is_null($this->presence)) {
            throw new sfException('Parametre présence n\'est pas défini');
        }

        if (!UrlSecurity::verifyAuthKey($authKey, $this->id, $this->identifiant)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        $this->setLayout(false);
        $this->degustation = DegustationClient::getInstance()->find($this->id);
        $degustateurs = $this->degustation->get("degustateurs");
        if (! $degustateurs->exist($this->college) || !$degustateurs->get($this->college)->exist($this->identifiant)) {
            throw new sfException("Le dégustateur $this->college n\'est pas dans la dégustation ");
        }
        $degustateur = $degustateurs->get($this->college)->get($this->identifiant);

        $previousPresence = null;
        if($degustateurs->get($this->college)->get($this->identifiant)->exist('confirmation')) {
            $previousPresence = $degustateurs->get($this->college)->get($this->identifiant)->get('confirmation');
        }

        $degustateurs->get($this->college)->get($this->identifiant)->add('confirmation', boolval($this->presence));

        $this->degustation->save(false);

        $this->emailSended = false;
        if($previousPresence === $degustateurs->get($this->college)->get($this->identifiant)->get('confirmation')) {

            return sfView::SUCCESS;
        }

        Email::getInstance()->sendActionDegustateurAuthMail($this->degustation, $degustateur, boolval($this->presence));
        $this->emailSended = true;
    }

    public function executeRetirerLot(sfWebRequest $request) {
        $declarant_id = $request->getParameter('id');
        $unique_id = $request->getParameter('unique_id');
        $degustation_id = $request->getParameter('degustation_id');

        $degustation = DegustationClient::getInstance()->find($degustation_id);
        $this->forward404Unless($degustation);

        $lot = $degustation->getLot($unique_id);
        $this->forward404Unless($lot);

        $degustation->removeLot($lot);
        $degustation->save();
        return $this->redirect('degustation_lot_historique', array('identifiant' => $declarant_id, 'unique_id' => $unique_id));

    }
}
