<?php

class ExportFactureCSV_nantes implements InterfaceDeclarationExportCsv {

    protected $facture = null;
    protected $header = false;

    protected $floatHelper = null;

    public function __construct($doc_or_id, $header = true) {
        if ($doc_or_id instanceof Facture) {
            $this->facture = $doc_or_id;
        } else {
            $this->facture = FactureClient::getInstance()->find($doc_or_id);
        }

        if (!$this->facture) {
            echo sprintf("WARNING;Le document n'existe pas %s\n", $doc_or_id);
            return;
        }
       $this->floatHelper = FloatHelper::getInstance();

        $this->header = $header;
    }

    public static function getHeaderCsv() {
        return "Crée le;Nom relation;Adresse;Code Postal;Ville;Téléphone fixe;Téléphone Portable;eMail;Pièce;Cotisation valorisation HT;Cotisation valorisation TVA;Cotisation valorisation TTC;Cotisation ODG TOTAL ou forfait;Droits I.N.A.O.;Cotisation ODG TOTAL ou forfait + INAO;Total Facture TTC\n";
    }

    public function export() {
        if($this->header) {

            $csv .= $this->getHeaderCsv();
        }

        $csv .= $this->exportFacture();

        return $csv;
    }


    public function exportFacture() {

        $declarant = $this->facture->declarant;
        $societe = $this->facture->getSociete();

        $date_facturation = DateTime::createFromFormat("Y-m-d",$this->facture->date_facturation)->format("d/m/Y");

        $csv = $date_facturation.";"
              .$declarant->nom.";"
              .$declarant->adresse.";"
              .$declarant->code_postal.";"
              .$declarant->commune.";"
              .$societe->telephone_bureau.";"
              .$societe->telephone_mobile.";"
              .$societe->email.";"
              .$this->facture->numero_facture.";";

        // valorisations
        $valorisation = $this->getCotisationNode('valorisation');
        if($valorisation){
          $csv.= $this->floatHelper->formatFr($valorisation->montant_ht, 2, 2).";".$this->floatHelper->formatFr($valorisation->montant_tva, 2, 2).";".$this->floatHelper->formatFr(($valorisation->montant_ht+$valorisation->montant_tva), 2, 2).";";
        }else{
          $csv.= ";;;";
        }

        // odg ou forfait
        $odg_ou_forfait = $this->getCotisationNode('odg');
        $odg_ou_forfait_inao_total=0.0;

        if(!$odg_ou_forfait){
          $odg_ou_forfait = $this->getCotisationNode('odg_forfait');
        }

        if($odg_ou_forfait){
          $csv .= $this->floatHelper->formatFr($odg_ou_forfait->montant_ht, 2, 2).";";
          $odg_ou_forfait_inao_total+=$odg_ou_forfait->montant_ht;
        }else{
          $csv .= ";";
        }

        // inao
        $inao = $this->getCotisationNode('inao');
        if($inao){
          $csv .= $this->floatHelper->formatFr($inao->montant_ht, 2 ,2).";";
          $odg_ou_forfait_inao_total+=$inao->montant_ht;
        }else{
          $csv .= ";";
        }

        // odg + inao
        $csv .= ($odg_ou_forfait_inao_total)? $this->floatHelper->formatFr($odg_ou_forfait_inao_total, 2, 2).";" : ";";

        //total
        $csv .= $this->floatHelper->formatFr($this->facture->total_ttc, 2, 2)."\n";

        return $csv;
    }

    public function getCotisationNode($cotisation){
      if($this->facture->lignes->exist($cotisation) && $this->facture->lignes->$cotisation && count($this->facture->lignes->$cotisation)){
        return $this->facture->lignes->$cotisation;
      }
      return null;
    }


}
