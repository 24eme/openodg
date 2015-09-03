<?php

class RendezvousClient extends acCouchdbClient {

    const TYPE_COUCHDB = 'RENDEZVOUS';
    const RENDEZVOUS_TYPE_RAISIN = "TYPE_RAISINS";
    const RENDEZVOUS_TYPE_VOLUME = "TYPE_VOLUME";
    const RENDEZVOUS_STATUT_PRIS = "STATUT_PRIS";
    const RENDEZVOUS_STATUT_REALISE = "STATUT_REALISE";
    const RENDEZVOUS_STATUT_PLANIFIE = "STATUT_PLANIFIE";

    public static $rendezvous_statut_libelles = array(self::RENDEZVOUS_STATUT_PRIS => 'A planifier',
        self::RENDEZVOUS_STATUT_PLANIFIE => 'Planifié',
        self::RENDEZVOUS_STATUT_REALISE => 'Réalisé');

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

    public function findOrCreate($compte, $idChai, $date, $heure, $commentaire = "") {
        $rendezvous = $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $compte->identifiant, $idChai, str_replace("-", "", $date) . str_replace(":", "", $heure)));
        if ($rendezvous) {
            return $rendezvous;
        }



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
        $rendezvous->date = $date;
        $rendezvous->heure = $heure;
        $rendezvous->commentaire = $commentaire;
        $rendezvous->type_rendezvous = self::RENDEZVOUS_TYPE_RAISIN;
        $rendezvous->statut = self::RENDEZVOUS_STATUT_PRIS;

        $rendezvous->constructId();
        return $rendezvous;
    }

    public function buildOrganisationNbDays($nb_days = 0, $dateToday) {
        if (!$dateToday) {
            $dateToday = date('Y-m-d');
        }
        $organisationsJournee = array();
        $dates = array(Date::addDelaiToDate("-2 day", $dateToday));
        for ($i = 1; $i <= $nb_days; $i++) {
            $dates = array_merge($dates, array(Date::addDelaiToDate("-" . $i . " day", $dateToday), Date::addDelaiToDate("+" . $i . " day", $dateToday)));
        }
        foreach ($dates as $date) {
            $organisationsJournee[$date] = $this->buildOrganisationJournee($date);            
        }
        return $organisationsJournee;
    }

    public function buildOrganisationJournee($date) {
        $organisationJournee = array();
        $resultsDate = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Rendezvous', $date);
        foreach ($resultsDate as $resultDate) {
            if (!array_key_exists($resultDate->value->statut, $organisationJournee)) {
                $organisationJournee[$resultDate->value->statut] = array();
            }
            $organisationJournee[$resultDate->value->statut][$resultDate->id] = $resultDate;
        }

        return $organisationJournee;
    }

}
