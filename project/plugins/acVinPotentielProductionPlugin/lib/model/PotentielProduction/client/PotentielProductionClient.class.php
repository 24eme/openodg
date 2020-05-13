<?php

class PotentielProductionClient extends acCouchdbClient {

      const TYPE_MODEL = "PotentielProduction";
      const TYPE_COUCHDB = "POTENTIELPRODUCTION";

      public static function getInstance() {
          return acCouchdbManager::getClient("PotentielProduction");
      }
      
}