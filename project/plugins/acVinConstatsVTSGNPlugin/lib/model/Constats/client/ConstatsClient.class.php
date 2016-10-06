<?php

class ConstatsClient extends acCouchdbClient {

    const TYPE_MODEL = "Constats";
    const TYPE_COUCHDB = 'CONSTATS';
    const STATUT_NONCONSTATE = 'NONCONSTATE';
    const STATUT_APPROUVE = 'APPROUVE';
    const STATUT_REFUSE = 'REFUSE';
    const TYPE_CONTENANT_BOTICHE = 'CONTENANT_BOTICHE';
    const CONSTAT_TYPE_RAISIN = 'TYPE_RAISIN';
    const CONSTAT_TYPE_VOLUME = 'TYPE_VOLUME';
    const CONTENANT_BOTTICHE = 'BOTTICHE';
    const CONTENANT_BEINE = 'BENNE';
    const CONTENANT_CAGETTE = 'CAGETTE';
    const CONTENANT_KG = 'KG';
    const CONTENANT_TYPE_PALOX = 'TYPE_PALOX';
    const RAISON_REFUS_ANNULE = 'ANNULE';
    const RAISON_REFUS_ASSEMBLE = 'ASSEMBLE';
    const RAISON_REFUS_DEGRE_INSUFFISANT = 'DEGRE_INSUFFISANT';
    const RAISON_REFUS_MULTI_CEPAGE = 'MULTI_CEPAGE';
    const RAISON_REFUS_PRESSURAGE_EN_COURS = 'PRESSURAGE_EN_COURS';
    const RAISON_REFUS_VENDANGES_MECANIQUE = 'VENDANGES_MECANIQUE';
    const RAISON_REFUS_PROBLEME = 'PROBLEME';

    public static $raisons_refus_libelle = array(
        self::RAISON_REFUS_ANNULE => 'Rendez-vous annulé',
        self::RAISON_REFUS_DEGRE_INSUFFISANT => 'Degré insuffisant',
        self::RAISON_REFUS_MULTI_CEPAGE => 'Multi-cépages',
        self::RAISON_REFUS_PRESSURAGE_EN_COURS => 'Pressurage en cours',
        self::RAISON_REFUS_VENDANGES_MECANIQUE => 'Vendanges mécaniques',
        self::RAISON_REFUS_PROBLEME => 'Problème',
    );
    public static $contenants_libelle = array(
        self::CONTENANT_BOTTICHE => 'Bottiche',
        self::CONTENANT_BEINE => 'Benne',
        self::CONTENANT_CAGETTE => 'Cagette',
        self::CONTENANT_KG => 'Kg',
        self::CONTENANT_TYPE_PALOX => 'Type palox',
    );

    public static function getInstance() {
        return acCouchdbManager::getClient("Constats");
    }

    public function findByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, $campagne), $hydrate);
    }

    public function findConstatsByRendezvous(Rendezvous $rendezvous) {
        $campagne = substr($rendezvous->date, 0, 4);
        $constats = $this->findByIdentifiantAndCampagne($rendezvous->cvi, $campagne);
        $constatsResult = array();
        foreach ($constats->getOrAdd('constats') as $uniqId => $constat) {

            if (($constat->rendezvous_raisin == $rendezvous->_id) && $rendezvous->isRendezvousRaisin()) {
                $constatsResult[$constats->_id . '_' . $uniqId] = $constat;
            }
            if ((($constat->rendezvous_volume == $rendezvous->_id) || ($constat->rendezvous_raisin == $rendezvous->_id)) && $rendezvous->isRendezvousVolume()) {

                $constatsResult[$constats->_id . '_' . $uniqId] = $constat;
            }
        }
        return $constatsResult;
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

    public function getRaisonsRefusLibelle() {

        return self::$raisons_refus_libelle;
    }

    public function getProduits() {

        return ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
    }

}
