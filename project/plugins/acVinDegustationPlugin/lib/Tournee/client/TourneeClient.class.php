<?php

class TourneeClient extends acCouchdbClient {

    const TYPE_MODEL = "Tournee";
    const TYPE_COUCHDB = "TOURNEE";
    const STATUT_ORGANISATION = 'ORGANISATION';
    const STATUT_TOURNEES = 'TOURNEES';
    const STATUT_AFFECTATION = 'AFFECTATION';
    const STATUT_DEGUSTATIONS = 'DEGUSTATIONS';
    const STATUT_COURRIERS = 'COURRIERS';
    const STATUT_TERMINE = 'TERMINE';
    const HEURE_NON_REPARTI = '99:99';
    const TYPE_TOURNEE_CONSTAT_VTSGN = "CONSTAT_VTSGN";
    const TYPE_TOURNEE_DEGUSTATION = "DEGUSTATION";

    public static function getInstance() {

        return acCouchdbManager::getClient("Tournee");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function createDoc($date) {
        $tournee = new Tournee();
        $tournee->date = $date;
        $tournee->type_tournee = self::TYPE_TOURNEE_DEGUSTATION;
        return $tournee;
    }

    public function findOrAddByDateAndAgent($date, $agent) {
        $tournee = $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, str_replace("-", "", $date), $agent->identifiant));
        if ($tournee) {
            return $tournee;
        }
        $tournee = $this->createDoc($date);
        $tournee->agent_unique = $agent->identifiant;
        $agentNode = $tournee->agents->add($agent->identifiant);
        $agentNode->nom = $agent->nom_a_afficher;
        $agentNode->email = $agent->email;
        $agentNode->adresse = $agent->adresse;
        $agentNode->commune = $agent->commune;
        $agentNode->code_postal = $agent->code_postal;
        $agentNode->lat = $agent->lat;
        $agentNode->lon = $agent->lon;
        $agentNode->dates = array();
        $tournee->type_tournee = self::TYPE_TOURNEE_CONSTAT_VTSGN;
        $tournee->constructId();
        $tournee->save();
        return $tournee;
    }

    public function buildTourneesJournee($date) {
        $tourneesJournee = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Tournee', $date);
        $tourneesObj = new stdClass();
        $tourneesObj->nbTotalRdvRaisin = 0;
        $tourneesObj->nbTotalRdvVolume = 0;
        $tourneesObj->tourneesJournee = array();
        $nbRendezvous = 0;
        $nbRendezvousRealise = 0;
        foreach ($tourneesJournee as $tourneeJournee) {
            $tournee = $tourneeJournee->value;
            if ($tournee->type_tournee != self::TYPE_TOURNEE_CONSTAT_VTSGN) {
                continue;
            }
            $tourneesObj->tourneesJournee[$tournee->appellation] = new stdClass();
            $tourneesObj->tourneesJournee[$tournee->appellation]->nbRdvRaisin = 0;
            $tourneesObj->tourneesJournee[$tournee->appellation]->nbRdvVolume = 0;
            $tourneesObj->tourneesJournee[$tournee->appellation]->agent = CompteClient::getInstance()->find('COMPTE-' . $tournee->agent_unique);
            $tourneeObj = TourneeClient::getInstance()->find('TOURNEE-' . $tournee->identifiant);

            $nbRealise = 0;
            foreach ($tourneeObj->degustations as $rendezvousId) {
                $rendezvous = RendezvousClient::getInstance()->find($rendezvousId);
                $nbRendezvous++;
                if ($rendezvous->isRendezvousRaisin()) {
                    $tourneesObj->tourneesJournee[$tournee->appellation]->nbRdvRaisin++;
                    $tourneesObj->nbTotalRdvRaisin++;
                }
                if ($rendezvous->isRendezvousVolume()) {
                    $tourneesObj->tourneesJournee[$tournee->appellation]->nbRdvVolume++;
                    $tourneesObj->nbTotalRdvVolume++;
                }
                if ($rendezvous->isRealise()) {
                    $nbRealise++;
                    $nbRendezvousRealise++;
                }
            }
            $tourneesObj->tourneesJournee[$tournee->appellation]->pourcentRealise = (!count($tourneeObj->degustations)) ? 0 : intval(($nbRealise / count($tourneeObj->degustations)) * 100);
            $tourneesObj->tourneesJournee[$tournee->appellation]->tournee = $tourneeObj;
        }

        $tourneesObj->pourcentTotalRealise = (!$nbRendezvous) ? "0" : intval(($nbRendezvousRealise / $nbRendezvous) * 100);

        return $tourneesObj;
    }

    public function getPrelevements($appellation, $date_from, $date_to) {

        return DRevPrelevementsView::getInstance()->getPrelevements($appellation, $date_from, $date_to);
    }

    public function getPrelevementsFiltered($appellation, $date_from, $date_to) {

        return $this->filterPrelevements($appellation, DRevPrelevementsView::getInstance()->getPrelevements($appellation, $date_from, $date_to));
    }

    public function getReportes($appellation) {
        $reportes = array();

        $degustations = DegustationClient::getInstance()->getDegustationsByAppellation($appellation);

        foreach ($degustations as $degustation) {
            if ($degustation->statut != DegustationClient::MOTIF_NON_PRELEVEMENT_REPORT) {

                continue;
            }

            $reportes[$degustation->identifiant] = $degustation;
        }

        return $reportes;
    }

    public function filterPrelevements($appellation, $prelevements) {
        $degustations = DegustationClient::getInstance()->getDegustationsByAppellation($appellation);

        $prelevements_filter = array();

        foreach ($prelevements as $key => $prelevement) {
            if (array_key_exists($prelevement->identifiant, $degustations)) {
                continue;
            }

            $prelevements_filter[$key] = $prelevement;
        }

        return $prelevements_filter;
    }

    public function getAgents($attribut = null) {

        $agents = CompteClient::getInstance()->getAllComptesPrefixed("A");
        $agents_result = array();

        foreach ($agents as $agent) {
            if ($agent->statut == CompteClient::STATUT_INACTIF) {
                continue;
            }

            if ($attribut && !isset($agent->infos->attributs->{$attribut})) {
                continue;
            }

            $agents_result[$agent->_id] = $agent;
        }

        return $agents_result;
    }

    public function getDegustateurs($attribut = null, $produit = null) {

        $degustateurs = CompteClient::getInstance()->getAllComptesPrefixed("D");
        $degustateurs_result = array();

        foreach ($degustateurs as $degustateur) {
            if ($degustateur->statut == CompteClient::STATUT_INACTIF) {
                continue;
            }

            if ($attribut && !isset($degustateur->infos->attributs->{$attribut})) {
                continue;
            }

            if ($produit && !isset($degustateur->infos->produits->{str_replace("/", "-", $produit)})) {
                continue;
            }

            $degustateurs_result[$degustateur->_id] = $degustateur;
        }

        return $degustateurs_result;
    }

    public function getTournees($hydrate = acCouchdbClient::HYDRATE_JSON) {

        return $this->startkey("TOURNEE-999999999-ZZZZZZZZZZ")
                        ->endkey("TOURNEE-00000000-AAAAAAAAA")
                        ->descending(true)
                        ->execute($hydrate);
    }

    public function getPrevious($tournee_id) {
        $tournees = $this->getTournees();

        $finded = false;
        foreach ($tournees as $row) {
            if ($row->_id == $tournee_id) {
                $finded = true;
                continue;
            }

            if (!$finded) {
                continue;
            }

            return $this->find($row->_id);
        }

        return null;
    }

}
