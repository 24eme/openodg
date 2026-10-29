<?php

class ExportFactureCSV4Sage implements InterfaceDeclarationExportCsv {

    protected $facture = null;
    protected $header = false;
    protected $region = null;

    const TYPE_LIGNE_LIGNE = 'LIGNE';
    const TYPE_LIGNE_PAIEMENT = 'PAIEMENT';
    const TYPE_LIGNE_ECHEANCE = 'ECHEANCE';
    const TYPE_LIGNE_TVA = 'TVA';

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

        return "#code journal;date;date de saisie;numero de facture;libelle;compte general;compte tiers;compte analytique;date echeance;sens;montant;piece;reference;id couchdb;type ligne;nom client;code comptable client;origine type;produit type;origine id;commentaire\n";
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

            $csv .= FactureConfiguration::getInstance()->getCodeJournalFacture().';';
            $csv .= $this->facture->date_facturation . ';';
            $csv .= $this->facture->date_emission . ';';
            $csv .= $this->facture->getNumeroOdg() . ';';
            $csv .= $libelle.' - '.$l->libelle.';';
            $csv .= self::formatNumeroCompte($l->produit_identifiant_analytique).';;;;';
            $csv .= (($l->montant_ht >= 0) ? "CREDIT" : "DEBIT") .';';
            $csv .= abs($l->montant_ht) . ';;;';
            $csv .= $this->facture->_id . ';';
            $csv .= self::TYPE_LIGNE_LIGNE . ';';
            $csv .= $this->facture->declarant->nom . ';';
            $csv .= $this->facture->code_comptable_client . ';';
            $csv .= $l->getOrigineType().';';
            $csv .= $l->libelle.';';
            $csv .= $l->getOrigineIdentifiant().';';
            $csv .= $commentaire;
            $csv .= "\n";
            if($l->montant_tva) {
                $csv .= FactureConfiguration::getInstance()->getCodeJournalFacture().';';
                $csv .= $this->facture->date_facturation . ';';
                $csv .= $this->facture->date_emission . ';';
                $csv .= $this->facture->getNumeroOdg() . ';';
                $csv .= $libelle.' - TVA '.$l->libelle.';';
                $csv .= $this->getSageCompteGeneralTVA($l).';;;;';
                $csv .= (($l->montant_tva >= 0) ? "CREDIT" : "DEBIT") .';';
                $csv .= abs($l->montant_tva) . ';;;';
                $csv .= $this->facture->_id . ';';
                $csv .= self::TYPE_LIGNE_TVA . ';';
                $csv .= $this->facture->declarant->nom . ';';
                $csv .= $this->facture->code_comptable_client . ';';
                $csv .= $l->getOrigineType().';';
                $csv .= $l->libelle.';';
                $csv .= $l->getOrigineIdentifiant().';';
                $csv .= $commentaire;
                $csv .= "\n";
            }


        }

        $csv .= FactureConfiguration::getInstance()->getCodeJournalFacture().';';
        $csv .= $this->facture->date_facturation . ';';
        $csv .= $this->facture->date_emission . ';';
        $csv .= $this->facture->getNumeroOdg() . ';';
        $csv .= $libelle.';';
        $csv .= self::formatNumeroCompte('411000').';';
        $csv .= $this->facture->code_comptable_client . ';;';
        $csv .= $this->facture->date_echeance . ';';
        $csv .= (($this->facture->total_ttc >= 0) ? "DEBIT" : "CREDIT") .';';
        $csv .= abs($this->facture->total_ttc) . ';;;';
        $csv .= $this->facture->_id . ';';
        $csv .= self::TYPE_LIGNE_ECHEANCE . ';';
        $csv .= $this->facture->declarant->nom . ';';
        $csv .= $this->facture->code_comptable_client . ';;;;';
        $csv .= "\n";

        return $csv;
    }

    public function exportPaiement() {
        $csv = "";

        if($this->facture->isAvoir()) {

            return;
        }

        if($this->facture->isPayee()) {
            foreach($this->facture->paiements as $p) if (!$p->versement_comptable) {
                $csv .= FactureConfiguration::getInstance()->getCodeJournalPaiement().';';
                $csv .= $p->date . ';';
                $csv .= $p->date . ';';
                $csv .= $this->facture->getNumeroOdg() . ';';
                $csv .= $this->getLibelleFacture().' - '.$p->type_reglement.' '.$p->getCommentaireCsv().';';
                $csv .= self::formatNumeroCompte('411000').';';
                $csv .= $this->facture->code_comptable_client . ';;';
                $csv .= $this->facture->date_echeance . ';CREDIT;';
                $csv .= $this->facture->montant_paiement . ';;;';
                $csv .= $this->facture->_id . ';';
                $csv .= self::TYPE_LIGNE_PAIEMENT . ';';
                $csv .= $this->facture->declarant->nom . ';';
                $csv .= $this->facture->code_comptable_client . ';;;;';
                $csv .= $p->getCommentaireCsv();
                $csv .= "\n";
                $csv .= FactureConfiguration::getInstance()->getCodeJournalPaiement().';';
                $csv .= $p->date . ';';
                $csv .= $p->date . ';';
                $csv .= $this->facture->getNumeroOdg() . ';';
                $csv .= $this->getLibelleFacture().' - '.$p->type_reglement.' '.$p->getCommentaireCsv().';';
                $csv .= self::formatNumeroCompte(FactureConfiguration::getInstance()->getNumeroCompteBanquePaiement()).';;;';
                $csv .= $this->facture->date_echeance . ';DEBIT;';
                $csv .= $this->facture->montant_paiement . ';;;';
                $csv .= $this->facture->_id . ';';
                $csv .= self::TYPE_LIGNE_PAIEMENT . ';';
                $csv .= $this->facture->declarant->nom . ';';
                $csv .= $this->facture->code_comptable_client . ';;;;';
                $csv .= "\n";
            }
        }

        return $csv;
    }

    protected function getSageCompteGeneralTVA($ligne) {
        $code = null;
        if ($ligne->getTauxTva() == 0.20) {

            $code = FactureConfiguration::getInstance()->getCompteTVANormal();
        }

        if ($ligne->getTauxTva() == 0.021) {

            $code = FactureConfiguration::getInstance()->getCompteTVASuperReduit();
        }

        if ($code) {
            return $code;
        }

        throw new sfException(sprintf("Code sage du Taux de TVA introuvable : %s (%s)", $ligne->getTauxTva(), $ligne->getDocument()->_id));
    }

    protected static function formatNumeroCompte($c) {
        $minlength = (int) FactureConfiguration::getInstance()->getNumeroCompteMaxLength();
        $diff = $minlength - strlen($c);
        if (!$minlength || $diff < 1) {
            return $c;
        }
        return sprintf('%s%0'.$diff.'s', $c, '');
    }

    public function setExtraArgs($args) {
    }

}
