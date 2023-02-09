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
            $this->getUser()->setFlash('erreur_import', $msg);
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

        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.csv"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");
        $this->content = "Commune;N° cadastraux;Superficie parcelle;Superficie UC;Cépage;Année plantation;Ecartement rang;Ecartement pied;\n";
        foreach ($parcellaire->declaration as $declaration) {
            foreach ($declaration->detail as $detail) {
                $superf      = $detail->getSuperficie();
                $cepage = $detail->cepage;
                if (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$detail->hasTroisiemeFeuille()) {
                    $cepage .= ' - jeunes vignes';
                }
                $ecart_pieds = ($detail->exist('ecart_pieds')) ? $detail->get('ecart_pieds') : '';
                $ecart_rang = ($detail->exist('ecart_rang')) ? $detail->get('ecart_rang') : '';
                $this->content .= "$detail->commune;$detail->section $detail->numero_parcelle;$detail->superficie_cadastrale;$superf;$cepage;$detail->campagne_plantation;$ecart_rang;$ecart_pieds;\n";
            }
        }
        echo $this->content;
        exit;
    }

    public function executeParcellaireExportGeo(sfWebRequest $request) {
        $this->secureTeledeclarant();
        
        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/vnd.geo+json");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.geojson"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        // Le json décodé des parcelles
        $geojson = $parcellaire->getDocument()->getGeoJson();

        // Ajoute des couleurs et l'identification
        foreach ($geojson->features as $feat) {
            $feat->properties->stroke = '#FF0000';
            $feat->properties->stroke_width = 4;
            $feat->properties->stroke_opacity = 1;
            $feat->properties->fill = '#fff';
            $feat->properties->fill_opacity = 0;
            $feat->properties->name = $feat->properties->section. ' ' . $feat->properties->numero;
        }

        // On y ajoute les json (décodés) des aires des appelations des communes associées
        foreach ($parcellaire->getCachedAires() as $aire) {
            foreach ($aire['jsons'] as $airejson) {
                $aireobj = json_decode($airejson);
                foreach ($aireobj->features as $feat) {
                    // Ajoute les couleurs et infos qui vont bien
                    $feat->properties->name = $aire['infos']['name'];
                    $feat->properties->fill = $aire['infos']['color'];
                    $feat->properties->fill_opacity = 0.5;
                    $feat->properties->stroke = '#000';
                    $feat->properties->stroke_width = 2;
                    $feat->properties->stroke_opacity = 0.1;
                    // Ajoute l'aire au début du tableau, les parcelles doivent être audessus pour être plus facilement clickables. 
                    array_unshift($geojson->features, $feat);
                }
            }
        }

        // Met le json dans le fichier .geojson à télécharger et met des - à la place des _ parce que c'est comme ça en geojson
        $geojson_str = str_replace('fill_opacity', 'fill-opacity', 
                str_replace('stroke_width', 'stroke-width', 
                    str_replace('stroke_opacity', 'stroke-opacity', json_encode($geojson) 
                    )
                )
            );

        echo $geojson_str;
        /*
        // A corriger. GeoPHP ne génère pas un kml valide.
        $gj_obj = GeoPHP::load($geojson_str, 'geojson');
        echo $gj_obj->out('kml');
        */

        exit;
    }

    public function executeParcellaireExportDoc(sfWebRequest $request) {
        $this->secureTeledeclarant();
        
        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: text/markdown; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.md"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        $this->content = "# Audit Vignoble";
        $this->content .= "\n\nRéférence : / Révision : V0 / Date : ";
        $this->content .= "\nType de contrôle: Standard";
        $this->content .= "\nActivité: ";
        $this->content .= "\n\n## " . $parcellaire->declarant['raison_sociale'] . "\n";
        $this->content .= "\n**N° Siret : " . $parcellaire->declarant['siret'] . " N° EVV / PPM : ". $parcellaire->declarant['cvi'] . "**";
        $this->content .= "\n\n## " . $parcellaire->pieces[0]['identifiant'];
        $this->content .= "\n\n### Fiche contact";
        $this->content .= "\n\n#### Adresse\n\n" . $parcellaire->declarant['nom'] . "\n" . $parcellaire->declarant['adresse'] . "\n" . $parcellaire->declarant['commune'];
        $this->content .= "\n\n#### Tel\n\n" . ($parcellaire->declarant['telephone_bureau'] ? ("Bureau : " . $parcellaire->declarant['telephone_bureau'] . " ") : "") . ($parcellaire->declarant['telephone_mobile'] ? ("Mobile : " . $parcellaire->declarant['telephone_mobile']) : "");
        $this->content .= "\n\n#### Mail\n\n" . $parcellaire->declarant['email'];
        $this->content .= "\n\n#### Fax\n\n" . $parcellaire->declarant['fax'];
        $this->content .= "\n\n#### Chai 1\n\n";
        $this->content .= "\n\n#### Chai 2\n\n";
        $this->content .= "\n\n### Contrôle Documentaire";
        $this->content .= "\n\n#### Surface totale (avec JV)\n\n" . $parcellaire->getSuperficieTotale();
        $this->content .= "\n\n#### Surface cadastrale totale\n\n" . $parcellaire->getSuperficieCadastraleTotale();
        $this->content .= "\n\n### Synthèse terrain\n";
        echo $this->content;
        exit;
    }


    public function secureTeledeclarant() {
        if(!$this->getUser()->isAdmin() && !$this->getUser()->isStalker() && (!class_exists("SocieteConfiguration") || !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration())) {
            throw new sfError403Exception();
        }
    }
}
