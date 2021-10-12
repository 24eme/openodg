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
class ExportParcellaireAffectationCSV implements InterfaceDeclarationExportCsv {

    protected $parcellaire = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Commune Parcelle;Section Parcelle;Numéro Parcelle;Appellation;Lieu;Cépage;Superficie;Campagne;CVI;Nom;Adresse;Code postal;Commune;Parcelle partagée ou dédiée;Acheteur CVI;Acheteur Nom;Autorisation de transmission;Date de validation / récéption;Type de transmission;VTSGN\n";
    }

    public function __construct($parcellaire, $header = true) {
        $this->parcellaire = $parcellaire;
        $this->header = $header;
    }

    public function getFileName($with_rev = true, $nomFilter = null) {

      return self::buildFileName($this->parcellaire, $with_rev, $nomFilter);
    }

    public static function buildFileName($parcellaire, $with_rev = false, $nomFilter = null) {

        $prefixName = $parcellaire->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $parcellaire->identifiant, $parcellaire->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($parcellaire->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $parcellaire->_rev;
        }

        return $filename . '.csv';
    }

    public function export($cviFilter = null) {
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

            if(!count($acheteurs) && count($acheteursGlobal) == 1) {
                $acheteurs = $acheteursGlobal;
            }

            if ($cviFilter && $this->parcellaire->hasRepartitionParParcelle($parcelle->getCepage()->getKey())
                && in_array($cviFilter, $this->parcellaire->hasRepartitionParParcelle($parcelle->getCepage()->getKey())))
            {
                if (! $parcelle->exist('acheteurs')
                    || ($parcelle->exist('acheteurs') && in_array($cviFilter, $parcelle->acheteurs->toArray(true, false)) === false))
                {
                    continue;
                }

                $acheteur = null;
                foreach ($acheteurs as $a) {
                    if ($a->cvi == $cviFilter) {
                        $acheteur = $a;
                    }
                }

                $export .= $this->addLigne($parcelle, $acheteurs, $acheteur);
            } else {
                foreach($acheteurs as $acheteur) {
                    if($cviFilter && $cviFilter != $acheteur->cvi) {
                        continue;
                    }

                    $export .= $this->addLigne($parcelle, $acheteurs, $acheteur);
                }
            }
        }

        return $export;
    }

    private function addLigne($parcelle, $acheteurs, $acheteur)
    {
        $export  = $parcelle->commune . ";";
        $export .= $parcelle->section . ";";
        $export .= $parcelle->numero_parcelle . ";";
        $export .= $parcelle->getAppellation()->getLibelle() . ";";
        $export .= str_replace(array('"',';'),array('',''),$parcelle->getLieuLibelle()) . ";";
        $export .= $parcelle->getCepageLibelle() . ";";
        $export .= sprintf("%01.02f", $parcelle->superficie) . ";";
        $export .= $this->parcellaire->campagne . ";";
        $export .= $this->parcellaire->declarant->cvi . ";";
        $export .= $this->parcellaire->declarant->nom . ";";
        $export .= $this->parcellaire->declarant->adresse . ";";
        $export .= $this->parcellaire->declarant->code_postal . ";";
        $export .= $this->parcellaire->declarant->commune . ";";
        if($acheteur->cvi != $this->parcellaire->identifiant) {
            $export .= (count($acheteurs) == 1) ? "DEDIÉE;" : "PARTAGÉE;";
            $export .= $acheteur->cvi . ";";
            $export .= $acheteur->nom . ";";
            $export .= (!$this->parcellaire->isPapier() ? (($this->parcellaire->autorisation_acheteur) ? "AUTORISÉE" : "REFUSÉE") : "").";";
        } else {
            $export .=";;;;";
        }
        $export .= $this->parcellaire->validation.";";
        $export .= ($this->parcellaire->isPapier()) ? "PAPIER" : "TÉLÉDECLARATION";
        $export .= ";".(($parcelle->vtsgn) ? "VTSGN" : "");
        $export .="\n";

        return $export;
    }
}
