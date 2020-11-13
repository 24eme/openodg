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

    public function getByStatut($campagne, $statut) {
        return $this->client->startkey(array(null, $campagne, $statut))
                            ->endkey(array(null, $campagne, $statut, array()))
                            ->getView($this->design, $this->view);
    }

    public function getByPrelevablePreleveRegionDateIdentifiantDocumentId($campagne, $statut, $region, $date, $declarant_identifiant, $document_id) {
        return $this->client->startkey(array($declarant_identifiant, $campagne, $statut, $region, $date, $document_id))
                            ->endkey(array($declarant_identifiant, $campagne, $statut, $region, $date, $document_id, array()))
                            ->getView($this->design, $this->view);
    }

    public function getByDeclarantIdentifiant($declarant_identifiant, $campagne = null, $statut = null) {
        $query = array($declarant_identifiant);
        if (!is_null($campagne)) {
            $query[] = $campagne;
            if (!is_null($statut)) {
                $query[] = $statut;
            }
        }
        return $this->client->startkey($query)
                            ->endkey(array_merge($query, array(array())))
                            ->getView($this->design, $this->view);
    }

    public function getAllByIdentifiantAndStatuts($declarant_identifiant, $statuts, $campagne = null) {
        $result = array();
        $cStart = ($campagne)? $campagne : "0000";
        $cEnd = ($campagne)? $campagne : "9999";
        foreach($statuts as $statut) {
          $start = array($declarant_identifiant, $cStart, $statut);
          $end = array($declarant_identifiant, $cEnd, $statut, array());
          $result = array_merge($result, $this->client->startkey($start)->endkey($end)->getView($this->design, $this->view)->rows);
        }
        return $result;
    }

    public static function getDestinationLibelle($lot) {
        $libelles = DRevClient::$lotDestinationsType;
        return (isset($libelles[$lot->destination_type]))? $libelles[$lot->destination_type] : '';
    }

    public static function generateLotByMvt($mvt) {
        $lot = new stdClass();
        $lot->date = $mvt->date;
        $lot->id_document = $mvt->origine_document_id;
        $lot->numero_dossier = $mvt->numero_dossier;
        $lot->numero_archive = $mvt->numero_archive;
        $lot->numero_cuve = $mvt->numero_cuve;
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
        $lot->elevage = (isset($mvt->elevage))? $mvt->elevage : null;
        $lot->statut = $mvt->statut;
        return $lot;
    }

}
