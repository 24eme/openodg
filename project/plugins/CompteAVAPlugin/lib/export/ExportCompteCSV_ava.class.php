<?php

/**
 * Description of ExportParcellairePdf
 *
 * @author mathurin
 */
class ExportCompteCSV_ava implements InterfaceDeclarationExportCsv {

    protected $compte = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv() {

        return "numéro de compte;intitulé;type (client/fournisseur);abrégé;adresse;address complément;code postal;ville;pays;code NAF;n° identifiant;n° siret;mise en sommeil;date de création;téléphone;fax;email;site;Région viticole;\n";
    }

    public function __construct($compte, $header = true, $region = null, $extraFields = false) {
        $this->compte = $compte;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {

        return $this->compte->_id . '_' . $this->compte->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;\n",
                            $this->compte->getCodeComptable(),
                            $this->compte->nom_a_afficher,
                            "CLIENT",
                            $this->compte->nom_a_afficher,
                            $this->compte->adresse,
                            $this->compte->adresse_complement_lieu,
                            $this->compte->code_postal,
                            $this->compte->commune,
                            $this->compte->pays,
                            "",
                            "",
                            $this->compte->siret,
                            $this->compte->statut,
                            $this->compte->date_creation,
                            ($this->compte->telephone_bureau) ? $this->compte->telephone_bureau : $this->compte->telephone_mobile,
                            $this->compte->fax,
                            $this->compte->email,
                            "https://declaration.ava-aoc.fr/compte-visualisation/".$this->compte->_id
                          );

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
