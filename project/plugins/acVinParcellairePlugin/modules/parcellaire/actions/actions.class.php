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
        $this->noscrape = $request->getParameter('noscrape', false);

        try {
            $errors = [];
            $errors['csv'] =  '';
            $errors['json'] = '';

            $msg = '';

            if (! ParcellaireClient::getInstance()->saveParcellaire($this->etablissement, $errors, null, !($this->noscrape)) ) {
                $msg = $errors['csv'].'\n'.$errors['json'];
            }
        } catch (Exception $e) {
            if (sfConfig::get('sf_environment') == 'dev') {
                throw $e;
            }
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

    public function executeCalculPPForm(sfWebRequest $request){
        $this->form = new ParcellaireCalculPPForm();

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        header("Content-Type: application/pdf; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-PP-%s.pdf"', time()));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        $dgc =  $this->form['dgc']->getValue();
        $cepages = [];
        foreach ($this->form as $key => $cepage) {
            if ($key == 'dgc') {
                continue;
            }
            $cepages[$key] = $cepage->getValue();
        }
        $ods = new ExportCalculPPODS($dgc, $cepages);
        echo $ods->createPDF();

        exit;
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

        $ods = new ExportParcellairePotentielProductionODS($parcellaire);
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

        $ods = new ExportParcellairePotentielProductionODS($parcellaire);
        echo $ods->createPDF();

        exit;
    }
    public function executeParcellaireExportKML(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        $has_parcelles = $request->getParameter('with_parcelles', true);
        $has_aires = $request->getParameter('with_aires', true);

        $type = '';
        if ($has_parcelles) {
            $type = 'parcelles';
        }
        if ($has_aires) {
            if ($type) {
                $type .= '-et-';
            }
            $type .= 'aires';
        }

        header("Content-Type: application/vnd.google-earth.kml+xml");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s-%s.kml"', $parcellaire->identifiant, $parcellaire->date, $type));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        echo $parcellaire->getKML($has_aires, $has_parcelles);

        exit;
    }

    public function executeParcellaireExportGeoJson(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/vnd.geo+json");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.geojson"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        echo json_encode($parcellaire->getGeoJsonWithAires());

        exit;
    }


    public function secureTeledeclarant() {
        if(!$this->getUser()->isAdmin() && !$this->getUser()->isStalker() && (!class_exists("SocieteConfiguration") || !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration())) {
            throw new sfError403Exception();
        }
    }

    private function addemptycepage($original, $keys, $value = 0) {
        foreach(array_keys($keys) as $k) {
            if (!isset($original[$k])) {
                $original[$k] = $keys[$k] * $value;
            }else{
                $original[$k] += $keys[$k] * $value;
            }
        }
        ksort($original);
        return $original;
    }
    public function executePotentieldeproduction(sfWebRequest $request) {
        $this->secureTeledeclarant();

        $parcellaire = $this->getRoute()->getParcellaire();
        $synthese = $parcellaire->getSyntheseProduitsCepages();


        $rewrite = $request->getParameter('rewrite');
        if ($rewrite) {
            $c = explode(':', $rewrite);
            $synthese['Côtes de Provence Rouge'][$c[0]]['superficie_max'] = floatval($c[1]);
        }


        $cepages_principaux = [];
        foreach(['GRENACHE N', 'SYRAH N', 'MOURVEDRE N', 'TIBOUREN N', 'CINSAUT N'] as $c) {
            if (isset($synthese['Côtes de Provence Rouge'][$c])) {
                $cepages_principaux[$c] = $synthese['Côtes de Provence Rouge'][$c]['superficie_max'];
            }
        }
        $cepages_blancs = [];
        foreach(['CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B'] as $c) {
            if (isset($synthese['Côtes de Provence Rouge'][$c])) {
                $cepages_blancs[$c] = $synthese['Côtes de Provence Rouge'][$c]['superficie_max'];
            }
        }
        $cepages_accessoires = [];
        foreach(['ROUSSELI RS','CALADOC N'] as $c) {
            if (isset($synthese['Côtes de Provence Rouge'][$c])) {
                $cepages_accessoires[$c] = $synthese['Côtes de Provence Rouge'][$c]['superficie_max'];
            }
        }
        $cepages_varietedinteret = [];
        foreach(['AGIORGITIKO N','CALABRESE N','MOSCHOFILERO RS','XINOMAVRO N','VERDEJO B'] as $c) {
            if (isset($synthese['Côtes de Provence Rouge'][$c])) {
                $cepages_accessoires[$c] = $synthese['Côtes de Provence Rouge'][$c]['superficie_max'];
            }
        }

        $cepages_a_max = [];
        $encepagement = 0;
        foreach($synthese['Côtes de Provence Rouge'] as $k => $superficies) {
            if ($k == 'Total') {
                continue;
            }
            $cepages_a_max[$k] = $superficies['superficie_max'];
            $encepagement += $superficies['superficie_max'];
        }
        $task = new Simplex\Task(new Simplex\Func($this->addemptycepage($cepages_a_max,$cepages_a_max)));

        $this->table_potentiel = [];
        $this->encepagement = [];
        $this->table_potentiel['Côtes de Provence Rouge'] = [];

        $this->table_potentiel['Côtes de Provence Rouge']['Somme(cepages) >= 1.50'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['Somme(cepages) >= 1.50']['somme'] = array_sum($cepages_a_max);
        $this->table_potentiel['Côtes de Provence Rouge']['Somme(cepages) >= 1.50']['limit'] = 1.5;
        $this->table_potentiel['Côtes de Provence Rouge']['Somme(cepages) >= 1.50']['cepages'] = $cepages_a_max;
        $this->table_potentiel['Côtes de Provence Rouge']['Somme(cepages) >= 1.50']['res'] = (array_sum($cepages_a_max) >= 1.5);
        $this->table_potentiel['Côtes de Provence Rouge']['Somme(cepages) >= 1.50']['sens'] = '>=';

        $this->table_potentiel['Côtes de Provence Rouge']['Nombre(cepages_principaux) >= 2'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['Nombre(cepages_principaux) >= 2']['somme'] = count($cepages_principaux);
        $this->table_potentiel['Côtes de Provence Rouge']['Nombre(cepages_principaux) >= 2']['limit'] = 2;
        $this->table_potentiel['Côtes de Provence Rouge']['Nombre(cepages_principaux) >= 2']['cepages'] = $cepages_principaux;
        $this->table_potentiel['Côtes de Provence Rouge']['Nombre(cepages_principaux) >= 2']['res'] = (count($cepages_principaux) >=  2);
        $this->table_potentiel['Côtes de Provence Rouge']['Nombre(cepages_principaux) >= 2']['sens'] = '>=';

        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_principaux) >= 0.70'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_principaux) >= 0.70']['somme'] = array_sum($cepages_principaux);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_principaux) >= 0.70']['limit'] = $encepagement * 0.7;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_principaux) >= 0.70']['cepages'] = $cepages_principaux;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_principaux) >= 0.70']['res'] = (array_sum($cepages_principaux) >=  $encepagement * 0.7);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_principaux) >= 0.70']['sens'] = '>=';
        $task->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_principaux, $cepages_a_max, -0.7), Simplex\Restriction::TYPE_GOE, 0));

        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['somme'] = '';
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['limit'] = $encepagement * 0.9;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['cepages'] = $cepages_principaux;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['res'] = true;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['sens'] = '<=';
        foreach(array_keys($cepages_principaux) as $c) {
            $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['somme'] .= $cepages_principaux[$c].',';
            $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['res'] &= ($cepages_principaux[$c] <=  $encepagement * 0.9);
            $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $cepages_principaux[$c]], $cepages_a_max, - 0.9), Simplex\Restriction::TYPE_LOE, 0));
        }
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['somme'] = substr($this->table_potentiel['Côtes de Provence Rouge']['PorportionChaque(cepages_principaux) <= 0.90']['somme'], 0, -1);

        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B,VERMENTINO B) <= 0.20'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B,VERMENTINO B) <= 0.20']['somme'] = array_sum($cepages_blancs);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B,VERMENTINO B) <= 0.20']['cepages'] = $cepages_blancs;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B,VERMENTINO B) <= 0.20']['limit'] = $encepagement * 0.2;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B,VERMENTINO B) <= 0.20']['res'] = (array_sum($cepages_blancs) <= $encepagement * 0.2);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B,VERMENTINO B) <= 0.20']['sens'] = '<=';
        $task->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_blancs, $cepages_a_max, - 0.2), Simplex\Restriction::TYPE_LOE, 0));

        unset($cepages_blancs['VERMENTINO B']);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B) <= 0.10'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B) <= 0.10']['somme'] = array_sum($cepages_blancs);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B) <= 0.10']['cepages'] = $cepages_blancs;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B) <= 0.10']['limit'] = $encepagement * 0.1;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B) <= 0.10']['res'] = (array_sum($cepages_blancs) <= $encepagement * 0.1);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(CLAIRETTE B,SEMILLON B,UGNI BLANC B) <= 0.10']['sens'] = '<=';
        $task->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_blancs, $cepages_a_max, - 0.1), Simplex\Restriction::TYPE_LOE, 0));

        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(ROUSSELI RS,CALADOC N) <= 0.10'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(ROUSSELI RS,CALADOC N) <= 0.10']['somme'] = array_sum($cepages_accessoires);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(ROUSSELI RS,CALADOC N) <= 0.10']['cepages'] = $cepages_accessoires;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(ROUSSELI RS,CALADOC N) <= 0.10']['limit'] = $encepagement * 0.1;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(ROUSSELI RS,CALADOC N) <= 0.10']['res'] = (array_sum($cepages_accessoires) <= $encepagement * 0.1);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(ROUSSELI RS,CALADOC N) <= 0.10']['sens'] = '<=';
        $task->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_accessoires, $cepages_a_max, - 0.1), Simplex\Restriction::TYPE_LOE, 0));

        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_varietedinteret) <= 0.05'] = [];
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_varietedinteret) <= 0.05']['somme'] = array_sum($cepages_varietedinteret);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_varietedinteret) <= 0.05']['cepages'] = $cepages_varietedinteret;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_varietedinteret) <= 0.05']['limit'] = $encepagement * 0.05;
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_varietedinteret) <= 0.05']['res'] = (array_sum($cepages_varietedinteret) <= $encepagement * 0.05);
        $this->table_potentiel['Côtes de Provence Rouge']['PorportionSomme(cepages_varietedinteret) <= 0.05']['sens'] = '<=';
        $task->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_varietedinteret, $cepages_a_max, - 0.05), Simplex\Restriction::TYPE_LOE, 0));

        foreach(array_keys($cepages_a_max) as $c) {
            $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $cepages_a_max[$c]], $cepages_a_max), Simplex\Restriction::TYPE_LOE, $cepages_a_max[$c]));
        }

        $this->potentiel_de_production = [];

        $solver = new Simplex\Solver($task);
        $solution = $solver->getSolution();
        if ($solution) {
            $optimum = $solver->getSolutionValue($solution);
            $this->potentiel_de_production['Côtes de Provence Rouge'] = round($optimum->toFloat(), 5);
        }else{
            $this->potentiel_de_production['Côtes de Provence Rouge'] = "IMPOSSIBLE";
        }
        $this->encepagement['Côtes de Provence Rouge'] = round($encepagement, 5);

        /*
        $printer = new Simplex\Printer;
        $printer->printSolution($solver);
        echo $printer->printSolver($solver); exit;
        */

        $rewrite = $request->getParameter('rewrite');
        if ($rewrite) {
            $c = explode(':', $rewrite);
            $synthese['Côtes de Provence Blanc'][$c[0]]['superficie_max'] = floatval($c[1]);
        }
        $cepages_principaux = [];
        foreach(['CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B'] as $c) {
            if (isset($synthese['Côtes de Provence Blanc'][$c])) {
                $cepages_principaux[$c] = $synthese['Côtes de Provence Blanc'][$c]['superficie_max'];
            }
        }
        $cepages_varietedinteret = [];
        foreach(['VERDEJO B'] as $c) {
            if (isset($synthese['Côtes de Provence Blanc'][$c])) {
                $cepages_varietedinteret[$c] = $synthese['Côtes de Provence Blanc'][$c]['superficie_max'];
            }
        }
        $cepages_a_max = array_merge($cepages_principaux, $cepages_varietedinteret);
        $encepagement = array_sum($cepages_a_max);

        $this->table_potentiel['Côtes de Provence Blanc']['Somme(cepages) >= 1.50'] = [];
        $this->table_potentiel['Côtes de Provence Blanc']['Somme(cepages) >= 1.50']['somme'] = array_sum($cepages_a_max);
        $this->table_potentiel['Côtes de Provence Blanc']['Somme(cepages) >= 1.50']['limit'] = 1.5;
        $this->table_potentiel['Côtes de Provence Blanc']['Somme(cepages) >= 1.50']['cepages'] = $cepages_a_max;
        $this->table_potentiel['Côtes de Provence Blanc']['Somme(cepages) >= 1.50']['res'] = (array_sum($cepages_a_max) >= 1.5);
        $this->table_potentiel['Côtes de Provence Blanc']['Somme(cepages) >= 1.50']['sens'] = '>=';

        $cepages_a_max = array_merge($cepages_principaux, $cepages_varietedinteret);
        $task_blanc = new Simplex\Task(new Simplex\Func($this->addemptycepage($cepages_a_max,$cepages_a_max)));
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(cepages_principaux) >= 0.50'] = [];
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(cepages_principaux) >= 0.50']['somme'] = array_sum($cepages_principaux);
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(cepages_principaux) >= 0.50']['limit'] = $encepagement * 0.5;
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(cepages_principaux) >= 0.50']['cepages'] = $cepages_principaux;
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(cepages_principaux) >= 0.50']['res'] = (array_sum($cepages_principaux) >=  $encepagement * 0.5);
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(cepages_principaux) >= 0.50']['sens'] = '>=';
        $task_blanc->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_principaux, $cepages_a_max, -0.5), Simplex\Restriction::TYPE_GOE, 0));

        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05'] = [];
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05']['somme'] = array_sum($cepages_varietedinteret);
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05']['limit'] = $encepagement * 0.05;
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05']['cepages'] = $cepages_varietedinteret;
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05']['res'] = (array_sum($cepages_varietedinteret) <=  $encepagement * 0.05);
        $this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05']['sens'] = '<=';
        if ($this->table_potentiel['Côtes de Provence Blanc']['PorportionSomme(VERDEJO B) <= 0.05']['somme']) {
            $task_blanc->addRestriction(new Simplex\Restriction($this->addemptycepage($cepages_varietedinteret, $cepages_a_max, - 0.05), Simplex\Restriction::TYPE_LOE, 0));
        }
        foreach(array_keys($cepages_a_max) as $c) {
            $task_blanc->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $cepages_a_max[$c]], $cepages_a_max), Simplex\Restriction::TYPE_LOE, $cepages_a_max[$c]));
        }

        $solver = new Simplex\Solver($task_blanc);
        $solution = $solver->getSolution();
        if ($solution) {
            $optimum = $solver->getSolutionValue($solution);
            $this->potentiel_de_production['Côtes de Provence Blanc'] = round($optimum->toFloat(), 5);
        }else{
            $this->potentiel_de_production['Côtes de Provence Blanc'] = "IMPOSSIBLE";
        }
        $this->encepagement['Côtes de Provence Blanc'] = round($encepagement, 5);
        /*
        $printer = new Simplex\Printer;
        $printer->printSolution($solver);
        echo $printer->printSolver($solver); exit;
        */

    }
}
