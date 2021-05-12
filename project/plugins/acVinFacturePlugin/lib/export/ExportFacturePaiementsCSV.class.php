<?php

class ExportFacturePaiementsCSV implements InterfaceDeclarationExportCsv {

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
        return "Identifiant;Raison Sociale;Code comptable client;Numéro facture;Date de paiement;Montant;Type de reglement;Commentaire;Montant restant a payer;Execute;Exporte;Facture doc ID;paiement ID\n";
    }

    public function export() {
        if($this->header) {

            $csv .= $this->getHeaderCsv();
        }

        $csv .= $this->exportFacturePaiements();

        return $csv;
    }


    public function exportFacturePaiements() {

        $societe = $this->facture->getSociete();

        $date_facturation = DateTime::createFromFormat("Y-m-d",$this->facture->date_facturation)->format("d/m/Y");
        $facture = $this->facture;
        $csv = '';
        $csv_prefix = $facture->identifiant.";".$this->facture->declarant->nom.";".$facture->code_comptable_client.';'.$facture->numero_facture.";";
        if($facture->exist('paiements')) {
          foreach ($facture->paiements as $paiement) {
              $csv .= $csv_prefix;
              $csv .= $paiement->date.";";
              $csv .= $this->floatHelper->formatFr($paiement->montant,2,2).";";
              $csv .= $paiement->type_reglement.";";
              $csv .= $paiement->commentaire.";";
              $csv .= $this->floatHelper->formatFr($facture->total_ttc - $facture->montant_paiement,2,2).';';
              $csv .= $paiement->exist('execute') ? $paiement->execute.";" : ";";
              $csv .= $facture->versement_comptable_paiement.";";
              $csv .= $facture->_id.";";
              $csv .= $paiement->getHash().';';
              $csv .= "\n";
          }
        }

        return $csv;
    }

}
