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
class ExportConstatsCSV implements InterfaceDeclarationExportCsv {

    protected $constats = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Campagne;CVI;Nom;Adresse;Code postal;Commune;Email;Statut;Raison du refus;Date de signature;Produit;Denomination / Lieu-dit;Type VT/SGN;Date RDV raisin;Agent RDV Raisin;Date RDV volume;Agent RDV volume;Quantité Raisin;Degré potentiel Raisin;Degré potentiel Volume;Volume obtenu;Mail envoyé\n";
    }

    public function __construct($constats, $header = true) {
        $this->constats = $constats;
        $this->header = $header;
    }

    public function getFileName() {
        
        return $this->constats->_id . '_' . $this->constats->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        foreach($this->constats->constats as $constat) {

            $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", 
                        $this->constats->campagne, 
                        $this->constats->cvi, 
                        $this->constats->raison_sociale, 
                        $this->constats->adresse, 
                        $this->constats->code_postal, 
                        $this->constats->commune, 
                        $this->constats->email, 
                        $this->getStatut($constat),
                        $constat->raison_refus_libelle,
                        $constat->date_signature,
                        $constat->produit_libelle, 
                        $constat->denomination_lieu_dit,
                        $constat->type_vtsgn,
                        $constat->getRDVDateHeure('raisin'),
                        $constat->getRDVAgentNom('raisin'),
                        $constat->getRDVDateHeure('volume'),
                        $constat->getRDVAgentNom('volume'),
                        sprintf("%s %s%s", $this->formatFloat($constat->nb_contenant), $constat->contenant_libelle, ($constat->nb_contenant > 1) ? "s" : ""),
                        $this->formatFloat($constat->degre_potentiel_raisin),
                        $this->formatFloat($constat->degre_potentiel_volume),
                        $this->formatFloat($constat->volume_obtenu),
                        (int)$constat->mail_sended);
        }

        return $csv;
    }

    protected function getStatut($constat) {
        if($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE && $constat->statut_volume == ConstatsClient::STATUT_APPROUVE) {

            return "Approuvé";
        }

        if($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE && $constat->statut_volume == ConstatsClient::STATUT_NONCONSTATE) {

            return "Non réalisé (Constat volume)";
        }

        if($constat->statut_raisin == ConstatsClient::STATUT_NONCONSTATE) {

            return "Non réalisé (Constat raisin)";
        }

        if($constat->statut_raisin == ConstatsClient::STATUT_REFUSE) {

            return "Refusé (Constat raisin)";
        }

        if($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE && $constat->statut_volume == ConstatsClient::STATUT_REFUSE) {

            return "Refusé (Constat volume)";
        }

        return "";
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
