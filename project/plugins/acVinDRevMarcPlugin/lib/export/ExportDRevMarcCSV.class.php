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

    public static function getHeaderCsv() {

        return "Campagne;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Produit;Superficie revendiqué;Volume revendiqué;prelevement à partir du;nombre de lots;Adresse du chai;Code postal du Chai;Commune du Chai;Type de déclaration\n";
    }

    public function __construct($drevmarc, $header = true) {
        $this->drevmarc = $drevmarc;
        $this->header = $header;
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

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
