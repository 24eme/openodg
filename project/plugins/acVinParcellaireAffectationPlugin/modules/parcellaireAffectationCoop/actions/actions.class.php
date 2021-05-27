<?php

class parcellaireAffectationCoopActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {

        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

        $parcellaireAffectationCoop = ParcellaireAffectationCoopClient::getInstance()->createDoc($this->etablissement->identifiant, $this->periode);
        $parcellaireAffectationCoop->save();

        return $this->redirect('parcellaireaffectationcoop_apporteurs', $parcellaireAffectationCoop);
    }

    public function executeApporteurs(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

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
    }

    public function executeSaisie(sfWebRequest $request) {
        $this->parcellaireAffectationCoop = $this->getRoute()->getObject();
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->findOrCreate($request->getParameter('apporteur'), substr($this->parcellaireAffectationCoop->campagne, 0, 4));

		$this->form = new ParcellaireAffectationCoopSaisieForm($this->parcellaireAffectation, $this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

        	return sfView::SUCCESS;
        }

        $this->form->save();

        $this->parcellaireAffectation->validate();
        $this->parcellaireAffectation->save();

        return $this->redirect('parcellaireaffectationcoop_liste', $this->parcellaireAffectationCoop);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($request->getParameter('id_document'));
    }

    public function executeExportcsv(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        $this->apporteurs = $this->etablissement->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR);
        $header = true;

        foreach($this->apporteurs as $liaison) {
            $doc = ParcellaireAffectationClient::getInstance()->find(ParcellaireAffectationClient::TYPE_COUCHDB."-".$liaison->getEtablissementIdentifiant()."-".$this->periode);
            if(!$doc) {
                continue;
            }
            $export = new ExportParcellaireAffectationCSV($doc, $header);
            $this->renderText($export->export());
            $header = false;
        }

        $attachement = sprintf("attachment; filename=export_affectation_parcellaire_%s_%s_%s.csv", $this->etablissement->identifiant, $this->periode, date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );

        return sfView::NONE;
    }

}
