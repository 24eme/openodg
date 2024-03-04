<?php

class ExportParcellaireManquantCSV implements InterfaceDeclarationExportCsv {

    protected $doc = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        return "Campagne;Identifiant Société;Identifiant Opérateur;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de déclaration;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;INAO;Produit;IDU;Code commune;Commune;Lieu-dit;Section;Numéro parcelle;Cépage;Année de plantation;Surface;Densite;Pourcentage de manquants;Signataire;Date de validation;Mode de declaration;Doc Id;Hash produit;Doc Region;Pseudo Production Id\n";
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

        $mode = ($this->doc->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        if($this->doc->isAutomatique()) {
            $mode = 'AUTOMATIQUE';
        }

        if (!$this->doc->getEtablissementObject() || !$this->doc->getEtablissementObject()->getSociete()) {
            throw new sfException("Problème avec la société (ou l'établissement) concernant ".$this->doc->id);
        }
        $ligne_base = sprintf("%s;\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"", $this->doc->campagne, $this->doc->getEtablissementObject()->getSociete()->identifiant, $this->doc->identifiant, $this->doc->declarant->cvi, $this->doc->declarant->siret, $this->protectStr($this->doc->declarant->raison_sociale), $this->protectStr($this->doc->declarant->adresse), $this->doc->declarant->code_postal, $this->protectStr($this->doc->declarant->commune), $this->doc->declarant->email);
        $isempty = true;
        foreach ($this->doc->declaration->getParcellesByCommune() as $commune => $parcelles) {
        	foreach ($parcelles as $parcelle) {
            	$configProduit = $parcelle->getProduit()->getConfig();

            	$inao = $configProduit->getCodeDouane();

            	$libelle_complet = $this->protectStr(trim($parcelle->getProduit()->getLibelle()));
                $csv .= sprintf("%s;Parcellaire Manquant;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", $ligne_base,
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
            	$parcelle->densite,
            	$this->formatFloat($parcelle->pourcentage),
            	$this->protectStr($this->doc->signataire),
            	$this->doc->validation,
            	$mode,
                $this->doc->_id,
                $parcelle->getProduit()->getHash(),
                $this->doc->region,
                $this->doc->declarant->cvi."-".substr($this->doc->campagne, 0, 4)
                );
        	}
        }
        $csv .= sprintf("%s;Parcellaire Manquant;;;;;;;;;;;;;;;;;;;;;;%s;%s;%s;;%s;%s\n", $ligne_base, $this->doc->validation, $mode, $this->doc->_id, $this->doc->region,$this->doc->declarant->cvi."-".substr($this->doc->campagne, 0, 4));

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
