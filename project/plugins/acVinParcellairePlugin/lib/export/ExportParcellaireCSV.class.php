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
class ExportParcellaireCSV implements InterfaceDeclarationExportCsv {

    protected $parcellaire = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Commune Parcelle;Section Parcelle;Numéro Parcelle;Appellation;Lieu;Cépage;Superficie;Campagne;CVI;Nom;Adresse;Code postal;Commune;Parcelle partagée;Acheteur CVI;Acheteur Nom;Autorisation de transmission;Date de validation / récéption;Type de transmission\n";
    }

    public function __construct($parcellaire, $header = true) {
        $this->parcellaire = $parcellaire;
        $this->header = $header;
    }

    public function getFileName() {
        
        return $this->parcellaire->_id . '_' . $this->parcellaire->_rev . '.csv';
    }

    public function export() {
        $export = "";
        if($this->header) { 
            $export = self::getHeaderCsv();
        }

        if(!$this->parcellaire->validation) {

            return;
        }
        
        $acheteursGlobal = $this->parcellaire->getAcheteursByCVI();
        
        foreach ($this->parcellaire->declaration->getProduitsCepageDetails() as $parcelle) {
            $acheteurs = $parcelle->getAcheteursByCVI();
            
            if(!count($acheteurs) && count($acheteursGlobal) > 1) {
                //echo sprintf("ERROR pas d'acheteur : %s : %s !\n", $this->parcellaire->_id, $parcelle->getHash());
            }

            if(!count($acheteurs) && count($acheteursGlobal) == 0) {
                //echo sprintf("ERROR pas d'acheteurs du tout : %s : %s !\n", $this->parcellaire->_id, $parcelle->getHash());
            }

            if(!count($acheteurs) && count($acheteursGlobal) == 1) {
                $acheteurs = $acheteursGlobal;
            }

            foreach($acheteurs as $acheteur) {
                $export.= $parcelle->commune . ";";
                $export.= $parcelle->section . ";";
                $export.= $parcelle->numero_parcelle . ";";
                $export.= $parcelle->getAppellation()->getLibelle() . ";";
                $export.= $parcelle->getLieuLibelle() . ";";
                $export.= $parcelle->getCepageLibelle() . ";";
                $export.= sprintf("%01.02f", $parcelle->superficie) . ";";
                $export.= $this->parcellaire->campagne . ";";
                $export.= $this->parcellaire->declarant->cvi . ";";
                $export.= $this->parcellaire->declarant->nom . ";";
                $export.= $this->parcellaire->declarant->adresse . ";";
                $export.= $this->parcellaire->declarant->code_postal . ";";
                $export.= $this->parcellaire->declarant->commune . ";";
                if($acheteur->cvi != $this->parcellaire->identifiant) {
                    $export.= (count($acheteurs) == 1) ? "DEDIÉE;" : "PARTAGÉE;";
                    $export.= $acheteur->cvi . ";";
                    $export.= $acheteur->nom . ";";
                    $export.= (!$this->parcellaire->isPapier() ? (($this->parcellaire->autorisation_acheteur) ? "AUTORISÉE" : "REFUSÉE") : "").";";
                } else {
                    $export.=";;;;";
                }
                $export.= $this->parcellaire->validation.";";
                $export.= ($this->parcellaire->isPapier()) ? "PAPIER" : "TÉLÉDECLARATION";
                $export.="\n";
            }
        }

        return $export;
    }

}
