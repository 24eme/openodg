<?php

class DegustationClient extends acCouchdbClient {

    const TYPE_MODEL = "Degustation";
    const TYPE_COUCHDB = "DEGUSTATION";

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

    public function getDegustationsByEtablissement($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $ids = $this->startkey(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, ""))
                        ->endkey(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, "zzz"))
                        ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        $degustations = array();
        foreach ($ids as $id) {
            $degustations[$id] = DegustationClient::getInstance()->find($id, $hydrate);
        }
        krsort($degustations);
        return $degustations;
    }
}
