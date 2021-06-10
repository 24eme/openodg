<?php

class ExportChgtDenomCSV implements InterfaceDeclarationExportCsv {

    protected $document = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        return "Type;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email Operateur;Origine Num dossier;Origine Num lot;Origine logement Opérateur;Origine Certification;Origine Genre;Origine Appellation;Origine Mention;Origine Lieu;Origine Couleur;Origine Cepage;Origine Produit;Origine Cépages;Origine Millésime;Origine Spécificités;Origine Volume;Type de changement;Num dossier;Num lot;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume changé;Prelevable;Preleve;Num dossier restant;Num lot restant;Volume restant;Mode de declaration;Date de validation;Date de validation ODG;Organisme;Origine Doc Id;Origin Lot unique Id;Origin Hash produit;Doc Id;Lot unique Id;Lot unique Id restant;Hash produit\n";
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

        $lotOrigine = $this->document->getLotOrigine();
        $lotChgt = $this->document->lots[0];
        $lotChgtRestant = null;
        if(count($this->document->lots) == 2) {
            $lotChgt = $this->document->lots[1];
            $lotChgtRestant = $this->document->lots[0];
        }


        $csv .= $this->document->type.";".
        $this->document->campagne.";".
        $this->document->identifiant.";".
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
        $this->document->getConfigProduitOrigine()->getCertification()->getKey().";".
        $this->document->getConfigProduitOrigine()->getGenre()->getKey().";".
        $this->document->getConfigProduitOrigine()->getAppellation()->getKey().";".
        $this->document->getConfigProduitOrigine()->getMention()->getKey().";".
        $this->document->getConfigProduitOrigine()->getLieu()->getKey().";".
        $this->document->getConfigProduitOrigine()->getCouleur()->getKey().";".
        $this->document->getConfigProduitOrigine()->getCepage()->getKey().";".
        $this->document->origine_produit_libelle.";".
        $lotOrigine->getCepagesLibelle().";".
        $this->document->origine_millesime.";".
        $this->document->origine_specificite.";".
        $this->document->origine_volume.";".
        $this->document->changement_type.";".
        $lotChgt->numero_dossier.";".
        $lotChgt->numero_archive.";".
        $this->document->changement_numero_logement_operateur.";".
        $this->document->getConfigProduitChangement()->getCertification()->getKey().";".
        $this->document->getConfigProduitChangement()->getGenre()->getKey().";".
        $this->document->getConfigProduitChangement()->getAppellation()->getKey().";".
        $this->document->getConfigProduitChangement()->getMention()->getKey().";".
        $this->document->getConfigProduitChangement()->getLieu()->getKey().";".
        $this->document->getConfigProduitChangement()->getCouleur()->getKey().";".
        $this->document->getConfigProduitChangement()->getCepage()->getKey().";".
        $this->document->changement_produit_libelle.";".
        $lotChgt->getCepagesLibelle().";".
        $this->document->changement_millesime.";".
        $this->document->changement_specificite.";".
        $this->document->changement_volume.";".
        $this->document->changement_affectable.";".
        $lotChgt->isAffecte().";".
        ($lotChgtRestant ? $lotChgtRestant->numero_dossier : null).";".
        ($lotChgtRestant ? $lotChgtRestant->numero_dossier : null).";".
        ($lotChgtRestant ? $lotChgtRestant->volume : null).";".
        $this->document->validation.";".
        $this->document->validation_odg.";".
        Organisme::getCurrentOrganisme().";".
        $this->document->changement_origine_id_document.";".
        $this->document->changement_origine_lot_unique_id.";".
        $this->document->origine_produit_hash.";".
        $this->document->_id.";".
        $lotChgt->unique_id.";".
        $lotChgtRestant->unique_id.";".
        $this->document->changement_produit_hash."\n";

        return $csv;
    }

}
