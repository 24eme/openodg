<?php

/**
 * Description of ExportParcellaireControleODS
 */
class ExportParcellaireControleODS {

    protected $parcellaire = null;

    public function __construct($parcellaire) {
        $this->parcellaire = $parcellaire;
    }

    public function create() {
        // Les fichiers nécesssaires pour la transfo de l'ODS
        $tmp_dir = sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'doc_controle';
        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir);
        }
        $ods_file = "$tmp_dir/feuille_controle.ods";
        $content_filename = 'content.xml';
        $content_file = "$tmp_dir/$content_filename";

        copy(dirname(__FILE__) . '/../../modules/parcellaire/templates/feuille_controle.ods', $ods_file);

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

        return file_get_contents($ods_file);
    }

    private function getParcellesLines($ods_content) {
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

        // Supprime le contenu p:text quand c'est une formule. Sinon il affiche p:text plutôt que le résultat de la formule.
        preg_match_all( '#table:formula=".*?</table:table-cell>#', $pattern_line, $matches_form, PREG_SET_ORDER);
        foreach ($matches_form as $match_form) {
            $replace = preg_replace ('#<text:p>[^<]*</text:p>#', '<text:p></text:p>', $match_form[0] );
            $replace = preg_replace ('#office:value="[^"]*"#', 'office:value=""', $replace );
            $pattern_line = str_replace($match_form[0], $replace, $pattern_line);
        }

        // Les lignes (ods) à mettre à la place
        $data_lines = '';
        
        // Crée les lignes à mettre dans l'ODS partir de $pattern_line
        $index = 0;
        
        foreach ($this->parcellaire->declaration as $declaration) {
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
        $datas = [
            '%%RAISON_SOCIALE' => $this->parcellaire->declarant['raison_sociale'],
            '%%SIRET' => $this->parcellaire->declarant['siret'],
            '%%EVV' => $this->parcellaire->declarant['cvi'],
            '%%CDP' => $this->parcellaire->pieces[0]['identifiant'],
            '%%ADRESSE1' => $this->parcellaire->declarant['adresse'],
            '%%ADRESSE2' => $this->parcellaire->declarant['commune'],
            '%%TELEPHONE' => ($this->parcellaire->declarant['telephone_bureau'] ? $this->parcellaire->declarant['telephone_bureau'] . " " : ""),
            '%%TEL_MOBILE' => ($this->parcellaire->declarant['telephone_mobile'] ? $this->parcellaire->declarant['telephone_mobile'] : ""),
            '%%EMAIL' => $this->parcellaire->declarant['email'],
            '%%FAX' => $this->parcellaire->declarant['fax'],
            '%%CHAIS1' => '',
            '%%CHAIS2' => '',
            '%%SURFACE_TOTALE' => $this->parcellaire->getSuperficieTotale(),
            '%%SURFACE_PRODUCTION' => $this->parcellaire->getSuperficieCadastraleTotale(),
        ];

        foreach ($datas as $key => $value) {
            $ods_content = str_replace($key, $value, $ods_content);
        }

        return $ods_content;
    }


}