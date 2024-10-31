<?php

class MouvementLotHistoryView extends acCouchdbView
{
    const KEY_REGION = 0;
    const KEY_DECLARANT_IDENTIFIANT = 1;
    const KEY_CAMPAGNE = 2;
    const KEY_NUMERO_DOSSIER = 3;
    const KEY_NUMERO_ARCHIVE = 4;
    const KEY_DOC_ORDRE = 5;
    const KEY_STATUT = 6;
    const KEY_ORIGINE_DOCUMENT_ID = 7;
    const KEY_UNIQUE_ID = 8;

    const VALUE_LOT = 0;

    public static function getInstance()
    {
        return acCouchdbManager::getView('mouvement', 'lotHistory');
    }

    public function getMouvementsByUniqueId($declarant, $uniqueId, $region = null, $documentOrdre = null, $statut = null, $descending = false)
    {

        return $this->getMouvements($declarant, LotsClient::getCampagneFromUniqueId($uniqueId), LotsClient::getNumeroDossierFromUniqueId($uniqueId), LotsClient::getNumeroArchiveFromUniqueId($uniqueId), $region, $documentOrdre, $statut, $descending);
    }

    public function getCampagneFromDeclarantMouvements($declarant) {
        $campagnes = array();
        foreach ($this->client
                    ->endkey(array($declarant))
                    ->startkey(array_merge(array($declarant,array())))
                    ->descending(true)
                    ->reduce(true)
                    ->group_level(self::KEY_CAMPAGNE + 1)
                    ->getView($this->design, $this->view)->rows as $r) {
            $campagnes[] = $r->value->campagne;
        }
        return $campagnes;
    }

    public function getMouvements($declarant, $campagne, $dossier, $archive, $region = null, $documentOrdre = null, $statut = null, $descending = false)
    {
        $keys = array($region, $declarant, $campagne, $dossier, $archive);
        if($documentOrdre) {
            $keys[] = $documentOrdre;
        }

        if($statut) {
            $keys[] = $statut;
        }

        if ($descending)
            return $this->client
                ->endkey($keys)
                ->startkey(array_merge($keys, array(array())))
                ->descending(true)
                ->reduce(false)
                ->getView($this->design, $this->view);

        return $this->client
                ->startkey($keys)
                ->endkey(array_merge($keys, array(array())))
                ->reduce(false)
                ->getView($this->design, $this->view);
    }

    public function getMouvementsByDeclarant($declarant,$campagne, $region = null,$level = self::KEY_NUMERO_ARCHIVE + 1)
    {
        $keys = array($region, $declarant, $campagne);

        return $this->client
                    ->endkey($keys)
                    ->startkey(array_merge($keys, array(array())))
                    ->reduce(true)->group_level($level)
                    ->descending(true)
                    ->getView($this->design, $this->view);
    }

    public function getAllLotsWithHistorique()
    {
      return $this->client
            ->reduce(false)
            ->getView($this->design, $this->view);
    }

    public static function isWaitingLotNotification($mvt_value) {
        if (($mvt_value->document_type == 'Degustation') && (!isset($mvt_value->date_notification) || !$mvt_value->date_notification)) {
            return true;
        }
        if (isset($mvt_value->date_commission) && ($mvt_value->date_commission > date('Y-m-d'))) {
            return true;
        }
        return false;
    }

    public function buildSyntheseLots($mouvements)
    {
        $syntheseLots = [];
        foreach ($mouvements as $mouvementLot) {
            if(!$mouvementLot->value->lot_unique_id) {
                continue;
            }
            # Démo: https://regex101.com/r/c3TWNq/1
            preg_match('/([\w -]+)(?: (\w+))? (moelleux|Doux| )*(\d{4})/uU', $mouvementLot->value->libelle, $matches);
            $libelle = $matches[0];
            $produit = $matches[1];
            $couleur = $matches[2];
            $millesime = $matches[4];

            if (array_key_exists($produit, $syntheseLots) === false) {
                $syntheseLots[$produit] = [];
                ksort($syntheseLots);
            }

            if (array_key_exists($millesime, $syntheseLots[$produit]) === false) {
                $syntheseLots[$produit][$millesime] = [];
                ksort($syntheseLots[$produit]);
            }

            if (array_key_exists($couleur, $syntheseLots[$produit][$millesime]) === false) {
                $syntheseLots[$produit][$millesime][$couleur]["Lot"] = 0;
                $syntheseLots[$produit][$millesime][$couleur][$mouvementLot->value->initial_type] = 0;
                ksort($syntheseLots[$produit][$millesime]);
            }

            $syntheseLots[$produit][$millesime][$couleur]["Lot"] += $mouvementLot->value->volume;
            @$syntheseLots[$produit][$millesime][$couleur][$mouvementLot->value->initial_type] += $mouvementLot->value->volume;
        };

        return $syntheseLots;
    }

}
