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

}
