<?php

class drevActions extends sfActions {

    public function executePushDR(sfWebRequest $request) {
        $this->url = $request->getParameter('url');
        $this->id = $request->getParameter('id');

        $file_path_csv = sprintf("%s/DR/%s.%s", sfConfig::get('sf_data_dir'), $this->id, "csv");
        $file_path_pdf = sprintf("%s/DR/%s.%s", sfConfig::get('sf_data_dir'), $this->id, "pdf");

        if (!file_exists($file_path_csv) || !file_exists($file_path_pdf)) {

            return $this->redirect($this->url);
        }

        $this->csv = base64_encode(file_get_contents($file_path_csv));
        $this->pdf = base64_encode(file_get_contents($file_path_pdf));
    }

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $campagne = $request->getParameter("campagne", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne);
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $campagne = $request->getParameter("campagne", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne, true);
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeEdit(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();

        $this->secure(DRevSecurity::EDITION, $drev);

        if ($drev->exist('etape') && $drev->etape) {
            return $this->redirect('drev_' . $drev->etape, $drev);
        }

        return $this->redirect('drev_exploitation', $drev);
    }

    public function executeDelete(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $etablissement = $drev->getEtablissementObject();
        $this->secure(DRevSecurity::EDITION, $drev);

        $drev->delete();
        $this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', $etablissement);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        if (!$this->getUser()->isAdmin()) {
          $this->secure(DRevSecurity::DEVALIDATION , $drev);
        }

        $drev->validation = null;
        $drev->validation_odg = null;
        $drev->add('etape', null);
        $drev->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('drev_edit', $drev));
    }

    public function executeDr(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
    }

    public function executeDrDouane(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        
        if (!$this->drev->hasDR()) {
        	$this->form = new DRevUploadDrForm(FichierClient::getInstance()->createDoc($this->drev->identifiant), array('libelle' => 'DR importée depuis la saisie de la DRev '.$this->drev->campagne));
        } else {
        	$this->form = null;
        }
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        	return sfView::SUCCESS;
        }
        
        if ($this->form) {
	        $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
	        if (!$this->form->isValid()) {
	        	return sfView::SUCCESS;
	        }
	        $fichier = $this->form->save();
	        $lienSymbolique = LienSymboliqueClient::getInstance()->createDoc('DR', $this->drev->identifiant, $this->drev->campagne, $fichier->_id);
	        $lienSymbolique->save();
        }
		$this->drev->save();
        
        return $this->redirect('drev_set_dr', $this->drev);
    }
    
    public function executeDrInDrev(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        

        $csvFile = $this->drev->getDR('csv');
        $csv = new DRDouaneCsvFile($csvFile);
        $csvContent = $csv->convert();
        $path = sfConfig::get('sf_cache_dir').'/dr/';
        $filename = 'DR-'.$this->drev->identifiant.'-'.$this->drev->campagne.'.csv';
        if (!is_dir($path)) {
        	exec('mkdir '.$path);
        }
        file_put_contents($path.$filename, $csvContent);
        $csv = new DRCsvFile($path.$filename);
        $this->drev->importCSVDouane($csv->getCsvAcheteur($this->drev->declarant->cvi));
        $this->drev->save();
    	
    }


    public function executeDrRecuperation(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $drev);

        return $this->redirect(sfConfig::get('app_url_dr_recuperation') .
                        "?" .
                        http_build_query(array(
                            'url' => $this->generateUrl('drev_dr_import', $drev, true),
                            'id' => sprintf('DR-%s-%s', $drev->identifiant, $drev->campagne))));
    }

    public function executeDrImport(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        umask(0002);
        $cache_dir = sfConfig::get('sf_cache_dir') . '/dr';
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir);
        }

        if (!$request->getParameter('csv') || !$request->getParameter('pdf')) {

            return sfView::SUCCESS;
        }

        file_put_contents($cache_dir . "/DR.csv", base64_decode($request->getParameter('csv')));
        $this->drev->storeAttachment($cache_dir . "/DR.csv", "text/csv");

        file_put_contents($cache_dir . "/DR.pdf", base64_decode($request->getParameter('pdf')));
        $this->drev->storeAttachment($cache_dir . "/DR.pdf", "application/pdf");

        $this->drev->updateFromCSV(true, true);
        $this->drev->save();

        return $this->redirect($this->generateUrl('drev_revendication', $this->drev));
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_EXPLOITATION))) {
            $this->drev->save();
        }

        $this->etablissement = $this->drev->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->drev->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->drev->storeDeclarant();
        $this->drev->save();

        if ($this->form->hasUpdatedValues() && !$this->drev->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->drev->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('drev_validation', $this->drev);
        }

        if (!$this->drev->isNonRecoltant() && !$this->drev->hasDr() && !$this->drev->isPapier()) {

            return $this->redirect('drev_dr', $this->drev);
        }

        return $this->redirect('drev_dr_douane', $this->drev);
    }

    public function executeRevendicationRecapitulatif(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->isBlocked = count($this->drev->getProduits(true)) < 1;
    }

    public function executeRevendication(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_REVENDICATION))) {
            $this->drev->save();
        }

        if ($this->drev->isNonRecoltant()) {
            if (!count($this->drev->declaration->getAppellations())) {

                return $this->redirect('drev_revendication_recapitulatif', $this->drev);
            }

            return $this->redirect('drev_revendication_cepage', $this->drev->declaration->getAppellations()->getFirst());
        }

        $this->appellation = false;
        if ($request->getParameter(('appellation'))) {
            $this->appellation = $request->getParameter(('appellation'));
            $this->appellation_field = substr(strrchr($this->appellation, '-'), 1);
            $this->appellation_hash = str_replace('-', '/', str_replace('-' . $this->appellation_field, '', $this->appellation));
        }

        $this->form = new DRevRevendicationForm($this->drev);
        $this->ajoutForm = new DRevRevendicationAjoutProduitForm($this->drev);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if (!$this->form->isValid() && $request->isXmlHttpRequest()) {
                return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
            }
            if ($this->form->isValid()) {
                $this->form->save();

                if ($request->isXmlHttpRequest()) {

                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
                }

                if ($request->getParameter('redirect', null)) {
                    return $this->redirect('drev_validation', $this->drev);
                }

                if(!DrevEtapes::getInstance()->exist(DrevEtapes::ETAPE_DEGUSTATION)) {

                    return $this->redirect('drev_vci', $this->drev);
                }

                return $this->redirect('drev_degustation_conseil', $this->drev);
            }
        }
    }

    public function executeRevendicationAjoutAppellation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->ajoutForm = new DRevAjoutAppellationForm($this->drev);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('drev_revendication', $this->drev);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('drev_revendication_cepage', $this->ajoutForm->getNoeud());
    }

    public function executeRevendicationAjoutProduit(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->ajoutForm = new DrevRevendicationAjoutProduitForm($this->drev);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('drev_revendication', $this->drev);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('drev_revendication', $this->drev);
    }

    public function executeRevendicationCepage(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->noeud = $this->drev->get("declaration/certification/genre/" . $request->getParameter("hash"));
        $this->form = new DRevRevendicationCepageForm($this->noeud);
        $this->ajoutForm = new DrevCepageAjoutProduitForm($this->noeud);
        $this->ajoutAppellationForm = new DRevRevendicationAjoutProduitForm($this->drev);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }


        if ($request->getParameter('redirect', null)) {

            return $this->redirect('drev_validation', $this->drev);
        }

        $next_sister = $this->noeud->getNextSister();

        if ($next_sister) {

            return $this->redirect('drev_revendication_cepage', $next_sister);
        } else {

            return $this->redirect('drev_revendication_recapitulatif', $this->drev);
        }
    }

    public function executeRevendicationCepageAjoutProduit(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->noeud = $this->getRoute()->getNoeud();

        $this->ajoutForm = new DrevCepageAjoutProduitForm($this->noeud);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('drev_revendication_cepage', $this->noeud);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('drev_revendication_cepage', $this->noeud);
    }

    public function executeVci(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_VCI))) {
            $this->drev->save();
        }

        $this->form = new DRevVciForm($this->drev);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('drev_validation', $this->drev);

    }

    public function executeDegustationConseil(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_DEGUSTATION))) {
            $this->drev->save();
        }

        $this->form = new DRevDegustationConseilForm($this->drev->prelevements);

        $this->formPrelevement = false;

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            $values = $request->getParameter($this->form->getName());
            if (isset($values['chai']) && $this->drev->getChaiKey(Drev::CUVE) != $values['chai']) {
                $this->formPrelevement = true;
            }
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }

        $this->drev->save();

        if ($request->getParameter('redirect', null)) {

            return $this->redirect('drev_validation', $this->drev);
        }

        if ($this->drev->prelevements->exist(Drev::CUVE_ALSACE)) {

            return $this->redirect('drev_lots', $this->drev->prelevements->get(Drev::CUVE_ALSACE));
        }

        if ($this->drev->prelevements->exist(Drev::CUVE_GRDCRU)) {

            return $this->redirect('drev_lots', $this->drev->prelevements->get(Drev::CUVE_GRDCRU));
        }

        if($this->drev->isNonConditionneur()) {

            return $this->redirect('drev_validation', $this->drev);
        }

        return $this->redirect('drev_controle_externe', $this->drev);
    }

    public function executeLots(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->prelevement = $this->getRoute()->getPrelevement();

        $this->form = new DRevLotsForm($this->prelevement);
        $this->ajoutForm = new DrevLotsAjoutProduitForm($this->prelevement);

        $this->setTemplate(lcfirst(sfInflector::camelize(strtolower(('lots_' . $this->prelevement->getKey())))));

        $this->error_produit = null;
        if ($request->getParameter(('error_produit'))) {
            $type_error = strstr($request->getParameter('error_produit'), '-', true);
            $error_produit = str_replace($type_error, '', $request->getParameter('error_produit'));
            $this->error_produit = str_replace('-', '_', $error_produit);
            if ($type_error == 'erreur') {
                $this->getUser()->setFlash("erreur", "Pour supprimer un lot, il suffit de vider la case.");
            }
            if ($type_error == 'vigilancewithFlash') {
                $this->getUser()->setFlash("warning", "Pour supprimer un lot, il suffit de vider la case.");
            }
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        if ($request->isXmlHttpRequest() && !$this->form->isValid()) {
            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }


        if ($request->getParameter('redirect', null)) {
            return $this->redirect('drev_validation', $this->drev);
        }

        if ($this->prelevement->getKey() == Drev::CUVE_ALSACE && $this->drev->prelevements->exist(Drev::CUVE_GRDCRU)) {
            return $this->redirect('drev_lots', $this->drev->prelevements->get(Drev::CUVE_GRDCRU));
        }

        if($this->drev->isNonConditionneur()) {

            return $this->redirect('drev_validation', $this->drev);
        }

        return $this->redirect('drev_controle_externe', $this->drev);
    }

    public function executeLotsAjoutProduit(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->prelevement = $this->getRoute()->getPrelevement();

        $this->ajoutForm = new DrevLotsAjoutProduitForm($this->prelevement);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');

            return $this->redirect('drev_lots', $this->prelevement);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('drev_lots', $this->prelevement);
    }

    public function executeControleExterne(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->focus = $request->getParameter("focus");

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_CONTROLE))) {
            $this->drev->save();
        }

        $this->form = new DRevControleExterneForm($this->drev->prelevements);

        $this->formPrelevement = false;

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if($request->getParameter('non_conditionneur')) {
            $this->drev->add('non_conditionneur', 1);
            if($this->drev->prelevements->exist(Drev::BOUTEILLE_ALSACE)) {
                $this->drev->prelevements->remove(Drev::BOUTEILLE_ALSACE);
                $this->drev->addPrelevement(Drev::BOUTEILLE_ALSACE);
            }

            if($this->drev->prelevements->exist(Drev::BOUTEILLE_GRDCRU)) {
                $this->drev->prelevements->remove(Drev::BOUTEILLE_GRDCRU);
                $this->drev->addPrelevement(Drev::BOUTEILLE_GRDCRU);
            }

            if($this->drev->prelevements->exist(Drev::BOUTEILLE_VTSGN)) {
                $this->drev->prelevements->remove(Drev::BOUTEILLE_VTSGN);
                $this->drev->addPrelevement(Drev::BOUTEILLE_VTSGN);
            }

            $this->drev->save();

            return $this->redirect('drev_validation', $this->drev);
        }

        $this->drev->remove('non_conditionneur', 1);

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            $values = $request->getParameter($this->form->getName());
            if (isset($values['chai']) && $this->drev->getChaiKey(Drev::BOUTEILLE) != $values['chai']) {
                $this->formPrelevement = true;
            }
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }

        $this->drev->save();

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('drev_validation', $this->drev);
        }

        return $this->redirect('drev_validation', $this->drev);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();

        $this->secure(DRevSecurity::EDITION, $this->drev);

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_VALIDATION))) {
            $this->drev->save();
        }

        $this->drev->cleanDoc();
        $this->validation = new DRevValidation($this->drev);

        $this->form = new DRevValidationForm($this->drev, array(), array('engagements' => $this->validation->getPoints(DrevValidation::TYPE_ENGAGEMENT)));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if (!$this->validation->isValide()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $documents = $this->drev->getOrAdd('documents');

        foreach ($this->validation->getPoints(DrevValidation::TYPE_ENGAGEMENT) as $engagement) {
            $document = $documents->add($engagement->getCode());
            $document->statut = (($engagement->getCode() == DRevDocuments::DOC_DR && $this->drev->hasDr()) || ($document->statut == DRevDocuments::STATUT_RECU)) ? DRevDocuments::STATUT_RECU : DRevDocuments::STATUT_EN_ATTENTE;
        }

        if($this->drev->isPapier()) {
            $this->drev->validate($this->form->getValue("date"));
        } else {
            $this->drev->validate();
        }

        if($this->getUser()->isAdmin() && $this->drev->hasCompleteDocuments()) {
            $this->drev->validateOdg();
        }

        $this->drev->save();

        if($this->getUser()->isAdmin()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            return $this->redirect('drev_visualisation', $this->drev);
        }

        $this->sendDRevValidation($this->drev);

        return $this->redirect('drev_confirmation', $this->drev);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $this->drev);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $this->drev);

        $this->service = $request->getParameter('service');

        $documents = $this->drev->getOrAdd('documents');

        if($this->getUser()->isAdmin() && $this->drev->validation && !$this->drev->validation_odg) {
            $this->validation = new DRevValidation($this->drev);
        }

        $this->form = (count($documents->toArray()) && !$this->drev->hasCompleteDocuments() && $this->getUser()->isAdmin() && $this->drev->validation && !$this->drev->validation_odg) ? new DRevDocumentsForm($documents) : null;

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('drev_visualisation', $this->drev);
    }

    public function executeValidationAdmin(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VALIDATION_ADMIN, $this->drev);

        $this->drev->validateOdg();
        $this->drev->save();

        if (!$this->drev->isPapier()) {
            $this->sendDRevConfirmee($this->drev);
        }

        $this->getUser()->setFlash("notice", "La déclaration a bien été approuvée. Un email a été envoyé au télédéclarant.");

        $service = $request->getParameter("service");

        return $this->redirect('drev_visualisation', array('sf_subject' => $this->drev, 'service' => isset($service) ? $service : null));
    }

    public function executeModificative(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();

        $drev_modificative = $drev->generateModificative();
        $drev_modificative->save();

        return $this->redirect('drev_edit', $drev_modificative);
    }

    public function executePDF(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $drev);

        if (!$drev->validation) {
            $drev->cleanDoc();
        }

        $this->document = new ExportDRevPdf($drev, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executeDrPdf(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $drev);

        $file = file_get_contents($drev->getAttachmentUri('DR.pdf'));

        if(!$file) {

            $this->forward404();
        }

        $this->getResponse()->setHttpHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="DR-%s-%s.pdf"', $drev->identifiant, $drev->campagne));
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText($file);
    }

    protected function getEtape($drev, $etape) {
        $drevEtapes = DrevEtapes::getInstance();
        if (!$drev->exist('etape')) {
            return $etape;
        }
        return ($drevEtapes->isLt($drev->etape, $etape)) ? $etape : $drev->etape;
    }

    protected function sendDRevValidation($drev) {
        $pdf = new ExportDRevPdf($drev, 'pdf', true);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->removeCache();
        $pdf->generate();
        Email::getInstance()->sendDRevValidation($drev);
    }

    protected function sendDrevConfirmee($drev) {
        Email::getInstance()->sendDrevConfirmee($drev);
    }


    protected function secure($droits, $doc) {
        if (!DRevSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}
