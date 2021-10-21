<?php

class ExportFactureCSV_provence implements InterfaceDeclarationExportCsv {

    protected $facture = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    const TYPE_LIGNE_LIGNE = 'LIGNE';
    const TYPE_LIGNE_PAIEMENT = 'PAIEMENT';
    const TYPE_LIGNE_TVA = 'TVA';
    const CODE_JOURNAL_FACTURE = "70";
    const IDENTIFIANT_ANALYTIQUE_ECHEANCE = "411000";

    public function __construct($doc_or_id, $header = true, $region = null, $extraFields = false) {
        if ($doc_or_id instanceof Facture) {
            $this->facture = $doc_or_id;
        } else {
            $this->facture = FactureClient::getInstance()->find($doc_or_id);
        }

        if (!$this->facture) {
            echo sprintf("WARNING;Le document n'existe pas %s\n", $doc_or_id);
            return;
        }

        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public static function getHeaderCsv() {

        return "Code Journal;Date de pièce;Npièce;Numéro Compte;Numéro Tiers;Libellé écriture;Date échèance;Mvts débit;Mvts crédit\n";
    }

    public function export() {
        if($this->header) {

            $csv .= $this->getHeaderCsv();
        }

        $csv .= $this->exportFacture();

        return $csv;
    }

    public function getLibelleFacture() {

        return (($this->facture->isAvoir()) ? "Avoir" : "Facture") . " n°" . $this->facture->code_comptable_client;
    }

    public function exportFacture() {
        $csv = "";

        if(!$this->facture->code_comptable_client) {

            throw new sfException(sprintf("Code comptable inexistant %s", $f->_id));
        }

        $datePiece = (new DateTime($this->facture->date_facturation))->format("dmy");
        $numeroPiece = $this->facture->numero_archive;
        $code_comptable_client = $this->facture->code_comptable_client;
        $libelle_ecriture = $this->facture->declarant->nom;
        foreach ($this->facture->lignes as $l) {
            $debit = $credit = "";
            if($l->montant_ht === 0) {
                continue;
            }elseif($l->montant_ht > 0){
              $credit = abs($l->montant_ht+$l->montant_tva);
            }else{
                $debit = abs($l->montant_ht+$l->montant_tva);
            }
            $csv .= self::CODE_JOURNAL_FACTURE.';' . $datePiece . ';' . $numeroPiece . ';'.$l->produit_identifiant_analytique.';;'.$libelle_ecriture.';;' . $debit . ';' . $credit;
            $csv .= "\n";
        }

        foreach ($this->facture->echeances as $e) {

          $matches = array();
          $dateEcheance = "";
          if(preg_match("/([0-9]{2})(.{1})([0-9]{2})(.{1})([0-9]{2})([0-9]{2})/",$e->echeance_date,$matches)){
            $dateEcheance = $matches[1].$matches[3].$matches[6];
          }else{
            $dateEcheance = $datePiece;
          }
          $debit = $credit = "";
          if($e->montant_ttc === 0) {
              continue;
          }elseif($e->montant_ttc > 0){
            $debit = abs($e->montant_ttc);
          }else{
            $credit = abs($e->montant_ttc);
          }
          $csv .= self::CODE_JOURNAL_FACTURE.';' . $datePiece . ';' . $numeroPiece . ';' . self::IDENTIFIANT_ANALYTIQUE_ECHEANCE   . ';'.$code_comptable_client.';'.$libelle_ecriture.';'.$dateEcheance.';' . $debit . ';' . $credit;
          $csv .= "\n";
        }

        return $csv;
    }
}
