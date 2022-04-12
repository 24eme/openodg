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

        $parcellaireAffectationCoop = ParcellaireAffectationCoopClient::getInstance()->createDoc($this->etablissement->identifiant, $this->periode);
        $parcellaireAffectationCoop->save();

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

    public function executeListe(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        if($this->parcellaireAffectationCoop->storeEtape($this->getEtape($this->parcellaireAffectationCoop, ParcellaireAffectationCoopEtapes::ETAPE_SAISIES))) {
            $this->parcellaireAffectationCoop->save();
    	}
    }

    public function executeSaisie(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->findOrCreate($request->getParameter('apporteur'), substr($this->parcellaireAffectationCoop->campagne, 0, 4));

        if($this->parcellaireAffectation->isValidee()) {

            return $this->redirect('parcellaireaffectationcoop_visualisation', array('sf_subject' => $this->parcellaireAffectationCoop, 'id_document' => $this->parcellaireAffectation->_id));
        }

		$this->form = new ParcellaireAffectationCoopSaisieForm($this->parcellaireAffectation, $this->etablissement);

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

    public function executeVisualisation(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($request->getParameter('id_document'));
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
            $doc = $apporteur->getAffectationParcellaire(acCouchdbClient::HYDRATE_DOCUMENT);
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
