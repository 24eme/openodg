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
        
        return $this->parcellaireExport();
    }

    private function parcellaireExport() {
        $export = "";
        if($this->header) { 
            $export = self::getHeaderCsv();
        }
        
        foreach ($this->parcellaire->declaration->getProduitsCepageDetails() as $parcelle) {
            $export .= $this->exportParcelleByAcheteurs($parcelle);
        }

        return $export;
    }

    private function exportParcelleByAcheteurs($parcelle) {
        $export = "";
        if(!count($parcelle->getCepage()->acheteurs)) {
            return $this->exportParcelle($parcelle);
        }
        foreach ($parcelle->getCepage()->acheteurs as $lieu_acheteurs) {

            foreach ($lieu_acheteurs as $typeAcheteur => $acheteurs) {
                if ($typeAcheteur == ParcellaireClient::DESTINATION_SUR_PLACE) {
                    $export .= $this->exportParcelle($parcelle);
                }
                if (($typeAcheteur == ParcellaireClient::DESTINATION_CAVE_COOPERATIVE) || ($typeAcheteur == ParcellaireClient::DESTINATION_NEGOCIANT)) {
                    $export .= $this->exportParcelle($parcelle, $acheteurs);
                }
            }
        }

        return $export;
    }

    private function exportParcelle($parcelle, $acheteurs = null) {
        $export = "";
        if (!$acheteurs) {
            $export.=$parcelle->commune . ";";
            $export.=$parcelle->section . ";";
            $export.=$parcelle->numero_parcelle . ";";
            $export.= $parcelle->getAppellation()->getLibelle() . ";";
            $export.= $parcelle->getLieuLibelle() . ";";
            $export.= $parcelle->getCepageLibelle() . ";";
            $export.=sprintf("%01.02f", $parcelle->superficie) . ";";
            $export.=$this->parcellaire->campagne . ";";
            $export.=$this->parcellaire->declarant->cvi . ";";
            $export.= $this->parcellaire->declarant->nom . ";";
            $export.=$this->parcellaire->declarant->adresse . ";";
            $export.=$this->parcellaire->declarant->code_postal . ";";
            $export.=$this->parcellaire->declarant->commune . ";";
            $export.=";;;;";
            $export.= $this->parcellaire->validation.";";
            $export.= ($this->parcellaire->isPapier()) ? "PAPIER" : "TÉLÉDECLARATION";
            $export.="\n";
        } else {
            foreach ($acheteurs as $cviAcheteur => $acheteur) {
                $export.=$parcelle->commune . ";";
                $export.=$parcelle->section . ";";
                $export.=$parcelle->numero_parcelle . ";";
                $export.= $parcelle->getAppellation()->getLibelle() . ";";
                $export.= $parcelle->getLieuLibelle() . ";";
                $export.= $parcelle->getCepageLibelle() . ";";
                $export.=sprintf("%01.02f", $parcelle->superficie) . ";";
                $export.=$this->parcellaire->campagne . ";";
                $export.=$this->parcellaire->declarant->cvi . ";";
                $export.= $this->parcellaire->declarant->nom . ";";
                $export.=$this->parcellaire->declarant->adresse . ";";
                $export.=$this->parcellaire->declarant->code_postal . ";";
                $export.=$this->parcellaire->declarant->commune . ";";
                $export.= (count($acheteurs) == 1) ? "DEDIÉE;" : "PARTAGÉE;";
                $export.=$acheteur->cvi . ";";
                $export.=$acheteur->nom . ";";
                $export.= (!$this->parcellaire->isPapier() ? (($this->parcellaire->autorisation_acheteur) ? "AUTORISÉE" : "REFUSÉE") : "").";";
                $export.= $this->parcellaire->validation.";";
                $export.= ($this->parcellaire->isPapier()) ? "PAPIER" : "TÉLÉDECLARATION";
                $export.="\n";
            }
        }
        return $export;
    }

}
