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
    protected $region = null;

    public static function getHeaderCsv() {

        return "Campagne;CVI;Nom;Adresse;Code postal;Commune;Email;Statut;Raison du refus;Date de signature;Appellation;Lieu/Lieu-dit;Couleur;Cépage;Dénomination;Type VT/SGN;Date RDV raisin;Agent RDV Raisin;Quantité Raisin;Degré potentiel Raisin;Date RDV volume;Agent RDV volume;Volume obtenu;Degré potentiel Volume;Mail envoyé\n";
    }

    public function __construct($constats, $header = true, $region = null) {
        $this->constats = $constats;
        $this->header = $header;
        $this->region = $region;
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
            $produitConfig = $constat->getProduitConfig();

            $lieu = null;
            if($produitConfig && $produitConfig->hasLieuEditable()) {
                $lieu = $constat->denomination_lieu_dit;
            } elseif($produitConfig) {
                $lieu = $constat->getProduitConfig()->getLieu()->getLibelleLong();
            }

            $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
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
                        ($constat->getProduitConfig()) ? $constat->getProduitConfig()->getAppellation()->getLibelleLong() : null,
                        $lieu,
                        ($constat->getProduitConfig()) ? $constat->getProduitConfig()->getCouleur()->getLibelleLong() : null,
                        ($constat->getProduitConfig()) ? $constat->getProduitConfig()->getLibelleLong() : null,
                        ($constat->denomination_lieu_dit != $lieu) ? $constat->denomination_lieu_dit : null,
                        $constat->type_vtsgn,
                        $constat->getRDVDateHeure('raisin'),
                        $constat->getRDVAgentNom('raisin'),
                        sprintf("%s %s%s", $this->formatFloat($constat->nb_contenant), $constat->contenant_libelle, ($constat->nb_contenant > 1) ? "s" : ""),
                        $this->formatFloat($constat->degre_potentiel_raisin),
                        $constat->getRDVDateHeure('volume'),
                        $constat->getRDVAgentNom('volume'),
                        $this->formatFloat($constat->volume_obtenu),
                        $this->formatFloat($constat->degre_potentiel_volume),
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

        if($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE && $constat->statut_volume == ConstatsClient::STATUT_REFUSE && $constat->raison_refus == ConstatsClient::RAISON_REFUS_ASSEMBLE) {

            return "Assemblé";
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
