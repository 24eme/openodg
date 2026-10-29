<?php

class ComptabiliteClient extends acCouchdbClient {

    const DOC_ID = "COMPTABILITE";

    public static function getInstance()
    {
      return acCouchdbManager::getClient("Comptabilite");
    }

    public function findCompta($organisme = null) {
        $id = $this->determineId($organisme);
        $compta = $this->find($id);
        if(!$compta) {
            $compta = new Comptabilite();
            $compta->set("_id", $id);
        }
        return $compta;
    }

    private function determineId($organisme = null) {
        return ($organisme)? self::DOC_ID.'-'.$organisme : self::DOC_ID;
    }
}
