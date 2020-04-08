<?php

class ExportFacturePaiementsCSV_nantes implements InterfaceDeclarationExportCsv {

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
        return "Raison Sociale;Numéro facture;Mode de paiement;Date de paiement;Montant\n";
    }

    public function export() {
        if($this->header) {

            $csv .= $this->getHeaderCsv();
        }

        $csv .= $this->exportFacturePaiements();

        return $csv;
    }


    public function exportFacturePaiements() {

        $declarant = $this->facture->declarant;
        $societe = $this->facture->getSociete();

        $date_facturation = DateTime::createFromFormat("Y-m-d",$this->facture->date_facturation)->format("d/m/Y");
        $facture = $this->facture;
        $csv = $declarant->nom.";".$facture->numero_facture.";";
        $csvPaiements = "";
        if($facture->exist('paiements') && $facture->paiements && count($facture->paiements)){
          foreach ($facture->paiements as $paiement) {
            $csvPaiements.=$csv.FactureClient::$types_paiements[$paiement->type_reglement].";".DateTime::createFromformat("Y-m-d",$paiement->date)->format('d/m/Y').";".$this->floatHelper->formatFr($paiement->montant,2,2)."\n";
          }
        }else{
          $csvPaiements.=$csv.";;\n";
        }

        return $csvPaiements;
    }

}
