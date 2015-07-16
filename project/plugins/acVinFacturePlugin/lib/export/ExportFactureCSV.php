<?php

class ExportFactureCSV implements InterfaceDeclarationExportCsv {

    protected $facture = null;
    protected $header = false;

    const TYPE_LIGNE_LIGNE = 'LIGNE';
    const TYPE_LIGNE_PAIEMENT = 'PAIEMENT';
    const TYPE_LIGNE_ECHEANCE = 'ECHEANCE';
    const TYPE_LIGNE_TVA = 'TVA';
    const CODE_JOURNAL_FACTURE = "VE00";
    const CODE_JOURNAL_PAIEMENT = "5200";

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

        $this->header = $header;
    }

    public static function getHeaderCsv() {

        return "code journal;date;date de saisie;numero de facture;libelle;compte general;compte tiers;compte analytique;date echeance;sens;montant;piece;reference;id couchdb;type ligne;nom client;code comptable client;origine type;produit type;origine id;commentaire\n";
    }

    public function export() {
        if($this->header) {

            $csv .= $this->getHeaderCsv();
        }

        $csv .= $this->exportFacture();
        $csv .= $this->exportPaiement();

        return $csv;
    }

    public function exportFacture() {
        $csv = "";

        if(!$this->facture->code_comptable_client) {

            throw new sfException(sprintf("Code comptable inexistant %s", $f->_id));
        }

        foreach ($this->facture->lignes as $l) {
                $csv .= self::CODE_JOURNAL_FACTURE.';' . $this->facture->date_facturation . ';' . $this->facture->date_emission . ';' . $this->facture->numero_interloire . ';Facture n°' . $this->facture->numero_interloire . ';'.$l->produit_identifiant_analytique.';;;;CREDIT;' . $l->montant_ht . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_LIGNE . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ';'.$l->getOrigineType().';'.$l->libelle.';'.$l->getOrigineIdentifiant().";";

                $csv .= "\n";
                if($l->montant_tva) {
                    $csv .= self::CODE_JOURNAL_FACTURE.';' . $this->facture->date_facturation . ';' . $this->facture->date_emission . ';' . $this->facture->numero_interloire . ';Facture n°' . $this->facture->numero_interloire . ';'.$this->getSageCompteGeneral($this->facture).';;;;CREDIT;' . $l->montant_tva . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_TVA . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";".$l->getOrigineType().';'.$l->libelle.';'.$l->getOrigineIdentifiant().";";

                    $csv .= "\n";
                }
        }
        
        $csv .= self::CODE_JOURNAL_FACTURE.';' . $this->facture->date_facturation . ';' . $this->facture->date_emission . ';' . $this->facture->numero_interloire . ';Facture n°' . $this->facture->numero_interloire . ';411000;' . $this->facture->code_comptable_client . ';;' . $this->facture->date_echeance . ';DEBIT;' . $this->facture->total_ttc . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_ECHEANCE . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";;;;;";
        $csv .= "\n";

        return $csv;
    }

    public function exportPaiement() {
        $csv = "";

        if($this->facture->isPayee()) {
            $csv .= self::CODE_JOURNAL_PAIEMENT.';' . $this->facture->date_paiement . ';' . $this->facture->date_paiement . ';' . $this->facture->numero_interloire . ';Facture n°' . $this->facture->numero_interloire . ';411000;' . $this->facture->code_comptable_client . ';;' . $this->facture->date_echeance . ';CREDIT;' . $this->facture->total_ttc . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_PAIEMENT . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";;;;".$this->facture->reglement_paiement;
            $csv .= "\n";
            $csv .= self::CODE_JOURNAL_PAIEMENT.';' . $this->facture->date_paiement . ';' . $this->facture->date_paiement . ';' . $this->facture->numero_interloire . ';Facture n°' . $this->facture->numero_interloire . ';511150;;;' . $this->facture->date_echeance . ';DEBIT;' . $this->facture->total_ttc . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_PAIEMENT . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";;;;";
            $csv .= "\n";
        } 

        return $csv;
    }

    protected function getSageCompteGeneral($facture) {
        if ($facture->getTauxTva() == 20.0) {
            
            return "445710";
        }

        if ($facture->getTauxTva() == 2.10) {

            return "445711";
        }

        throw new sfException(sprintf("Code sage du Taux de TVA introuvable : %s (%s)", $facture->getTauxTva(), $facture->_id));
    }

}
