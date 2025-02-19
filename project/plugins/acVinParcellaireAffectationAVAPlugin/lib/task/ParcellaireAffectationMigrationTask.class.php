<?php

class ParcellaireAffectatioMigrationTask extends sfBaseTask
{
    protected $communes = [
        "ALBE" => "67003",
        "ALTORF" => "67008",
        "ANDLAU" => "67010",
        "AVOLSHEIM" => "67016",
        "BALBRONN" => "67018",
        "BARR" => "67021",
        "BERGBIETEN" => "67030",
        "BERNARDSWILLER" => "67031",
        "BERNARDVILLE" => "67032",
        "BERSTETT" => "67034",
        "BISCHOFFSHEIM" => "67045",
        "BLIENSCHWILLER" => "67051",
        "BOERSCH" => "67052",
        "BOURGHEIM" => "67060",
        "CHATENOIS" => "67073",
        "CLEEBOURG" => "67074",
        "DAHLENHEIM" => "67081",
        "DAMBACH-LA-VILLE" => "67084",
        "DANGOLSHEIM" => "67085",
        "DIEFFENTHAL" => "67094",
        "DORLISHEIM" => "67101",
        "EICHHOFFEN" => "67120",
        "EPFIG" => "67125",
        "ERGERSHEIM" => "67127",
        "FLEXBOURG" => "67139",
        "FURDENHEIM" => "67150",
        "GERTWILLER" => "67155",
        "GOXWILLER" => "67164",
        "HANDSCHUHEIM" => "67181",
        "HEILIGENSTEIN" => "67189",
        "HURTIGHEIM" => "67214",
        "ITTERSWILLER" => "67227",
        "KIENHEIM" => "67236",
        "KINTZHEIM" => "67239",
        "KIRCHHEIM" => "67240",
        "KUTTOLSHEIM" => "67253",
        "MARLENHEIM" => "67282",
        "MITTELBERGHEIM" => "67295",
        "MOLSHEIM" => "67300",
        "MUTZIG" => "67313",
        "NORDHEIM" => "67335",
        "NOTHALTEN" => "67337",
        "OBERHOFFEN-LES-WISSEMBOURG" => "67344",
        "OBERNAI" => "67348",
        "ODRATZHEIM" => "67354",
        "ORSCHWILLER" => "67362",
        "OSTHOFFEN" => "67363",
        "OTTROTT" => "67368",
        "QUATZENHEIM" => "67382",
        "REICHSFELD" => "67387",
        "RIEDSELTZ" => "67400",
        "ROSENWILLER" => "67410",
        "ROSHEIM" => "67411",
        "ROTT" => "67416",
        "SAINT-NABOR" => "67428",
        "SAINT-PIERRE" => "67429",
        "SAINT-PIERRE-BOIS" => "67430",
        "SCHARRACHBERGHEIM-IRMSTETT" => "67442",
        "SCHARRACHBERGHEIM" => "67442",
        "SCHERWILLER" => "67445",
        "SOULTZ-LES-BAINS" => "67473",
        "SIGOLSHEIM" => "68310",
        "STEINSELTZ" => "67479",
        "STOTZHEIM" => "67481",
        "TRAENHEIM" => "67492",
        "VILLE" => "67507",
        "WANGEN" => "67517",
        "WESTHOFFEN" => "67525",
        "WINGERSHEIM LES QUATRE BANS" => "67539",
        "WINTZENHEIM-KOCHERSBERG" => "67542",
        "WISSEMBOURG" => "67544",
        "WOLXHEIM" => "67554",
        "ZELLWILLER" => "67557",
        "AMMERSCHWIHR" => "68005",
        "BEBLENHEIM" => "68023",
        "BENNWIHR" => "68026",
        "BERGHEIM" => "68028",
        "BERGHOLTZ" => "68029",
        "BERGHOLTZZELL" => "68030",
        "BERRWILLER" => "68032",
        "BUHL" => "68058",
        "CERNAY" => "68063",
        "COLMAR" => "68066",
        "EGUISHEIM" => "68078",
        "GUEBERSCHWIHR" => "68111",
        "GUEBWILLER" => "68112",
        "HARTMANNSWILLER" => "68122",
        "HATTSTATT" => "68123",
        "HERRLISHEIM-PRES-COLMAR" => "68134",
        "HOUSSEN" => "68146",
        "HUNAWIHR" => "68147",
        "HUSSEREN-LES-CHATEAUX" => "68150",
        "INGERSHEIM" => "68155",
        "JUNGHOLTZ" => "68159",
        "KATZENTHAL" => "68161",
        "KAYSERSBERG VIGNOBLE" => "68162",
        "KAYSERSBERG-VIGNOBLE" => "68162",
        "KAYSERSBERG-VIGNOBLE-VIGNOBLE" => "68162",
        "KIENTZHEIM" => "68164",
        "LEIMBACH" => "68180",
        "MERXHEIM" => "68203",
        "MITTELWIHR" => "68209",
        "NIEDERMORSCHWIHR" => "68237",
        "OBERMORSCHWIHR" => "68244",
        "ORSCHWIHR" => "68250",
        "OSENBACH" => "68251",
        "PFAFFENHEIM" => "68255",
        "RIBEAUVILLE" => "68269",
        "RIQUEWIHR" => "68277",
        "RODERN" => "68280",
        "RORSCHWIHR" => "68285",
        "ROUFFACH" => "68287",
        "SAINT-HIPPOLYTE" => "68296",
        "SOULTZ-HAUT-RHIN" => "68315",
        "SOULTZ" => "68315",
        "SOULTZMATT" => "68318",
        "STEINBACH" => "68322",
        "THANN" => "68334",
        "TURCKHEIM" => "68338",
        "UFFHOLTZ" => "68342",
        "VIEUX-THANN" => "68348",
        "VOEGTLINSHOFFEN" => "68350",
        "WALBACH" => "68354",
        "WATTWILLER" => "68359",
        "WESTHALTEN" => "68364",
        "WETTOLSHEIM" => "68365",
        "WIHR-AU-VAL" => "68368",
        "WINTZENHEIM" => "68374",
        "WUENHEIM" => "68381",
        "ZELLENBERG" => "68383",
        "ZIMMERBACH" => "68385",
    ];

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'migration';
        $this->briefDescription = "Migre les infos du parcellaire";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $parcellaire = ParcellaireAffectationClient::getInstance()->find($arguments['doc_id']);
        foreach($parcellaire->declaration->getParcelles() as $parcelle) {
            if($parcelle->origine_doc) {
                continue;
            }
            if(array_key_exists($parcelle->commune, $this->communes)) {
                $parcelle->code_commune = $this->communes[$parcelle->commune];
            } else {
                echo "code commune non trouvé : ".$parcelle->commune."\n";
            }
            if (is_null($parcelle->prefix) && preg_match('/[A-Z]-([0-9]{3})[0-9]{2}-/', $parcelle->getKey(), $m)) {
                $parcelle->prefix = $m[1];
            } elseif(is_null($parcelle->prefix)) {
                $parcelle->prefix = "000";
            }
            $parcelle->numero_parcelle = preg_replace("/^0+/", "", $parcelle->numero_parcelle);
            $parcelle->section = preg_replace("/^0+/", "", $parcelle->section);
            if (is_null($parcelle->numero_ordre) && preg_match('/-[0-9]+-[0-9]+-([0-9]{2})(-[A-Z]|-?$)/', $parcelle->getKey(), $m)) {
                $parcelle->numero_ordre = $m[1];
            }

            if(!is_null($parcelle->code_commune) && !is_null($parcelle->prefix) && !is_null($parcelle->numero_ordre)) {
                $parcelle->idu = sprintf('%05s%03s%02s%04s', $parcelle->code_commune, $parcelle->prefix, $parcelle->section, $parcelle->numero_parcelle);
                $parcelle->parcelle_id = sprintf("%s-%02s", $parcelle->idu, $parcelle->numero_ordre);
            }

            if (preg_match('/[A-Z]-([12][0-9][0-9][0-9]-[12][0-9][0-9][0-9])-[A-Z]/', $parcelle->getKey(), $m)) {
                $parcelle->campagne_plantation = $m[1];
            }
            $parcelle->superficie = round($parcelle->superficie / 100, 4);
            if(is_null($parcelle->_get('superficie_parcellaire'))) {
                $parcelle->superficie_parcellaire = $parcelle->superficie;
            }

            $parcelle->origine_doc = $parcellaire->_id;
        }

        if($parcellaire->isModified()) {
            echo $parcellaire->_id.":".json_encode($parcellaire->getModifications())."\n";
            $parcellaire->save();
        }

    }
}
