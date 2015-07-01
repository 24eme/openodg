<?php

class ExportFactureCSV {

    const TYPE_LIGNE_LIGNE = 'LIGNE';
    const TYPE_LIGNE_PAIEMENT = 'PAIEMENT';
    const TYPE_LIGNE_ECHEANCE = 'ECHEANCE';
    const TYPE_LIGNE_TVA = 'TVA';
    const CODE_JOURNAL_FACTURE = "VE00";
    const CODE_JOURNAL_PAIEMENT = "5200";

    public function __construct() {
        
    }

    public static function printHeaderAnneeComptable() {
        echo self::printHeaderBase() . ";code postal;commune;type societe\n";
    }

    public static function printHeader() {
        echo self::printHeaderBase() . "\n";
    }

    private static function printHeaderBase() {
        echo "code journal;date;date de saisie;numero de facture;libelle;compte general;compte tiers;compte analytique;date echeance;sens;montant;piece;reference;id couchdb;type ligne;nom client;code comptable client;origine type;produit type;origine id;commentaire";
    }

    public function printFacture($doc_or_id, $export_annee_comptable = false) {

        if ($doc_or_id instanceof Facture) {
            $facture = $doc_or_id;
        } else {
            $facture = FactureClient::getInstance()->find($doc_or_id);
        }

        if (!$facture) {
            echo sprintf("WARNING;Le document n'existe pas %s\n", $doc_or_id);
            return;
        }
        $societe = null;
        if ($export_annee_comptable) {
            $societe = SocieteClient::getInstance()->find($facture->identifiant);
        }
        foreach ($facture->lignes as $l) {
                echo self::CODE_JOURNAL_FACTURE.';' . $facture->date_facturation . ';' . $facture->date_emission . ';' . $facture->numero_interloire . ';Facture n°' . $facture->numero_interloire . ' COTISATION;'.$l->produit_identifiant_analytique.';;;;CREDIT;' . $l->montant_ht . ';;;' . $facture->_id . ';' . self::TYPE_LIGNE_LIGNE . ';' . $facture->declarant->nom . ";" . $facture->code_comptable_client . ';'.$l->getOrigineType().';'.$l->libelle.';'.$l->getOrigineIdentifiant().";";
                if ($export_annee_comptable) {
                    echo $societe->siege->code_postal . ";" . $societe->siege->commune . ";" . $societe->type_societe . ";";
                }

                echo "\n";
                if($l->montant_tva) {
                    echo self::CODE_JOURNAL_FACTURE.';' . $facture->date_facturation . ';' . $facture->date_emission . ';' . $facture->numero_interloire . ';Facture n°' . $facture->numero_interloire . ' TVA;445710;;;;CREDIT;' . $l->montant_tva . ';;;' . $facture->_id . ';' . self::TYPE_LIGNE_TVA . ';' . $facture->declarant->nom . ";" . $facture->code_comptable_client . ";".$l->getOrigineType().';'.$l->libelle.';'.$l->getOrigineIdentifiant().";";
                    if ($export_annee_comptable) {
                        echo $societe->siege->code_postal . ";" . $societe->siege->commune . ";" . $societe->type_societe . ";";
                    }

                    echo "\n";
                }
        }
        
        echo self::CODE_JOURNAL_FACTURE.';' . $facture->date_facturation . ';' . $facture->date_emission . ';' . $facture->numero_interloire . ';Facture n°' . $facture->numero_interloire . ' ECHEANCE;411000;' . $facture->code_comptable_client . ';;' . $facture->date_echeance . ';DEBIT;' . $facture->total_ttc . ';;;' . $facture->_id . ';' . self::TYPE_LIGNE_ECHEANCE . ';' . $facture->declarant->nom . ";" . $facture->code_comptable_client . ";;;;;";
        if ($export_annee_comptable) {
            echo $societe->siege->code_postal . ";" . $societe->siege->commune . ";" . $societe->type_societe . ";";
        }
        echo "\n";

        if($facture->isPayee()) {
            echo self::CODE_JOURNAL_PAIEMENT.';' . $facture->date_paiement . ';' . $facture->date_paiement . ';' . $facture->numero_interloire . ';Facture n°' . $facture->numero_interloire . ' REGLEMENT;411000;' . $facture->code_comptable_client . ';;' . $facture->date_echeance . ';CREDIT;' . $facture->total_ttc . ';;;' . $facture->_id . ';' . self::TYPE_LIGNE_PAIEMENT . ';' . $facture->declarant->nom . ";" . $facture->code_comptable_client . ";;;;".$facture->reglement_paiement;
            echo "\n";
            echo self::CODE_JOURNAL_PAIEMENT.';' . $facture->date_paiement . ';' . $facture->date_paiement . ';' . $facture->numero_interloire . ';Facture n°' . $facture->numero_interloire . ' REGLEMENT;511100;;;' . $facture->date_echeance . ';DEBIT;' . $facture->total_ttc . ';;;' . $facture->_id . ';' . self::TYPE_LIGNE_PAIEMENT . ';' . $facture->declarant->nom . ";" . $facture->code_comptable_client . ";;;;";
            echo "\n";
        }
    }

    protected function getSageCompteGeneral($facture) {
        if ($facture->getTauxTva() == 20.0) {
            return "44570100";
        }

        return "44570000";
    }

}
