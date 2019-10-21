<?php

class ExportSocieteCSV implements InterfaceDeclarationExportCsv {

    protected $societe = null;
    protected $header = false;
    protected $routing = false;

    const ISCLIENT = 1;
    const ISFOURNISSEUR = 2;

    public static function getHeaderCsv() {

        return "numéro de compte;intitulé;type (client/fournisseur);abrégé;adresse;address complément;code postal;ville;pays;code NAF;n° identifiant;n° siret;mise en sommeil;date de création;téléphone;fax;email;site;Région viticole;Actif;\n";
    }

    public function __construct($societe, $header = true, $routing = null) {
        $this->societe = $societe;
        $this->header = $header;
        $this->routing = $routing;
    }

    public function getFileName() {
        $name = $this->societe->_id;
        $name .= $this->societe->_rev;
        return  $name . '.csv';
    }

    public function exportCompte($compte, $isclient = 1) {
        $csv = "";
        $csv .= $compte.";";
        $csv .= $this->societe->raison_sociale.";";
        if ($isclient == self::ISCLIENT) {
          $csv .= "CLIENT;";
        }else{
          $csv .= "FOURNISSEUR;";
        }
        $csv .= $this->societe->raison_sociale_abregee.";";
        $csv .= preg_replace('/;.*/', '', $this->societe->getSiegeAdresses()).";";
        if (preg_match('/;/', $this->societe->getSiegeAdresses())) {
            $csv .= str_replace(';', '-', preg_replace('/.*;/', '', $this->societe->getSiegeAdresses()));
        }
        $csv .= ";";
        $csv .= $this->societe->siege->code_postal.";";
        $csv .= $this->societe->siege->commune.";";
        $csv .= "France;";
        $csv .= ";"; //NAF
        $csv .= $this->societe->no_tva_intracommunautaire.";";
        $csv .= $this->societe->siret.";";
        $csv .= $this->societe->statut.";";
        $csv .= $this->societe->date_modification.";";
        $csv .= preg_replace('/[^\+0-9]/i', '', $this->societe->telephone).";";
        $csv .= preg_replace('/[^\+0-9]/i', '', $this->societe->fax).";";
        $csv .= $this->societe->email.";";
        $csv .= (($this->routing) ? $this->routing->generate('societe_visualisation', $this->societe, true) : "").';';
        try {
          if ($isclient == self::ISCLIENT) {
    	$csv .= $this->societe->getRegionViticole(false).';';
          }
        }catch(sfException $e) {
          $csv .= "INCONNUE;";
        }
        $csv .= $this->societe->isActif().';';
        $csv .= "\n";

        return $csv;
    }

    public function export() {
        $csv = null;
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        if ($this->societe->code_comptable_client) {
  	        $csv .= $this->exportCompte($this->societe->code_comptable_client, self::ISCLIENT);
        }
        if ($this->societe->code_comptable_fournisseur) {
  	        $csv .= $$this->exportCompte($this->societe->code_comptable_fournisseur, self::ISFOURNISSEUR);
        }

        return $csv;
    }

}
