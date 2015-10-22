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
class ExportConstatsCSV implements InterfaceDeclarationExportCsv {

    protected $constats = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Campagne;CVI Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Produit;Type VT/SGN;Statut volume;Volume\n";
    }

    public function __construct($constats, $header = true) {
        $this->constats = $constats;
        $this->header = $header;
    }

    public function getFileName() {
        
        return $this->constats->_id . '_' . $this->constats->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        foreach($this->constats->constats as $constat) {
            if($constat->statut_volume != 'APPROUVE') {
                continue;
            }
            $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", $this->constats->campagne, $this->constats->cvi, $this->constats->raison_sociale, $this->constats->adresse, $this->constats->code_postal, $this->constats->commune, $this->constats->email, $constat->produit_libelle, $constat->type_vtsgn
, $constat->statut_volume, $this->formatFloat($constat->volume_obtenu));
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
