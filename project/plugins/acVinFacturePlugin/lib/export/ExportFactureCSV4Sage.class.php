<?php

class ExportFactureCSV4Sage implements InterfaceDeclarationExportCsv {

    protected $facture = null;
    protected $header = false;
    protected $region = null;

    const TYPE_LIGNE_LIGNE = 'LIGNE';
    const TYPE_LIGNE_PAIEMENT = 'PAIEMENT';
    const TYPE_LIGNE_ECHEANCE = 'ECHEANCE';
    const TYPE_LIGNE_TVA = 'TVA';
    const CODE_JOURNAL_FACTURE = "VE00";
    const CODE_JOURNAL_PAIEMENT = "5200";

    public function __construct($doc_or_id, $header = true, $region = null) {
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

    public function getLibelleFacture() {

        return (($this->facture->isAvoir()) ? "Avoir" : "Facture") . " n°" . $this->facture->getNumeroOdg();
    }

    public function exportFacture() {
        $csv = "";

        if(!$this->facture->code_comptable_client) {

            throw new sfException(sprintf("Code comptable inexistant %s", $f->_id));
        }

        $libelle = $this->getLibelleFacture();

        foreach ($this->facture->lignes as $l) {
            if($l->montant_ht === 0) {
                continue;
            }

            $commentaire = null;
            if($l->getKey() == 'syndicat_viticole' && count($l->details) >= 1) {
                $commentaire = $l->details[0]->libelle;
            }

            if(in_array($l->getKey(), array('odg_ava', 'ava_syndicale')) && isset($l->details[1])) {
                preg_match('/\(([0-9\.]+)[ ares]*\)/', $l->details[1]->libelle, $matches);
                $commentaire = $matches[1];
            }

            $csv .= self::CODE_JOURNAL_FACTURE.';' . $this->facture->date_facturation . ';' . $this->facture->date_emission . ';' . $this->facture->getNumeroOdg() . ';'.$libelle.';'.$l->produit_identifiant_analytique.';;;;' . (($l->montant_ht >= 0) ? "CREDIT" : "DEBIT") .';' . abs($l->montant_ht) . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_LIGNE . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ';'.$l->getOrigineType().';'.$l->libelle.';'.$l->getOrigineIdentifiant().";".$commentaire;

            $csv .= "\n";
            if($l->montant_tva) {
                $csv .= self::CODE_JOURNAL_FACTURE.';' . $this->facture->date_facturation . ';' . $this->facture->date_emission . ';' . $this->facture->getNumeroOdg() . ';'.$libelle.';'.$this->getSageCompteGeneral($l).';;;;' . (($l->montant_tva >= 0) ? "CREDIT" : "DEBIT") .';' . abs($l->montant_tva) . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_TVA . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";".$l->getOrigineType().';'.$l->libelle.';'.$l->getOrigineIdentifiant().";".$commentaire;

                $csv .= "\n";
            }


        }

        $csv .= self::CODE_JOURNAL_FACTURE.';' . $this->facture->date_facturation . ';' . $this->facture->date_emission . ';' . $this->facture->getNumeroOdg() . ';'.$libelle.';411000;' . $this->facture->code_comptable_client . ';;' . $this->facture->date_echeance . ';' . (($this->facture->total_ttc >= 0) ? "DEBIT" : "CREDIT") .';' . abs($this->facture->total_ttc) . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_ECHEANCE . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";;;;";

        $csv .= "\n";

        return $csv;
    }

    public function exportPaiement() {
        $csv = "";

        if($this->facture->isAvoir()) {

            return;
        }

        if($this->facture->isPayee()) {
            $csv .= self::CODE_JOURNAL_PAIEMENT.';' . $this->facture->date_paiement . ';' . $this->facture->date_paiement . ';' . $this->facture->getNumeroOdg() . ';'.$this->getLibelleFacture().';411000;' . $this->facture->code_comptable_client . ';;' . $this->facture->date_echeance . ';CREDIT;' . $this->facture->montant_paiement . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_PAIEMENT . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";;;;".$this->facture->reglement_paiement;
            $csv .= "\n";
            $csv .= self::CODE_JOURNAL_PAIEMENT.';' . $this->facture->date_paiement . ';' . $this->facture->date_paiement . ';' . $this->facture->getNumeroOdg() . ';'.$this->getLibelleFacture().';511150;;;' . $this->facture->date_echeance . ';DEBIT;' . $this->facture->montant_paiement . ';;;' . $this->facture->_id . ';' . self::TYPE_LIGNE_PAIEMENT . ';' . $this->facture->declarant->nom . ";" . $this->facture->code_comptable_client . ";;;;";
            $csv .= "\n";
        }

        return $csv;
    }

    protected function getSageCompteGeneral($ligne) {
        if ($ligne->getTauxTva() == 0.20) {

            return "445710";
        }

        if ($ligne->getTauxTva() == 0.021) {

            return "445711";
        }

        throw new sfException(sprintf("Code sage du Taux de TVA introuvable : %s (%s)", $ligne->getTauxTva(), $ligne->getDocument()->_id));
    }

    public function setExtraArgs($args) {
    }

}
