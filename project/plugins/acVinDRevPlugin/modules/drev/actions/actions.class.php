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

    public function executeSocieteChoixEtablissement(sfWebRequest $request) {
      $usurpation = $request->getParameter('usurpation',null);
      $login = $request->getParameter('login',null);
      if($usurpation && $login){
          $this->getUser()->usurpationOn($login, $request->getReferer());
      }
      $this->etablissement = $this->getRoute()->getEtablissement();
      $this->societe = $this->etablissement->getSociete();
      $this->form = new SocieteEtablissementChoiceForm($this->etablissement);

      if ($request->isMethod(sfWebRequest::POST)) {
          $parameters = $request->getParameter($this->form->getName());
          $this->form->bind($parameters);
          if ($this->form->isValid()) {
              $values = $this->form->getValues();
              $etablissementId = $values['etablissementChoice'];
              if (!$etablissementId) {
                  throw new sfException("L'établissement n'a pas été choisi");
              }
              $etablissement = EtablissementClient::getInstance()->findByIdentifiant($etablissementId);
              if (!$etablissement) {
                  throw new sfException("L'établissement n'existe plus dans la base de donné");
              }
              $this->redirect('declaration_etablissement', array('identifiant' => $etablissementId));
          }
       }
    }


    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentYearPeriode());
        $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $periode);
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentYearPeriode());
        $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $periode, true);
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

        return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $drev->campagne));
    }

    public function executeDevalidation(sfWebRequest $request) {

        $drev = $this->getRoute()->getDRev();
        if (!$this->getUser()->isAdmin()) {
          $this->secure(DRevSecurity::DEVALIDATION , $drev);
        }

        if($drev->hasLotsUtilises()) {
            throw new Exception("Dévalidation impossible car des lots dans cette déclaration sont utilisés");
        }

        if(!$drev->isMaster()) {
            throw new Exception("Dévalidation impossible car cette déclaration n'est pas la dernière version");
        }

        $drev->validation = null;
        $drev->validation_odg = null;
        foreach ($drev->getProduits() as $produit) {
          if($produit->exist('validation_odg') && $produit->validation_odg){
            $produit->validation_odg = null;
          }
        }
        $drev->add('etape', null);
        $drev->devalidate();
        $drev->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('drev_edit', $drev));
    }

    public function executeDr(sfWebRequest $request) {
    	$this->drev = $this->getRoute()->getDRev();
    	$this->secure(DRevSecurity::EDITION, $this->drev);

        if ($this->drev->hasDocumentDouanier()) {
            return $this->redirect('drev_revendication_superficie', $this->drev);
        }
        try {
            $imported = $this->drev->resetAndImportFromDocumentDouanier();
        } catch (Exception $e) {
            $message = 'Le fichier que vous avez importé ne semble pas contenir les données attendus ('.$e->getMessage().').';
            if($this->drev->getDocumentDouanierType() != DRCsvFile::CSV_TYPE_DR) {
                $message .= "<br/> Pour les SV11 et les SV12 veillez à bien utiliser le fichier organisé par apporteurs/fournisseurs (et non que celui organisé par produit).";
            }
            $this->getUser()->setFlash('error', $message);

            return $this->redirect('drev_dr_upload', $this->drev);
        }

        if($imported) {
            $this->drev->save();
        }
    }

    public function executeScrapeDr(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        try {
          	FichierClient::getInstance()->scrapeAndSaveFiles($this->drev->getEtablissementObject(), $this->drev->getDocumentDouanierType(), $this->drev->periode);
        } catch(Exception $e) {
        }

        if (!$this->drev->hasDocumentDouanier()) {

            return $this->redirect('drev_dr_upload', $this->drev);
        }

        $this->drev->resetAndImportFromDocumentDouanier();
        $this->drev->save();

        return $this->redirect('drev_revendication_superficie', $this->drev);
    }

    public function executeDrUpload(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        $client = $this->drev->getDocumentDouanierClient();
        if (!$client) {
        	throw new sfException('Client not found');
        }

        $fichier = $client->findByArgs($this->drev->identifiant, $this->drev->periode);

        if(!$fichier) {
            $fichier = $client->createDoc($this->drev->identifiant, $this->drev->periode);
        }
        $fichier->libelle = 'Données de Récolte importées depuis la saisie de la DRev '.$this->drev->periode;
        $this->form = new DRevUploadDrForm($fichier, array('libelle' => 'Données de Récolte importées depuis la saisie de la DRev '.$this->drev->periode), array("papier" => $this->drev->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {
        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
	    }

        if (!$this->form->getValue('file')) {

        	return $this->redirect('drev_revendication_superficie', $this->drev);
        }

        try {
            $this->form->save();
            if (!$this->drev->resetAndImportFromDocumentDouanier()) {
                throw new sfException("Mauvais format");
            }
        } catch(Exception $e) {
            if($this->form->getFichier()) {
                $this->form->getFichier()->delete();
            }

            $message = 'Le fichier que vous avez importé ne semble pas contenir les données attendus ('.$e->getMessage().').';

            if($this->drev->getDocumentDouanierType() != DRCsvFile::CSV_TYPE_DR) {
                $message .= " Pour les SV11 et les SV12 veillez à bien utiliser le fichier organisé par apporteurs/fournisseurs (et non celui organisé par produit).";
            }

            $this->getUser()->setFlash('error', $message);

            return $this->redirect('drev_dr_upload', $this->drev);
        }
	    $this->drev->save();

        return $this->redirect('drev_revendication_superficie', $this->drev);
    }

    public function executeDrRecuperation(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $drev);

        return $this->redirect(sfConfig::get('app_url_dr_recuperation') .
                        "?" .
                        http_build_query(array(
                            'url' => $this->generateUrl('drev_dr_import', $drev, true),
                            'id' => sprintf('DR-%s-%s', $drev->identifiant, $drev->periode))));
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_EXPLOITATION))) {
            $this->drev->save();
        }

        $this->etablissement = $this->drev->getEtablissementObject();

        $this->form = new EtablissementForm($this->drev->declarant, array("use_email" => !$this->drev->isPapier()));

        $this->denominationAutoForm = new DRevDenominationAutoForm($this->drev);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        if (DrevConfiguration::getInstance()->hasExploitationSave()) {
          $this->form->save();
        }

        if ($this->form->hasUpdatedValues() && !$this->drev->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->drev->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
        }

        if ($request->getParameter('redirect', null)) {
            return $this->redirect('drev_validation', $this->drev);
        }


        if(!DrevConfiguration::getInstance()->isDrDouaneRequired() && !$request->getParameter('import_dr_prodouane')){
          	return $this->redirect('drev_revendication_superficie', $this->drev);
        }

        return $this->redirect('drev_dr', $this->drev);
    }

    public function executeDenominationAuto(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->form = new DRevDenominationAutoForm($this->drev);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->drev->add('denomination_auto',$this->form->getValue('denomination_auto'));
                $this->drev->save();
                return $this->redirect('drev_exploitation', $this->drev);
            }

        }


        return $this->redirect('drev_exploitation', $this->drev);
    }

    private function needDrDouane() {
        if(!DrevConfiguration::getInstance()->isDrDouaneRequired()){
          return false;
        }
        return (!$this->drev->hasDocumentDouanier() && ($this->drev->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) && !$this->drev->isPapier());
    }

    public function executeRevendicationSuperficie(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();

        if(DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE, $this->drev)) {
            if ($request->getParameter('prec')) {
                return $this->redirect('drev_dr', $this->drev);
            }else{
                return $this->redirect('drev_vci', $this->drev);
            }
        }

        if (DrevConfiguration::getInstance()->hasEtapeSuperficie() === false) {
            return $this->redirect('drev_vci', $this->drev);
        }

        $this->secure(DRevSecurity::EDITION, $this->drev);
        if ($this->needDrDouane() && !$this->getUser()->isAdmin()) {
        	return $this->redirect('drev_dr_upload', $this->drev);
        }

        if (!count($this->drev->declaration)) {
            $this->drev->resetAndImportFromDocumentDouanier();
        }

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE))) {
            $this->drev->save();
        }

        $this->ajoutForm = new DRevRevendicationAjoutProduitForm($this->drev);
        $this->form = new DRevSuperficieForm($this->drev, array('disabled_dr' => true));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid() && $request->isXmlHttpRequest()) {
               return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }
        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

           return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
       }

        if ($this->drev->exist('etape') && $this->drev->etape == DrevEtapes::ETAPE_VALIDATION) {

            return $this->redirect('drev_validation', $this->drev);
        }

        return $this->redirect('drev_vci', $this->drev);

    }

    public function executeResetVolumes(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        foreach ($this->drev->getProduits() as $prod) {
            if(!$prod->canCalculTheoriticalVolumeRevendiqueIssuRecolte()) {
                $prod->volume_revendique_issu_recolte = null;
                continue;
            }

            $prod->volume_revendique_issu_recolte = $prod->getTheoriticalVolumeRevendiqueIssuRecole();
        }
        $this->drev->save();
        return $this->redirect('drev_revendication', $this->drev);
    }

    public function executeLots(sfWebRequest $request)
    {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        $this->isAdmin = $this->getUser()->isAdmin();

        if ($this->needDrDouane()) {

            return $this->redirect('drev_dr_upload', $this->drev);
        }
        $has = false;
        if(count($this->drev->getLots())){
            $has = true;
        }

        if(!$has && !count($this->drev->getProduitsLots()) && !$request->getParameter('prec') && !$this->drev->isModificative() && DrevConfiguration::getInstance()->isDrDouaneRequired()) {

            return $this->redirect('drev_revendication', $this->drev);
        }

        if(!$has && !count($this->drev->getProduitsLots()) && $request->getParameter('prec') && DrevConfiguration::getInstance()->isDrDouaneRequired()) {

            return $this->redirect('drev_vci', array('sf_subject' => $this->drev, 'prec' => 1));
        }

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_LOTS))) {
            $this->drev->save();
        }

        if (count($this->drev->getLots()) == 0 || current(array_reverse($this->drev->getLots()->toArray()))->produit_hash != null || $request->getParameter('submit') == "add") {
            $this->drev->addLot();
        }
        $this->form = new DRevLotsForm($this->drev);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($request->getParameter('submit') == 'add') {
            return $this->redirect($this->generateUrl('drev_lots', $this->drev).'#dernier');
        }

        if($this->drev->isModificative()) {
          return $this->redirect('drev_validation', $this->drev);
        }

        return $this->redirect('drev_revendication', $this->drev);
    }

    public function executeDrevDeleteLot(sfWebRequest $request){
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $lot = $this->drev->getLot($request->getParameter('unique_id'));
        LotsClient::getInstance()->deleteAndSave($lot->declarant_identifiant, $lot->unique_id);

        return $this->redirect('drev_lots', $this->drev);

    }

    public function executeRevendication(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        if($this->drev->isModificative() && !$this->getUser()->hasDrevAdmin()){
            throw new sfException("Il est impossible d'acceder à une Drev modificatrice pour les volumes revendiquées si vous n'êtes pas administrateur.");
        }

        if(DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_REVENDICATION, $this->drev)) {
            if ($request->getParameter('prec')) {
                return $this->redirect('drev_lots', $this->drev);
            }
            return $this->redirect('drev_validation', $this->drev);
        }

        if ($this->needDrDouane()) {

        	return $this->redirect('drev_dr_upload', $this->drev);
        }

        if(!count($this->drev->getProduitsWithoutLots()) && !$request->getParameter('prec')) {

            return $this->redirect('drev_validation', $this->drev);
        }
        $produits = $this->drev->getProduitsLots();


        if(!count($this->drev->getProduitsWithoutLots()) && $request->getParameter('prec')) {

            return $this->redirect('drev_lots', $this->drev);
        }

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_REVENDICATION))) {
            $this->drev->save();
        }

        $this->appellation = false;
        if ($request->getParameter(('appellation'))) {
            $this->appellation = $request->getParameter(('appellation'));
            $this->appellation_field = substr(strrchr($this->appellation, '-'), 1);
            $this->appellation_hash = str_replace('-', '/', str_replace('-' . $this->appellation_field, '', $this->appellation));
        }

        $this->form = new DRevRevendicationForm($this->drev, array('disabled_dr' => true));
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

                return $this->redirect('drev_validation', $this->drev);
            }
        }
    }

    public function executeRevendicationAjoutProduit(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        $this->ajoutForm = new DrevRevendicationAjoutProduitForm($this->drev);
        $this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if (!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("error", 'Une erreur est survenue.');

            return $this->redirect('drev_revendication_superficie', $this->drev);
        }

        $this->ajoutForm->save();

        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');

        return $this->redirect('drev_revendication_superficie', $this->drev);
    }

    public function executeRevendicationProduitDenominationAuto(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        $this->produit = $this->drev->get(str_replace('__', '/', $request->getParameter('hash')));

        $this->form = new DrevRevendicationProduitDenominationAutoForm($this->produit);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            $this->getUser()->setFlash("error", 'Une erreur est survenue.');

            return $this->redirect('drev_revendication_superficie', $this->drev);
        }

        $this->form->save();

        return $this->redirect('drev_revendication_superficie', $this->drev);
    }

    public function executeRevendicationCepageSuppressionProduit(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        $this->hash = str_replace('__', '/', $request->getParameter('hash'));
        if ($this->hash) {
        	$this->drev->remove($this->hash);
        	$this->drev->save();
        }
        return $this->redirect('drev_revendication_superficie', $this->drev);
    }

    public function executeVci(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);

        if(DrevEtapes::getInstance()->isEtapeDisabled(DrevEtapes::ETAPE_VCI, $this->drev) || !count($this->drev->getProduitsVci()) ) {

            if($request->getParameter('prec')) {

                return $this->redirect('drev_revendication_superficie', $this->drev);
            }

            if(count($this->drev->declaration->getProduitsLots()) > 0 || ConfigurationClient::getCurrent()->declaration->isRevendicationParLots()) {

                return $this->redirect('drev_lots', $this->drev);
            }

            return $this->redirect('drev_revendication', $this->drev);
        }

        if ($this->needDrDouane()) {

        	return $this->redirect('drev_dr_upload', $this->drev);
        }

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_VCI))) {
            $this->drev->save();
        }

        $this->form = new DRevVciForm($this->drev);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid() && $request->isXmlHttpRequest()) {
               return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
        }

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

           return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drev->_id, "revision" => $this->drev->_rev))));
       }

        if ($this->drev->exist('etape') && $this->drev->etape == DrevEtapes::ETAPE_VALIDATION) {

            return $this->redirect('drev_validation', $this->drev);
        }

        if(count($this->drev->declaration->getProduitsLots()) > 0 || ConfigurationClient::getCurrent()->declaration->isRevendicationParLots()){
            return $this->redirect('drev_lots', $this->drev);
        }

        return $this->redirect('drev_revendication', $this->drev);

    }

    public function executeValidation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::EDITION, $this->drev);
        $this->isAdmin = $this->getUser()->isAdmin();

        $this->vip2c = VIP2C::gatherInformations($this->drev, $this->drev->getPeriode());

        if ($this->needDrDouane()) {

        	return $this->redirect('drev_dr_upload', $this->drev);
        }

        if($this->drev->storeEtape($this->getEtape($this->drev, DrevEtapes::ETAPE_VALIDATION))) {
            $this->drev->save();
        }

        $this->drev->cleanDoc();

        $this->validation = new DRevValidation($this->drev);

        $this->form = new DRevValidationForm($this->drev, array(), array('isAdmin' => $this->isAdmin, 'engagements' => $this->validation->getEngagements()));
        $this->dr = DRClient::getInstance()->findByArgs($this->drev->identifiant, $this->drev->periode);
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if (!$this->validation->isValide() && $this->drev->isTeledeclare() && !$this->getUser()->hasDrevAdmin()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->form->save();

        if (count($this->validation->getEngagements())) {
            $this->drev->remove('documents');
            $documents = $this->drev->getOrAdd('documents');

            foreach ($this->validation->getEngagements() as $engagement) {
                if(!$this->form->getValue("engagement_".$engagement->getCode())) {
                    continue;
                }
                $key = $engagement->getCode();
                if ($addInfo = $engagement->getAdditionalInfo()) {
                    $key .= '_'.$addInfo;
                }
                $document = $documents->add($key);
                $document->libelle = $engagement->getMessage();
                if($engagement->getInfo()) {
                    $document->libelle .= " : ".$engagement->getInfo();
                }
                $document->statut = DRevDocuments::getStatutInital($engagement->getCode());
            }
        }

        if (DrevConfiguration::getInstance()->hasDegustation()) {
            $this->drev->setDateDegustationSouhaitee($this->form->getValue('date_degustation_voulue'));
        }

        $this->drev->validate(date('c'));
        $this->drev->cleanLots();
        $this->drev->save();
        if(!$this->getUser()->hasDrevAdmin()){
          {
            $this->getUser()->setFlash("notice", "La déclaration de revendication a été validée, elle devra être approuvée par l'ensemble des ODG concernées");

            return $this->redirect('drev_visualisation', $this->drev);
          }
        }

        if($this->getUser()->hasDrevAdmin() && $this->drev->isPapier()) {
            $this->drev->validateOdg(null, $this->getUser()->getRegion());
            $this->drev->cleanLots();
            $this->drev->save();

            if (!DrevConfiguration::getInstance()->hasEmailDisabled()) {
                $nbSent = Email::getInstance()->sendDRevValidation($this->drev);
            }

            if($nbSent > 0) {
                $this->getUser()->setFlash("notice", "La déclaration de revendication papier a été validée et approuvée, un email a été envoyé au déclarant");
            }

            return $this->redirect('drev_visualisation', $this->drev);
        }

        if($this->getUser()->hasDrevAdmin()){
          $this->drev->validateOdg(null, $this->getUser()->getRegion());
          $this->drev->save();
          $this->getUser()->setFlash("notice", "La déclaration de revendication a été validée et approuvée");

          return $this->redirect('drev_visualisation', $this->drev);
        }

        if(DrevConfiguration::getInstance()->hasValidationOdgAuto() && !$this->validation->hasPoints()) {
            $this->drev->validateOdg();
            $this->drev->save();
        }

        if (!DrevConfiguration::getInstance()->hasEmailDisabled()) {
            Email::getInstance()->sendDRevValidation($this->drev);
        }

        return $this->redirect('drev_confirmation', $this->drev);
    }

    public function executeValidationAdmin(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(array(DRevSecurity::VALIDATION_ADMIN), $this->drev);
        $this->regionParam = $request->getParameter('region',null);
        if (!$this->regionParam && $this->getUser()->getRegion()) {
            $this->regionParam = $this->getUser()->getRegion();
        }

        $service = $request->getParameter("service", null);
        $params = array('sf_subject' => $this->drev, 'service' => $service);
        if($this->regionParam){
          $params = array_merge($params,array('region' => $this->regionParam));
        }

        try {
            $this->drev->validateOdg(null,$this->regionParam);
            $this->drev->save();
        }catch(sfException $s) {
            $this->getUser()->setFlash('error', $s->getMessage());
            return $this->redirect('drev_visualisation', $params);
        }

        $mother = $this->drev->getMother();
        while ($mother) {
            $mother->validateOdg(null, $this->regionParam);
            $mother->save();
            $mother = $mother->getMother();
        }

        if($this->drev->validation_odg && !DrevConfiguration::getInstance()->hasEmailDisabled()) {
            $nbSent = Email::getInstance()->sendDRevValidation($this->drev);
            if($nbSent > 0) {
                $this->getUser()->setFlash("notice", "La déclaration a été approuvée. Un email a été envoyé au télédéclarant.");
            }
        }

        return $this->redirect('drev_visualisation', $params);
    }

    public function executeEnattenteAdmin(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(array(DRevSecurity::VALIDATION_ADMIN), $this->drev);
        $this->regionParam = $request->getParameter('region',null);

        if (!$this->drev->isValidee()) {
            throw sfException("Une DREV non validée ne peut être mise en attente");
        }

        if ($this->drev->isValideeOdg($this->regionParam)) {
            throw sfException("Une DREV validée par une région ne peut être mise en attente par celle-ci");
        }

        if ($this->drev->isMiseEnAttenteOdg()) {
            $this->drev->remove('statut_odg');
        }else{
            $this->drev->setStatutOdgByRegion(DRevClient::STATUT_EN_ATTENTE, $this->regionParam);
        }
        $this->drev->save();

        $service = $request->getParameter("service", null);
        $params = array('sf_subject' => $this->drev, 'service' => $service);
        if($this->regionParam){
          $params = array_merge($params,array('region' => $this->regionParam));
        }
        return $this->redirect('drev_visualisation', $params);

    }


    public function executeConfirmation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $this->drev);
    }

    public function executeGenerateMouvementsFactures(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $this->drev);

        if(count($this->drev->mouvements)) {

            return $this->redirect('drev_visualisation', $this->drev);
        }

        $this->drev->generateMouvementsFactures();
        $this->drev->save();

        $this->getUser()->setFlash('notice', 'Les mouvements ont été générés');

        return $this->redirect('drev_visualisation', $this->drev);
    }


    public function executeVip2c(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $drev);
        $vip2c = VIP2C::gatherInformations($drev, $drev->getPeriode());
        header('Content-Type: application/json');
        echo json_encode($vip2c);
        exit;
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VISUALISATION, $this->drev);

        $this->isAdmin = $this->getUser()->isAdminODG();
        $this->service = $request->getParameter('service');
        if (!$this->drev->validation) {
            $this->drev->cleanDoc();
        }

        $documents = $this->drev->getOrAdd('documents');
        $this->regionParam = $request->getParameter('region',null);
        if (!$this->regionParam && $this->getUser()->getRegion()) {
            $this->regionParam = $this->getUser()->getRegion();
        }

        $this->vip2c = VIP2C::gatherInformations($this->drev, $this->drev->getPeriode());

        $this->form = null;
        if($this->getUser()->hasDrevAdmin() || $this->drev->validation) {
            $this->validation = new DRevValidation($this->drev);
            $this->form = new DRevValidationForm($this->drev, array(), array('isAdmin' => $this->isAdmin, 'engagements' => $this->validation->getEngagements()));
        }

        if($this->isAdmin) {
            $this->drevCommentaireValidationForm = new DRevCommentaireValidationForm($this->drev);
        }
        $this->drev->declaration->cleanNode();

        $this->dr = DRClient::getInstance()->findByArgs($this->drev->identifiant, $this->drev->periode);

        if (!$request->isMethod(sfWebRequest::POST)) {
          return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin && $this->drev->isValidee() && $this->drev->isValideeODG() === false){
          return $this->redirect('drev_validation_admin', $this->drev);
        }

        return $this->redirect('drev_visualisation', $this->drev);
    }

    public function executeUpdateCommentaire(sfWebRequest $request)
    {
        $this->drev = $this->getRoute()->getDRev();
        $this->secure(DRevSecurity::VALIDATION_ADMIN, $this->drev);

        $this->drevCommentaireValidationForm = new DRevCommentaireValidationForm($this->drev);
        $this->drevCommentaireValidationForm->bind($request->getParameter($this->drevCommentaireValidationForm->getName()));

        if (! $this->drevCommentaireValidationForm->isValid()) {
            return $this->redirect('drev_visualisation', $this->drev);
        }

        if($this->drevCommentaireValidationForm->getValue("commentaire")) {
            $this->drev->commentaire = $this->drevCommentaireValidationForm->getValue("commentaire");
        }

        $this->drev->save();
        return $this->redirect('drev_visualisation', $this->drev);
    }


        public function executeSwitchEleve(sfWebRequest $request) {
            $docid = $request->getParameter('id');
            $doc = acCouchdbManager::getClient()->find($docid);
            $this->forward404Unless($doc);
            $lot_unique_id = $request->getParameter('unique_id');
            $lot = $doc->getLot($lot_unique_id);
            $this->forward404Unless($lot);

            $lot->switchEleve();

            $doc->generateMouvementsLots();
            $doc->save();

            return $this->redirect("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));
        }

    public function executeModificative(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();

        $drev_modificative = $drev->generateModificative();
        $drev_modificative->save();
        if(ConfigurationClient::getCurrent()->declaration->isRevendicationParLots()){
          return $this->redirect('drev_exploitation', $drev_modificative);
        }

        return $this->redirect('drev_edit', $drev_modificative);
    }

    public function executePDF(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev(['allow_habilitation' => true, 'allow_stalker' => true]);
        $this->secure(DRevSecurity::PDF, $drev);

        if (!$drev->validation) {
            $drev->cleanDoc();
        }

        if ($numero_dossier = $request->getParameter('numero_dossier', null)) {
            $drev = $drev->cloneDRevForOneDossier($numero_dossier);
        }

        $this->document = new ExportDRevPDF($drev, $this->getRequestParameter('region', null), $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executeXML(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $region = $request->getParameter('region');
        $this->secure(DRevSecurity::VISUALISATION, $drev);
        if (!$drev->validation) {
            $drev->cleanDoc();
        }
		$xml = $this->getPartial('drev/xml', array('drev' => $drev, 'region' => $region));
        $this->getResponse()->setContentType('text/xml');
        if (!$region) {
            $region = 'TOUT';
        }
        return $this->renderText($xml);
    }

    public function executeSendoi(sfWebRequest $request) {

    	$drev = $this->getRoute()->getDRev();
    	$this->secure(DRevSecurity::VISUALISATION, $drev);
      $drevOi = new DRevOI($drev, null);
      $drevOi->send();

    	return $this->redirect('drev_visualisation', $drev);
    }

    public function executeDocumentDouanier(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();

        $fileContent = file_get_contents($drev->getDocumentDouanierFile('pdf'));
        $extension = 'pdf';

        if(!$fileContent) {
            $fileContent = file_get_contents($drev->getDocumentDouanierFile('xls'));
            $extension = 'xls';
        }

        if(!$fileContent) {
            $fileContent = file_get_contents($drev->getDocumentDouanierFile('csv'));
            $extension = 'csv';
        }

        if(!$fileContent) {

            return $this->forward404();
        }

        $this->getResponse()->setHttpHeader('Content-Type', 'application/'.$extension);
        $this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="'.$drev->getDocumentDouanierType().'-%s-%s.'.$extension.'"', $drev->identifiant, $drev->periode));
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText($fileContent);
    }

    public function executeUpdateFromDocumentDouanier(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        $drev->resetAndImportFromDocumentDouanier();
        $drev->save();
        return $this->redirect('drev_visualisation', $drev);
    }

    public function executeMain()
    {
    }

    protected function getEtape($drev, $etape) {
        $drevEtapes = DrevEtapes::getInstance();
        if (!$drev->exist('etape')) {
            return $etape;
        }
        return ($drevEtapes->isLt($drev->etape, $etape)) ? $etape : $drev->etape;
    }

    protected function secure($droits, $doc) {
        if ($droits == DRevSecurity::EDITION) {
            return $this->forward404Unless($doc && !$doc->validation);
        }
        if (!DRevSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {
            throw new sfError403Exception($etablissement->_id." n'a pas les droits pour accéder à la DREV");
            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

    protected function checkIfAffecte($drev)
    {
        $lotsAffectes = [];
        foreach ($drev->getLots() as $lot) {
            if ($lot->isAffecte() || $lot->isChange()) {
                $lotsAffectes[] = $lot->getHash();
            }
        }
        if (count($lotsAffectes) > 0) {
            throw new Exception('Les lots suivants de la DREV '.$drev->_id.' sont affectés : '.implode(', ', $lotsAffectes));
        }
    }

    public function executeDeclarvapi(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        print_r([VIP2C::getContratsAPIURL($drev->declarant->cvi, $drev->getDefaultMillesime()), VIP2C::getContratsFromAPI($drev->declarant->cvi, $drev->getDefaultMillesime())]);
        exit;
    }

}
