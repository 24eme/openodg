<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->form = new DegustationCreationForm();
        $this->lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
        $this->lotsElevages = MouvementLotView::getInstance()->getByStatut(Lot::STATUT_ELEVAGE_EN_ATTENTE)->rows;
        $this->lotsManquements = MouvementLotView::getInstance()->getByStatut(Lot::STATUT_MANQUEMENT_EN_ATTENTE)->rows;

        $this->campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();

        $this->degustations = DegustationClient::getInstance()->getHistory();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $degustation = $this->form->save();

        return $this->redirect('degustation_prelevement_lots', $degustation);
    }

    public function executeListe(sfWebRequest $request)
    {
        $this->campagne = $request->getParameter('campagne');
        $this->degustations = DegustationClient::getInstance()->getHistory(9999, acCouchdbClient::HYDRATE_JSON);
    }

    public function executeListeDeclarant(sfWebRequest $request)
    {
        $this->campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneVinicole()->getCurrent());
        $this->etablissement = $request->getParameter('identifiant');
        $this->degustations = [];

        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByDeclarant($this->etablissement, $this->campagne)->rows;

        foreach ($mouvements as $lot) {
            if (in_array($lot->value->document_id, $this->degustations)) {
                continue;
            }

            $this->degustations[$lot->value->document_id] = DegustationClient::getInstance()->find($lot->value->document_id, acCouchdbClient::HYDRATE_JSON);
        }
    }

    public function executePrelevables(sfWebRequest $request)
    {
        $this->lotsPrelevables = DegustationClient::getInstance()->getLotsPrelevables();
    }

    public function executePrelevementLots(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        $this->redirectIfIsAnonymized();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_LOTS))) {
            $this->degustation->save(false);
        }

        $this->form = new DegustationPrelevementLotsForm($this->degustation);

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

    public function executePreleve(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->infosDegustation = $this->degustation->getInfosDegustation();

        $this->form = new DegustationPreleveLotsForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_prelevements_etape', $this->degustation);
    }

    public function executeUpdateLot(sfWebRequest $request)
    {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->lotkey = $request->getParameter('lot');
        $this->lot = $this->degustation->lots->get($request->getParameter('lot'));

        $this->form = new DegustationLotForm($this->lot);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if ($this->form->isValid()) {
                $this->form->save();
                return $this->redirect('degustation_preleve', $this->degustation);
            }
        }
    }

    public function executeSupprimerLotNonPreleve(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->lot = $request->getParameter('lot');

        $lots = $this->degustation->lots;

        foreach ($lots as $key => $value) {
          if($this->lot <= $key && isset($this->degustation->lots[$key+1])){
            $this->degustation->lots[$key] = $this->degustation->lots[$key+1];
          }
          if(!isset($this->degustation->lots[$key+1])){
            unset($this->degustation->lots[$key]);
            break;
          }
        }


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
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_CONVOCATIONS))) {
            $this->degustation->save(false);
          }
    }

    public function executeConvocationsMails(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        Email::getInstance()->sendConfirmationDegustateursMails($this->degustation);
        $this->getUser()->setFlash("notice", "Les mails de convocations ont été envoyés aux dégustateurs.");
        $this->degustation->save(false);
        return $this->redirect('degustation_convocations', $this->degustation);
    }


    public function executePrelevementsEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_PRELEVEMENTS))) {
            $this->degustation->save(false);
        }
    }

    public function executeTablesEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        if (count($this->degustation->getLotsPreleves()) < 1) {
            return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_PRELEVEMENTS), $this->degustation);
        }
        $this->redirectIfIsAnonymized();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_TABLES))) {
            $this->degustation->save(false);
        }
    }

    public function executeAnonymatsEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_ANONYMATS))) {
            $this->degustation->save(false);
          }
    }

    public function executeCommissionEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_COMMISSION))) {
            $this->degustation->save(false);
          }
    }

    public function executeResultatsEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_RESULTATS))) {
            $this->degustation->save();
          }
    }

    public function executeNotificationsEtape(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_NOTIFICATIONS))) {
            $this->degustation->save();
        }
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
      $this->degustation->save(false);

      return $this->redirect('degustation_degustateurs_confirmation', $this->degustation);

    }

    public function executeOrganisationTable(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
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

    public function executeUpPositionLot(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();
        $index = $request->getParameter('index');
        $tri = $request->getParameter('tri');
        $numero_table = $request->getParameter('numero_table');

        $this->forward404Unless($degustation->lots->exist($index));
        $lot = $degustation->lots->get($index);
        $lot->upPosition();

        $tri = array_merge(['Manuel'], explode('|', $tri));
        $tri = array_unique($tri);
        $tri = implode('|', $tri);

        $degustation->save(false);
        return $this->redirect($this->generateUrl('degustation_organisation_table', array('id' => $degustation->_id, 'numero_table' => $numero_table, 'tri' => $degustation->tri))."#form-organisation-table");
    }

    public function executeOrganisationTableRecap(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->tri = $this->degustation->tri;
        $this->form = new DegustationOrganisationTableRecapForm($this->degustation);
        $this->triTableForm = new DegustationTriTableForm($this->degustation->getTriArray(), true);

        $this->syntheseLots = $this->degustation->getSyntheseLotsTable(null);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();

        return $this->redirect('degustation_tables_etape', $this->degustation);
    }

    public function executeAjoutLeurre(sfWebRequest $request){
        $this->degustation = $this->getRoute()->getDegustation();
        $this->ajoutLeurreForm = new DegustationAjoutLeurreForm($this->degustation);
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->ajoutLeurreForm->bind($request->getParameter($this->ajoutLeurreForm->getName()));

        if (!$this->ajoutLeurreForm->isValid()) {

            $this->getUser()->setFlash('error', 'Formulaire d\'ajout de leurre invalide');
            return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => 0));
        }
        $this->ajoutLeurreForm->save();

        $table = $this->ajoutLeurreForm->getValue('table');
        if ($table == null) {
            $table = 0;
        }

        return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => $table));
    }

      public function executeResultats(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->numero_table = $request->getParameter('numero_table',0);
        $this->popup_validation = $request->getParameter('popup',0);

        if(!$this->numero_table && $this->degustation->getFirstNumeroTable()){
          return $this->redirect('degustation_resultats', array('id' => $this->degustation->_id, 'numero_table' => $this->degustation->getFirstNumeroTable()));
        }

        $this->tableLots = $this->degustation->getLotsByTable($this->numero_table);
        $this->nb_tables = count($this->degustation->getTablesWithFreeLots());
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
        $this->numero_table = $request->getParameter('numero_table',0);

        if(!$this->numero_table && $this->degustation->getFirstNumeroTable()){
          return $this->redirect('degustation_presences', array('id' => $this->degustation->_id, 'numero_table' => $this->degustation->getFirstNumeroTable()));
        }

        $this->nb_tables = count($this->degustation->getTablesWithFreeLots());
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
        $etape = $this->getRouteEtape($this->degustation->etape);
        if(!$etape){

            return $this->redirect('degustation_prelevement_lots', $this->degustation);
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
        $etablissement_identifiant = $request->getParameter('identifiant');
        $params = explode('-', $request->getParameter('unique_id'));
        $this->campagne = $params[0].'-'.$params[1];
        $this->numero_dossier = $params[2];
        $this->numero_archive = $params[3];
        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($etablissement_identifiant);
        $this->mouvements =  MouvementLotHistoryView::getInstance()->getMouvements($etablissement_identifiant, $this->campagne, $this->numero_dossier,$this->numero_archive)->rows;
    }

    public function executeList(sfWebRequest $request) {
        $identifiant = $request->getParameter('identifiant');
        $this->etablissement = EtablissementClient::getInstance()->find($identifiant);
        $this->forward404Unless($this->etablissement);
        $this->campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneVinicole()->getCurrent());

        $this->mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByDeclarant($identifiant, $this->campagne)->rows;
    }

    public function executeLot(sfWebRequest $request) {
        $periode = $request->getParameter('periode');
        $lot_id = $request->getParameter('id');
        $this->lotsStepsHistory = array();

    }

    public function executeManquements(sfWebRequest $request) {
      $this->chgtDenoms = [];
      $this->campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneVinicole()->getCurrent());
      $this->manquements = DegustationClient::getInstance()->getManquements($this->campagne);
    }

    public function executeElevages(sfWebRequest $request) {
      $this->lotsElevages = DegustationClient::getInstance()->getElevages($request->getParameter('campagne'));
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
        $doc = DegustationClient::getInstance()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($lotid);
        $this->forward404Unless($lot);

        $lot->conformeAppel();

        $doc->generateMouvementsLots();
        $doc->save();

        return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
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
      $degustation->save();
      return $this->redirect('degustation_anonymats_etape', $degustation);
    }

    public function executeMailPrevisualisation(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();

      $this->identifiant_operateur = $request->getParameter('identifiant');
      $this->lotsOperateur = $this->degustation->getLotsByOperateurs($this->identifiant_operateur);

      $this->popup = true;

      $this->setTemplate('notificationsEtape');
    }

    public function executeSetEnvoiMail(sfWebRequest $request){
      $this->degustation = $this->getRoute()->getDegustation();
      $date = $request->getParameter('envoye',date('Y-m-d H:i:s'));
      if(!boolval($date)){ $date = null; }

      $this->setTemplate('notificationsEtape');
      $this->degustation->setMailEnvoyeEtablissement($request->getParameter('identifiant'),$date);
      $this->degustation->save();

      return $this->redirect('degustation_notifications_etape', $this->degustation);
    }

    public function executeTriTable(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();
        $numero_table = $request->getParameter('numero_table');
        $this->triTableForm = new DegustationTriTableForm(array());

        if (!$request->isMethod(sfWebRequest::POST)) {
            return $this->redirect('degustation_organisation_table', array('id' => $degustation->_id, 'numero_table' => $numero_table));
        }

        $this->triTableForm->bind($request->getParameter($this->triTableForm->getName()));
        $recap = $this->triTableForm->getValue('recap');

        if (!$this->triTableForm->isValid()) {
            if($recap) {
                return $this->redirect('degustation_organisation_table_recap', array('id' => $degustation->_id));
            }
            return $this->redirect('degustation_organisation_table', array('id' => $degustation->_id, 'numero_table' => $numero_table));
        }

        $values = $this->triTableForm->getValues();
        unset($values['recap']);

        $degustation->tri = join('|', array_filter(array_unique(array_values($values))));
        $degustation->save();

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
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationEtiquettesPrlvmtPDF($degustation, $request->getParameter('anonymat4labo', false), $request->getParameter('output', 'pdf'), false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeEtiquettesAnonymesPDF(sfWebRequest $request) {
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationEtiquettesAnonymesPDF($degustation, $request->getParameter('output', 'pdf'), false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheIndividuellePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheIndividuellePDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheEchantillonsPrelevesPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheEchantillonsPrelevesPDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheEchantillonsPrelevesTablePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheEchantillonsPrelevesTablePDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationAllNotificationsPDF(sfWebRequest $request)
    {
        $degustation = $this->getRoute()->getDegustation();
        $this->document = new ExportDegustationAllNotificationsPDF($degustation, $request->getParameter('output', 'pdf'), false);
        return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationConformitePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $etablissement = EtablissementClient::getInstance()->findByIdentifiant($request->getParameter('identifiant'));
      $this->document = new ExportDegustationConformitePDF($degustation,$etablissement,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeDegustationNonConformitePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $lot_dossier = $request->getParameter('lot_dossier');
      $lot_archive = $request->getParameter('lot_archive');
      $lot = $degustation->getLotByNumDossierNumArchive($lot_dossier, $lot_archive);
      $this->document = new ExportDegustationNonConformitePDF($degustation,$lot, $request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheRecapTablesPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheRecapTablesPDF($degustation,$request->getParameter('output','pdf'),false);
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
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheLotsAPreleverPDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFicheIndividuelleLotsAPreleverPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheIndividuelleLotsAPreleverPDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeFichePresenceDegustateursPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFichePresenceDegustateursPDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    public function executeProcesVerbalDegustationPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();
      $this->document = new ExportDegustationFicheProcesVerbalDegustationPDF($degustation,$request->getParameter('output','pdf'),false);
      return $this->mutualExcecutePDF($request);
    }

    private function mutualExcecutePDF(sfWebRequest $request) {
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generate();
        $this->document->addHeaders($this->getResponse());
        return $this->renderText($this->document->output());
    }


    private function redirectIfIsAnonymized(){
      if ($this->degustation->isAnonymized()) {
          $etape = $this->getRouteEtape($this->degustation->etape);
          if (DegustationEtapes::$etapes[$this->degustation->etape] < DegustationEtapes::$etapes[DegustationEtapes::ETAPE_ANONYMATS]) {
              return $this->redirect($this->getRouteEtape(DegustationEtapes::ETAPE_ANONYMATS),$this->degustation);
          } else {
              return $this->redirect($etape, $this->degustation);
          }
      }
    }

    public function executeGetCourrierWithAuth(sfWebRequest $request) {
        $authKey = $request->getParameter('auth');
        $degustation_id = $request->getParameter('id');
        $identifiant = $request->getParameter('identifiant', null);
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

        if (!UrlSecurity::verifyAuthKey($degustation_id, $discriminant, $authKey)) {
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

        if (!UrlSecurity::verifyAuthKey($this->id, $this->identifiant, $authKey)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        $this->setLayout(false);
        $this->degustation = DegustationClient::getInstance()->find($this->id);
        $degustateurs = $this->degustation->get("degustateurs");
        if (! $degustateurs->exist($this->college) || !$degustateurs->get($this->college)->exist($this->identifiant)) {
            throw new sfException("Le dégustateur $this->college n\'est pas dans la dégustation ");
        }
        $degustateur = $degustateurs->get($this->college)->get($this->identifiant);

        $degustateurs->get($this->college)->get($this->identifiant)->add('confirmation', boolval($this->presence));
        $this->degustation->save(false);

    }
}
