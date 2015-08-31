<?php

class TourneeClient extends acCouchdbClient {
    public static function getInstance()
    {
      return acCouchdbManager::getClient("Tournee");
    }  
}
