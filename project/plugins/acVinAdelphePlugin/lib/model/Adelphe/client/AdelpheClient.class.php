<?php

class AdelpheClient extends acCouchdbClient {

  const TYPE_MODEL = "Adelphe";
  const TYPE_COUCHDB = "ADELPHE";

  public static function getInstance() {
      return acCouchdbManager::getClient("Adelphe");
  }

  public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
      $doc = parent::find($id, $hydrate, $force_return_ls);
      if($doc && $doc->type != self::TYPE_MODEL) {
          throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
      }
      return $doc;
    }

    public function createDoc($identifiant, $periode, $papier = false)
    {
        $adelphe = new Adelphe();
        $adelphe->initDoc($identifiant, $periode);
        $adelphe->storeDeclarant();
        if($papier) {
            $adelphe->add('papier', 1);
        }
        return $adelphe;
    }
}
