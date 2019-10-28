<?php

class ExportSocieteCSV implements InterfaceDeclarationExportCsv {

    protected $societe = null;
    protected $header = false;

    public static function getHeaderCsv() {

        return "Identifiant;Titre;Raison sociale;Adresse;Adresse 2;Adresse 3;Code postal;Commune;Pays;Code comptable;Code NAF;Siret;TVA Intra;Téléphone;Téléphone portable;Fax;Email;Site;Région;Type;Statut;Date de modification;Observation\n";
    }

    public function __construct($societe, $header = true) {
        $this->societe = $societe;
        $this->header = $header;
    }

    public function getFileName() {
        $name = $this->societe->_id;
        $name .= $this->societe->_rev;
        return  $name . '.csv';
    }

    public function export() {
        $csv = null;
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $adresses_complementaires = explode(' − ', str_replace(array('"', ',', ';'),array('','',''), $this->societe->siege->adresse_complementaire));
        $adresse_complementaire = array_shift($adresses_complementaires);
        $extractIntitule = Societe::extractIntitule($this->societe->raison_sociale);
        $intitule = $extractIntitule[0];
        $raisonSociale = $extractIntitule[1];

        $csv .= $this->societe->identifiant.";";
        $csv .= $extractIntitule.";";
        $csv .= $raisonSociale.";";
        $csv .= str_replace(array('"',',', ';'), array('','', ''), $this->societe->siege->adresse).";";
        $csv .= str_replace(array('"',',', ';'), array('','', ''), $this->societe->siege->adresse_complementaire).";";
        $csv .= implode(' − ', $adresses_complementaires).";";
        $csv .= $this->societe->siege->code_postal.";";
        $csv .= $this->societe->siege->commune.";";
        $csv .= $this->societe->siege->pays.";";
        $csv .= $this->societe->code_comptable_client.";";
        $csv .= ";"; //NAF
        $csv .= $this->societe->siret.";";
        $csv .= $this->societe->no_tva_intracommunautaire.";";
        $csv .= preg_replace('/[^\+0-9]/i', '', $this->societe->telephone).";";
        $csv .= preg_replace('/[^\+0-9]/i', '', $this->societe->telephone_mobile).";";
        $csv .= preg_replace('/[^\+0-9]/i', '', $this->societe->fax).";";
        $csv .= $this->societe->email.";";
        $csv .= str_replace(array(',', ';', "\n", "\r"), array(' / ', ' / ', ' '), $this->societe->site_internet).";";
        $csv .= ";";
        $csv .= $this->societe->type_societe.";";
        $csv .= (($this->societe->statut) ? $this->societe->statut : EtablissementClient::STATUT_ACTIF).";";
        $csv .= $this->societe->date_modification.";";
        $csv .= '"'.str_replace('"', "''", str_replace(array(',', ';', "\n", "\r"), array(' / ', ' / ', ' '), $this->societe->commentaire)).'"';
        $csv .= "\n";

        return $csv;
    }

}
