<?php

class ExportChgtDenomCSV implements InterfaceDeclarationExportCsv {

    protected $document = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        return "Type;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email Operateur;Origine Num dossier;Origine Num lot;Origine logement Opérateur;Origine Certification;Origine Genre;Origine Appellation;Origine Mention;Origine Lieu;Origine Couleur;Origine Cepage;Origine Produit;Origine Cépages;Origine Millésime;Origine Spécificités;Origine Statut;Origine Volume;Type de changement;Num dossier;Num lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume changé;Prelevable;Preleve;Mode de declaration;Date de validation;Date de validation ODG;Organisme;Origine Doc Id;Origin Lot unique Id;Origin Hash produit;Doc Id;Lot unique Id;Hash produit\n";
    }

    public function __construct($document, $header = true, $region = null) {
        $this->document = $document;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {
        $name = $this->document->_id;
        $name .= ($this->region)? "_".$this->region : "";
        $name .= $this->document->_rev;
        return  $name . '.csv';
    }

    public function protectStr($str) {
    	return '"'.str_replace(';', '−', str_replace('"', '', $str)).'"';
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $mode = ($this->document->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';
        if($this->document->isAutomatique()) {
            $mode = 'AUTOMATIQUE';
        }

        if (!$this->document->isApprouve()) {
            $this->document->generateLots();
        }

        $lotOrigine = $this->document->getLotOrigine();
        $lotChgtRestant = $this->document->lots[0];
        if(isset($this->document->lots[1])) {
            $lotChgt = $this->document->lots[1];
        }

        $base = $lotChgtRestant->initial_type.";".
        $this->document->campagne.";".
        $this->document->identifiant.";".
        $this->document->declarant->famille.";".
        $this->document->declarant->cvi.";".
        $this->document->declarant->siret.";".
        '"'.$this->document->declarant->nom."\";".
        '"'.$this->document->declarant->adresse."\";".
        $this->document->declarant->code_postal.";".
        '"'.$this->document->declarant->commune."\";".
        $this->document->declarant->email.";".
        $lotOrigine->numero_dossier.";".
        $lotOrigine->numero_archive.";".
        $this->document->origine_numero_logement_operateur.";".
        DeclarationExportCsv::getProduitKeysCsv($this->document->getConfigProduitOrigine()).';'.
        $this->document->origine_produit_libelle.";".
        (($lotOrigine) ? $lotOrigine->getCepagesLibelle() : "").";".
        $this->document->origine_millesime.";".
        $this->document->origine_specificite.";".
        $this->document->origine_statut.";".
        $this->formatFloat($this->document->origine_volume).";";

        if($this->document->changement_type == ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT) {
            $csv .= $base.
            $this->document->changement_type.";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            ";".
            $this->formatFloat($this->document->changement_volume).";".
            ";".
            ";".
            $mode.";".
            $this->document->validation.";".
            $this->document->validation_odg.";".
            Organisme::getCurrentOrganisme().";".
            $this->document->changement_origine_id_document.";".
            $this->document->changement_origine_lot_unique_id.";".
            $this->document->origine_produit_hash.";".
            $this->document->_id.";".
            ";\n";
        } else {
            $csv .= $base.
            $this->document->changement_type.";".
            $lotChgt->numero_dossier.";".
            $lotChgt->numero_archive.";".
            $this->document->changement_numero_logement_operateur.";".
            DeclarationExportCsv::getProduitKeysCsv($this->document->getConfigProduitChangement()).';'.
            $this->document->changement_produit_libelle.";".
            $lotChgt->getCepagesLibelle().";".
            $this->document->changement_millesime.";".
            $this->document->changement_specificite.";".
            $this->formatFloat($this->document->changement_volume).";".
            $this->document->changement_affectable.";".
            $lotChgt->isAffecte().";".
            $mode.";".
            $this->document->validation.";".
            $this->document->validation_odg.";".
            Organisme::getCurrentOrganisme().";".
            $this->document->changement_origine_id_document.";".
            $this->document->changement_origine_lot_unique_id.";".
            $this->document->origine_produit_hash.";".
            $this->document->_id.";".
            $lotChgt->unique_id.";".
            $this->document->changement_produit_hash."\n";
        }

        return $csv;
    }

}
