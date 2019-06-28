<?php

class ExportHabilitationDemandesPublipostageCSV {
    protected $dateFrom = null;
    protected $dateTo = null;
    protected $header = null;

    public static function getHeaderCsv() {

        return "Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Adresse complémentaire 1;Adresse complémentaire 2;Code postal Opérateur;Commune Opérateur;Email;Téléphone Bureau;Téléphone Mobile;Demande;Libellé activités;Produit;Statut;Date Statut;Statut précédent;Date précédent statut;Statut suivant;Date statut suivant;Id du doc;Clé de la demande\n";
    }

    public function __construct($dateFrom = null, $dateTo = null, $header = true) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->header = $header;
    }

    public function export($stream = false) {
        $csv = null;
        if($this->header && $stream) {
            echo self::getHeaderCsv();
        } elseif($this->header) {
            $csv .= self::getHeaderCsv();
        }
        if($this->dateFrom) {
            $rows = HabilitationHistoriqueView::getInstance()->getByDate($this->dateFrom, $this->dateTo);
        } else {
            $rows = HabilitationHistoriqueView::getInstance()->getAll();
        }
        foreach ($rows as $row) {
            if (strpos($row->key[HabilitationHistoriqueView::KEY_IDDOC], 'demandes') === false) {
                continue;
            }
            $keysHash = explode(":", $row->key[HabilitationHistoriqueView::KEY_IDDOC]);
            $hab = HabilitationClient::getInstance()->find($row->id);
            $demandeHash = $keysHash[1];
            $demande = $hab->get($demandeHash);

            $historiquePrecedent = $demande->getHistoriquePrecedent($row->key[HabilitationHistoriqueView::KEY_STATUT], $row->key[HabilitationHistoriqueView::KEY_DATE]);

            $historiqueSuivant = $demande->getHistoriqueSuivant($row->key[HabilitationHistoriqueView::KEY_STATUT], $row->key[HabilitationHistoriqueView::KEY_DATE]);

            $declarant = $hab->getDeclarant();
            $adresse = str_replace('"', '', $declarant->adresse);
            $acs = explode('−',$declarant->adresse_complementaire);
            $adresse_complementaire = "";
            $adresse_complementaire_bis = "";
            $adresse_complementaire = str_replace('"', '', $acs[0]);
            if(count($acs) > 1){
                $adresse_complementaire_bis = str_replace('"', '', $acs[1]);
            }

            $csvLigne = $row->key[HabilitationHistoriqueView::KEY_IDENTIFIANT].";".$declarant->cvi.";".$declarant->siret.";".$declarant->raison_sociale.";".$adresse.";".$adresse_complementaire.";".$adresse_complementaire_bis.";".$declarant->code_postal.";" .$declarant->commune.";".str_replace(";",",",$declarant->email).";".str_replace(";",",",$declarant->telephone_bureau).";".str_replace(";",",",$declarant->telephone_mobile).";".$demande->demande.";".implode(", ", $demande->getActivitesLibelle()).";".$demande->produit_libelle.";".$row->key[HabilitationHistoriqueView::KEY_STATUT].";".$row->key[HabilitationHistoriqueView::KEY_DATE].";".(($historiquePrecedent) ? $historiquePrecedent->statut : null).";".(($historiquePrecedent) ? $historiquePrecedent->date : null).";".(($historiqueSuivant) ? $historiqueSuivant->statut : null).";".(($historiqueSuivant) ? $historiqueSuivant->date : null).";".$row->id.";".$demande->getKey()."\n";

            if($stream) {
                echo $csvLigne;
            } else {
                $csv .= $csvLigne;
            }
        }

        return $csv;
    }
}
