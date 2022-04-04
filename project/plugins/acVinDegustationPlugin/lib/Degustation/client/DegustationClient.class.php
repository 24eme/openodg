<?php

class DegustationClient extends acCouchdbClient implements FacturableClient {

    const TYPE_MODEL = "Degustation";
    const TYPE_COUCHDB = "DEGUSTATION";
    const SPECIFICITE_PASSAGES = "Xème passage";

    const DEGUSTATION_TRI_APPELLATION = "appellation";
    const DEGUSTATION_TRI_GENRE = "genre";
    const DEGUSTATION_TRI_COULEUR = "couleur";
    const DEGUSTATION_TRI_CEPAGE = "cepage";
    const DEGUSTATION_TRI_MILLESIME = "millesime";
    const DEGUSTATION_TRI_MANUEL = "manuel";
    const DEGUSTATION_TRI_NUMERO_ANONYMAT = "numero_anonymat";
    const DEGUSTATION_TRI_PRODUIT = "produit";


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

    public function getHistory($limit = 10, $annee = "", $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->startkey(self::TYPE_COUCHDB."-".$annee."Z")->endkey(self::TYPE_COUCHDB."-".$annee)->descending(true)->limit($limit)->execute($hydrate);
    }

    public function getHistoryEncours() {

        $history = $this->getHistory(20);
        $degustations = array();
        foreach($history as $degustation) {
            if(in_array($degustation->etape, array(DegustationEtapes::ETAPE_RESULTATS, DegustationEtapes::ETAPE_NOTIFICATIONS))) {
                continue;
            }

            $degustations[$degustation->_id] = $degustation;
        }

        return $degustations;
    }

    public function cleanLotForDegustation($lot) {
        if (get_class($lot) != 'stdClass') {
            $lot = $lot->toJson();
        }
        $lotDef = DegustationLot::freeInstance(new Degustation());
        foreach($lot as $key => $value) {
            if($lotDef->getDefinition()->exist($key)) {
                continue;
            }
            unset($lot->{$key});
        }
        return $lot;
    }

	public function getLotsPrelevables() {
	    $lots = array();
	    foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_AFFECTABLE)->rows as $lot) {
	        $lots[$lot->value->unique_id] = $this->cleanLotForDegustation($lot->value);
            $lots[$lot->value->unique_id]->type_document = substr($lot->id, 0, 4);
            $nb_passage = MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($lot->value) + 1;
            if ($nb_passage > 1) {
                $lots[$lot->value->unique_id]->specificite = Lot::generateTextePassage($lots[$lot->value->unique_id], $nb_passage);
            }
	    }
        ksort($lots);
        return $lots;
	}

    public static function sortLotByDate($lot1, $lot2) {

        return $lot1->date > $lot2->date;
    }

    public static function getNumeroTableStr($numero_table){
      $alphas = range('A', 'Z');
      return intval($numero_table) ? $alphas[$numero_table-1] : false;
    }

    public function getElevages($campagne = null) {
        $elevages = array();
        foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_ELEVAGE_EN_ATTENTE)->rows as $item) {
            $item->value->id_document = $item->id;
            $elevages[$item->value->unique_id] = $this->cleanLotForDegustation($item->value);
        }
        ksort($elevages);
        return $elevages;
    }


    public function getManquements($campagne = null) {
        $manquements = array();
        foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_MANQUEMENT_EN_ATTENTE)->rows as $item) {
            $item->value->id_document = $item->id;
            $manquement = $this->cleanLotForDegustation($item->value);
            if ($campagne && $manquement->campagne != $campagne) {
                continue;
            }
            $manquements[$manquement->date.$item->value->unique_id] = $manquement;
        }

        ksort($manquements);
        return $manquements;
    }

    public function findFacturable($identifiant, $campagne) {
        // TODO : A optimiser : aujourd'hui on doit récuperer toutes les Degustations du declarant

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
