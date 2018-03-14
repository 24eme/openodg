<?php

class ExportParcellaireIrrigableCSV implements InterfaceDeclarationExportCsv {

    protected $doc = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Campagne;Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO;Produit;Identifiant parcelle;Code commune;Commune;Lieu-dit;Section;Numéro parcelle;Cépage;Année de plantation;Surface;Type de matériel;Type de ressource;Type de declaration\n";
    }

    public function __construct($doc, $header = true) {
        $this->doc = $doc;
        $this->header = $header;
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

        $mode = ($this->doc->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        if($this->doc->isAutomatique()) {
            $mode = 'AUTOMATIQUE';
        }

        $ligne_base = sprintf("%s;\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"", $this->doc->campagne, $this->doc->identifiant, $this->doc->declarant->cvi, $this->doc->declarant->siret, $this->protectStr($this->doc->declarant->raison_sociale), $this->protectStr($this->doc->declarant->adresse), $this->doc->declarant->code_postal, $this->protectStr($this->doc->declarant->commune), $this->doc->declarant->email);
        foreach ($this->doc->declaration->getParcellesByCommune() as $commune => $parcelles) {
        	foreach ($parcelles as $parcelle) {
            	$configProduit = $parcelle->getProduit()->getConfig();
            	
            	$certification = $configProduit->getCertification()->getKey();
            	$genre = $configProduit->getGenre()->getKey();
            	$appellation = $configProduit->getAppellation()->getKey();
            	$mention = $configProduit->getMention()->getKey();
            	$lieu = $configProduit->getLieu()->getKey();
            	$couleur = $configProduit->getCouleur()->getKey();
            	$cepage = $configProduit->getCepage()->getKey();
            	$inao = $configProduit->getCodeDouane();

            	$libelle_complet = $this->protectStr(trim($parcelle->getProduit()->getLibelle()));
            	$csv .= sprintf("%s;Parcellaire Irrigable;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", $ligne_base,
            	$certification,$genre,$appellation,$mention,$lieu,$couleur,$cepage,$inao,$libelle_complet, 
            	$parcelle->idu,
            	$parcelle->code_commune,
            	$this->protectStr($parcelle->commune), 
            	$this->protectStr($parcelle->lieu), 
            	$parcelle->section,
            	$parcelle->numero_parcelle,
            	$this->protectStr($parcelle->cepage), 
            	$this->protectStr($parcelle->campagne_plantation), 
            	$this->formatFloat($parcelle->superficie),
            	$this->protectStr($parcelle->materiel), 
            	$this->protectStr($parcelle->ressource),
            	$mode);
        	}
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
