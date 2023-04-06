<?php

/**
 * Classe de base pour modifier le contenu d'un fichiers ODS modele
 * se trouvant dans modules/parcellaire/templates/.
 * Le fichier est ensuite enregistré dans le cache dans le dossier ods.
 * La fonction create() renvoie le contenu de l'ods transformé.
 */
abstract class BaseExportParcellaireODS {

    private $parcellaire = null;

    private $ods_filename = null;

    private $ods_tmp_file = null;

    private $tmp_dir = null;

    // le contenu du xml content.xml du fichier ODS à modifier.
    private string $ods_content = "";

    public function __construct($parcellaire, $ods_filename) {
        $this->parcellaire = $parcellaire;
        $this->ods_filename = $ods_filename;

        // Les chemins des fichiers
        $this->tmp_dir = sfConfig::get('sf_cache_dir');
        $this->ods_tmp_file = $this->tmp_dir . '/' . str_replace('.ods', '', $this->ods_filename) . $this->parcellaire->get('_id') . $this->parcellaire->get('_rev') . '.ods';
    }

    protected function getParcellaire() {
        return $this->parcellaire;
    }

    /**
     * Crée le fichier ODS en récupérant l'ODS modèle, en le transformant avec parseDocument(), le mets dans le cache et renvoie son contenu.
     * 
     * @return string le contenu du fichier ODS
     */
    public function create() {
        // Si on l'a déjà créé on prend l'existant.
        if (file_exists($this->ods_tmp_file) && ! sfContext::getInstance()->getConfiguration()->isDebug()) {
            return file_get_contents($this->ods_tmp_file);
        }

        $content_filename = 'content.xml';
        $content_file = $this->tmp_dir . '/' . $content_filename;

        copy(dirname(__FILE__) . '/../../../modules/parcellaire/templates/' . $this->ods_filename, $this->ods_tmp_file);

        // Prend le content.xml en dézippant l'ODS
        $zip = new ZipArchive();
        $zip->open($this->ods_tmp_file);
        $zip->extractTo($this->tmp_dir, $content_filename);

        $this->ods_content = file_get_contents($content_file);

        $this->parseDocument();

        // Et remet dans le zip
        file_put_contents($content_file, $this->ods_content);
        $zip->addFile($content_file, $content_filename);
        $zip->close();

        return file_get_contents($this->ods_tmp_file);
    }

    /**
     * Génère une PDF à partir de l'ods en cache.
     * 
     * @return string le contenu du PDF
     */
    public function createPDF() {
        $this->create();
        
        exec("libreoffice --headless --convert-to pdf {$this->ods_tmp_file} --outdir {$this->tmp_dir}");

        return file_get_contents(str_replace('.ods', '.pdf', $this->ods_tmp_file));
    }

    /**
     * Les opérations à faire dans le content.xml de l'ODS
     * Modifie $this->ods_content
     */
    protected function parseDocument() {

    }

    /**
     * Prend un tableau clé/valeur en paramètre. 
     * Remplace dans le contenu de l'ods les clés par les valeurs.
     * 
     * @param mixed $keys_values : un tableau de type ['%%CLE_1' => 'VAL_1', ...]
     */
    protected function parse($keys_values, $content=null) {
        if (!$content) {
            $content = &$this->ods_content;
        }

        // Remplace les clés par les valeurs
        foreach ($keys_values as $key => $value) {
            // Si c'est un float on doit spécifier à libreoffice que le format est float (notamment pour être utilisé dans les formules)
            if (gettype($value) == 'double') {
                $content = preg_replace(
                    '#(<table:table-cell *table:style-name="[^"]+".*?office:value-type=")string"( *calcext:value-type=")string("[^>]*><text:p>)'.$key.'(</text:p></table:table-cell>)#',
                    '${1}float" office:value="'.$value.'" ${2}float${3}'.str_replace('.', ',', $value).'${4}',
                    $content
                );
            } else {
                $content = str_replace($key, $value, $content);
            }
        }

        // Supprime le contenu p:text quand c'est une formule. Sinon il affiche p:text plutôt que le résultat de la formule.
        preg_match_all( '#table:formula=".*?</table:table-cell>#', $content, $matches_form, PREG_SET_ORDER);
        foreach ($matches_form as $match_form) {
            $replace = preg_replace ('#<text:p>[^<]*</text:p>#', '<text:p></text:p>', $match_form[0] );
            $replace = preg_replace ('#office:((?:string-)?)value="[^"]*"#', 'office:$1value=""', $replace );
            $content = str_replace($match_form[0], $replace, $content);
        }

        return $content;
    }

    /**
     * Prend la ou les lignes entre %%BEGIN et %%END. Ces lignes ont des clés. Duplique la ligne autant de fois qu'il y en a besoin en y mettant les bonnes valeurs.
     * 
     * @param mixed $keys_vals: tableau de tableaux de clés valeurs. [[%%CLE1 => val1, ...], ...]
     */
    protected function create_rows($keys_vals) {
        // Met la ligne entre %%begin et %%end dans $matches[1]
        preg_match('#<table:table-row[^>]*><table:table-cell[^>]*><text:p>%%BEGIN</text:p></table:table-cell>.*?</table:table-row>(.*?)<table:table-row[^>]*><table:table-cell[^>]*><text:p>%%END</text:p></table:table-cell>.*?</table:table-row>#', $this->ods_content, $matches);
        // La ligne à dupliquer et à remplir avec les bonnes valeurs
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

        // Les lignes qui vont remplacer toute ce qui est entre %%begin et %%end
        $data_lines = '';

        // Crée les lignes à mettre dans l'ODS partir de $pattern_line
        foreach ($keys_vals as $key_val) {
            // Ajoute la ligne avec les bonnes valeurs dans les lignes à créer.
            $data_lines .= $this->parse($key_val, $pattern_line);

            // Incrémente le numéro de ligne dans les formules pour la prochaine ligne à mettre dans $data_lines
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

        // Met les lignes nouvellement créées à la place de ce qui est entre %%begin et %%end
        $this->ods_content = str_replace($matches[0], $data_lines, $this->ods_content);
    }

}