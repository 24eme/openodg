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

    // le contenu du xml content.xml du fichier ODS à modifier.
    private string $ods_content = "";

    public function __construct($parcellaire, $ods_filename) {
        $this->parcellaire = $parcellaire;
        $this->ods_filename = $ods_filename;
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
        // Les fichiers nécesssaires pour la transfo de l'ODS
        $tmp_dir = sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'ods';
        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir);
        }
        $ods_tmp_file = $tmp_dir . '/' . $this->ods_filename;
        $content_filename = 'content.xml';
        $content_file = "$tmp_dir/$content_filename";

        copy(dirname(__FILE__) . '/../../../modules/parcellaire/templates/' . $this->ods_filename, $ods_tmp_file);

        // Prend le content.xml en dézippant l'ODS
        $zip = new ZipArchive();
        $zip->open($ods_tmp_file);
        $zip->extractTo($tmp_dir, $content_filename);

        $this->ods_content = file_get_contents($content_file);

        $this->parseDocument();

        // Et remet dans le zip
        file_put_contents($content_file, $this->ods_content);
        $zip->addFile($content_file, $content_filename);
        $zip->close();

        return file_get_contents($ods_tmp_file);
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
    protected function parse($keys_values) {

        // Remplace les clés par les valeurs
        foreach ($keys_values as $key => $value) {
            // Si c'est un float on doit spécifier à libreoffice que le format est float (notamment pour être utilisé dans les formules)
            if (gettype($value) == 'double') {
                $this->ods_content = preg_replace(
                    '#(<table:table-cell *table:style-name="[^"]+".*?office:value-type=")string"( *calcext:value-type=")string("[^>]*><text:p>)'.$key.'(</text:p></table:table-cell>)#',
                    '${1}float" office:value="'.$value.'" ${2}float${3}'.str_replace('.', ',', $value).'${4}',
                    $this->ods_content
                );
            } else {
                $this->ods_content = str_replace($key, $value, $this->ods_content);
            }
        }

        // Supprime le contenu p:text quand c'est une formule. Sinon il affiche p:text plutôt que le résultat de la formule.
        preg_match_all( '#table:formula=".*?</table:table-cell>#', $this->ods_content, $matches_form, PREG_SET_ORDER);
        foreach ($matches_form as $match_form) {
            $replace = preg_replace ('#<text:p>[^<]*</text:p>#', '<text:p></text:p>', $match_form[0] );
            $replace = preg_replace ('#office:value="[^"]*"#', 'office:value=""', $replace );
            $this->ods_content = str_replace($match_form[0], $replace, $this->ods_content);
        }

    }

}