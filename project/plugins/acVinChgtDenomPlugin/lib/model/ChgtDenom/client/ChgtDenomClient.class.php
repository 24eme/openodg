<?php

class ChgtDenomClient extends acCouchdbClient {

    const TYPE_MODEL = "ChgtDenom";
    const TYPE_COUCHDB = "CHGTDENOM";
    public static $ORIGINE_LOT = array("drev");

    const FORMAT_DATE = 'Y-m-d\THis';


    public static function getInstance() {
        return acCouchdbManager::getClient("ChgtDenom");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if($doc && $doc->type != self::TYPE_MODEL) {
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }
        return $doc;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000-00-00T000000";
        $campagne_to = "9999-99-99T999999";
        return $this->startkey(sprintf("CHGTDENOM-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf("CHGTDENOM-%s-%s_ZZZZZZZZZZZZZZ", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function createDoc($identifiant, $date = null, $papier = false) {
        $chgtdenom = new ChgtDenom();
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $date))? $date : date(self::FORMAT_DATE);
        $chgtdenom->initDoc($identifiant, $date);
        if($papier) {
            $chgtdenom->add('papier', 1);
        }
        $chgtdenom->storeDeclarant();
        return $chgtdenom;
    }

}
