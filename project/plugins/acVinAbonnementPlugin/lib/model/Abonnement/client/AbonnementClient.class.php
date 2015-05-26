<?php

class AbonnementClient extends acCouchdbClient implements FacturableClient {
    
    public static function getInstance()
    {
        
        return acCouchdbManager::getClient("Abonnement");
    }

    public function findFacturable($identifiant, $campagne) {

        return $this->find(sprintf("ABONNEMENT-%s-%s", $identifiant, $campagne));
    }
}
