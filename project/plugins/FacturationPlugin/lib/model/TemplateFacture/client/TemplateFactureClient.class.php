<?php

class TemplateFactureClient extends acCouchdbClient {
    public static function getInstance()
    {
      return acCouchdbManager::getClient("TemplateFacture");
    }  
}
