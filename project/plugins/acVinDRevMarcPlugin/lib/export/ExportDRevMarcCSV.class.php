<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportParcellairePdf
 *
 * @author mathurin
 */
class ExportDRevMarcCSV implements InterfaceDeclarationExportCsv {

    protected $drevmarc = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv() {

        return "Campagne;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Début Distillation;Fin Distillation;Quantité de Marc (kg);Volume total obtenu (hl);Titre alcoométrique volumique (Degré);Type de déclaration\n";
    }

    public function __construct($drevmarc, $header = true, $region = null, $extraFields = false) {
        $this->drevmarc = $drevmarc;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {

        return $this->drevmarc->_id . '_' . $this->drevmarc->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $mode = ($this->drevmarc->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        $csv .= sprintf("%s;\"%s\";\"%s\";%s;%s;\"%s\";%s;%s;%s;%s;%s;%s;%s;%s\n", $this->drevmarc->campagne, $this->drevmarc->declarant->cvi, $this->drevmarc->declarant->siret, $this->drevmarc->declarant->raison_sociale, $this->drevmarc->declarant->adresse, $this->drevmarc->declarant->code_postal, $this->drevmarc->declarant->commune, $this->drevmarc->declarant->email, $this->drevmarc->debut_distillation, $this->drevmarc->fin_distillation, $this->formatFloat($this->drevmarc->qte_marc), $this->formatFloat($this->drevmarc->volume_obtenu), $this->formatFloat($this->drevmarc->titre_alcool_vol), $mode);

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
