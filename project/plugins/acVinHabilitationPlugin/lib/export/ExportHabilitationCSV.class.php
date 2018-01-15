<?php

class ExportHabilitationCSV implements InterfaceDeclarationExportCsv {

    protected $habilitation = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Nom Opérateur (Raison Sociale);Identifiant;Produit (libellé appellation);CVI Opérateur;Siret Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Téléphone fixe;Téléphone mobile;Email;Activité;Statut;Date;Id du doc;Commentaire\n";
    }

    public function __construct($habilitation, $header = true) {
        $this->habilitation = $habilitation;
        $this->header = $header;
    }

    public function getFileName() {

        return $this->habilitation->_id . '_' . $this->habilitation->_rev . '.csv';
    }

    public function protectStr($str) {
    	return str_replace('"', '', $str);
    }

    public function export() {
        $csv = "";

        $declarant = $this->habilitation->getDeclarant();
        $raison_sociale = $this->protectStr($this->habilitation->getDeclarant()->raison_sociale);
        $identifiant = $this->habilitation->identifiant;
        //libellé appellation
        $cvi = $declarant->cvi;
        $siret = $declarant->siret;
        $adresse = $this->protectStr($declarant->adresse);
        $code_postal = $declarant->code_postal;
        $commune = $this->protectStr($declarant->commune);
        $tel_fixe = $declarant->telephone_bureau;
        $tel_portable =$declarant->telephone_mobile;
        $email = $this->habilitation->getDeclarant()->email;


        foreach($this->habilitation->getProduits() as $produit) {
            foreach($produit->activites as $activite) {
                if(!$activite->statut) {
                    continue;
                }

                $csv .= sprintf("\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"\n",
                      $raison_sociale,
                      $identifiant,
                      $produit->libelle,
                      $cvi,
                      $siret,
                      $adresse,
                      $code_postal,
                      $commune,
                      $tel_fixe,
                      $tel_portable,
                      $email,
                      $activite->getKey(),
                      $activite->statut,
                      $activite->date,
                      $this->habilitation->_id,
                      $activite->commentaire);
            }
        }
        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }
}
