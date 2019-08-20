<?php

class ExportHabilitationDemandesPublipostageCSV {
    protected $dateFrom = null;
    protected $dateTo = null;
    protected $header = null;

    protected static $delimiter = ';';

    public static function getHeaderCsv() {
        $headers = [
            'Identifiant',
            'CVI Opérateur',
            'Siret Opérateur',
            'Nom Opérateur',
            'Adresse Opérateur',
            'Adresse complémentaire Opérateur 1',
            'Adresse complémentaire Opérateur 2',
            'Code postal Opérateur',
            'Commune Opérateur',
            'Adresse Société',
            'Adresse complémentaire Société 1',
            'Adresse complémentaire Société 2',
            'Code postal Société',
            'Commune Société',
            'Email',
            'Téléphone Bureau',
            'Téléphone Mobile',
            'Demande',
            'Libellé activités',
            'Produit',
            'Statut',
            'Date Statut',
            'Statut précédent',
            'Date précédent statut',
            'Statut suivant',
            'Date statut suivant',
            'Commentaire',
            'Id du doc',
            "Clé de la demande\n"
        ];
        return implode(self::$delimiter, $headers);
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
            $societe = $hab->getSociete();

            $declarant_adresse = str_replace('"', '', $declarant->adresse);
            $acs = explode('−',$declarant->adresse_complementaire);
            $declarant_adresse_complementaire = "";
            $declarant_adresse_complementaire_bis = "";
            $declarant_adresse_complementaire = str_replace('"', '', $acs[0]);
            if(count($acs) > 1){
                $declarant_adresse_complementaire_bis = str_replace('"', '', $acs[1]);
            }

            $societe_adresse_complementaire = explode('−', $societe->siege->adresse_complementaire);
            $societe_adresse_complementaire1 = '';
            $societe_adresse_complementaire2 = '';
            $societe_adresse_complementaire1 = str_replace('"', '', $societe_adresse_complementaire[0]);
            if (count($societe_adresse_complementaire) > 1) {
                $societe_adresse_complementaire2 = str_replace('"', '', $societe_adresse_complementaire[1]);
            }

            $csvLigne = $row->key[HabilitationHistoriqueView::KEY_IDENTIFIANT].";"
                .$declarant->cvi.";"
                .$declarant->siret.";"
                .$declarant->raison_sociale.";"
                .$declarant_adresse.";"
                .$declarant_adresse_complementaire.";"
                .$declarant_adresse_complementaire_bis.";"
                .$declarant->code_postal.";"
                .$declarant->commune.";"
                .$societe->siege->adresse.";"
                .$societe_adresse_complementaire1.";"
                .$societe_adresse_complementaire2.";"
                .$societe->siege->code_postal.";"
                .$societe->siege->commune.";"
                .str_replace(";",",",$declarant->email).";"
                .str_replace(";",",",$declarant->telephone_bureau).";"
                .str_replace(";",",",$declarant->telephone_mobile).";"
                .$demande->demande.";"
                .implode(", ", $demande->getActivitesLibelle()).";"
                .$demande->produit_libelle.";"
                .$row->key[HabilitationHistoriqueView::KEY_STATUT].";"
                .$row->key[HabilitationHistoriqueView::KEY_DATE].";"
                .(($historiquePrecedent) ? $historiquePrecedent->statut : null).";"
                .(($historiquePrecedent) ? $historiquePrecedent->date : null).";"
                .(($historiqueSuivant) ? $historiqueSuivant->statut : null).";"
                .(($historiqueSuivant) ? $historiqueSuivant->date : null).";"
                ."\"".$row->key[HabilitationHistoriqueView::KEY_COMMENTAIRE]."\";"
                .$row->id.";"
                .$demande->getKey()
                ."\n";

            if($stream) {
                echo $csvLigne;
            } else {
                $csv .= $csvLigne;
            }
        }

        return $csv;
    }
}
