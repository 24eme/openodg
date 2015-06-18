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

    public static function getHeaderCsv() {

        return "Campagne;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Produit;Superficie revendiqué;Volume revendiqué;prelevement à partir du;nombre de lots;Adresse du chai;Code postal du Chai;Commune du Chai;Type de déclaration\n";
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
            $csv .= self::getHeaderCsv();
        }

        $mode = ($this->drev->isPapier()) ? 'PAPIER' : 'TELEDECLARATION';

        if($this->drev->isAutomatique()) {
            $mode = 'AUTOMATIQUE';
        }

        $ligne_base = sprintf("%s;\"%s\";\"%s\";%s;%s;\"%s\";%s;%s", $this->drev->campagne, $this->drev->declarant->cvi, $this->drev->declarant->siret, $this->drev->declarant->raison_sociale, $this->drev->declarant->adresse, $this->drev->declarant->code_postal, $this->drev->declarant->commune, $this->drev->declarant->email);

        foreach($this->drev->declaration->getProduits() as $produit) {
            $libelle_complet = $produit->getLibelleComplet();
            $csv .= sprintf("%s;Revendication;%s;%s;%s;;;;;;%s\n", $ligne_base, trim($libelle_complet), $this->formatFloat($produit->superficie_revendique), $this->formatFloat($produit->volume_revendique), $mode);
            foreach($produit->getProduitsCepage() as $detail) {
                $csv .= sprintf("%s;Revendication;%s;%s;%s;;;;;;%s\n", $ligne_base, trim($libelle_complet)." ".trim($detail->getLibelle()), $this->formatFloat($detail->superficie_revendique_total), $this->formatFloat($detail->volume_revendique_total), $mode);
            }
        }

        $csv .= sprintf("%s;Revendication;TOTAL;%s;%s;;;;;;%s\n", $ligne_base, $this->formatFloat($this->drev->declaration->getTotalTotalSuperficie()), $this->formatFloat($this->drev->declaration->getTotalVolumeRevendique()), $mode);

        foreach($this->drev->getPrelevementsOrdered(null, true) as $prelevementsOrdered) {
            foreach ($prelevementsOrdered->prelevements as $prelevement) {
                $chai = $prelevement->getChai();
                $csv .= sprintf("%s;%s;%s;;;%s;%s;%s;%s;%s;%s\n", $ligne_base, $prelevementsOrdered->libelle, trim($prelevement->libelle_produit), $prelevement->date, ($prelevement->total_lots) ? $prelevement->total_lots : "", $chai->adresse, $chai->code_postal, $chai->commune, $mode);
                foreach($prelevement->lots as $lot) {
                    $csv .= sprintf("%s;%s;%s;;%s;%s;%s;%s;%s;%s;%s\n", $ligne_base, $prelevementsOrdered->libelle, trim($prelevement->libelle_produit)." ".$lot->libelle, $this->formatFloat($lot->volume_revendique), $prelevement->date, $lot->nb_hors_vtsgn, $chai->adresse, $chai->code_postal, $chai->commune, $mode);
                }
            }
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
