<?php

class ExportDegustationCSV implements InterfaceDeclarationExportCsv {

    protected $degustation = null;
    protected $header = false;
    protected $region = null;

    public static function getHeaderCsv() {
        return "Campagne;Date;Heure;Num archive;Lieu Dégustation;Adresse lieu;Code postal lieu;Commune lieu;Id Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Num dossier;Num lot;Num logement Opérateur;Num Anonymat;Num Table;Certification;Genre;Appellation;Mention;Lieu;Couleur;Cepage;Produit;Cépages;Millésime;Spécificités;Volume;Statut de lot;Date prélévement;Conformité;Motif;Observation;Date envoi email resultat;Date recours;Date de conformité en appel;Organisme;Doc Id;Lot unique Id;Produit hash;\n";
    }

    public function __construct($degustation, $header = true, $region = null) {
        $this->degustation = $degustation;
        $this->header = $header;
        $this->region = $region;
    }

    public function getFileName() {

        return $this->degustation->_id . '_' . $this->degustation->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        $date = preg_split('/( |T)/', $this->degustation->date, -1, PREG_SPLIT_NO_EMPTY);
        $lieu = explode(' — ', $this->degustation->lieu);
        $organisme = $lieu[0];
        $adresse = null;
        $code_postal = null;
        $commune = null;
        if (preg_match('/^(.+)([0-9]{5})(.+)$/', $lieu[1], $m)) {
          $adresse = trim($m[1]);
          $code_postal = $m[2];
          $commune = trim($m[3]);
        }

        $ligne_base = sprintf("%s;%s;%s;%s;%s;%s;%s;%s",
            $this->degustation->campagne,
            $date[0],
            $date[1],
            $this->degustation->numero_archive,
            $organisme,
            $adresse,
            $code_postal,
            $commune
          );

          foreach($this->degustation->lots as $lot) {
            if ($lot->leurre) {
              continue;
            }
            $adresse = null;
            $code_postal = null;
            $commune = null;
            $adresseTab = explode(' — ', $lot->adresse_logement);
            if (preg_match('/^([0-9]{5})$/', $adresseTab[2])) {
                $adresse = $adresseTab[1];
                $code_postal = $adresseTab[2];
                $commune = $adresseTab[3];
            } elseif (preg_match('/^(.+)([0-9]{5})(.+)$/', $adresseTab[1], $m)) {
              $adresse = trim($m[1]);
              $code_postal = $m[2];
              $commune = trim($m[3]);
            }
            $cepages = ($lot->cepages)? implode(',', array_keys($lot->cepages->toArray(true,false))) : '';
            $statut = (isset(Lot::$libellesStatuts[$lot->statut]))? Lot::$libellesStatuts[$lot->statut] : $lot->statut;
            $conformite = (isset(Lot::$libellesConformites[$lot->conformite]))? Lot::$libellesConformites[$lot->conformite] : $lot->conformite;
            $dateRecours = ($lot->recours_oc)? preg_split('/( |T)/', $lot->recours_oc, -1, PREG_SPLIT_NO_EMPTY)[0] : null;
            $dateEmail = ($lot->email_envoye)? preg_split('/( |T)/', $lot->email_envoye, -1, PREG_SPLIT_NO_EMPTY)[0] : null;
            $csv .= str_replace('donnée non présente dans l\'import', '', sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;\n",
                $ligne_base,
                $lot->declarant_identifiant,
                $lot->declarant_nom,
                $this->protectStr($adresse),
                $code_postal,
                $this->protectStr($commune),
                $lot->numero_dossier,
                $lot->numero_archive,
                $this->protectStr($lot->numero_logement_operateur),
                $lot->numero_anonymat,
                $lot->numero_table,
                DeclarationExportCsv::getProduitKeysCsv($lot->getConfigProduit()),
                trim($this->protectStr($lot->produit_libelle)),
                $cepages,
                $lot->millesime,
                $this->protectStr($lot->specificite),
                $this->formatFloat($lot->volume),
                $statut,
                $lot->preleve,
                $conformite,
                $this->protectStr($lot->motif),
                $this->protectStr($lot->observation),
                $dateEmail,
                $dateRecours,
                $lot->conforme_appel,
                Organisme::getCurrentOrganisme(),
                $lot->id_document,
                $lot->unique_id,
                $lot->produit_hash
            ));
          }

        return $csv;
    }

    public function protectStr($str) {
        return str_replace('\n', ' ', str_replace(';', ',', str_replace('"', '', $str)));
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
    }

}
