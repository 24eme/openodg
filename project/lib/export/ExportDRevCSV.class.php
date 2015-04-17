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
class ExportDRevCSV {

    protected $drev = null;
    protected $header = false;

    public function getHeaderCsv() {

        return "CVI Vinificateur;Nom Vinificateur;Produit;Superficie revendiqué;Volume revendiqué;type de prevelement;prelevement à partir du;nombre de lots;chai adresse;chai code postal;chai commune;\n";
    }

    public function __construct($drev, $header = true) {
        $this->drev = $drev;
        $this->header = $header;
    }

    public function getFileName() {
        
        return $this->drev->_id . '_' . $this->drev->_rev . '.csv';
    }

    public function export() {

        echo $this->getHeaderCsv();
        foreach($this->drev->declaration->getProduits() as $produit) {
            echo sprintf("%s;%s;%s;%s;%s\n", $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, trim($produit->getLibelleComplet()), $produit->superficie_revendique, $produit->volume_revendique);
            foreach($produit->getProduitsCepage() as $detail) {
                echo sprintf("%s;%s;%s;%s;%s\n", $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, trim($detail->getLibelle()), $detail->superficie_revendique_total, $detail->volume_revendique_total);
            }
        }

        foreach($this->drev->getPrelevementsOrdered() as $prelevementsOrdered) {
            foreach ($prelevementsOrdered->prelevements as $prelevement) {
                echo sprintf("%s;%s;%s;;;%s;%s;%s;%s;%s;%s\n", $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, trim($prelevement->libelle_produit), $prelevementsOrdered->libelle, $prelevement->date, ($prelevement->total_lots) ? $prelevement->total_lots : "", $prelevement->getChai()->adresse, $prelevement->getChai()->commune, $prelevement->getChai()->code_postal);
            }
        }
    }

}
