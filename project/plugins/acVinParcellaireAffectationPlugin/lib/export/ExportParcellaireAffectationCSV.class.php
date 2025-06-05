<?php

class ExportParcellaireAffectationCSV implements InterfaceDeclarationExportCsv {

    protected $doc = null;
    protected $header = false;
    protected $region = null;
    protected $destination = null;

    public static function getHeaderCsv() {

        return "Campagne;Identifiant Société;Identifiant Opérateur;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de déclaration;Certification;Genre;Appellation;Mention;Lieu;Produit;IDU;Code commune;Commune;Lieu-dit;Section;Numéro parcelle;Cépage;Année de plantation;Surface;Dénomination complémentaire;Surface identifiée;Destination identifiant;Destination nom;Destination CVI;Destination superficie;Signataire;Date de validation;Type de declaration;Pseudo production id;doc id\n";
    }

    public function __construct($doc, $header = true, $region = null, $destinationIdentifiant = null) {
        $this->doc = $doc;
        $this->header = $header;
        $this->region = $region;
        $this->destinationIdentifiant = $destinationIdentifiant;
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
        foreach ($this->doc->declaration->getParcelles() as $parcelle) {
            if (!$parcelle->affectee) {
                continue;
            }

            if (! $parcelle->exist('destinations')) {
                $destination = new stdclass();
                $destination->identifiant = null;
                $destination->nom = null;
                $destination->cvi = null;
                $destination->superficie = null;
                $destinations = [$destination];
            } else {
                $destinations = $parcelle->destinations;
            }

            foreach ($destinations as $destination) {
                if($this->destinationIdentifiant && $this->destinationIdentifiant != $destination->identifiant) {
                    continue;
                }
            	$configProduit = $parcelle->getProduit()->getConfig();

            	$certification = $configProduit->getCertification()->getKey();
            	$genre = $configProduit->getGenre()->getKey();
            	$appellation = $configProduit->getAppellation()->getKey();
            	$mention = $configProduit->getMention()->getKey();
            	$lieu = $configProduit->getLieu()->getKey();

            	$libelle_complet = $this->protectStr(trim($parcelle->getProduit()->getLibelle()));
                $csv .= sprintf("%s;Parcellaire Affectation;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", $ligne_base,
            	$certification,$genre,$appellation,$mention,$lieu,$libelle_complet,
            	$this->protectStr($parcelle->idu),
            	$parcelle->code_commune,
            	$this->protectStr($parcelle->commune),
            	$this->protectStr($parcelle->lieu),
            	$parcelle->section,
            	$parcelle->numero_parcelle,
            	$this->protectStr($parcelle->cepage),
            	$this->protectStr($parcelle->campagne_plantation),
            	$this->formatFloat($parcelle->getSuperficieParcellaire()),
                $this->protectStr(strtoupper($parcelle->getDgc())),
            	$this->formatFloat($parcelle->superficie),
                $destination->identifiant,
                $this->protectStr($destination->nom),
                $destination->cvi,
                $this->formatFloat($destination->superficie),
                $this->doc->exist('signataire') ? $this->protectStr($this->doc->signataire) : null,
            	$this->doc->validation,
            	$mode,
                str_replace(explode('-', $this->doc->_id)[0].'-', '', $this->doc->_id),
                $this->doc->_id
                );
            }
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
