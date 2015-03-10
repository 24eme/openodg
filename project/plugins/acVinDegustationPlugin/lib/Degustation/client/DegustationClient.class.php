<?php

class DegustationClient extends acCouchdbClient {
        
    const TYPE_MODEL = "Degustation"; 
    const TYPE_COUCHDB = "DEGUSTATION";

    const NOTE_TYPE_QUALITE_TECHNIQUE = "QUALITE_TECHNIQUE"; 
    const NOTE_TYPE_MATIERE = "MATIERE"; 
    const NOTE_TYPE_TYPICITE = "TYPICITE"; 
    const NOTE_TYPE_CONCENTRATION = "CONCENTRATION"; 
    const NOTE_TYPE_EQUILIBRE = "EQUILIBRE";

    public static $note_type_libelles = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => "Qualité technique",
        self::NOTE_TYPE_MATIERE => "Matière",
        self::NOTE_TYPE_TYPICITE => "Typicité",
        self::NOTE_TYPE_CONCENTRATION => "Concentration",
        self::NOTE_TYPE_EQUILIBRE => "Équilibre",
    );

    public static $note_type_defaults = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("Defaut 1"),
        self::NOTE_TYPE_MATIERE => array("Defaut 1"),
        self::NOTE_TYPE_TYPICITE => array("Defaut 1"),
        self::NOTE_TYPE_CONCENTRATION => array("Defaut 1"),
        self::NOTE_TYPE_EQUILIBRE => array("Defaut 1"),
    );
    
    public static function getInstance()
    {
        
        return acCouchdbManager::getClient("Degustation");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }


    public function createDoc($date) {
        $degustation = new Degustation();
        $degustation->date = $date;

        return $degustation;
    }

    public function getPrelevements($date_from, $date_to) {
        
        return DRevPrelevementsView::getInstance()->getPrelevements($date_from, $date_to);
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

    public function getDegustations($hydrate = acCouchdbClient::HYDRATE_JSON) {

        return $this->startkey("DEGUSTATION-00000000-AAAAAAAAA")
                    ->endkey("DEGUSTATION-999999999-ZZZZZZZZZZ")
                    ->execute($hydrate);
    }
    
}
