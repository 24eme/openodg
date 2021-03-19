<?php

class DegustationClient extends acCouchdbClient {

    const TYPE_MODEL = "Degustation";
    const TYPE_COUCHDB = "DEGUSTATION";
    const SPECIFICITE_PASSAGES = "Xème passage";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("Degustation");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if($doc && $doc->type != self::TYPE_MODEL) {
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }
        return $doc;
    }

    public function getHistory($limit = 10, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return $this->startkey(self::TYPE_COUCHDB."Z")->endkey(self::TYPE_COUCHDB)->descending(true)->limit($limit)->execute($hydrate);
    }

    public function getHistoryLieux($limit = 50) {
        $degusts = $this->getHistory($limit, acCouchdbClient::HYDRATE_JSON);
        $lieux = array();
        foreach ($degusts as $d) {
            $lieux[$d->lieu] = $d->lieu;
        }
        if (!count($lieux)) {
            return array("Salle de dégustation par défaut" => "Salle de dégustation par défaut");
        }
        return $lieux;
    }


	public function getLotsPrelevables() {
	    $lots = array();
	    foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_AFFECTABLE)->rows as $mouvement) {
	        $lots[$mouvement->value->unique_id] = $mouvement->value;
            $lots[$mouvement->value->unique_id]->id_document_provenance = $mouvement->id;
            $lots[$mouvement->value->unique_id]->provenance = substr($mouvement->id, 0, 4);
	    }
        uasort($lots, function ($lot1, $lot2) {
            $date1 = DateTime::createFromFormat('Y-m-d', $lot1->date);
            $date2 = DateTime::createFromFormat('Y-m-d', $lot2->date);

            if ($date1 == $date2) {
                return 0;
            }
            return ($date1 < $date2) ? -1 : 1;
        });

        return $lots;
	}

    public static function getNumeroTableStr($numero_table){
      $alphas = range('A', 'Z');
      return $alphas[$numero_table-1];
    }

    public function getManquements() {
        $manquements = array();
        foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_MANQUEMENT_EN_ATTENTE)->rows as $item) {
            $item->value->id_document = $item->id;
            $manquements[$item->value->unique_id] = $item->value;
        }
        return $manquements;
    }

}
