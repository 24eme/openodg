<?php

class ExportDeclarationLotsCSV implements InterfaceDeclarationExportCsv {

    protected $document = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {

        return "Type;Campagne;Identifiant;Famille;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email Operateur;Num dossier;Num lot;Date lot;Date de commission;Num logement Opérateur;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume;Destination;Date de destination;Pays de destination;Centilisation;Elevage;Eleve;Prelevable;Preleve;Changé;Logement Adresse;Mode de declaration;Date de validation;Date de validation ODG;Date de degustation voulue;Date d'envoi OI;Type document douanier if exists;Organisme;Doc Id;Lot unique Id;Hash produit;Pseudo Production Id\n";
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
    	return '"'.str_replace(array('\n', '"', ';'), array('', '', '−'), $str).'"';
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

        foreach($this->document->getLots() as $lot) {
            if(!$this->document->validation && $lot->isCurrent()) {
                continue;
            }
            $keyproduit = '';
            if ($lot->getConfigProduit()) {
                $keyproduit = DeclarationExportCsv::getProduitKeysCsv($lot->getConfigProduit());
            }
            $docOrigine = $lot->getDocOrigine();

            $dateDegustationVoulue = null;
            if($lot->exist('date_degustation_voulue') && $lot->date_degustation_voulue) {
                $dateDegustationVoulue = $lot->date_degustation_voulue;
            } elseif($this->document->exist('date_degustation_voulue') && $this->document->date_degustation_voulue) {
                $dateDegustationVoulue = $this->document->date_degustation_voulue;
            }

            $csv .= $this->document->type.";".
            $this->document->campagne.";".
            $this->document->identifiant.";".
            $this->document->declarant->famille.";".
            $this->document->declarant->cvi.";".
            $this->document->declarant->siret.";".
            $this->protectStr($this->document->declarant->nom).";".
            $this->protectStr($this->document->declarant->adresse).";".
            $this->document->declarant->code_postal.";".
            $this->protectStr($this->document->declarant->commune).";".
            $this->document->declarant->email.";".
            $lot->numero_dossier.";".
            $lot->numero_archive.";".
            $lot->date.";".
            ($lot->date_commission ? $lot->date_commission : $lot->date) .";".
            $this->protectStr($lot->numero_logement_operateur).";".
            $keyproduit.';'.
            $lot->getProduitLibelle().";".
            $lot->getCepagesLibelle().";".
            $lot->millesime.";".
            $lot->specificite.";".
            $this->formatFloat($lot->volume).";".
            $lot->destination_type.";".
            $lot->destination_date.";".
            $lot->pays.";".
            $lot->centilisation.";".
            $lot->elevage.";".
            $lot->eleve.";".
            $lot->affectable.";".
            $lot->isAffecte().";".
            $lot->isChange().";".
            $this->protectStr($lot->adresse_logement).";".
            "PAPIER;".
            $docOrigine->validation.";".
            $docOrigine->validation_odg.";".
            $dateDegustationVoulue.";".
            (($docOrigine && $docOrigine->exist('envoi_oi')) ? $docOrigine->envoi_oi : null).";".
            (($docOrigine->hasDocumentDouanier()) ? $docOrigine->getDocumentDouanierType() : null).";".
            Organisme::getCurrentOrganisme().";".
            $docOrigine->_id.";".
            $lot->unique_id.";".
            $lot->produit_hash.";".
            $this->document->declarant->cvi."-".substr($this->document->campagne, 0, 4).
            "\n";
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
