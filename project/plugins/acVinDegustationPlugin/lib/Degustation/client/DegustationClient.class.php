<?php

class DegustationClient extends acCouchdbClient {
    
    public static function getInstance()
    {
      return acCouchdbManager::getClient("Degustation");
    }

    public function createDoc($date) {
        $degustation = new Degustation();
        $degustation->date = $date;

        return $degustation;
    }

}
