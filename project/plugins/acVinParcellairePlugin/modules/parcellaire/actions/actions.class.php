<?php

class parcellaireActions extends sfActions {
    public function executeIndex(sfWebRequest $request)
    {
        if(class_exists("EtablissementChoiceForm")) {
            $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        } elseif(class_exists("LoginForm")) {
            $this->form = new LoginForm();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('parcellaire_declarant', $this->form->getValue('etablissement'));
    }


    public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {

            return $this->redirect('parcellaire');
        }

        return $this->redirect('parcellaire_declarant', $form->getEtablissement());
    }

    public function executeDeclarant(sfWebRequest $request) {
        $this->secureTeledeclarant();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant);
        if(class_exists("EtablissementChoiceForm")) {
            $this->form = new EtablissementChoiceForm(sfConfig::get('app_interpro', 'INTERPRO-declaration'), array('identifiant' => $this->etablissement->identifiant), true);
        }
        $this->setTemplate('parcellaire');
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->forward404Unless(method_exists($this->getRoute(), 'getParcellaire'));
        $this->secureTeledeclarant();
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->setTemplate('parcellaire');
    }

    public function executeScrape(sfWebRequest $request)
    {
        $this->secureTeledeclarant();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->noscrape = $request->getParameter('noscrape', false);

        if($request->getParameter('url')) {
            $this->url = $request->getParameter('url');
        }
    }

    public function executeImport(sfWebRequest $request)
    {
        $this->secureTeledeclarant();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $parcellaire_client = ParcellaireClient::getInstance();
        $this->noscrape = $request->getParameter('noscrape', false);

        try {
            $errors = [];
            $errors['csv'] =  '';
            $errors['json'] = '';

            $msg = '';

            if (! $parcellaire_client->saveParcellaire($this->etablissement, $errors, null, !($this->noscrape)) ) {
                $msg = $errors['csv'].'\n'.$errors['json'];
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
        }

        if (! empty($msg)) {
            $this->getUser()->setFlash('error', $msg);
        }else{
            $this->getUser()->setFlash('success_import', "La mise à jour a été un succès.");
        }

        if($request->getParameter('url')) {

            return $this->redirect($request->getParameter('url'));
        }

        $this->redirect('parcellaire_declarant', $this->etablissement);
    }

    public function executeParcellairePDF(sfWebRequest $request) {
        $this->secureTeledeclarant();
        
        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/pdf");
        header("Content-disposition: ".sprintf('attachment; filename="PARCELLAIRE-%s-%s.pdf"', $parcellaire->identifiant, $parcellaire->date));
        header("Content-Transfer-Encoding: binary");
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");
        $this->content = $parcellaire->getParcellairePDF();
        echo $this->content;
        exit;
    }

    public function executeParcellaireExportCSV(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/csv; charset=iso-8859-1");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.csv"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        $csv = new ExportParcellaireCSV($parcellaire);
        echo iconv("UTF-8", "ISO-8859-1//TRANSLIT", $csv->export());

        exit;
    }

    public function executeParcellaireExportODS(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/vnd.oasis.opendocument.spreadsheet; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.ods"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        $ods = new ExportParcellaireControleODS($parcellaire);
        echo $ods->create();

        exit;
    }

    public function executeParcellaireExportPPODS(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/vnd.oasis.opendocument.spreadsheet; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-PP-%s-%s.ods"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        $ods = new ExportParcellairePPODS($parcellaire);
        echo $ods->create();

        exit;
    }

    public function executeParcellaireExportPPPDF(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/pdf; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-PP-%s-%s.pdf"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        $ods = new ExportParcellairePPODS($parcellaire);
        echo $ods->createPDF();

        exit;
    }
    public function executeParcellaireExportGeo(sfWebRequest $request) {
        $this->secureTeledeclarant();
        
        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/vnd.google-earth.kml+xml");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.kml"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        echo $parcellaire->getKML();

        exit;        
    }


    public function secureTeledeclarant() {
        if(!$this->getUser()->isAdmin() && !$this->getUser()->isStalker() && (!class_exists("SocieteConfiguration") || !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration())) {
            throw new sfError403Exception();
        }
    }
}
