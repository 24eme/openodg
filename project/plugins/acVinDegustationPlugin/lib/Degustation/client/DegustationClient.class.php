<?php

class DegustationClient extends acCouchdbClient {
        
    const TYPE_MODEL = "Degustation"; 
    const TYPE_COUCHDB = "DEGUSTATION";

    const NOTE_TYPE_QUALITE_TECHNIQUE = "QUALITE_TECHNIQUE"; 
    const NOTE_TYPE_MATIERE = "MATIERE"; 
    const NOTE_TYPE_TYPICITE = "TYPICITE"; 
    const NOTE_TYPE_CONCENTRATION = "CONCENTRATION"; 
    const NOTE_TYPE_EQUILIBRE = "EQUILIBRE";
    
    const COURRIER_TYPE_OPE = "OPE" ;
    const COURRIER_TYPE_OK = "OK" ;
    const COURRIER_TYPE_VISITE = "VISITE" ;

    public static $types_courrier_libelle = array(
        self::COURRIER_TYPE_OPE => "OPE",
        self::COURRIER_TYPE_OK => "OK",
             self::COURRIER_TYPE_VISITE => "Visite"
    );
    
    
    public static $note_type_libelles = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => "Qualité technique",
        self::NOTE_TYPE_MATIERE => "Matière",
        /*self::NOTE_TYPE_TYPICITE => "Typicité",
        self::NOTE_TYPE_CONCENTRATION => "Concentration",
        self::NOTE_TYPE_EQUILIBRE => "Équilibre",*/
    );

    public static $note_type_defaults = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("Acescence","Acétate d'éthyl","Acétique","Acide","Acidité volatile","Aigre-doux","Alcooleux","Alliacé","Amande amère","Amer","Amylique","Apre","Asséchant","Astringent","Bactérien","Bock","Botrytis","Bouchonné","Bourbes","Brunissement","Butyrique","Caoutchouc","Cassé","Champignon","Ciment","Couleur altérée","Créosote","Croupi","Cuit","Cuivre","Décoloré","Désagréable","Déséquilibré","Douceureux","Ecurie","Eventé","Evolué","Fatigué","Fermentaire","Filant","Foxé","Gazeux","Géranium","Gouache","Goudron","Goût de bois sec","Goût de colle","Grêle","Grossier","H2S","Herbacé","Huileux","Hydrocarbures","Insuffisant","Iodé","Lactique","Levure","Lie","Logement","Lourd","Madérisé","Malpropre","Manque de finesse","Manque de fruit","Manque de structure","Mauvais boisé","Mauvais goût","Mauvaise odeur","Mercaptans","Métallique","Moisi","Mou","Oignon","Oxydé","Papier","Pas net","Pharmaceutique","Phéniqué","Phénolé","Piqué","Plastique","Plat","Plombé","Poivron","Pourri","Pourriture grise","Poussiéreux","Punaise","Putride","Rafle","Rance","Réduit","Résinique","Sale","Savonneux","Sec","Serpilière","Sirupeux","SO2","Solvant","Souris","Squelettique","Styrène","Taché","Tannique","Tartre sec","Terreux","Trop boisé","Trouble","Tuilé","Usé","Végétal","Vert","Vin non terminé"),
        self::NOTE_TYPE_MATIERE => array("Champignon","Court","Creux","Dilué","Insuffisant","Maigre","Manque de corps","Manque de fruit","Manque de matière","Manque de puissance","Manque de structure","Manque de typicité dans le cépage","Pourri","Pourriture grise","Végétal","Vert"),
        self::NOTE_TYPE_TYPICITE => array("Defaut 1"),
        self::NOTE_TYPE_CONCENTRATION => array("Defaut 1"),
        self::NOTE_TYPE_EQUILIBRE => array("Defaut 1"),
    );

    public static $note_type_notes = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("3" => "3 - Absence de défaut", "2" => "2 - Défaut minime", "1" => "1 - Défaut important", "0" => "0 - Retrait du bénéfice de l'AOC"),
        self::NOTE_TYPE_MATIERE => array("A" => "A - Remarquable", "B" => "B - Conforme", "C" => "C - Améliorations souhaitables", "D" => "D - Qualité insuffisante"),
        self::NOTE_TYPE_TYPICITE => array("Defaut 1"),
        self::NOTE_TYPE_CONCENTRATION => array("Defaut 1"),
        self::NOTE_TYPE_EQUILIBRE => array("Defaut 1"),
    );

    public static $ordre_cepages = array(
        'SY' => '01',
        'AU' => '02',
        'PB' => '03',
        'RI' => '04',
        'MU' => '05',
        'PG' => '06',
        'GW' => '07',
        'PN' => '08',
        'PR' => '09',
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

        return $this->startkey("DEGUSTATION-999999999-ZZZZZZZZZZ")
                    ->endkey("DEGUSTATION-00000000-AAAAAAAAA")
                    ->descending(true)
                    ->execute($hydrate);
    }

    public function getPrevious($degustation_id) {
        $degustations = $this->getDegustations();

        $finded = false;
        foreach($degustations as $row) {
            if($row->_id == $degustation_id) { $finded = true; continue; }

            if(!$finded) { continue; }

            return $this->find($row->_id);
        }

        return null;
    }
    
}
