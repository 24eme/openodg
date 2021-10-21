<?php

class ExportDegustationCSV implements InterfaceDeclarationExportCsv {

    protected $degustation = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv() {
        $header = "Millésime;CVI Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Date de la dégustation;Organisme dégustateur;Produit;Dénomination complémentaire;Malo-lactique;Composition cepages;N° anonymat;N° prélévement;Cuve;Volume";

        foreach(DegustationClient::$note_type_libelles as $keyNote => $libelleNote) {
            $header .= ";".$libelleNote." note;".$libelleNote." défauts";
        }

        return $header."\n";
    }

    public function __construct($degustation, $header = true, $region = null, $extraFields = false) {
        $this->degustation = $degustation;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {

        return $this->degustation->_id . '_' . $this->degustation->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $ligne_base = sprintf("%s;%s;\"%s\";\"%s\";%s;\"%s\";%s;%s",
                $this->degustation->campagne,
                $this->degustation->cvi,
                $this->degustation->raison_sociale,
                $this->degustation->adresse,
                $this->degustation->code_postal,
                $this->degustation->commune,
                $this->degustation->date_degustation,
                $this->degustation->organisme);

        foreach($this->degustation->prelevements as $prelevement) {
            $libelle_complet = $prelevement->getLibelleComplet();
            $csv .= sprintf("%s;%s;\"%s\";%s;%s;%s;%s;%s;%s", $ligne_base, trim($libelle_complet), $prelevement->denomination_complementaire, $prelevement->exist('fermentation_lactique') && $prelevement->get('fermentation_lactique') ? "FML" : null, $prelevement->composition, $prelevement->anonymat_degustation, $prelevement->anonymat_prelevement_complet, $prelevement->cuve, $this->formatFloat($prelevement->volume_revendique));
            foreach(DegustationClient::$note_type_libelles as $keyNote => $libelleNote) {
                $note = null;
                $defauts = null;
                if($prelevement->exist('notes/'.$keyNote)) {
                    $note = $prelevement->get('notes/'.$keyNote)->note . " - " . $prelevement->get('notes/'.$keyNote)->getLibelle();
                    if($prelevement->get('notes/'.$keyNote)->note == "X") {
                        $note = "Non dégusté";
                    }
                    if(count($prelevement->get('notes/'.$keyNote)->defauts)) {
                        $defauts = "\"".implode(", ", $prelevement->get('notes/'.$keyNote)->defauts->toArray(true, false))."\"";
                    }
                }
                $csv .= ";".$note.";".$defauts;
            }
            $csv .= "\n";
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
