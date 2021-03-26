<?php

class DegustationClient extends acCouchdbClient implements FacturableClient {

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

    public function createDoc($date) {
        $degustation = new Degustation();
        $degustation->date = $date;
        $degustation->constructId();

        return $degustation;
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
	    foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_AFFECTABLE)->rows as $lot) {
	        $lots[$lot->value->unique_id] = $lot->value;
            $lots[$lot->value->unique_id]->type_document = substr($lot->id, 0, 4);
            $nb_passage = MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($lot->value) + 1;
            if ($nb_passage > 1) {
                $lots[$lot->value->unique_id]->specificite = Lot::generateTextePassage($lots[$lot->value->unique_id], $nb_passage);
            }
	    }
        uasort($lots, array("DegustationClient", "sortLotByDate"));

        return $lots;
	}

    public static function sortLotByDate($lot1, $lot2) {

        return $lot1->date > $lot2->date;
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

    public function findFacturable($identifiant, $campagne) {
        // TODO : A optimiser : aujourd'hui on doit récuperer toutes les Degustations du declarant
        return array();
        $lotsView = MouvementLotView::getInstance()->getByIdentifiant($identifiant)->rows;

        $facturables = array();
        foreach ($lotsView as $lotView) {
            if(preg_match("/^".self::TYPE_COUCHDB."-".($campagne+1)."/", $lotView->id) && !array_key_exists($lotView->id,$facturables)){
                $facturables[$lotView->id] = $this->find($lotView->id);
            }
        }
        return $facturables;
    }

}
