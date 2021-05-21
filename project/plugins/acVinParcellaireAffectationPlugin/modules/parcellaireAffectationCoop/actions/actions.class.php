<?php

class parcellaireAffectationCoopActions extends sfActions {

    public function executeApporteurs(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        $sv11 = SV11Client::getInstance()->find("SV11-".$this->etablissement->identifiant."-".$this->periode);

        if(!$sv11) {
            $sv11 = SV11Client::getInstance()->createDoc($this->etablissement->identifiant, $this->periode);
        }

        $this->form = new SV11ApporteursForm($sv11);
        $this->apporteurs = $this->form->getApporteurs();
        $this->apporteursSV11 = $this->form->getApporteursSV11();

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

        return $this->redirect('parcellaireaffectationcoop_liste', array('sf_subject' => $this->etablissement, 'periode' => $this->periode));
    }

    public function executeListe(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        $this->apporteurs = $this->etablissement->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR);
        uasort($this->apporteurs, function($e1, $e2) { return $e1->libelle_etablissement > $e2->libelle_etablissement; });
        $this->documents = array();
        foreach($this->apporteurs as $liaison) {
            $id = ParcellaireAffectationClient::TYPE_COUCHDB."-".$liaison->getEtablissementIdentifiant()."-".$this->periode;
            if(!ParcellaireAffectationClient::getInstance()->find($id, acCouchdbClient::HYDRATE_JSON)) {
                continue;
            }
            $this->documents[$liaison->id_etablissement] = $id;
        }
    }

    public function executeSaisie(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->findOrCreate($request->getParameter('apporteur'), $this->periode);

		$this->form = new ParcellaireAffectationProduitsForm($this->parcellaireAffectation);

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

        return $this->redirect('parcellaireaffectationcoop_liste', array('sf_subject' => $this->etablissement, 'periode' => $this->periode));
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getObject();
        $this->periode = $request->getParameter('periode');

        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($request->getParameter('id_document'));
    }

}
