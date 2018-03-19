<?php

class ExportHabilitationCSV implements InterfaceDeclarationExportCsv {

    protected $habilitation = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Nom Opérateur (Raison Sociale);Identifiant;Produit (libellé appellation);CVI Opérateur;Siret Opérateur;Adresse (etablissement);Adresse complémentaire (etablissement);Adresse complémentaire 2 (etablissement);Code postal  (etablissement);Commune (etablissement);Téléphone fixe (etablissement);Téléphone mobile (etablissement);Email (etablissement);Adresse (société);Adresse complémentaire (société);Adresse complémentaire 2 (société);Code postal (société);Commune (société);Téléphone fixe (société);Téléphone mobile (société);Email (société);Activité;Statut;Date;Id du doc;Commentaire\n";
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
        $acs = explode('−',$declarant->adresse_complementaire);
        $adresse_complementaire = "";
        $adresse_complementaire_bis = "";
        $adresse_complementaire = $this->protectStr($acs[0]);
        if(count($acs) > 1){
            $adresse_complementaire_bis = $this->protectStr($acs[1]);
        }
        $code_postal = $declarant->code_postal;
        $commune = $this->protectStr($declarant->commune);
        $tel_fixe = $declarant->telephone_bureau;
        $tel_portable =$declarant->telephone_mobile;
        $email = $declarant->email;

        $adresse_societe = $this->protectStr($declarant->adresse_societe);
        $acss = explode('−',$declarant->adresse_complementaire_societe);
        $adresse_complementaire_societe = "";
        $adresse_complementaire_societe_bis = "";
        $adresse_complementaire_societe = $this->protectStr($acss[0]);
        if(count($acss) > 1){
          $adresse_complementaire_societe_bis = $this->protectStr($acss[1]);
        }

        $code_postal_societe = $declarant->code_postal_societe;
        $commune_societe = $this->protectStr($declarant->commune_societe);
        $tel_fixe_societe = $declarant->telephone_bureau_societe;
        $tel_portable_societe =$declarant->telephone_mobile_societe;
        $email_societe = $declarant->email_societe;


        foreach($this->habilitation->getProduits() as $produit) {
            foreach($produit->activites as $activite) {
                if(!$activite->statut) {
                    continue;
                }

                $csv .= sprintf("\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"\n",
                      $raison_sociale,
                      $identifiant,
                      $produit->libelle,
                      $cvi,
                      $siret,

                      $adresse,
                      $adresse_complementaire,
                      $adresse_complementaire_bis,
                      $code_postal,
                      $commune,
                      $tel_fixe,
                      $tel_portable,
                      $email,

                      $adresse_societe,
                      $adresse_complementaire_societe,
                      $adresse_complementaire_societe_bis,
                      $code_postal_societe,
                      $commune_societe,
                      $tel_fixe_societe,
                      $tel_portable_societe,
                      $email_societe,

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
