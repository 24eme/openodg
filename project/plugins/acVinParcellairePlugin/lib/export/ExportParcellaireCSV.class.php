<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportParcellaireCSV
 *
 * @author mathurin
 */
class ExportParcellaireCSV implements InterfaceDeclarationExportCsv {

    protected $doc = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        return "Identifiant Société;Identifiant Opérateur;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de déclaration;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO;Produit;IDU;Code commune;Commune;Lieu-dit;Section;Numéro parcelle;Cépage;Année de plantation;Surface;Ecart pieds;Ecart rang;Type de declaration\n";
    }

    public function __construct($doc, $header = true, $region = null) {
        $this->doc = $doc;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {

        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function protectStr($str) {
    	return str_replace('"', '', $str);
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $mode = 'TELEDECLARATION';
		$emails = explode(';', $this->doc->declarant->email);
		$email = isset($emails[0])? trim($emails[0]) : null;
        $ligne_base = sprintf("\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"", $this->doc->getEtablissementObject()->getSociete()->identifiant, $this->doc->identifiant, $this->doc->declarant->cvi, $this->formatSiret($this->doc->declarant->siret), $this->protectStr($this->doc->declarant->raison_sociale), $this->protectStr($this->doc->declarant->adresse), $this->doc->declarant->code_postal, $this->protectStr($this->doc->declarant->commune), $email);
        foreach ($this->doc->declaration->getParcellesByCommune() as $commune => $parcelles) {
        	foreach ($parcelles as $parcelle) {
            	$configProduit = $parcelle->getProduit()->getConfig();

            	$inao = $configProduit->getCodeDouane();

            	$libelle_complet = $this->protectStr(trim($parcelle->getProduit()->getLibelle()));
            	$csv .= sprintf("%s;Parcellaire;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", $ligne_base,
                DeclarationExportCsv::getProduitKeysCsv($configProduit),
                $inao,$libelle_complet,
            	$this->protectStr($parcelle->idu),
            	$parcelle->code_commune,
            	$this->protectStr($parcelle->commune),
            	$this->protectStr($parcelle->lieu),
            	$parcelle->section,
            	$parcelle->numero_parcelle,
            	$this->protectStr($parcelle->cepage),
            	$this->protectStr($parcelle->campagne_plantation),
            	$this->formatFloat($parcelle->superficie),
            	$this->protectStr($parcelle->ecart_pieds),
            	$this->protectStr($parcelle->ecart_rang),
            	$mode);
        	}
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    protected function formatSiret($siret) {
      //return $siret;
      return preg_replace('/^(\d\d\d)(\d\d\d)(\d\d\d)/', '\1 \2 \3 ', $siret);
    }


}
