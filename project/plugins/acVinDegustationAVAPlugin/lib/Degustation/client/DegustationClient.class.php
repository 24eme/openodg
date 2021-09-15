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
    const MOTIF_NON_PRELEVEMENT_DECLASSEMENT = "DECLASSEMENT";
    const MOTIF_NON_PRELEVEMENT_SOUCIS = "SOUCIS";

    const COURRIER_TYPE_OPE = "OPE" ;
    const COURRIER_TYPE_OK = "OK" ;
    const COURRIER_TYPE_VISITE = "VISITE" ;

    const ORGANISME_DEFAUT = "ODG-AVA";

    public static $note_type_libelles = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => "Qualité technique",
        self::NOTE_TYPE_MATIERE => "Matière",
        self::NOTE_TYPE_TYPICITE => "Typicité",
        self::NOTE_TYPE_CONCENTRATION => "Concentration",
        self::NOTE_TYPE_EQUILIBRE => "Équilibre",
    );

    public static $appellations = array(
        "ALSACE" => "AOC Alsace",
        "CREMANT" => "AOC Crémant d'Alsace",
        "VTSGN" => "VT / SGN",
        "GRDCRU" => "AOC Alsace Grand Cru",
    );

    public static $note_type_by_appellation = array(
        'ALSACE' => array(self::NOTE_TYPE_QUALITE_TECHNIQUE, self::NOTE_TYPE_MATIERE),
        'CREMANT' => array(self::NOTE_TYPE_MATIERE),
        'VTSGN'=> array(self::NOTE_TYPE_QUALITE_TECHNIQUE, self::NOTE_TYPE_CONCENTRATION, self::NOTE_TYPE_EQUILIBRE),
        'GRDCRU' => array(self::NOTE_TYPE_QUALITE_TECHNIQUE, self::NOTE_TYPE_MATIERE, self::NOTE_TYPE_TYPICITE),
    );

    public static $note_type_libelles_help = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => "Maîtrise de la vinification",
        self::NOTE_TYPE_MATIERE => null,
    );

    public static $motif_non_prelevement_libelles = array(
        self::MOTIF_NON_PRELEVEMENT_REPORT => "Report",
        self::MOTIF_NON_PRELEVEMENT_PLUS_DE_VIN => "Plus de vin",
        self::MOTIF_NON_PRELEVEMENT_DECLASSEMENT => "Déclassement",
        self::MOTIF_NON_PRELEVEMENT_SOUCIS => "Soucis",
    );

    public static $note_type_defauts = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("Acescence", "Acétate d'éthyl","Acétique","Acide","Acidité volatile","Aigre-doux","Alcooleux","Alliacé","Amande amère","Amer","Amylique","Apre","Asséchant","Astringent","Bactérien","Bock","Botrytis","Bouchonné","Bourbes","Brunissement","Butyrique","Caoutchouc","Cassé","Champignon","Ciment","Couleur altérée","Créosote","Croupi","Cuit","Cuivre","Décoloré","Désagréable","Déséquilibré","Douceureux","Ecurie","Eventé","Evolué","Fatigué","Fermentaire","Filant","Foxé","Gazeux","Géranium","Gouache","Goudron","Goût de bois sec","Goût de colle","Grêle","Grossier","H2S","Herbacé","Huileux","Hydrocarbures","Insuffisant","Iodé","Lactique","Levure","Lie","Logement","Lourd","Madérisé","Malpropre","Manque de finesse","Manque de fruit","Manque de structure", "Marc","Mauvais boisé","Mauvais goût","Mauvaise odeur","Mercaptans","Métallique","Moisi","Mou","Oignon","Oxydé","Papier","Pas net","Pharmaceutique","Phéniqué","Phénolé","Piqué","Plastique","Plat","Plombé","Poivron","Pourri","Pourriture grise","Poussiéreux","Punaise","Putride","Rafle","Rance","Réduit","Résinique","Sale","Savonneux","Sec","Serpilière","Sirupeux","SO2","Solvant","Souris","Squelettique","Styrène","Taché","Tannique","Tartre sec","Terreux","Trop boisé","Trouble","Tuilé","Usé","Végétal","Vert","Vin non terminé", "Fermentation en cours"),
        self::NOTE_TYPE_MATIERE => array("Champignon","Court","Creux","Dilué","Insuffisant","Maigre","Manque de corps","Manque de fruit","Manque de matière","Manque de puissance","Manque de structure","Manque de typicité dans le cépage","Pourri","Pourriture grise","Végétal","Vert", "Acide", "Lourd", "Moisi", "Mou", "Poivron", "Poussiéreux", "Herbacé"),
        self::NOTE_TYPE_TYPICITE => array(),
        self::NOTE_TYPE_CONCENTRATION => array("Court","Creux","Dilué","Insuffisant","Maigre","Manque de corps","Manque de fruit","Manque de matière","Manque de puissance","Manque de structure"),
        self::NOTE_TYPE_EQUILIBRE => array("Aigre-doux","Alcooleux","Désagréable","Déséquilibré","Douceureux","Lourd"),
    );

    public static $note_type_notes = array(
        self::NOTE_TYPE_QUALITE_TECHNIQUE => array("3" => "3 - Absence de défaut", "2" => "2 - Défaut minime", "1" => "1 - Défaut important", "0" => "0 - Retrait du bénéfice de l'AOC"),
        self::NOTE_TYPE_MATIERE => array("A" => "A - Remarquable", "B" => "B - Conforme", "C" => "C - Améliorations souhaitables", "D" => "D - Qualité insuffisante"),
        self::NOTE_TYPE_TYPICITE => array("A" => "A - Remarquable", "B" => "B - Conforme", "C" => "C - Améliorations souhaitables", "D" => "D - Qualité insuffisante"),
        self::NOTE_TYPE_CONCENTRATION => array("A" => "A - Remarquable", "B" => "B - Conforme", "C" => "C - Améliorations souhaitables", "D" => "D - Qualité insuffisante"),
        self::NOTE_TYPE_EQUILIBRE => array("A" => "A - Remarquable", "B" => "B - Conforme", "C" => "C - Améliorations souhaitables", "D" => "D - Qualité insuffisante"),
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

    public function create($data, $force_return_ls = false) {
        if (!isset($data->type)) {

            throw new acCouchdbException('Property "type" ($data->type)');
        }
        if (!class_exists($data->type)) {

            throw new acCouchdbException('Class "' . $data->type . '" not found');
        }

        $doc = new $data->type();
        $doc->loadFromCouchdb($data);

        if($doc->getType() == "LS" && $force_return_ls == false )
          return $this->find($doc->getPointeur());

        return $doc;
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findOrCreateByTournee(Tournee $tournee, $identifiant) {
        $id = sprintf("%s-%s-%s-%s", self::TYPE_COUCHDB, $identifiant, str_replace("-", "", $tournee->date), $tournee->appellation);

        if($tournee->appellation_complement) {
            $id .= $tournee->appellation_complement;
        }

        if($tournee->millesime) {
            $id .= $tournee->millesime;
        }

        $degustation = $this->find($id);

        if($degustation) {

            return $degustation;
        }

        $degustation = new Degustation();
        $degustation->identifiant = $identifiant;
        $degustation->date_degustation = $tournee->date;
        $degustation->appellation = $tournee->appellation;
        $degustation->appellation_complement = $tournee->appellation_complement;
        $degustation->millesime = $tournee->millesime;
        $degustation->organisme = $tournee->organisme;
        $degustation->libelle = $tournee->libelle;
        $degustation->constructId();

        return $degustation;
    }

    public function findOrCreateForSaisieByTournee(Tournee $tournee, $identifiant) {
        $degustation = $this->findOrCreateByTournee($tournee, $identifiant);
        $degustation->updateFromEtablissement();
        $degustation->updateFromCompte();

        return $degustation;
    }

    public static function sortOperateursByDatePrelevement($operateur_a, $operateur_b) {

        return $operateur_a->date_demande > $operateur_b->date_demande;
    }

    public function getNotesTypeByAppellation($appellation) {
        if(!isset(self::$note_type_by_appellation[$appellation])) {

            return array();
        }

        $note_types = array();

        foreach(self::$note_type_by_appellation[$appellation] as $note_type) {
            $note_types[$note_type] = self::$note_type_libelles[$note_type];
        }

        return $note_types;
    }

    public function getDegustationsByEtablissement($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $ids = $this->startkey(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, ""))
                        ->endkey(sprintf("%s-%s-%s", self::TYPE_COUCHDB, $identifiant, "zzz"))
                        ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $degustations = array();

        foreach ($ids as $id) {
            $degustations[$id] = DegustationClient::getInstance()->find($id, $hydrate);
        }

        krsort($degustations);

        return $degustations;
    }

    public function getDegustationsByAppellation($appellation, $campagne) {

        return DegustationTousView::getInstance()->getDegustationsByAppellation($appellation, $campagne);
    }

    public function getLastDegustationByStatut($appellation, $identifiant, $statut) {

        return DegustationTousView::getInstance()->getLastDegustationByStatut($appellation, $identifiant, $statut);
    }

    public function getAppellationLibelle($appellation) {

        return self::$appellations[$appellation];
    }
}
