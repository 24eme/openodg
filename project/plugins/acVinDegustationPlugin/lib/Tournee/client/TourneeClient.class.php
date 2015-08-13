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
    
    public static function getInstance()
    {
        
        return acCouchdbManager::getClient("Tournee");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }


    public function createDoc($date) {
        $tournee = new Tournee();
        $tournee->date = $date;

        return $tournee;
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

        foreach($degustations as $degustation)  {
            if($degustation->statut != DegustationClient::MOTIF_NON_PRELEVEMENT_REPORT) {

                continue;
            }

            $reportes[$degustation->identifiant] = $degustation; 
        }

        return $reportes;
    }

    public function filterPrelevements($appellation, $prelevements) {
        $degustations = DegustationClient::getInstance()->getDegustationsByAppellation($appellation);

        $prelevements_filter = array();

        foreach($prelevements as $key => $prelevement) {
            if(array_key_exists($prelevement->identifiant, $degustations)) {
                continue;
            }

            $prelevements_filter[$key] = $prelevement;
        }

        return $prelevements_filter;
    }

    public function getAgents($attribut = null) {

        $agents = CompteClient::getInstance()->getAllComptesPrefixed("A");
        $agents_result = array();

        foreach($agents as $agent) {
            if($agent->statut == CompteClient::STATUT_INACTIF) {
                continue;
            }

            if($attribut && !isset($agent->infos->attributs->{$attribut})) {
                continue;
            }
            
            $agents_result[$agent->_id] = $agent;
        }

        return $agents_result;
    }

    public function getDegustateurs($attribut = null, $produit = null) {

        $degustateurs = CompteClient::getInstance()->getAllComptesPrefixed("D");
        $degustateurs_result = array();

        foreach($degustateurs as $degustateur) {
            if($degustateur->statut == CompteClient::STATUT_INACTIF) {
                continue;
            }

            if($attribut && !isset($degustateur->infos->attributs->{$attribut})) {
                continue;
            }

            if($produit && !isset($degustateur->infos->produits->{str_replace("/", "-", $produit)})) {
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
        foreach($tournees as $row) {
            if($row->_id == $tournee_id) { $finded = true; continue; }

            if(!$finded) { continue; }

            return $this->find($row->_id);
        }

        return null;
    }
    
}
