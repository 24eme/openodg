<?php

class DegustationClient extends acCouchdbClient {
    
    const TYPE_MODEL = "Degustation"; 
    const TYPE_COUCHDB = "DEGUSTATION";

    const NOTE_TYPE_QUALITE_TECHNIQUE = "QUALITE_TECHNIQUE"; 
    const NOTE_TYPE_MATIERE = "MATIERE"; 
    const NOTE_TYPE_TYPICITE = "TYPICITE"; 
    const NOTE_TYPE_CONCENTRATION = "CONCENTRATION"; 
    const NOTE_TYPE_EQUILIBRE = "EQUILIBRE";

    const MOTIF_NON_PRELEVEMENT_REPORT = "REPORT";
    const MOTIF_NON_PRELEVEMENT_PLUS_DE_VIN = "PLUS_DE_VIN";
    const MOTIF_NON_PRELEVEMENT_SOUCIS = "SOUCIS";

    const COURRIER_TYPE_OPE = "OPE" ;
    const COURRIER_TYPE_OK = "OK" ;
    const COURRIER_TYPE_VISITE = "VISITE" ;

    public static $note_type_libelles = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => "Qualité technique",
        self::NOTE_TYPE_MATIERE => "Matière",
        /*self::NOTE_TYPE_TYPICITE => "Typicité",
        self::NOTE_TYPE_CONCENTRATION => "Concentration",
        self::NOTE_TYPE_EQUILIBRE => "Équilibre",*/
    );

    public static $note_type_libelles_help = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => "Maîtrise de la vinification",
        self::NOTE_TYPE_MATIERE => null,
    );

    public static $motif_non_prelevement_libelles = array(
        self::MOTIF_NON_PRELEVEMENT_REPORT => "Report",
        self::MOTIF_NON_PRELEVEMENT_PLUS_DE_VIN => "Plus de vin",
        self::MOTIF_NON_PRELEVEMENT_SOUCIS => "Soucis",
    );

    public static $note_type_defauts = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("Acescence", "Acétate d'éthyl","Acétique","Acide","Acidité volatile","Aigre-doux","Alcooleux","Alliacé","Amande amère","Amer","Amylique","Apre","Asséchant","Astringent","Bactérien","Bock","Botrytis","Bouchonné","Bourbes","Brunissement","Butyrique","Caoutchouc","Cassé","Champignon","Ciment","Couleur altérée","Créosote","Croupi","Cuit","Cuivre","Décoloré","Désagréable","Déséquilibré","Douceureux","Ecurie","Eventé","Evolué","Fatigué","Fermentaire","Filant","Foxé","Gazeux","Géranium","Gouache","Goudron","Goût de bois sec","Goût de colle","Grêle","Grossier","H2S","Herbacé","Huileux","Hydrocarbures","Insuffisant","Iodé","Lactique","Levure","Lie","Logement","Lourd","Madérisé","Malpropre","Manque de finesse","Manque de fruit","Manque de structure","Mauvais boisé","Mauvais goût","Mauvaise odeur","Mercaptans","Métallique","Moisi","Mou","Oignon","Oxydé","Papier","Pas net","Pharmaceutique","Phéniqué","Phénolé","Piqué","Plastique","Plat","Plombé","Poivron","Pourri","Pourriture grise","Poussiéreux","Punaise","Putride","Rafle","Rance","Réduit","Résinique","Sale","Savonneux","Sec","Serpilière","Sirupeux","SO2","Solvant","Souris","Squelettique","Styrène","Taché","Tannique","Tartre sec","Terreux","Trop boisé","Trouble","Tuilé","Usé","Végétal","Vert","Vin non terminé"),
        self::NOTE_TYPE_MATIERE => array("Champignon","Court","Creux","Dilué","Insuffisant","Maigre","Manque de corps","Manque de fruit","Manque de matière","Manque de puissance","Manque de structure","Manque de typicité dans le cépage","Pourri","Pourriture grise","Végétal","Vert", "Acide", "Lourd", "Moisi", "Mou", "Poivron", "Poussiéreux", "Herbacé"),
        self::NOTE_TYPE_TYPICITE => array(),
        self::NOTE_TYPE_CONCENTRATION => array(),
        self::NOTE_TYPE_EQUILIBRE => array(),
    );

    public static $note_type_notes = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("3" => "3 - Absence de défaut", "2" => "2 - Défaut minime", "1" => "1 - Défaut important", "0" => "0 - Retrait du bénéfice de l'AOC"),
        self::NOTE_TYPE_MATIERE => array("A" => "A - Remarquable", "B" => "B - Conforme", "C" => "C - Améliorations souhaitables", "D" => "D - Qualité insuffisante"),
        self::NOTE_TYPE_TYPICITE => array("Defaut 1"),
        self::NOTE_TYPE_CONCENTRATION => array("Defaut 1"),
        self::NOTE_TYPE_EQUILIBRE => array("Defaut 1"),
    );

    public static $types_courrier_libelle = array(
        self::COURRIER_TYPE_OPE => "OPE",
        self::COURRIER_TYPE_OK => "OK",
             self::COURRIER_TYPE_VISITE => "Visite"
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

    public function findOrCreate($identifiant, $date, $appellation) {
        $degustation = $this->find(sprintf("%s-%s-%s-%s", self::TYPE_COUCHDB, $identifiant, str_replace("-", "", $date), $appellation));
        if($degustation) {

            return $degustation;
        }

        $degustation = new Degustation();
        $degustation->identifiant = $identifiant;
        $degustation->date_degustation = $date;
        $degustation->appellation = $appellation;

        return $degustation;
    }

    public function getDegustationsByDRev($drev_id) {
        
    }

    public function getDegustationsByIdentifiant($cvi) {

        return $this->startkey(sprintf("DEGUSTATION-%s-%s-%s", $identifiant, "00000000", "ALSACE"))
                    ->endkey(sprintf("DEGUSTATION-%s-%s-%s", $identifiant, "99999999", "ALSACE"))
                    ->execute($hydrate);
    }

    public function getDoc($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        
    }

    public static function sortOperateursByDatePrelevement($operateur_a, $operateur_b) {

        return $operateur_a->date_demande > $operateur_b->date_demande;
    }

}
