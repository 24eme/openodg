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
class ExportTravauxMarcCSV implements InterfaceDeclarationExportCsv {

    protected $travauxmarc = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        return "Campagne;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Date de distillation;Distillation par un prestataire;Alambic décrit dans la DI;Adresse de distillation;Code postal de distillation;Commune de distillation;Nom du fournisseur;Date de livraison;Quantité de marc livré (en kg);Type de déclaration\n";
    }

    public function __construct($travauxmarc, $header = true, $region = null) {
        $this->travauxmarc = $travauxmarc;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {

        return $this->travauxmarc->_id . '_' . $this->travauxmarc->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $mode = ($this->travauxmarc->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        $ligneBase = sprintf("%s;%s;%s;\"%s\";\"%s\";%s;%s;%s;%s;%s;%s;\"%s\";%s;%s", $this->travauxmarc->campagne, $this->travauxmarc->declarant->cvi, $this->travauxmarc->declarant->siret, $this->travauxmarc->declarant->raison_sociale, $this->travauxmarc->declarant->adresse, $this->travauxmarc->declarant->code_postal, $this->travauxmarc->declarant->commune, $this->travauxmarc->declarant->email, $this->travauxmarc->date_distillation, $this->travauxmarc->distillation_prestataire, $this->travauxmarc->alambic_connu, $this->travauxmarc->adresse_distillation->adresse, $this->travauxmarc->adresse_distillation->code_postal, $this->travauxmarc->adresse_distillation->commune);

        foreach($this->travauxmarc->fournisseurs as $fournisseur) {
            $csv .= $ligneBase.sprintf(";\"%s\";%s;%s;%s\n", $fournisseur->nom, $fournisseur->date_livraison, $this->formatFloat($fournisseur->quantite), $mode);
        }

        if(!count($this->travauxmarc->fournisseurs)) {
            $csv .= $ligneBase.";;;;;".$mode;
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
