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

        $this->parcellaire = $this->getRoute()->getParcellaire();

        $categories = [];
        $this->table_potentiel = [];
        $this->potentiel_de_production = [];
        $this->encepagement = [];


        foreach (ParcellaireConfiguration::getInstance()->getPotentielGroupes() as $groupe_key) {
            $groupe_synthese = ParcellaireConfiguration::getInstance()->getGroupeSyntheseLibelle($groupe_key);
            $synthese = $this->parcellaire->getSyntheseProduitsCepages(ParcellaireConfiguration::getInstance()->getGroupeFilterProduitHash($groupe_key));
            foreach (ParcellaireConfiguration::getInstance()->getGroupeCategories($groupe_key) as $category_key => $category_cepages) {
                $categories[$category_key] = [];
                foreach($category_cepages as $c) {
                    if (isset($synthese[$groupe_synthese]) && isset($synthese[$groupe_synthese]['Cepage'][$c])) {
                        $categories[$category_key][$c] = $synthese[$groupe_synthese]['Cepage'][$c]['superficie_max'];
                    }
                }
            }
            $categories['cepages_couleur'] = [];
            $categories['cepages_toutes_couleurs'] = [];
            $encepagement = 0;
            foreach($synthese as $synthese_libelle => $synthese_couleur) {
                foreach($synthese_couleur as $cepages) {
                    foreach($cepages as $k => $superficies) {
                        if ($k == 'Total') {
                            continue;
                        }
                        if (strpos($k, 'XXX') !== false) {
                            continue;
                        }
                        if (!isset($superficies['superficie_max'])) {
                            continue;
                        }
                        if (!isset($categories['cepages_toutes_couleurs'][$k])) {
                            $categories['cepages_toutes_couleurs'][$k] = $superficies['superficie_max'];
                        }
                        if ($synthese_libelle != $groupe_synthese) {
                            continue;
                        }
                        $categories['cepages_couleur'][$k] = $superficies['superficie_max'];
                        $encepagement += $superficies['superficie_max'];
                    }
                }
            }

            $task = new Simplex\Task(new Simplex\Func($this->addemptycepage($categories['cepages_couleur'],$categories['cepages_couleur'])));

            $this->table_potentiel[$groupe_synthese] = [];
            foreach(ParcellaireConfiguration::getInstance()->getGroupeRegles($groupe_key) as $regle) {
                $regle_nom = $regle['fonction'].'('.$regle['category'].') '.$regle['sens'].' '.$regle['limit'];
                $this->table_potentiel[$groupe_synthese][$regle_nom] = [];

                if (($regle['sens'] != '>=') && ($regle['sens'] != '<=')) {
                    throw new sfException('sens '.$regle['sens'].' non géré');
                }
                $this->table_potentiel[$groupe_synthese][$regle_nom]['sens'] = $regle['sens'];

                $this->table_potentiel[$groupe_synthese][$regle_nom]['cepages'] = $categories[$regle['category']];

                switch ($regle['fonction']) {
                    case 'SAppliqueSiSomme':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = array_sum($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }
                        break;
                    case 'Nombre':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = count($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }
                        break;
                    case 'ProportionSomme':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = array_sum($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $encepagement * $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                            $task->addRestriction(new Simplex\Restriction($this->addemptycepage($categories[$regle['category']], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_GOE, 0));
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                            $task->addRestriction(new Simplex\Restriction($this->addemptycepage($categories[$regle['category']], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_LOE, 0));
                        }
                        break;
                    case 'ProportionChaque':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = 0;
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $encepagement * $regle['limit'];
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = true;
                        foreach(array_keys($categories['cepages_principaux']) as $c) {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] .= $categories[$regle['category']][$c].',';

                            if ($regle['sens'] == '>=') {
                                $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] &= ($categories[$regle['category']][$c] >=  $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                                $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $categories[$regle['category']][$c]], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_GOE, 0));
                            }elseif ($regle['sens'] == '<=') {
                                $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] &= ($categories[$regle['category']][$c] <=  $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                                $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $categories[$regle['category']][$c]], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_LOE, 0));
                            }
                        }
                        break;
                    default:
                        throw new sfException('Fonction de Potentiel de production "'.$regle['fonction'].'" non gérée');
                }
            }
            foreach(array_keys($categories['cepages_couleur']) as $c) {
                if ($categories['cepages_couleur'][$c]) {
                    $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $categories['cepages_couleur'][$c]], $categories['cepages_couleur']), Simplex\Restriction::TYPE_LOE, $categories['cepages_couleur'][$c]));
                }
            }

            $solver = new Simplex\Solver($task);
            $solution = $solver->getSolution();
            if ($solution) {
                $optimum = $solver->getSolutionValue($solution);
                $this->potentiel_de_production[$groupe_synthese] = round($optimum->toFloat(), 5);
            }else{
                $this->potentiel_de_production[$groupe_synthese] = "IMPOSSIBLE";
            }
            $this->encepagement[$groupe_synthese] = round($encepagement, 5);
        }
    }
}
