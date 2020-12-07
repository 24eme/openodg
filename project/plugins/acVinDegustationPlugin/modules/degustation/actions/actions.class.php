<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $newDegutation = new Degustation();
        $this->form = new DegustationCreationForm($newDegutation);
        $newDegutation->getMvtLotsPrelevables();
        $this->lotsPrelevables = $newDegutation->getLotsPrelevables();
        $this->lotsElevages = MouvementLotView::getInstance()->getByStatut(null, Lot::STATUT_ELEVAGE)->rows;

        $this->degustations = DegustationClient::getInstance()->getHistory();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $degustation = $this->form->save();

        return $this->redirect('degustation_redirect', $degustation);
    }

    public function executePrelevementLots(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->infosDegustation = $this->degustation->getInfosDegustation();
        $this->redirectIfIsValidee();

        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_LOTS))) {
            $this->degustation->save();
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

        return $this->redirect('degustation_visualisation', $this->degustation);
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

                $drev = DRevClient::getInstance()->find($this->lot->id_document);

                $mvmt_degust = $this->degustation->mouvements_lots->get($this->lot->declarant_identifiant)->get($this->lot->getGeneratedMvtKey());

                $modificatrice = $drev->generateModificative();
                $modificatrice->lots->remove($mvmt_degust->origine_hash);
                $modificatrice->addLotFromDegustation($this->form->getObject());
                $modificatrice->generateMouvementsLots();

                $mvmt = $drev->get($this->lot->origine_mouvement);
                $mvmt->prelevable = 0;

                $drev->save();
                $modificatrice->validate();
                $modificatrice->validateOdg();
                $modificatrice->save();

                $l = $this->form->getObject();
                $l->id_document = $modificatrice->_id;
                $this->form->save();

                $this->degustation->updateOrigineLots(Lot::STATUT_NONPRELEVABLE);

                $this->degustation->validate($this->degustation->validation);

                return $this->redirect('degustation_preleve', $this->degustation);
            }
        }
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
        $this->redirectIfIsValidee();
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
            $this->degustation->save();
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
          ($next = $this->getRouteNextEtape(DegustationEtapes::ETAPE_DEGUSTATEURS))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
        }

        return $this->redirect('degustation_selection_degustateurs', array('id' => $this->degustation->_id ,'college' => $next_college));
    }

    public function executeValidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsValidee();
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_VALIDATION))) {
            $this->degustation->save();
        }

        $this->validation = new DegustationValidation($this->degustation);
        $this->form = new DegustationValidationForm($this->degustation);

         if (!$request->isMethod(sfWebRequest::POST)) {

             return sfView::SUCCESS;
         }

         $this->form->bind($request->getParameter($this->form->getName()));

         if (!$this->form->isValid()) {

             return sfView::SUCCESS;
         }

         $this->form->save();

        return $this->redirect('degustation_visualisation', array('id' => $this->degustation->_id));
    }


    public function executeConfirmation(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
    }

    public function executeVisualisation(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
      $this->infosDegustation = $this->degustation->getInfosDegustation();
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

      if ($request->isXmlHttpRequest()) {

        return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
      }

      return $this->redirect('degustation_visualisation', $this->degustation);

    }

    public function executeDegustateurAbsence(sfWebRequest $request) {
      $this->degustation = $this->getRoute()->getDegustation();
      $college = $request->getParameter('college',null);
      $degustateurId = $request->getParameter('degustateurId',null);
      if(!$college || !$degustateurId){
        return $this->redirect('degustation_degustateurs_confirmation', $this->degustation);
      }
      $this->degustation->degustateurs->getOrAdd($college)->getOrAdd($degustateurId)->add('confirmation',false);
      $this->degustation->save();

      return $this->redirect('degustation_degustateurs_confirmation', $this->degustation);

    }

    public function executeOrganisationTable(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        if(!$request->getParameter('numero_table')) {

            return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => 1));
        }

        $this->numero_table = $request->getParameter('numero_table');
        $this->syntheseLots = $this->degustation->getSyntheseLotsTable($this->numero_table);
        $this->form = new DegustationOrganisationTableForm($this->degustation, $this->numero_table);
        $this->ajoutLeurreForm = new DegustationAjoutLeurreForm($this->degustation, array('table' => $this->numero_table));

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

            return $this->redirect('degustation_organisation_table_recap', array('id' => $this->degustation->_id));
        }

        if($this->degustation->hasFreeLots()) {

            return $this->redirect('degustation_organisation_table', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table + 1));
        }

        return $this->redirect('degustation_organisation_table_recap', array('id' => $this->degustation->_id));
    }

    public function executeOrganisationTableRecap(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->form = new DegustationOrganisationTableRecapForm($this->degustation);
        $this->syntheseLots = $this->degustation->getSyntheseLotsTable(null);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();

        return $this->redirect('degustation_visualisation', $this->degustation);
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
        $options = array('tableLots' => $this->tableLots, 'numero_table' => $this->numero_table);
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

        return $this->redirect('degustation_visualisation', $this->degustation);
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

        if ($request->isXmlHttpRequest()) {
          $this->degustation = $this->getRoute()->getDegustation();
          $this->form->save();
          return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->degustation->_id, "revision" => $this->degustation->_rev))));
        }

        $this->form->save();

        if($this->numero_table && ($this->numero_table < $this->degustation->getLastNumeroTable())){
          return $this->redirect('degustation_presences', array('id' => $this->degustation->_id, 'numero_table' => $this->numero_table+1));
        }

        return $this->redirect('degustation_visualisation', $this->degustation);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->degustation->devalidate();
        $this->degustation->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect('degustation_validation', $this->degustation);
    }

    public function executeRedirect(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->redirectIfIsValidee();
        return ($next = $this->getRouteNextEtape($this->degustation->etape))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }

    public function redirectIfIsValidee(){
      if ($this->degustation->isValidee()) {
          return $this->redirect('degustation_confirmation', $this->degustation);
      }
    }

    protected function getEtape($doc, $etape, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
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

    public function executeList(sfWebRequest $request) {
        $etablissement_id = $request->getParameter('id');
        $this->etablissement = EtablissementClient::getInstance()->find($etablissement_id);
        $this->forward404Unless($this->etablissement);

        $this->lots = array();
        foreach (MouvementLotView::getInstance()->getByDeclarantIdentifiant($etablissement_id)->rows as $item) {
            $key = Lot::generateMvtKey($item->value);
            if (!isset($this->lots[$key])) {
                $this->lots[$key] = $item->value;
                $this->lots[$key]->steps = array();
            }
            $this->lots[$key]->steps[] = $item->value;
        }
    }

    public function executeManquements(sfWebRequest $request) {
      $this->chgtDenoms = [];
      $this->manquements = DegustationClient::getInstance()->getManquements();
      foreach ($this->manquements as $keyLot => $manquement) {
          $etablissement = EtablissementClient::getInstance()->find($manquement->declarant_identifiant);
          $chgtDenom = ChgtDenomClient::getInstance()->getLast($etablissement->identifiant);
          if($chgtDenom == null){
            $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant);
            $chgtDenom->save();
          }
          $this->chgtDenoms[$keyLot] = $chgtDenom;
      }
    }

    public function executeElevages(sfWebRequest $request) {
      $this->lotsElevages = MouvementLotView::getInstance()->getByStatut(null, Lot::STATUT_ELEVAGE)->rows;
    }

    public function executeEtiquettesPdf(sfWebRequest $request) {
      $degustation = $this->getRoute()->getDegustation();

      $this->document = new ExportDegustationEtiquettesPdf($degustation, $this->getRequestParameter('output', 'pdf'), false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());
    }


    public function executeFicheIndividuellePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $this->document = new ExportDegustationFicheIndividuellePDF($degustation,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());

    }

    public function executeFicheEchantillonsPrelevesPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $this->document = new ExportDegustationFicheEchantillonsPrelevesPDF($degustation,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());

    }
    public function executeDegustationConformitePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$request['identifiant']);

      $this->document = new ExportDegustationConformitePDF($degustation,$etablissement,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());

    }

    public function executeDegustationNonConformitePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$request['identifiant']);

      $this->document = new ExportDegustationNonConformitePDF($degustation,$etablissement,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());

    }

    public function executeRetraitNonConformitePDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$request['identifiant']);

      $this->document = new ExportRetraitNonConformitePDF($degustation,$etablissement,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());

    }

    public function executeFicheRecapTablesPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $this->document = new ExportDegustationFicheRecapTablesPDF($degustation,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());

    }

    public function executeFicheLotsAPreleverPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $this->document = new ExportDegustationFicheLotsAPreleverPDF($degustation,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());
    }

    public function executeFichePresenceDegustateursPDF(sfWebRequest $request){
      $degustation = $this->getRoute()->getDegustation();

      $this->document = new ExportDegustationFichePresenceDegustateursPDF($degustation,$this->getRequestParameter('output','pdf'),false);
      $this->document->setPartialFunction(array($this, 'getPartial'));

      if ($request->getParameter('force')) {
          $this->document->removeCache();
      }

      $this->document->generate();

      $this->document->addHeaders($this->getResponse());

      return $this->renderText($this->document->output());
    }
}
