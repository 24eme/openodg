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

        return "Campagne;CVI Vinificateur;Nom Vinificateur;Type de ligne;Produit;Superficie revendiqué;Volume revendiqué;prelevement à partir du;nombre de lots;Adresse du chai;Code postal du Chai;Commune du Chai\n";
    }

    public function __construct($drev, $header = true) {
        $this->drev = $drev;
        $this->header = $header;
    }

    public function getFileName() {
        
        return $this->drev->_id . '_' . $this->drev->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= $this->getHeaderCsv();
        }

        foreach($this->drev->declaration->getProduits() as $produit) {
            $libelle_complet = $produit->getLibelleComplet();
            $csv .= sprintf("%s;%s;%s;Revendication;%s;%s;%s\n", $this->drev->campagne, $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, trim($libelle_complet), $produit->superficie_revendique, $produit->volume_revendique);
            foreach($produit->getProduitsCepage() as $detail) {
                $csv .= sprintf("%s;%s;%s;Revendication;%s;%s;%s\n", $this->drev->campagne, $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, trim($libelle_complet)." ".trim($detail->getLibelle()), $detail->superficie_revendique_total, $detail->volume_revendique_total);
            }
        }

        foreach($this->drev->getPrelevementsOrdered() as $prelevementsOrdered) {
            foreach ($drev->prelevements as $prelevement) {
                $chai = $prelevement->getChai();
                $csv .= sprintf("%s;%s;%s;%s;%s;;;%s;%s;%s;%s;%s\n", $this->drev->campagne, $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, $prelevementsOrdered->libelle, trim($prelevement->libelle_produit), $prelevement->date, ($prelevement->total_lots) ? $prelevement->total_lots : "", $chai->adresse, $chai->commune, $chai->code_postal);
                foreach($prelevement->lots as $lot) {
                    $csv .= sprintf("%s;%s;%s;%s;%s;;;%s;%s;%s;%s;%s\n", $this->drev->campagne, $this->drev->declarant->cvi, $this->drev->declarant->raison_sociale, $prelevementsOrdered->libelle, trim($prelevement->libelle_produit)." ".$lot->libelle, $prelevement->date, $lot->nb_hors_vtsgn, $chai->adresse, $chai->commune, $chai->code_postal);
                }
            }
        }

        return $csv;
    }
}
