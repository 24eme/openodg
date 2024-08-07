<?php

class ExportCourrierCSV implements InterfaceDeclarationExportCsv {

    protected $courrier = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {
        return "Date du Courrier;Identifiant Société;Identifiant Opérateur;CVI Opérateur;SIRET Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Titre de Courrier;Type de Courrier;Doc Id;Extra;\n";
    }

    public function __construct($courrier, $header = true, $region = null) {
        $this->courrier = $courrier;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {

        return $this->courrier->_id . '_' . $this->courrier->_rev . ".csv";
    }

    public function protectStr($str) {
        return str_replace(';', '−', str_replace('"', '', $str));
    }

    public function export() {
        $csv = "";
        if ($this->header) {
            $csv .= self::getHeaderCsv();
        }
        $ligne_base = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;",
            $this->courrier->date, $this->courrier->getEtablissementObject()->getSociete()->identifiant, $this->courrier->identifiant, $this->courrier->declarant->cvi, $this->courrier->declarant->siret,  $this->protectStr($this->courrier->declarant->raison_sociale), $this->protectStr($this->courrier->declarant->adresse), $this->courrier->declarant->code_postal, $this->protectStr($this->courrier->declarant->commune),
            $this->courrier->declarant->email, $this->courrier->courrier_titre, $this->courrier->courrier_type, $this->courrier->_id);

        foreach ($this->courrier->extras as $key => $value) {
            $extras .= sprintf("%s:%s|", $key, $value);
        }

        $csv .= $ligne_base . substr($extras, 0, -1);

        return $csv;
    }

    public function setExtraArgs($args) {
    }
}
