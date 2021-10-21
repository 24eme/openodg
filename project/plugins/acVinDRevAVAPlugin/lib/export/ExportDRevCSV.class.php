<?php

class ExportDRevCSV implements InterfaceDeclarationExportCsv {

    protected $drev = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv() {

        return "Campagne;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Produit;Superficie revendiqué;Superficie Vinifiee;Volume revendiqué issu du VCI;Volume revendiqué;prelevement à partir du;nombre de lots;Adresse du chai;Code postal du Chai;Commune du Chai;Type de déclaration\n";
    }

    public function __construct($drev, $header = true, $region = null, $extraFields = false) {
        $this->drev = $drev;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
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
            $superficie = $produit->superficie_revendique;
            $superficie_vtsgn = ($produit->exist('superficie_revendique_vtsgn')) ? $produit->superficie_revendique_vtsgn : 0;
            $superficie_vinifiee = ($produit->exist('superficie_vinifiee')) ? $produit->superficie_vinifiee : 0;
            $superficie_vinifiee_vtsgn = ($produit->exist('superficie_vinifiee_vtsgn')) ? $produit->superficie_vinifiee_vtsgn : 0;
            $volume_vci = ($produit->exist('volume_revendique_vci')) ? $produit->volume_revendique_vci : 0;
            $volume = $produit->volume_revendique;
            $volume_vtsgn = ($produit->exist('volume_revendique_vtsgn')) ? $produit->volume_revendique_vtsgn : 0;

            $csv .= sprintf("%s;Revendication;%s;%s;%s;%s;%s;;;;;;%s\n", $ligne_base, trim($libelle_complet), $this->formatFloat($superficie), $this->formatFloat($superficie_vinifiee), $this->formatFloat($volume_vci), $this->formatFloat($volume), $mode);
            if($superficie_vtsgn || $volume_vtsgn || $superficie_vinifiee_vtsgn) {
                $csv .= sprintf("%s;Revendication;%s;%s;%s;%s;%s;;;;;;%s\n", $ligne_base, trim($libelle_complet). " VT/SGN", $this->formatFloat($superficie_vtsgn), $this->formatFloat($superficie_vinifiee_vtsgn), "", $this->formatFloat($volume_vtsgn), $mode);
            }
        }

        foreach($this->drev->getPrelevementsOrdered(null, true) as $prelevementsOrdered) {
            foreach ($prelevementsOrdered->prelevements as $prelevement) {
                $chai = $prelevement->getChai();
                $csv .= sprintf("%s;%s;%s;;;;;%s;%s;%s;%s;%s;%s\n", $ligne_base, $prelevementsOrdered->libelle, trim($prelevement->libelle_produit), $prelevement->date, ($prelevement->total_lots) ? $prelevement->total_lots : "", $chai->adresse, $chai->code_postal, $chai->commune, $mode);
                foreach($prelevement->lots as $lot) {
                    $csv .= sprintf("%s;%s;%s;;;;%s;%s;%s;%s;%s;%s;%s\n", $ligne_base, $prelevementsOrdered->libelle, trim($prelevement->libelle_produit)." ".$lot->libelle, $this->formatFloat($lot->volume_revendique), $prelevement->date, $lot->nb_hors_vtsgn, $chai->adresse, $chai->code_postal, $chai->commune, $mode);
                }
            }
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
