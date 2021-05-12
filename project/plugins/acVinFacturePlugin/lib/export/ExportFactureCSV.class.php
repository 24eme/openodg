<?php

class ExportFactureCSV implements InterfaceDeclarationExportCsv {

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
        return "Date;Identifiant societe;Code comptable client;Raison sociale;Adresse;Code Postal;Ville;Telephone fixe;Telephone Portable;eMail;Piece;Identifiant Analytique;Nom Cotisation;Cotisation Prix unitaire;Quantite Cotisation;Prix HT;TVA;Prix TTC;Montant payé;Export comptable;id facture\n";
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

        $csv_line = $this->facture->date_facturation.";"
              .$societe->identifiant.";"
              .$societe->code_comptable_client.";"
              .$declarant->nom.";"
              .$societe->adresse.";"
              .$societe->code_postal.";"
              .$societe->commune.";"
              .$societe->telephone_bureau.";"
              .$societe->telephone_mobile.";"
              .$societe->email.";"
              .$this->facture->numero_facture.";";

        foreach ($this->facture->lignes as $type => $ligne) {
            foreach($ligne->details as $detail) {
                $csv .= $csv_line;
                $csv .= $ligne->produit_identifiant_analytique.";";
                $csv .= $ligne->libelle;
                if ($ligne->libelle && $detail->libelle) {
                    $csv .= " ";
                }
                if($detail->libelle) {
                    $csv .= $detail->libelle;
                }
                $csv .= ";";
                $csv .= $this->floatHelper->formatFr($detail->prix_unitaire).";";
                $csv .= $this->floatHelper->formatFr($detail->quantite).";";
                $csv .= $this->floatHelper->formatFr($detail->montant_ht).";";
                $csv .= $this->floatHelper->formatFr($detail->montant_tva).";";
                $csv .= $this->floatHelper->formatFr($detail->montant_ht + $detail->montant_tva).";";
                $csv .= ";";
                $csv .= $this->facture->_id;
                $csv .= "\n";
            }
        }

        $csv .= $csv_line;
        $csv .= ";";
        $csv .= "Total facture;";
        $csv .= ";";
        $csv .= ";";
        $csv .= $this->floatHelper->formatFr($this->facture->total_ht).";";
        $csv .= $this->floatHelper->formatFr($this->facture->total_taxe).";";
        $csv .= $this->floatHelper->formatFr($this->facture->total_ttc).";";
        $csv .= $this->floatHelper->formatFr($this->facture->getMontantPaiement()).";";
        $csv .= $this->facture->versement_comptable.';';
        $csv .= $this->facture->_id;
        $csv .= "\n";


        return $csv;
    }

}
