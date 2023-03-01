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

    public function executeParcellaireExportGeo(sfWebRequest $request) {
        $this->secureTeledeclarant();
        
        $parcellaire = $this->getRoute()->getParcellaire();
        $this->forward404Unless($parcellaire);

        header("Content-Type: application/vnd.google-earth.kml+xml");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.kml"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        echo '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>';
        
        // L'hexa de la couleur est inversé par rapport à la notation habituelle
        // aabbggrr, où aa=alpha (00 à ff) ; bb=blue (00 à ff) ; gg=green(00 à ff) ; rr=red (00 à ff).
        echo '<Style id="parcelle-style">
        <LineStyle>
          <width>2</width>
        </LineStyle>
        <PolyStyle>
          <color>7d0000ff</color>
        </PolyStyle>
      </Style>';

        $styles = [];
        foreach ($parcellaire->getCachedAires() as $aire) {
            foreach ($aire['jsons'] as $airejson) {
                $aireobj = json_decode($airejson);
                foreach ($aireobj->features as $feat) {
                    $color = '7d' . str_replace('#', '', $aire['infos']['color']);
                    $styles[$color] = '<Style id="aire-style-'.$color.'">
            <LineStyle>
            <width>1</width>
            </LineStyle>
            <PolyStyle>
            <color>'.$color.'</color>
            </PolyStyle>
        </Style>';
                }
            }
        }

        foreach ($styles as $style) {
            echo $style;
        }

        // Pour mémoire la possibilité de mettre du texte directement dans la carte en mettant du texte en PNG
        /*
        echo '<Placemark>
        <name>Test</name>
        <Style>
            <IconStyle>
                <scale>0.03125</scale>
                <Icon>
                    <href>data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNiYAAAAAkAAxkR2eQAAAAASUVORK5CYII=</href>
                    <gx:w>1</gx:w>
                    <gx:h>1</gx:h>
                </Icon>
                <hotSpot x="0" y="1" xunits="pixels" yunits="pixels"/>
            </IconStyle>
            <LabelStyle>
                <color>ff000000</color>
                <LabelStyleSimpleExtensionGroup xmlns="" fontFamily="Sans" haloColor="ffffffff" haloRadius="3" haloOpacity="1"/>
            </LabelStyle>
        </Style>
        <Point>
            <coordinates>6.096128276094139,43.24822642695386</coordinates>
        </Point>
    </Placemark>';
    */

        // Le json décodé des parcelles
        $geojson = $parcellaire->getDocument()->getGeoJson();

        // On y ajoute les json (décodés) des aires des appelations des communes associées
        foreach ($parcellaire->getCachedAires() as $aire) {
            foreach ($aire['jsons'] as $airejson) {
                $aireobj = json_decode($airejson);
                foreach ($aireobj->features as $feat) {
                    $feat_str = json_encode($feat);
                    $feat_obj = GeoPHP::load($feat_str, 'geojson');
        
                    echo '<Placemark>';
                    echo '<name>'.$aire['infos']['name'].'</name>';
                    echo '<styleUrl>#aire-style-7d' . str_replace('#', '', $aire['infos']['color']) . '</styleUrl>';
                    echo $feat_obj->out('kml');
                    echo '</Placemark>';
                }
            }
        }

        // Ajoute des couleurs et l'identification
        foreach ($geojson->features as $feat) {
            $feat_str = json_encode($feat);
            $feat_obj = GeoPHP::load($feat_str, 'geojson');

            echo '<Placemark>';
            echo '<name>'.$feat->properties->section. ' ' . $feat->properties->numero.'</name>';
            echo '<styleUrl>#parcelle-style</styleUrl>';
            echo $feat_obj->out('kml');
            echo '</Placemark>';
        }

        echo '</Document></kml>';
        exit;        
    }


    public function secureTeledeclarant() {
        if(!$this->getUser()->isAdmin() && !$this->getUser()->isStalker() && (!class_exists("SocieteConfiguration") || !SocieteConfiguration::getInstance()->isVisualisationTeledeclaration())) {
            throw new sfError403Exception();
        }
    }
}
