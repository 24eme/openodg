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
class ExportParcellaireCSV {

    protected $parcellaire = null;
    protected $campagne = null;

    public function __construct($campagne = null, $parcellaire = null) {
        $this->campagne = $campagne;
        $this->parcellaire = $parcellaire;
    }

    public function getFileName() {
        if ($this->isParcellaireExport()) {
            return 'exportCsv_Parcellaire_' . $this->parcellaire->declarant->cvi . '_' . date('Ymd');
        }
        if ($this->isCampagneExport()) {
            return 'exportCsv_Parcellaire_' . $this->campagne . '_' . date('Ymd');
        }
    }

    public function export() {
        if ($this->isParcellaireExport()) {
            return $this->parcellaireExport();
        }
        return "";
    }

    private function parcellaireExport() {
        $export = "Commune Parcelle;Section Parcelle;Numéro Parcelle;Appellation;Cépage;Supérficie;CVI;Nom;Adresse;Code postal;Commune;Téléphone;Parcelle partagée;Acheteur CVI;Acheteur Nom\n";
        foreach ($this->parcellaire->getAllParcellesByAppellations() as $parcellesByAppellation) {
            foreach ($parcellesByAppellation->parcelles as $parcelle) {
                $export.=$this->exportParcelleByAcheteurs($parcellesByAppellation, $parcelle);
            }
        }
        return $export;
    }

    private function exportParcelleByAcheteurs($parcellesByAppellation, $parcelle) {
        $exportParcelleByAcheteurs = "";
        foreach ($parcelle->getCepage()->acheteurs->lieu as $typeAcheteur => $acheteurs) {
            if ($typeAcheteur == ParcellaireClient::DESTINATION_SUR_PLACE) {
                $exportParcelleByAcheteurs.=$this->exportParcelle($parcellesByAppellation, $parcelle);
            }
            if (($typeAcheteur == ParcellaireClient::DESTINATION_CAVE_COOPERATIVE) || ($typeAcheteur == ParcellaireClient::DESTINATION_NEGOCIANT)) {
                $exportParcelleByAcheteurs.=$this->exportParcelle($parcellesByAppellation, $parcelle, $acheteurs);
            }
        }

        return $exportParcelleByAcheteurs;
    }

    private function exportParcelle($parcellesByAppellation, $parcelle, $acheteurs = null) {
        $export = "";
        if (!$acheteurs) {
            $export.=$parcelle->commune . ";";
            $export.=$parcelle->section . ";";
            $export.=$parcelle->numero_parcelle . ";";
            $export.=$parcellesByAppellation->appellation->getKey() . ";";
            $export.=$parcelle->getCepageLibelle() . ";";
            $export.=sprintf("%01.02f", $parcelle->superficie) . ";";
            $export.=$this->parcellaire->declarant->cvi . ";";
            $export.= $this->parcellaire->declarant->nom . ";";
            $export.=$this->parcellaire->declarant->adresse . ";";
            $export.=$this->parcellaire->declarant->code_postal . ";";
            $export.=$this->parcellaire->declarant->commune . ";";
            $export.=$this->parcellaire->declarant->telephone.";";
            $export.=";;";
            $export.="\n";
        } else {
            foreach ($acheteurs as $cviAcheteur => $acheteur) {
                $export.=$parcelle->commune . ";";
                $export.=$parcelle->section . ";";
                $export.=$parcelle->numero_parcelle . ";";
                $export.=$parcellesByAppellation->appellation->getKey() . ";";
                $export.=$parcelle->getCepageLibelle() . ";";
                $export.=sprintf("%01.02f", $parcelle->superficie) . ";";
                $export.=$this->parcellaire->declarant->cvi . ";";
                $export.= $this->parcellaire->declarant->nom . ";";
                $export.=$this->parcellaire->declarant->adresse . ";";
                $export.=$this->parcellaire->declarant->code_postal . ";";
                $export.=$this->parcellaire->declarant->commune . ";";
                $export.=$this->parcellaire->declarant->telephone.";";
                $export.= (count($acheteurs) == 1) ? "NON;" : "OUI;";
                $export.=$acheteur->cvi . ";";
                $export.=$acheteur->nom;
                $export.="\n";
            }
        }
        return $export;
    }

    private function isParcellaireExport() {
        return boolval($this->parcellaire);
    }

    private function isCampagneExport() {
        return boolval($this->campagne);
    }

}
