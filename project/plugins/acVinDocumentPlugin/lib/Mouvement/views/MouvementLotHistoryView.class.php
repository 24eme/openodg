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

    const VALUE_LOT = 0;

    public static function getInstance()
    {
        return acCouchdbManager::getView('mouvement', 'lotHistory');
    }

    public function getMouvements($declarant, $campagne, $dossier, $archive, $documentOrdre = null)
    {
        $keys = array($declarant, $campagne, $dossier, $archive);
        if($documentOrdre) {
            $keys[] = $documentOrdre;
        }

        return $this->client
                    ->startkey($keys)
                    ->endkey(array_merge($keys, array(array())))
                    ->reduce(false)
                    ->getView($this->design, $this->view);
    }

    public function getMouvementsByDeclarant($declarant,$level = 4)
    {
        $keys = array($declarant);

        return $this->client
                    ->startkey($keys)
                    ->endkey(array_merge($keys, array(array())))
                    ->reduce(true)->group_level($level)
                    ->getView($this->design, $this->view);
    }

}
