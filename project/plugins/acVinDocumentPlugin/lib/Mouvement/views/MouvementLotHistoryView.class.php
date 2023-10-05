<?php

class MouvementLotHistoryView extends acCouchdbView
{
    const KEY_DECLARANT_IDENTIFIANT = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_NUMERO_DOSSIER = 2;
    const KEY_NUMERO_ARCHIVE = 3;
    const KEY_DOC_ORDRE = 4;
    const KEY_STATUT = 5;
    const KEY_ORIGINE_DOCUMENT_ID = 6;
    const KEY_UNIQUE_ID = 7;

    const VALUE_LOT = 0;

    public static function getInstance()
    {
        return acCouchdbManager::getView('mouvement', 'lotHistory');
    }

    public function getMouvementsByUniqueId($declarant, $uniqueId, $documentOrdre = null, $statut = null, $descending = false)
    {

        return $this->getMouvements($declarant, LotsClient::getCampagneFromUniqueId($uniqueId), LotsClient::getNumeroDossierFromUniqueId($uniqueId), LotsClient::getNumeroArchiveFromUniqueId($uniqueId), $documentOrdre, $statut, $descending);
    }

    public function getCampagneFromDeclarantMouvements($declarant) {
        $campagnes = array();
        foreach ($this->client
                    ->endkey(array($declarant))
                    ->startkey(array_merge(array($declarant,array())))
                    ->descending(true)
                    ->reduce(true)
                    ->group_level(2)
                    ->getView($this->design, $this->view)->rows as $r) {
            $campagnes[] = $r->value->campagne;
        }
        return $campagnes;
    }

    public function getMouvements($declarant, $campagne, $dossier, $archive, $documentOrdre = null, $statut = null, $descending = false)
    {
        $keys = array($declarant, $campagne, $dossier, $archive);
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

    public function getMouvementsByDeclarant($declarant,$campagne,$level = 4)
    {
        $keys = array($declarant, $campagne);

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

    public function buildSyntheseLots($mouvements)
    {
        $syntheseLots = [];
        foreach ($mouvements as $mouvementLot) {
            if(!$mouvementLot->value->lot_unique_id) {
                continue;
            }
            # Démo: https://regex101.com/r/J9XQnv/4
            preg_match('/([\w -]+)(?: (\w+))? (\d{4})/uU', $mouvementLot->value->libelle, $matches);
            $libelle = $matches[0];
            $produit = $matches[1];
            $couleur = $matches[2];
            $millesime = $matches[3];

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
                $syntheseLots[$produit][$millesime][$couleur]["DRev"] = 0;
                ksort($syntheseLots[$produit][$millesime]);
            }

            $syntheseLots[$produit][$millesime][$couleur]["Lot"] += $mouvementLot->value->volume;
        };

        return $syntheseLots;
    }
}
