<?php

class ExportTirageCSV implements InterfaceDeclarationExportCsv {

    protected $tirage = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        $header = "Campagne;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Lieu de stockage;Couleur;Cépages;Millésime;Ventilation du millésime;Fermentation malo-lactique;Date de début de mise en bouteille;Date de fin de mis en bouteille;Volume Total";

        foreach(sfConfig::get('app_contenances_bouteilles') as $libelle => $volume) {
            $header .= ";".$libelle;
        }
        $header .= ";Date de validation / récéption;Date de validation ODG;Type de déclaration\n";

        return $header;
    }

    public function __construct($tirage, $header = true, $region = null) {
        $this->tirage = $tirage;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {

        return $this->tirage->_id . '_' . $this->tirage->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $mode = ($this->tirage->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        $cepages = "";
        $cpt = 1;
        foreach ($this->tirage->getCepagesSelectionnes() as $cepage) {
            $cepages .= $cepage->getLibelle();
            $cepages .= ($cpt < count($this->tirage->getCepagesSelectionnes())) ? "," : "";
            $cpt++;
        }

        $csv .= sprintf("%s;\"%s\";\"%s\";%s;%s;\"%s\";%s;%s;\"%s\";%s;\"%s\";%s;\"%s\";%s;%s;%s;%s",
            $this->tirage->campagne,
            $this->tirage->declarant->cvi,
            $this->tirage->declarant->siret,
            $this->tirage->declarant->raison_sociale,
            $this->tirage->declarant->adresse,
            $this->tirage->declarant->code_postal,
            $this->tirage->declarant->commune,
            $this->tirage->declarant->email,
            ($this->tirage->lieu_stockage)? $this->tirage->lieu_stockage : $this->tirage->declarant->adresse.' '.$this->tirage->declarant->code_postal.' '.$this->tirage->declarant->commune,
            "Crémant ".$this->tirage->couleur_libelle,
            $cepages,
            $this->tirage->millesime_libelle,
            $this->tirage->millesime_ventilation,
            ($this->tirage->fermentation_lactique) ? "Fermentation malo-lactique" : "",
            $this->tirage->date_mise_en_bouteille_debut,
            $this->tirage->date_mise_en_bouteille_fin,
            $this->formatFloat($this->tirage->getVolumeTotalComposition())
            );

        foreach(sfConfig::get('app_contenances_bouteilles') as $libelle => $volume) {
            $find = false;
            foreach($this->tirage->composition as $composition) {
                if($composition->contenance == $libelle) {
                    $csv .= ";".$composition->nombre;
                    $find = true;
                    break;
                }
            }
            if(!$find) {
                $csv .= ";";
            }
        }

        $csv .= ";".$this->tirage->validation.";".$this->tirage->validation_odg.";".$mode."\n";

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
