<?php

class ConstatsClient extends acCouchdbClient {

    const TYPE_COUCHDB = 'CONSTATS';
    const STATUT_NONCONSTATE = 'NONCONSTATE';
    const TYPE_CONTENANT_BOTICHE = 'CONTENANT_BOTICHE';
    
    public static $types_botiche = array(self::TYPE_CONTENANT_BOTICHE => 'Botiche');

    public static function getInstance() {
        return acCouchdbManager::getClient("Constats");
    }

    public function findByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, $campagne), $hydrate);
    }

    public function updateOrCreateConstatFromRendezVous(Rendezvous $rendezvous) {

        $constats = $this->findByIdentifiantAndCampagne($rendezvous->cvi, substr($rendezvous->date, 0, 4));

        if ($constats) {
            return $this->updateConstatFromRendezVous($rendezvous, $constats);
        }
        $constats = new Constats();

        $constats->synchroFromRendezVous($rendezvous);

        $constats->constructId();
        $constats->add('constats')->getOrAdd($rendezvous->getDateHeure())->createFromRendezVous($rendezvous);
        return $constats;
    }

    public function updateConstatFromRendezVous(Rendezvous $rendezvous, Constats $constats) {
        $constats->add('constats')->getOrAdd($rendezvous->getDateHeure())->createFromRendezVous($rendezvous);
        return $constats;
    }

    public function getProduits() { 
        
        return ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
        
    }
    
}
