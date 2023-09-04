<?php

class TourneeClient extends DegustationClient {

    const TYPE_MODEL = "Tournee";
    const TYPE_COUCHDB = "TOURNEE";

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

    public function createDoc($date, $region = null) {
        $degustation = new Tournee();
        $degustation->date = $date;
        if($region) {
            $degustation->add('region', $region);
        }
        $degustation->constructId();

        return $degustation;
    }

}
