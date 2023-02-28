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

        sfConfig::get("sf_cache_dir");

        // Les fichiers nécesssaires pour la transfo de l'ODS
        $tmp_dir = '/tmp';
        $ods_file = "$tmp_dir/feuille_controle.ods";
        $content_filename = 'content.xml';
        $content_file = "$tmp_dir/$content_filename";

        copy(dirname(__FILE__) . '/../templates/feuille_controle.ods', $ods_file);

        // Prend le content.xml en dézippant l'ODS
        $zip = new ZipArchive();
        $res = $zip->open($ods_file);
        $zip->extractTo($tmp_dir, $content_filename);
        $ods_content = file_get_contents($content_file);

        $ods_content = $this->getParcellesLines($ods_content);
        $ods_content = $this->getPochette($ods_content);

        // Et remet dans le zip
        file_put_contents($content_file, $ods_content);
        $zip->addFile($content_file, $content_filename);
        $zip->close();

        header("Content-Type: application/vnd.oasis.opendocument.spreadsheet; charset=UTF-8");
        header("Content-disposition: attachment; filename=".sprintf('"PARCELLAIRE-%s-%s.ods"', $parcellaire->identifiant, $parcellaire->date));
        header("Pragma: ");
        header("Cache-Control: public");
        header("Expires: 0");

        echo file_get_contents($ods_file);

        exit;
    }

    private function getParcellesLines($ods_content) {
        $parcellaire = $this->getRoute()->getParcellaire();

        preg_match('#<table:table-row[^>]*><table:table-cell[^>]*><text:p>%%BEGIN</text:p></table:table-cell>.*?</table:table-row>(.*?)<table:table-row[^>]*><table:table-cell[^>]*><text:p>%%END</text:p></table:table-cell>.*?</table:table-row>#', $ods_content, $matches);
        
        // La ligne avec les %%* à remplacer
        $pattern_line = $matches[1];

        // Change les formules de la ligne pour commencer une ligne plus haut
        // Parce qu'on supprime la ligne avec %%Begin et on commence donc un ligne plus haut que la ligne modèle
        preg_match_all( '/table:formula="(.*?)"/', $pattern_line, $matches_form, PREG_SET_ORDER);
        foreach ($matches_form as $match_form) {
            $replace = preg_replace_callback (
                '/(\[\.\w+)(\d+)(\])/',
                function($matches_matr) { return $matches_matr[1] . ((int)$matches_matr[2] - 1) . $matches_matr[3]; }, 
                $match_form[1]
              );
            $pattern_line = str_replace($match_form[1], $replace, $pattern_line);
        }

        // Les lignes (ods) à mettre à la place
        $data_lines = '';
        
        // Crée les lignes à mettre dans l'ODS partir de $pattern_line
        $index = 0;
        
        foreach ($parcellaire->declaration as $declaration) {
            foreach ($declaration->detail as $detail) {
                $new_line = $pattern_line;
                $ecart_rang = intval(($detail->exist('ecart_rang')) ? $detail->get('ecart_rang') : 0);
                $ecart_pieds = intval(($detail->exist('ecart_pieds')) ? $detail->get('ecart_pieds') : 0);
                $datas = [
                    '%%LIGNE' => ++$index,
                    '%%COMMUNE' => $detail->commune,
                    '%%CADASTRE' => "$detail->section $detail->numero_parcelle",
                    '%%SUP_CADASTRALE' => $detail->superficie_cadastrale,
                    '%%SUP_UTILISEE' => $detail->getSuperficie(),
                    '%%CEPAGE' => $detail->cepage,
                    '%%ANNEE' => $detail->campagne_plantation,
                    '%%ECART_RANG' => $ecart_rang,
                    '%%ECART_PIED' => $ecart_pieds,
                    '%%JEUNE_VIGNE' => (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$detail->hasTroisiemeFeuille()) ? 'JV' : '',
                    '%%MODE_FAIRE_VALOIR' => '', // TODO : voir de quoi il s'agit
                    '%%NON_AOC' => '', // TODO : voir de quoi il s'agit
                    '%%CONFORMITE' => ($ecart_rang > 250 || $ecart_pieds < 80 || $ecart_rang * $ecart_pieds / 10000 > 2.5) ? 'NC' : 'C',
                ];

                // Les cellules qui doivent être traitées comme des nombres
                $floats = [
                    '%%LIGNE',
                    '%%ECART_RANG',
                    '%%ECART_PIED',                    
                    '%%SUP_CADASTRALE',
                    '%%SUP_UTILISEE',
                ];
        
                foreach ($datas as $key => $value) {
                    // Si c'est un float faut changer tout le noeud XML
                    if (array_search($key, $floats)) {
                        $new_line = preg_replace(
                            '#(<table:table-cell *table:style-name="[^"]+" *office:value-type=")string"( *calcext:value-type=")string("[^>]*><text:p>)'.$key.'(</text:p></table:table-cell>)#',
                            '${1}float" office:value="'.$value.'" ${2}float${3}'.str_replace('.', ',', $value).'${4}',
                            $new_line
                        );
                        continue;
                    }
                    $new_line = str_replace($key, $value, $new_line);
                }
                $data_lines .= $new_line;

                // Incrémente le numéro de ligne dans les formules pour la prochaine new_line
                preg_match_all( '/table:formula="(.*?)"/', $pattern_line, $matches_form, PREG_SET_ORDER);
                foreach ($matches_form as $match_form) {
                    $replace = preg_replace_callback (
                        '/(\[\.[A-Z]+)([0-9]+)(\])/',
                        function($matches_matr) { return $matches_matr[1] . ((int)$matches_matr[2] + 1) . $matches_matr[3]; }, 
                        $match_form[1]
                    );
                    $pattern_line = str_replace($match_form[1], $replace, $pattern_line);
                }
            }
        }

        // Remplace les données modèles par les données réelles
        return str_replace($matches[0], $data_lines,  $ods_content);
    }

    private function getPochette($ods_content) {
        $parcellaire = $this->getRoute()->getParcellaire();

        $datas = [
            '%%RAISON_SOCIALE' => $parcellaire->declarant['raison_sociale'],
            '%%SIRET' => $parcellaire->declarant['siret'],
            '%%EVV' => $parcellaire->declarant['cvi'],
            '%%CDP' => $parcellaire->pieces[0]['identifiant'],
            '%%ADRESSE1' => $parcellaire->declarant['adresse'],
            '%%ADRESSE2' => $parcellaire->declarant['commune'],
            '%%TELEPHONE' => ($parcellaire->declarant['telephone_bureau'] ? $parcellaire->declarant['telephone_bureau'] . " " : ""),
            '%%TEL_MOBILE' => ($parcellaire->declarant['telephone_mobile'] ? $parcellaire->declarant['telephone_mobile'] : ""),
            '%%EMAIL' => $parcellaire->declarant['email'],
            '%%FAX' => $parcellaire->declarant['fax'],
            '%%CHAIS1' => '',
            '%%CHAIS2' => '',
            '%%SURFACE_TOTALE' => $parcellaire->getSuperficieTotale(),
            '%%SURFACE_PRODUCTION' => $parcellaire->getSuperficieCadastraleTotale(),
        ];

        foreach ($datas as $key => $value) {
            $ods_content = str_replace($key, $value, $ods_content);
        }

        return $ods_content;
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
