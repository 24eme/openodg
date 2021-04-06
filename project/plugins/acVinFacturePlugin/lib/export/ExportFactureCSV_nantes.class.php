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
        return "Crée le;Nom relation;Adresse;Code Postal;Ville;Téléphone fixe;Téléphone Portable;eMail;Pièce;Cotisation valorisation HT;Cotisation valorisation TVA;Remboursement valorisation covid HT;Remboursement valorisation covid TVA;Cotisation valorisation TTC;Cotisation ODG TOTAL ou forfait;Remboursement ODG covid;Droits I.N.A.O.;Cotisation ODG TOTAL ou forfait + INAO;Total Facture TTC;id facture\n";
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
          $montant_covid_valorisation_ht = 0.0;
          $montant_covid_valorisation_tva = 0.0;
          foreach ($valorisation->details as $detailName => $detail) {
            if($detail->libelle == "Remise exceptionnelle Covid"){ //Goretterie car pas le temps de refactoriser tout ça
              $montant_covid_valorisation_ht += $detail->montant_ht;
              $montant_covid_valorisation_tva += $detail->montant_tva;
            }
          }
          $csv.= $this->floatHelper->formatFr($valorisation->montant_ht-$montant_covid_valorisation_ht, 2, 2).";".
          $this->floatHelper->formatFr($valorisation->montant_tva-$montant_covid_valorisation_tva, 2, 2).";".
          $this->floatHelper->formatFr($montant_covid_valorisation_ht, 2, 2).";".
          $this->floatHelper->formatFr($montant_covid_valorisation_tva, 2, 2).";".
          $this->floatHelper->formatFr($valorisation->montant_tva+$valorisation->montant_ht, 2, 2).";";
        }else{
          $csv.= ";;;;;";
        }

        // odg ou forfait
        $odg_ou_forfait = $this->getCotisationNode('odg');
        $odg_ou_forfait_inao_total=0.0;

        if(!$odg_ou_forfait){
          $odg_ou_forfait = $this->getCotisationNode('odg_forfait');
        }

        if($odg_ou_forfait){
          $montant_covid_odg_ht = 0.0;
          foreach ($odg_ou_forfait->details as $detailName => $detail) {
              if($detail->libelle == "Remise exceptionnelle Covid"){ //Goretterie car pas le temps de refactoriser tout ça
                  $montant_covid_odg_ht += $detail->montant_ht;
              }
          }
          $csv .= $this->floatHelper->formatFr(($odg_ou_forfait->montant_ht), 2, 2).";";
          $csv .= $this->floatHelper->formatFr($montant_covid_odg_ht, 2, 2).";";
          $odg_ou_forfait_inao_total += $odg_ou_forfait->montant_ht + $montant_covid_odg_ht;
        }else{
          $csv .= ";;";
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
        $csv .= $this->floatHelper->formatFr($odg_ou_forfait_inao_total, 2, 2).";" ;

        //total
        $csv .= $this->floatHelper->formatFr($this->facture->total_ttc, 2, 2);
        $csv .= ";".$this->facture->_id;
        $csv .= "\n";

        return $csv;
    }

    public function getCotisationNode($cotisation){
      if($this->facture->lignes->exist($cotisation) && $this->facture->lignes->$cotisation && count($this->facture->lignes->$cotisation)){
        return $this->facture->lignes->$cotisation;
      }
      return null;
    }


}
