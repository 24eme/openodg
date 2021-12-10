<?php

class TourneeClient extends acCouchdbClient {

    const TYPE_MODEL = "Tournee";
    const TYPE_COUCHDB = "TOURNEE";
    const STATUT_ORGANISATION = 'ORGANISATION';
    const STATUT_SAISIE = 'SAISIE';
    const STATUT_TOURNEES = 'TOURNEES';
    const STATUT_AFFECTATION = 'AFFECTATION';
    const STATUT_DEGUSTATIONS = 'DEGUSTATIONS';
    const STATUT_COURRIERS = 'COURRIERS';
    const STATUT_TERMINE = 'TERMINE';
    const HEURE_NON_REPARTI = '99:99';
    const TYPE_TOURNEE_CONSTAT_VTSGN = "CONSTAT_VTSGN";
    const TYPE_TOURNEE_DEGUSTATION = "DEGUSTATION";

    public static $statutsLibelle = array(
        self::STATUT_SAISIE => "Saisie",
        self::STATUT_ORGANISATION => "Organisation",
        self::STATUT_TOURNEES => "Tournée",
        self::STATUT_AFFECTATION => "Affectation",
        self::STATUT_DEGUSTATIONS => "Dégustation",
        self::STATUT_COURRIERS => "Courriers à envoyer",
        self::STATUT_TERMINE => "Terminée",
    );

    public static $couleursStatut = array(
        TourneeClient::STATUT_SAISIE => "default-step",
        TourneeClient::STATUT_ORGANISATION => "default-step",
        TourneeClient::STATUT_TOURNEES => "info",
        TourneeClient::STATUT_AFFECTATION => "warning",
        TourneeClient::STATUT_DEGUSTATIONS => "danger",
        TourneeClient::STATUT_COURRIERS => "success",
        TourneeClient::STATUT_TERMINE => "default",
    );

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

    public function createDoc($type, $date) {
        $tournee = new Tournee();
        $tournee->type_tournee = $type;
        $tournee->date = $date;

        return $tournee;
    }

    public function createOrFindForDegustation($appellation, $date, $date_debut_prelevement, $gap_fin_prelevement = 5) {
        $tournee = $this->createDoc(self::TYPE_TOURNEE_DEGUSTATION, $date);
        $tournee->appellation = $appellation;
        $tournee->statut = TourneeClient::STATUT_ORGANISATION;
        $tournee->millesime = ((int) substr($tournee->date, 0, 4) - 1)."";
        $tournee->date_prelevement_debut = $date_debut_prelevement;
        $tournee->date_prelevement_fin = (new DateTime($tournee->date))->modify("-".$gap_fin_prelevement." days")->format('Y-m-d');
        $tournee->organisme = DegustationClient::ORGANISME_DEFAUT;
        $tournee->getLibelle();

        return $tournee;
    }

    public function createOrFindForSaisieDegustation($appellation, $date) {
        $tournee = $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, str_replace("-", "", $date), $appellation));

        if($tournee) {

            return $tournee;
        }

        $tournee = new Tournee();
        $tournee->appellation = $appellation;
        $tournee->date = $date;
        $tournee->nombre_commissions = 1;
        $tournee->type_tournee = self::TYPE_TOURNEE_DEGUSTATION;
        $tournee->statut = TourneeClient::STATUT_SAISIE;
        $tournee->organisme = "Gestion locale";

        return $tournee;
    }

    public function findTourneeDegustationByDateAndAgent($date, $agent) {
        foreach($this->getTourneesByType(TourneeClient::TYPE_TOURNEE_DEGUSTATION) as $tournee) {
            if(isset($tournee->agents->{$agent->_id}) && in_array($date, $tournee->agents->{$agent->_id}->dates)) {

                return $this->find($tournee->_id);
            }
        }

        return null;
    }

    public function findByDateAndAgent($date, $agent) {
        return $this->find(sprintf("%s-%s-%s", self::TYPE_COUCHDB, str_replace("-", "", $date), $agent->identifiant));
    }

    public function findOrAddByDateAndAgent($date, $agent) {
        $tournee = $this->findByDateAndAgent($date, $agent);
        if ($tournee) {
            return $tournee;
        }
        $tournee = $this->createDoc(self::TYPE_TOURNEE_CONSTAT_VTSGN, $date);
        $tournee->agent_unique = $agent->identifiant;
        $agentNode = $tournee->agents->add($agent->identifiant);
        $agentNode->nom = sprintf("%s %s.", $agent->prenom, substr($agent->nom, 0, 1));
        $agentNode->email = $agent->email;
        $agentNode->adresse = $agent->adresse;
        $agentNode->commune = $agent->commune;
        $agentNode->code_postal = $agent->code_postal;
        $agentNode->lat = $agent->lat;
        $agentNode->lon = $agent->lon;
        $agentNode->dates = array();
        $tournee->constructId();
        $tournee->save();
        return $tournee;
    }

    public function getTourneesByDateAndType($date, $type) {
        $tournees = array();
        $tourneesRows = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Tournee', $date);

        foreach ($tourneesRows as $tourneeRow) {
            $tourneeJson = TourneeClient::getInstance()->find($tourneeRow->id, acCouchdbClient::HYDRATE_JSON);
            if ($tourneeJson->type_tournee != $type) {
                continue;
            }
            $tournees[$tourneeRow->id] = TourneeClient::getInstance()->find($tourneeRow->id);
        }

        return $tournees;
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
            $tourneesObj->tourneesJournee[$tournee->agent_unique] = new stdClass();
            $tourneesObj->tourneesJournee[$tournee->agent_unique]->nbRdvRaisin = 0;
            $tourneesObj->tourneesJournee[$tournee->agent_unique]->nbRdvVolume = 0;
            $tourneesObj->tourneesJournee[$tournee->agent_unique]->agent = CompteClient::getInstance()->find('COMPTE-' . $tournee->agent_unique);
            $tourneeObj = TourneeClient::getInstance()->find('TOURNEE-' . $tournee->identifiant);

            $nbRealise = 0;
            foreach ($tourneeObj->rendezvous as $rendezvousId => $rdv) {
                $rendezvous = RendezvousClient::getInstance()->find($rendezvousId);
                $nbRendezvous++;
                if ($rendezvous && $rendezvous->isRendezvousRaisin()) {
                    $tourneesObj->tourneesJournee[$tournee->agent_unique]->nbRdvRaisin++;
                    $tourneesObj->nbTotalRdvRaisin++;
                }
                if ($rendezvous && $rendezvous->isRendezvousVolume()) {
                    $tourneesObj->tourneesJournee[$tournee->agent_unique]->nbRdvVolume++;
                    $tourneesObj->nbTotalRdvVolume++;
                }
                if ($rendezvous && $rendezvous->isRealise()) {
                    $nbRealise++;
                    $nbRendezvousRealise++;
                }
            }
            $tourneesObj->tourneesJournee[$tournee->agent_unique]->pourcentRealise = (!count($tourneeObj->rendezvous)) ? 0 : intval(($nbRealise / count($tourneeObj->rendezvous)) * 100);
            $tourneesObj->tourneesJournee[$tournee->agent_unique]->tournee = $tourneeObj;
        }

        $tourneesObj->pourcentTotalRealise = (!$nbRendezvous) ? "0" : intval(($nbRendezvousRealise / $nbRendezvous) * 100);

        return $tourneesObj;
    }

    public function getPrelevements($appellation, $date_from, $date_to) {

        return DRevPrelevementsView::getInstance()->getPrelevements($appellation, $date_from, $date_to);
    }

    public function getPrelevementsFiltered($appellation, $date_from, $date_to, $campagne = null) {
        $prelevements = [];

        $prelevements = array_merge($prelevements, $this->filterPrelevements(
            $appellation,
            DRevPrelevementsView::getInstance()
                ->getPrelevements(1, $appellation, null, null, $campagne),
            $campagne
        ));

        $prelevements = array_merge($prelevements, $this->filterPrelevements(
            $appellation,
            DRevPrelevementsView::getInstance()
                ->getPrelevements(0, $appellation, $date_from, $date_to, $campagne),
            $campagne
        ));

        return $prelevements;
    }

    public function getReportes($appellation, $campagne = null) {
        $reportes = array();

        $degustations = DegustationClient::getInstance()->getDegustationsByAppellation($appellation, $campagne);

        foreach ($degustations as $degustation) {
            if ($degustation->statut != DegustationClient::MOTIF_NON_PRELEVEMENT_REPORT) {

                continue;
            }

            $reportes[$degustation->identifiant] = $degustation;
        }

        return $reportes;
    }

    public function filterPrelevements($appellation, $prelevements, $campagne = null) {
        $degustations = DegustationClient::getInstance()->getDegustationsByAppellation($appellation, $campagne);

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

    public function getTourneesByType($type, $hydrate = acCouchdbClient::HYDRATE_JSON) {

        $resultats = $this->startkey("TOURNEE-999999999-ZZZZZZZZZZ")
                        ->endkey("TOURNEE-00000000-AAAAAAAAA")
                        ->descending(true)
                        ->execute($hydrate);

        $tournees = array();
        foreach($resultats as $tournee) {
            if($tournee->type_tournee != $type) {
                continue;
            }

            $tournees[$tournee->_id] = $tournee;
        }

        return $tournees;
    }

    public function findTourneeByIdRendezvous($idRendezvous) {
        $rendezvous = RendezvousClient::getInstance()->find($idRendezvous);
        $resultsDate = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Tournee', $rendezvous->getDate());
        foreach ($resultsDate as $tournee) {
            foreach ($tournee->value->rendezvous as $rendevousId => $rdv) {
                if ($rendevousId == $idRendezvous) {
                    return $this->find($tournee->id);
                }
            }
        }
        return null;
    }

}
