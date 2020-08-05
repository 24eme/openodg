<?php

class MouvementLotView extends acCouchdbView
{
    const KEY_PRELEVABLE = 0;
    const KEY_PRELEVE = 1;
    const KEY_REGION = 2;
    const KEY_DATE = 3;
    const KEY_IDENTIFIANT = 4;
    const KEY_ORIGINE_DOCUMENT_ID = 5;

    const VALUE_LOT = 0;

    public static function getInstance() {

        return acCouchdbManager::getView('mouvement', 'lot');
    }

    public function getByPrelevablePreleve($prelevable, $preleve) {
        return $this->client->startkey(array($prelevable, $preleve))
                            ->endkey(array($prelevable, $preleve, array()))
                            ->getView($this->design, $this->view);
    }

    public function getByPrelevablePreleveRegionDateIdentifiantDocumentId($prelevable, $preleve, $region, $date, $identifiant, $document_id) {
        return $this->client->startkey(array($prelevable, $preleve, $region, $date, $identifiant, $document_id))
                            ->endkey(array($prelevable, $preleve, $region, $date, $identifiant, $document_id, array()))
                            ->getView($this->design, $this->view);
    }
    
    public static function getDestinationLibelle($lot) {
        $libelles = DRevClient::$lotDestinationsType;
        return (isset($libelles[$lot->destination_type]))? $libelles[$lot->destination_type] : '';
    }

}
