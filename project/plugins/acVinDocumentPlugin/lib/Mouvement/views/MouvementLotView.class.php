<?php

class MouvementLotView extends acCouchdbView
{
    const KEY_DECLARANT_IDENTIFIANT = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_PRELEVABLE = 2;
    const KEY_PRELEVE = 3;
    const KEY_REGION = 4;
    const KEY_DATE = 5;
    const KEY_ORIGINE_DOCUMENT_ID = 6;

    const VALUE_LOT = 0;

    public static function getInstance() {

        return acCouchdbManager::getView('mouvement', 'lot');
    }

    public function getByPrelevablePreleve($campagne, $prelevable, $preleve) {
        return $this->client->startkey(array(null, $campagne, $prelevable, $preleve))
                            ->endkey(array(null, $campagne, $prelevable, $preleve, array()))
                            ->getView($this->design, $this->view);
    }

    public function getByPrelevablePreleveRegionDateIdentifiantDocumentId($campagne, $prelevable, $preleve, $region, $date, $declarant_identifiant, $document_id) {
        return $this->client->startkey(array($declarant_identifiant, $campagne, $prelevable, $preleve, $region, $date, $document_id))
                            ->endkey(array($declarant_identifiant, $campagne, $prelevable, $preleve, $region, $date, $document_id, array()))
                            ->getView($this->design, $this->view);
    }

    public function getByDeclarantIdentifiant($declarant_identifiant, $campagne = null, $prelevable = null, $preleve = null) {
        $query = array($declarant_identifiant);
        if (!is_null($campagne)) {
            $query[] = $campagne;
            if (!is_null($prelevable)) {
                $query[] = $prelevable;
                if (!is_null($preleve)) {
                    $query[] = $preleve;
                }
            }
        }
        return $this->client->startkey($query)
                            ->endkey(array_merge($query, array(array())))
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
        $lot->elevage = $mvt->elevage;
        return $lot;
    }

}
