<?php

class RendezvousClient extends acCouchdbClient {

    const TYPE_COUCHDB = 'RENDEZVOUS';
    const RENDEZVOUS_TYPE_RAISIN = "TYPE_RAISINS";
    const RENDEZVOUS_TYPE_VOLUME = "TYPE_VOLUME";
    const RENDEZVOUS_STATUT_PRIS = "STATUT_PRIS";
    const RENDEZVOUS_STATUT_REALISE = "STATUT_REALISE";
    const RENDEZVOUS_STATUT_PLANIFIE = "STATUT_PLANIFIE";
    const RENDEZVOUS_STATUT_ANNULE = "STATUT_ANNULE";

    public static $rendezvous_statut_libelles = array(self::RENDEZVOUS_STATUT_PRIS => 'A planifier',
        self::RENDEZVOUS_STATUT_PLANIFIE => 'Planifié',
        self::RENDEZVOUS_STATUT_REALISE => 'Réalisé',
        self::RENDEZVOUS_STATUT_ANNULE => 'Annulé');

    public static function getInstance() {
        return acCouchdbManager::getClient("Rendezvous");
    }

    public function findByIdentifiantAndDateHeure($identifiant, $dateheure, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, $dateheure), $hydrate);
    }

    public function getRendezvousByCompte($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $ids = $this->startkey(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, "0000000000"))
                        ->endkey(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, "9999999999"))
                        ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        $rendezvous = array();

        foreach ($ids as $id) {
            $rendezvous[$id] = FactureClient::getInstance()->find($id, $hydrate);
        }

        krsort($rendezvous);

        return $rendezvous;
    }

    public function getRendezvousConstatsByCompte($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $rendezvousConstats = new stdClass();
        $rendezvousConstats->rendezvous = array();
        $rendezvousConstats->constats = array();
        $rendezvousByCompte = $this->getRendezvousByCompte($identifiant, $hydrate);
        foreach ($rendezvousByCompte as $keyRendezvous => $rendezvous) {
            $rendezvousConstats->rendezvous[$keyRendezvous] = $rendezvous;
            $rendezvousConstats->constats[$keyRendezvous] = new stdClass();
            $rendezvousConstats->constats[$keyRendezvous]->hasRealises = false;

            if ($rendezvous->statut == RendezvousClient::RENDEZVOUS_STATUT_REALISE) {
                $rendezvousConstats->constats[$keyRendezvous]->hasRealises = true;
                $rendezvousConstats->constats[$keyRendezvous]->constats = array();
                $rendezvousConstats->constats[$keyRendezvous]->a_finir = 0;
                $rendezvousConstats->constats[$keyRendezvous]->nb_approuves = 0;
                $rendezvousConstats->constats[$keyRendezvous]->nb_refuses = 0;
                $rendezvousConstats->constats[$keyRendezvous]->nb_nonconstate = 0;
                $constatsForDateRdv = ConstatsClient::getInstance()->findConstatsByRendezvous($rendezvous);

                foreach ($constatsForDateRdv as $constatRdvKey => $constat) {
                    if (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_APPROUVE)) {

                        $rendezvousConstats->constats[$keyRendezvous]->nb_approuves++;
                    } elseif (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_REFUSE)) {
                        $rendezvousConstats->constats[$keyRendezvous]->nb_refuses++;
                    } elseif (($constat->statut_raisin == ConstatsClient::STATUT_APPROUVE) && ($constat->statut_volume == ConstatsClient::STATUT_NONCONSTATE)) {
                        $rendezvousConstats->constats[$keyRendezvous]->a_finir++;
                    } elseif ($constat->statut_raisin == ConstatsClient::STATUT_REFUSE) {
                        $rendezvousConstats->constats[$keyRendezvous]->nb_refuses++;
                    } else {
                        $rendezvousConstats->constats[$keyRendezvous]->nb_nonconstate++;
                    }
                    $rendezvousConstats->constats[$keyRendezvous]->constats[$constatRdvKey] = $constat;
                }
            }
        }
        return $rendezvousConstats;
    }

    public function getRendezvousByNonPlanifiesNbDays($nb_days, $date) {
        $resultRdv = array();
        $rdvNonPlanifies = array();
        $dates = array($date);
        for ($i = 1; $i <= $nb_days; $i++) {
            $dates = array_merge($dates, array(Date::addDelaiToDate("-" . $i . " day", $date), Date::addDelaiToDate("+" . $i . " day", $date)));
        }
        foreach ($dates as $date) {
            $rdvNonPlanifies = array_merge($rdvNonPlanifies, $this->getRendezvousByDateAndStatut($date, self::RENDEZVOUS_STATUT_PRIS));
        }
        foreach ($rdvNonPlanifies as $key => $rdv) {
            $resultRdv[$rdv->date . $rdv->heure . $rdv->_id] = $rdv;
        }
        ksort($resultRdv);
        return $resultRdv;
    }

    public function getRendezvousByDateAndStatut($date, $statut) {
        $resultsDate = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Rendezvous', $date);
        $rdvs = array();
        foreach ($resultsDate as $item) {
            $rdv = $this->find($item->id, acCouchdbClient::HYDRATE_JSON);
            if ($rdv->statut != $statut) {
                continue;
            }
            $rdvs[$item->id] = $rdv;
        }

        return $rdvs;
    }

    public function findOrCreate($compte, $idChai, $date, $heure, $commentaire = "") {
        $rendezvous = $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $compte->identifiant, $idChai, str_replace("-", "", $date) . str_replace(":", "", $heure)));
        if ($rendezvous) {
            return $rendezvous;
        }

        $compte->updateCoordonneesLongLat();

        $rendezvous = new Rendezvous();
        $rendezvous->identifiant = $compte->identifiant;
        $rendezvous->cvi = $compte->cvi;

        $rendezvous->email = $compte->email;
        $rendezvous->date = $date;
        $rendezvous->idchai = $idChai;
        $rendezvous->raison_sociale = $compte->raison_sociale;
        $rendezvous->lat = $compte->lat;
        $rendezvous->lon = $compte->lon;
        $rendezvous->adresse = $compte->chais->get($idChai)->adresse;
        $rendezvous->commune = $compte->chais->get($idChai)->commune;
        $rendezvous->code_postal = $compte->chais->get($idChai)->code_postal;
        $rendezvous->telephone_prive = $compte->telephone_prive;
        $rendezvous->telephone_bureau = $compte->telephone_bureau;
        $rendezvous->telephone_mobile = $compte->telephone_mobile;
        $rendezvous->date = $date;
        $rendezvous->heure = $heure;
        $rendezvous->commentaire = $commentaire;
        $rendezvous->type_rendezvous = self::RENDEZVOUS_TYPE_RAISIN;
        $rendezvous->statut = self::RENDEZVOUS_STATUT_PRIS;

        $rendezvous->constructId();
        return $rendezvous;
    }

    public function findRendezvousVolumeFromIdRendezvous($idRdvOrigine) {
        $rdvOrigine = $this->find($idRdvOrigine);
        ;
        $this->findByIdentifiantAndDateHeure($rdvOrigine->cvi, self::getNextDate($rdvOrigine->date));
    }

    public function findOrCreateRendezvousVolumeFromIdRendezvous($idRdvOrigine, $nom_agent_origine = "") {
        $rdvOrigine = $this->find($idRdvOrigine);
        $rendezvous = clone $rdvOrigine;
        $rendezvous->date = self::getNextDate($rdvOrigine->date);
        $rendezvous->nom_agent_origine = $nom_agent_origine;
        $rendezvous->type_rendezvous = self::RENDEZVOUS_TYPE_VOLUME;
        $rendezvous->constructId();
        $rendezvousExistant = $this->getRendezVousVolume($rendezvous->_id);
        if ($rendezvousExistant) {
            $rendezvous = $rendezvousExistant;
        }
        $rendezvous->statut = self::RENDEZVOUS_STATUT_PRIS;
        $rendezvous->rendezvous_raisin = $idRdvOrigine;

        return $rendezvous;
    }

    public function incrementId($id) {
        preg_match("/^RENDEZVOUS-[0-9]+-([0-9]+)$/", $id, $matches);

        $numero = $matches[1]*1 + 1;

        return preg_replace("/^(RENDEZVOUS-[0-9]+-)([0-9]+)$/", '${1}'.$numero , $id);
    }

    public function getRendezVousVolume($id) {
        for($i = 0; $i <= 8; $i++) {
            $rdv = $this->find($id);

            if(!$rdv) {
                continue;
            }

            if($rdv && $rdv->type_rendezvous == self::RENDEZVOUS_TYPE_VOLUME) {

                return $rdv;
            }

            $id = $this->incrementId($id);
        }

        return null;
    }

    public function buildOrganisationNbDays($nb_days = 0, $dateToday) {
        if (!$dateToday) {
            $dateToday = date('Y-m-d');
        }
        $organisationsJournee = array();
        $dates = array($dateToday);
        for ($i = 1; $i <= $nb_days; $i++) {
            $dates = array_merge($dates, array(Date::addDelaiToDate("-" . $i . " day", $dateToday), Date::addDelaiToDate("+" . $i . " day", $dateToday)));
        }
        foreach ($dates as $date) {
            $organisationsJournee[$date] = $this->buildRendezvousJournee($date);
        }
        ksort($organisationsJournee);
        return $organisationsJournee;
    }

    public function buildRendezvousJournee($date) {
        $rendezvousJournee = array();
        $resultsDate = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Rendezvous', $date);
        foreach ($resultsDate as $resultDate) {
            if (!array_key_exists($resultDate->value->statut, $rendezvousJournee)) {
                $rendezvousJournee[$resultDate->value->statut] = array();
            }
            $rendezvousJournee[$resultDate->value->statut][$resultDate->id] = $resultDate;
        }

        return $rendezvousJournee;
    }

    public static function getNextDate($date) {
        $delai = 1;
        if (date('N', strtotime($date)) > 5) {
            $delai++;
        }
        $returnDate = Date::addDelaiToDate("+" . $delai . " day", $date);
        if ((date('md', strtotime($returnDate)) == '0111') || (date('md', strtotime($returnDate)) == '1111')) {
            $returnDate = Date::addDelaiToDate("+1 day", $returnDate);
        }
        return $returnDate;
    }

    public static function getPreviousDate($date) {
        $delai = 1;
        if (date('N', strtotime($date)) < 2) {
            $delai++;
        }
        $returnDate = Date::addDelaiToDate("-" . $delai . " day", $date);
        if ((date('md', strtotime($returnDate)) == '0111') || (date('md', strtotime($returnDate)) == '1111')) {
            $returnDate = Date::addDelaiToDate("-1 day", $returnDate);
        }
        return $returnDate;
    }

    public static function isDateToByPass($date) {
        return date('Ymd', strtotime($date)) == '20161031' ||date('md', strtotime($date)) == '1101' || date('md', strtotime($date)) == '1111' || date('N', strtotime($date)) == 7;
    }

}
