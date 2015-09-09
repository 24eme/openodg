<?php

class ConstatsClient extends acCouchdbClient {

    const TYPE_COUCHDB = 'CONSTATS';
    const STATUT_NONCONSTATE = 'NONCONSTATE';
    const STATUT_APPROUVE = 'APPROUVE';
    const TYPE_CONTENANT_BOTICHE = 'CONTENANT_BOTICHE';
    const CONSTAT_TYPE_RAISIN = 'TYPE_RAISIN';
    const CONSTAT_TYPE_VOLUME = 'TYPE_VOLUME';
    
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
        $constats->add('constats')->getOrAdd($constats->getConstatIdNode($rendezvous))->createFromRendezVous($rendezvous);
        return $constats;
    }

    public function updateConstatFromRendezVous(Rendezvous $rendezvous, Constats $constats) {
        //ICI update Node SI plutot que de créer si le rdv est du volume    
        $constats->add('constats')->getOrAdd($constats->getConstatIdNode($rendezvous))->createFromRendezVous($rendezvous);
        return $constats;
    }
    
   

    public function getProduits() { 
        
        return ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
        
    }
    
}
