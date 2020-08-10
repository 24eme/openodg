<?php

class MouvementLotView extends acCouchdbView
{
    const KEY_PRELEVABLE = 0;
    const KEY_PRELEVE = 1;
    const KEY_REGION = 2;
    const KEY_DATE = 3;
    const KEY_DECLARANT_IDENTIFIANT = 4;
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

    public function getByPrelevablePreleveRegionDateIdentifiantDocumentId($prelevable, $preleve, $region, $date, $declarant_identifiant, $document_id) {
        return $this->client->startkey(array($prelevable, $preleve, $region, $date, $declarant_identifiant, $document_id))
                            ->endkey(array($prelevable, $preleve, $region, $date, $declarant_identifiant, $document_id, array()))
                            ->getView($this->design, $this->view);
    }

    public static function getDestinationLibelle($lot) {
        $libelles = DRevClient::$lotDestinationsType;
        return (isset($libelles[$lot->destination_type]))? $libelles[$lot->destination_type] : '';
    }

    public static function generateLotByMvt($mvt) {
        $lot = new stdClass();
        $lot->date = $mvt->date;
        $lot->id_document = $mvt->origine_document_id;
        $lot->numero = $mvt->numero;
        $lot->millesime = $mvt->millesime;
        $lot->volume = $mvt->volume;
        $lot->destination_type = $mvt->destination_type;
        $lot->destination_date = $mvt->destination_date;
        $lot->produit_hash = $mvt->produit_hash;
        $lot->produit_libelle = $mvt->produit_libelle;
        $lot->declarant_nom = $mvt->declarant_nom;
        $lot->declarant_identifiant = $mvt->declarant_identifiant;
        $lot->origine_mouvement = $mvt->origine_mouvement;
        $lot->details = $mvt->details;
        return $lot;
    }

}
