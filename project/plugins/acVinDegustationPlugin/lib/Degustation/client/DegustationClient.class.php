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

    public function getHistory($limit = 10, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $res = $this->getAllDocsByType(self::TYPE_COUCHDB, $limit);
        $objects = array();
        foreach($res->rows as $row) {
            $objects[] = $this->find($row->id, $hydrate);
        }
        return $objects;
    }

    public static function getNumeroTableStr($numero_table){
      $alphas = range('A', 'Z');
      return $alphas[$numero_table-1];
    }
}
