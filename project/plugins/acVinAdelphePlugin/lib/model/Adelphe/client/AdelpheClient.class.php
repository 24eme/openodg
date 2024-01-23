<?php

class AdelpheClient extends acCouchdbClient {

  const TYPE_MODEL = "Adelphe";
  const TYPE_COUCHDB = "ADELPHE";
  const DROIT_ADELPHE = "ADELPHE";

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

  public function createDoc($identifiant, $periode, $papier = false) {
    $adelphe = new Adelphe();
    $adelphe->initDoc($identifiant, $periode);
    if($papier) {
        $adelphe->add('papier', 1);
    }
    return $adelphe;
  }

  public function findMasterByIdentifiantAndPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
    return $this->findMasterByIdentifiantAndCampagne($identifiant, $periode.'-'.($periode + 1), $hydrate);
  }

  public function findMasterByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
    $docs = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, $campagne, self::TYPE_MODEL);
    foreach ($docs as $id => $doc) {
      return $this->find($id, $hydrate);
    }
    return null;
  }
}
