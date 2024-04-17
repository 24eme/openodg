<?php

class TourneeClient extends DegustationClient {

    const TYPE_MODEL = "Tournee";
    const TYPE_COUCHDB = "TOURNEE";

    const TYPE_TOURNEE_LOT_ALEATOIRE = 'Aleatoire';
    const TYPE_TOURNEE_LOT_ALEATOIRE_RENFORCE = 'Renforce';
    const TYPE_TOURNEE_LOT_SUPPLEMENTAIRE = 'Supplementaire';
    const TYPE_TOURNEE_LOT_RECOURS = 'Recours';
    const TYPE_TOURNEE_LOT_NC_OI = 'NC OI';

    public static $lotTourneeChoices = array(
        TourneeClient::TYPE_TOURNEE_LOT_ALEATOIRE => "Aléatoire",
        TourneeClient::TYPE_TOURNEE_LOT_ALEATOIRE_RENFORCE => "Aléatoire renforcé",
        TourneeClient::TYPE_TOURNEE_LOT_SUPPLEMENTAIRE => "Supplémentaire",
    );

    public static function getInstance()
    {
        return acCouchdbManager::getClient("Tournee");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if($doc && $doc->type != self::TYPE_MODEL) {
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }
        return $doc;
    }

    public function getHistory($limit = 10, $annee = "", $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $region = null) {
        $docs = $this->startkey(self::TYPE_COUCHDB."-".$annee."Z")->endkey(self::TYPE_COUCHDB."-".$annee)->descending(true)->limit(($region) ? $limit * 5 : $limit)->execute($hydrate);

        if($region) {
            $docsByRegion = [];
            foreach($docs as $doc) {
                if(isset($doc->region) && $doc->region == $region) {
                    $docsByRegion[] = $doc;
                }
                if(count($docsByRegion) >= $limit) {
                    break;
                }
            }

            return $docsByRegion;
        }

        return $docs;
    }

    public function findOrCreate($date, $region = null) {
        $newTournee = $this->createDoc($date, $region);

        $tournee = $this->find($newTournee->_id);

        if(!$tournee) {

            return $newTournee;
        }

        return $tournee;
    }

    public function createDoc($date, $region = null) {
        $degustation = new Tournee();
        $degustation->date = $date;
        if($region) {
            $degustation->add('region', $region);
        }
        $degustation->constructId();

        return $degustation;
    }

    public function getLotsEnAttente($region, $date = null) {

        return parent::getLotsEnAttente($region);
	}

}
