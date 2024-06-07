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
    const DEGUSTATION_TRI_OPERATEUR = 'operateur';
    const DEGUSTATION_TRI_MANUEL = "manuel";
    const DEGUSTATION_TRI_NUMERO_ANONYMAT = "numero_anonymat";
    const DEGUSTATION_TRI_PRODUIT = "produit";
    const DEGUSTATION_SANS_SECTEUR = "SANS_SECTEUR";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("Degustation");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if($doc && $doc->type != self::TYPE_MODEL && $doc->type != TourneeClient::TYPE_MODEL) {
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }
        return $doc;
    }

    public function createDoc($date, $region = null) {
        $degustation = new Degustation();
        $degustation->date = $date;
        if($region) {
            $degustation->add('region', $region);
        }
        $degustation->constructId();

        return $degustation;
    }

    public function getHistory($limit = 10, $annee = "", $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $region = null) {
        $docs = $this->startkey(self::TYPE_COUCHDB."-".$annee."Z")->endkey(self::TYPE_COUCHDB."-".$annee)->descending(true)->limit(($region) ? $limit * 5 : $limit)->execute($hydrate);

        if($region) {
            $docsByRegion = [];
            foreach($docs as $doc) {
                if(!$doc instanceof Degustation && isset($doc->region) && strpos($doc->region, $region) === 0) {
                    $docsByRegion[] = $doc;
                    continue;
                }
                if($doc instanceof Degustation && $doc->region == $region) {
                    $docsByRegion[] = $doc;
                    continue;
                }
                if(count($docsByRegion) >= $limit) {
                    break;
                }
            }
            return $docsByRegion;
        }

        return $docs;
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
        if (!$lot) {
            throw new sfException("Le lot ne doit pas être vide");
        }
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

    public function getLotsEnAttente($region, $date = null) {
	    $lots = array();
        $statut = Lot::STATUT_AFFECTABLE;
        if(DegustationConfiguration::getInstance()->isTourneeAutonome() && get_called_class() != "TourneeClient") {
            $statut = Lot::STATUT_AFFECTABLE_PRELEVE;
        }
        $rows = MouvementLotView::getInstance()->getByStatut($statut)->rows;
	    foreach ($rows as $lot) {
            if($region && $region !== Organisme::getInstance()->getOIRegion() && !RegionConfiguration::getInstance()->isHashProduitInRegion($region, $lot->value->produit_hash)) {
                continue;
            }
            if ($region === Organisme::getInstance()->getOIRegion() && $lot->key[MouvementLotView::KEY_REGION] !== $region) {
                continue;
            }
            if ($lot->key[MouvementLotView::KEY_REGION] === Organisme::getInstance()->getOIRegion() && $lot->key[MouvementLotView::KEY_REGION] !== $region) {
                continue;
            }
            if (isset($lot->value->date_degustation_voulue) && $date && strtotime($lot->value->date_degustation_voulue) > strtotime($date)) {
              continue;
            }
            if (!$lot->value) {
                throw new sfException("Lot ne devrait pas être vide : ".print_r($lot, true));
            }
            $lotKey = $lot->value->date.$lot->value->unique_id;
	        $lots[$lotKey] = $this->cleanLotForDegustation($lot->value);
            $lots[$lotKey]->type_document = substr(strtok($lot->id, '-'), 0, 4);
            $nb_passage = MouvementLotView::getInstance()->getNombreAffecteSourceAvantMoi($lot->value) + 1;
            if ($nb_passage > 1) {
                $lots[$lotKey]->specificite = Lot::generateTextePassage($lots[$lotKey], $nb_passage);
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

    public function getElevages($campagne = null, $region = null) {
        $elevages = array();
        foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_ELEVAGE_EN_ATTENTE)->rows as $item) {
            if($region && !RegionConfiguration::getInstance()->isHashProduitInRegion($region, $item->value->produit_hash)) {
                continue;
            }
            $item->value->id_document = $item->id;
            $elevages[$item->value->unique_id] = $this->cleanLotForDegustation($item->value);
        }
        ksort($elevages);
        return $elevages;
    }


    public function getManquements($campagne = null, $region = null) {
        $manquements = array();
        foreach (MouvementLotView::getInstance()->getByStatut(Lot::STATUT_MANQUEMENT_EN_ATTENTE, )->rows as $item) {
            if($region && !RegionConfiguration::getInstance()->isHashProduitInRegion($region, $item->value->produit_hash)) {
                continue;
            }
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
