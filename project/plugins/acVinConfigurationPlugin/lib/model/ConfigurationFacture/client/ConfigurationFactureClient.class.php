<?php

class ConfigurationFactureClient extends acCouchdbClient {
    public static function getInstance()
    {
      return acCouchdbManager::getClient("ConfigurationFacture");
    }  
}
