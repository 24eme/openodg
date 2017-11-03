<?php

class ExportHabilitationCSV implements InterfaceDeclarationExportCsv {

    protected $habilitation = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Produit;Activité;Statut;Date;Id du doc;Commentaire\n";
    }

    public function __construct($habilitation, $header = true) {
        $this->habilitation = $habilitation;
        $this->header = $header;
    }

    public function getFileName() {

        return $this->habilitation->_id . '_' . $this->habilitation->_rev . '.csv';
    }

    public function export() {
        $csv = "";

        $ligneBase = sprintf("\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"", $this->habilitation->identifiant, $this->habilitation->declarant->cvi, $this->habilitation->declarant->siret, $this->habilitation->declarant->raison_sociale, $this->habilitation->declarant->adresse, $this->habilitation->declarant->code_postal, $this->habilitation->declarant->commune, $this->habilitation->declarant->email);

        foreach($this->habilitation->getProduits() as $produit) {
            foreach($produit->activites as $activite) {
                if(!$activite->statut) {
                    continue;
                }

                $csv .= $ligneBase.";".$produit->libelle.";".$activite->getKey().";".$activite->statut.";".$activite->date.";".$this->habilitation->_id.";".$activite->commentaire."\n";
            }
        }

        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
