<?php

class ConstatsClient extends acCouchdbClient {

    const TYPE_COUCHDB = 'CONSTATS';
    const STATUT_NONCONSTATE = 'NONCONSTATE';
    const STATUT_APPROUVE = 'APPROUVE';
    const TYPE_CONTENANT_BOTICHE = 'CONTENANT_BOTICHE';
    const CONSTAT_TYPE_RAISIN = 'TYPE_RAISIN';
    const CONSTAT_TYPE_VOLUME = 'TYPE_VOLUME';

    const CONTENANT_BOTTICHE = 'BOTTICHE';
    const CONTENANT_BEINE = 'BEINE';
    const CONTENANT_CAJETTE = 'CAJETTE';
    const CONTENANT_KG = 'KG';
    const CONTENANT_TYPE_PALOX = 'TYPE_PALOX';

    public static $contenants_libelle = array(
        self::CONTENANT_BOTTICHE => 'Bottiche',
        self::CONTENANT_BEINE => 'Beine',
        self::CONTENANT_CAJETTE => 'Cajette',
        self::CONTENANT_KG => 'Kg',
        self::CONTENANT_TYPE_PALOX => 'Type palox',
    );
    
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
        $constats->add('constats')->getOrAdd($constats->getConstatIdNode($rendezvous))->createOrUpdateFromRendezVous($rendezvous);
        return $constats;
    }

    public function updateConstatFromRendezVous(Rendezvous $rendezvous, Constats $constats) {
        $idNodeConstat = $constats->getConstatIdNode($rendezvous);        
        $constats->add('constats')->getOrAdd($idNodeConstat)->createOrUpdateFromRendezVous($rendezvous);
        return $constats;
    }
    
   public function getContenantsLibelle() {

        return self::$contenants_libelle;
   }

    public function getProduits() { 
        
        return ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
        
    }
    
}
