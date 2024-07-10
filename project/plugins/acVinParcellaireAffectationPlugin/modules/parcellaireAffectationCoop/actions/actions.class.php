<?php

class parcellaireAffectationCoopActions extends sfActions {

    public function executeEdit(sfWebRequest $request) {

        $etablissement = $this->getRoute()->getObject();
        $periode = $request->getParameter('periode');

        $parcellaireAffectationCoop = ParcellaireAffectationCoopClient::getInstance()->find(ParcellaireAffectationCoopClient::getInstance()->buildId($etablissement->identifiant, $periode));

        if(!$parcellaireAffectationCoop) {

            return $this->redirect('parcellaireaffectationcoop_create', array('identifiant' => $etablissement->identifiant, 'periode' => $periode));
        }


        if (!$parcellaireAffectationCoop->exist('etape') || !$parcellaireAffectationCoop->etape) {

            return $this->redirect('parcellaireaffectationcoop_apporteurs', $parcellaireAffectationCoop);
        }

        return $this->redirect(ParcellaireAffectationCoopEtapes::getInstance()->getRouteLink($parcellaireAffectationCoop->etape), $parcellaireAffectationCoop);
    }

    public function executeCreate(sfWebRequest $request) {

        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

        $parcellaireAffectationCoop = ParcellaireAffectationCoopClient::getInstance()->findOrCreate($this->etablissement->identifiant, $this->periode);

        if($parcellaireAffectationCoop->isNew()) {
            $parcellaireAffectationCoop->save();
        }

        return $this->redirect('parcellaireaffectationcoop_liste', $parcellaireAffectationCoop);
    }

    public function executeApporteurs(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        if($this->parcellaireAffectationCoop->storeEtape($this->getEtape($this->parcellaireAffectationCoop, ParcellaireAffectationCoopEtapes::ETAPE_APPORTEURS))) {
            $this->parcellaireAffectationCoop->save();
    	}

        $this->form = new ParcellaireAffectationCoopApporteursForm($this->parcellaireAffectationCoop);

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));
    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeAjoutApporteurs(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->form = new ParcellaireAffectationCoopAjoutApporteursForm($this->parcellaireAffectationCoop);

        if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $etablissement = EtablissementClient::getInstance()->findByCvi($this->form->getValues()['cviApporteur']);
        if (! $etablissement) {
            $this->getUser()->setFlash("error", "Le CVI est invalide.");
            return $this->redirect('parcellaireaffectationcoop_ajout_apporteurs', $this->parcellaireAffectationCoop);
        } elseif ((in_array($etablissement->_id, $this->parcellaireAffectationCoop->getApporteursChoisis()))) {
            $this->getUser()->setFlash("error", "Cet apporteur est déjà dans la liste.");
            return $this->redirect('parcellaireaffectationcoop_ajout_apporteurs', $this->parcellaireAffectationCoop);
        } else {
            $this->parcellaireAffectationCoop->addApporteur($etablissement->_id);
            $this->getUser()->setFlash("success", "Apporteur ajouté avec succès.");
            $this->parcellaireAffectationCoop->save();
        }
        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeListe(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        if($this->parcellaireAffectationCoop->storeEtape($this->getEtape($this->parcellaireAffectationCoop, ParcellaireAffectationCoopEtapes::ETAPE_SAISIES))) {
            $this->parcellaireAffectationCoop->save();
    	}
    }

    public function executeSwitch(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->apporteur = $request->getParameter('apporteur');
        $this->parcellaireAffectationCoop->apporteurs->{'ETABLISSEMENT-'.$this->apporteur}->updateParcelles();
        $this->parcellaireAffectationCoop->apporteurs->{'ETABLISSEMENT-'.$this->apporteur}->intention = ($request->getParameter('sens')) ? true : false;
        $this->parcellaireAffectationCoop->save();
        return $this->redirect('parcellaireaffectationcoop_liste', array('sf_subject' => $this->parcellaireAffectationCoop));
    }

    public function executeSaisie(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->findOrCreate($request->getParameter('apporteur'), substr($this->parcellaireAffectationCoop->campagne, 0, 4));
        $this->parcellaireAffectation->updateParcellesAffectation();
        if($this->parcellaireAffectation->isValidee()) {

            return $this->redirect('parcellaireaffectationcoop_visualisation', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireAffectation->_id));
        }

        $this->parcellaireAffectation->add('signataire', $this->etablissement->raison_sociale);
		$this->form = new ParcellaireAffectationCoopSaisieForm($this->parcellaireAffectation, $this->etablissement);

        if(isset($_POST['decla-affect-a-manquant'])) {
            $identifiantApporteur = $this->parcellaireAffectation->getEtablissementObject()->_id;

            return $this->redirect('parcellaireaffectationcoop_manquant_saisie', array('id' => $this->parcellaireAffectationCoop->_id, 'apporteur' => str_replace("ETABLISSEMENT-", "", $identifiantApporteur)));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

        	return sfView::SUCCESS;
        }

        $this->form->save();

        if(array_key_exists('retour', $_POST)) {
            $this->parcellaireAffectation->signataire = null;
        } else {
            $this->parcellaireAffectation->validate();
            $this->parcellaireAffectation->validateOdg();
        }

        $this->parcellaireAffectation->save();
        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeSaisieManquant(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->findOrCreate($request->getParameter('apporteur'), substr($this->parcellaireAffectationCoop->campagne, 0, 4));

        if($this->parcellaireManquant->isValidee()) {
            return $this->redirect('parcellaireaffectationcoop_manquant_visualisation', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireManquant->_id));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->parcellaireManquant->setParcellesFromParcellaire($request->getPostParameter('parcelles', array()));
        $this->parcellaireManquant->save();

        return $this->redirect('parcellaireaffectationcoop_manquant_saisie_infos', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireManquant->_id));
    }

    public function executeSaisieIrrigable(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($request->getParameter('apporteur'), substr($this->parcellaireAffectationCoop->campagne, 0, 4));

        if($this->parcellaireIrrigable->isValidee()) {
            return $this->redirect('parcellaireaffectationcoop_visualisation', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireIrrigable->_id));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->parcellaireIrrigable->setParcellesFromParcellaire($request->getPostParameter('parcelles', array()));
        $this->parcellaireIrrigable->save();

        return $this->redirect('parcellaireaffectationcoop_irrigable_saisie_infos', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireIrrigable->_id));
    }

    public function executeSaisieIrrigableInfos(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find($request->getParameter('id_document'));

        if($this->parcellaireIrrigable->isValidee()) {
            return $this->redirect('parcellaireaffectationcoop_irrigable_visualisation', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireIrrigable->_id));
        }

        $this->form = new ParcellaireIrrigableProduitsForm($this->parcellaireIrrigable);

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

        if(!array_key_exists('retour', $_POST)) {
            $this->parcellaireIrrigable->validate();
            $this->parcellaireIrrigable->validateOdg();
            $this->parcellaireIrrigable->save();
        } else {
            return $this->redirect('parcellaireaffectationcoop_irrigable_saisie', ['sf_subject' => $this->parcellaireAffectationCoop, 'apporteur' => $this->parcellaireIrrigable->identifiant]);
        }

        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeSaisieManquantInfos(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->find($request->getParameter('id_document'));

        if($this->parcellaireManquant->isValidee()) {
            return $this->redirect('parcellaireaffectationcoop_manquant_visualisation', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireManquant->_id));
        }

        $this->form = new ParcellaireManquantInfosForm($this->parcellaireManquant);

        if(isset($_POST['decla-manquant-a-irrigable'])) {
            return $this->redirect('parcellaireaffectationcoop_irrigable_saisie', array('id' => $this->parcellaireAffectationCoop->_id, 'apporteur' => $this->parcellaireManquant->identifiant));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

        if(!array_key_exists('retour', $_POST)) {
            $this->parcellaireManquant->validate();
            $this->parcellaireManquant->validateOdg();
            $this->parcellaireManquant->save();
        } else {
            return $this->redirect('parcellaireaffectationcoop_manquant_saisie', ['sf_subject' => $this->parcellaireAffectationCoop, 'apporteur' => $this->parcellaireManquant->identifiant]);
        }

        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeReconductionManquant(sfWebRequest $request)
    {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->apporteur = $request->getParameter('apporteur');

        $doc = ParcellaireManquantClient::getInstance()->createDoc($this->apporteur, $this->parcellaireAffectationCoop->periode);

        if ($last = ParcellaireManquantClient::getInstance()->getLast($this->apporteur)) {
            $parcellesids = array_keys($last->getParcelles());
            $doc->setParcellesFromParcellaire($parcellesids);
        }

        $doc->validate();
        $doc->validateOdg();
        $doc->save();

        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeReconductionIrrigable(sfWebRequest $request)
    {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->apporteur = $request->getParameter('apporteur');

        $doc = ParcellaireIrrigableClient::getInstance()->createDoc($this->apporteur, $this->parcellaireAffectationCoop->periode);
        if ($last = ParcellaireIrrigableClient::getInstance()->getLast($this->apporteur)) {
            $parcellesids = array_keys($last->getParcelles());
            $doc->setParcellesFromParcellaire($parcellesids);
        }

        $doc->validate();
        $doc->validateOdg();
        $doc->save();

        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($request->getParameter('id_document'));
    }

    public function executeVisualisationIrrigable(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find($request->getParameter('id_document'));
    }

    public function executeVisualisationManquant(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->find($request->getParameter('id_document'));
    }

    public function executeRecap(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        if (!$this->getUser()->isAdmin()) {
          throw new sfException("La page de recap des liaisons n'est disponible qu'en admin");

        }
    }

    public function executeExportcsv(sfWebRequest $request) {
        $parcellaireAffectationCoop = $this->getRoute()->getObject();
        $etablissement = $this->getRoute()->getEtablissement();

        $header = true;
        foreach($parcellaireAffectationCoop->getApporteursChoisis() as $apporteur) {
            $doc = $apporteur->getDeclaration(ParcellaireAffectationClient::TYPE_MODEL, acCouchdbClient::HYDRATE_DOCUMENT);
            if(!$doc) {
                continue;
            }
            if(!$doc->isValidee()) {
                continue;
            }
            $export = new ExportParcellaireAffectationCSV($doc, $header);
            $this->renderText($export->export());
            $header = false;
        }
        if ($header) {
            $this->renderText("Aucune affectation validée");
        }

        $attachement = sprintf("attachment; filename=export_affectation_parcellaire_%s_%s_%s.csv", $etablissement->identifiant, $parcellaireAffectationCoop->getPeriode(), date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );

        return sfView::NONE;
    }

    protected function getEtape($parcellaireAffectationCoop, $etape) {
        $parcellaireAffectationCoopEtapes = ParcellaireAffectationCoopEtapes::getInstance();
        if (!$parcellaireAffectationCoopEtapes->exist('etape')) {
            return $etape;
        }
        return ($parcellaireAffectationCoopEtapes->isLt($parcellaireAffectationCoopEtapes->etape, $etape)) ? $etape : $parcellaireAffectationCoopEtapes->etape;
    }

}
