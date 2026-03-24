<?php

class PriseDeMousseClient extends ChgtDenomClient {

    const TYPE_MODEL = "PriseDeMousse";

    public static function getInstance() {
        return acCouchdbManager::getClient("PriseDeMousse");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if($this->document->changement_type != ChgtDenomClient::CHANGEMENT_TYPE_PRISEDEMOUSSE) {
            throw new sfException("N'est pas un doc prise de mousse");
        }
        return $doc;
    }


    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $global_docs = parent::getHistory($identifiant, $hydrate);
        return $global_docs;
    }

    public function getHistoryCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $global_docs = parent::getHistoryCampagne($identifiant, $campagne, $hydrate);
        return $global_docs;
    }

    public function findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $h = $this->getHistory($identifiant, $date, $hydrate);
        if (!count($h)) {
        return NULL;
        }
        $h = $h->getDocs();
        end($h);
        $doc = $h[key($h)];
        return $doc;
    }

    public function getLotsAvailable($identifiant) {
        $lots = array();
        $lots_filtre = array();
        foreach (MouvementLotView::getInstance()->getByIdentifiant($identifiant, Lot::STATUT_CONFORME)->rows as $row_lot) {
            if (strpos($row_lot->value->produit_hash, '/VDB/') === false) {
                continue;
            }
            $lots[$row_lot->value->unique_id] = $row_lot->value;
            $lots[$row_lot->value->unique_id]->type_document = substr($row_lot->value->id_document, 0, 4);
        }

        if($campagne){
          foreach ($lots as $unique_id => $lot) {
            if($campagne && $campagne == $lot->campagne){
              $lots_filtre[$unique_id] = $lot;
            }
          }
        }else{
          $lots_filtre = $lots;
        }

        return $lots_filtre;
    }

    public function createDoc($identifiant, $lot, $date = null, $papier = false) {
        $pdm = new PriseDeMousse();

        if(!$date) {
            $date = new DateTime();
        } else {
            $date = new DateTime($date);
        }

        $pdm->identifiant = $identifiant;
        $pdm->date = $date->format('Y-m-d H:i:s');

        if($papier) {
            $pdm->add('papier', 1);
        }
        $pdm->changement_type = self::CHANGEMENT_TYPE_PRISEDEMOUSSE;
        $pdm->storeDeclarant();
        $pdm->setLotOrigine($lot);
        $pdm->constructId();

        return $pdm;
    }

    public function getChgtDenomProduction($identifiant, $campagne)
    {
        throw new sfException('fonction non compatible prise de mousse');
    }

    public function findFacturable($identifiant, $campagne) {

        throw new sfException('fonction non compatible prise de mousse');
    }

}
