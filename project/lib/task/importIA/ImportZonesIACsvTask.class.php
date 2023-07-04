<?php

require_once(dirname(__FILE__).'/../../../plugins/acVinParcellairePlugin/lib/vendor/geoPHP/geoPHP.inc');

class ImportZonesIACsvTask extends importOperateurIAAOCCsvTask
{
    const CSV_SECTEUR = 0;
    const CSV_RAISON_SOCIALE_OPERATEUR = 1;

    protected $etablissements = null;
    protected $etablissementsCache = array();

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'zones-ia';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $zones = [];
        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");
            if($data[1] == "RaisonSociale") {
                continue;
            }
            if(!trim($data[5]) || !trim($data[6])) {
                continue;
            }
            $zones[$data[0]][] = $data;
        }

        $geojson = [];
        foreach($zones as $zone => $datas) {
            $points = [];
            $color = "rgb(".rand(0,255).",".rand(0,255).",".rand(0,255).")";
            foreach($datas as $data) {
                $resultat = CompteClient::getInstance()->calculCoordonnees($data[2], $data[6], $data[5]);

                $points[] = ["type" => "Feature", "properties" => ["Zone" => $zone, "Raison Sociale" => $data[1], "Adresse" => $data[2], "Code postal" => $data[5], "Commune" => $data[6], "marker-color" => $color], "geometry" => ["coordinates" => [$resultat['lon'], $resultat['lat']], "type" => "Point"]];
            }
            $geojson = array_merge($geojson, $points);

            $geo = geoPHP::load(json_encode(["type" => "FeatureCollection", "features" => $points]));
            $area = ["type" => "Feature", "properties" => ["Zone" => $zone, "fill" => $color], "geometry" => json_decode($geo->envelope()->out("json"))];
            $geojson[] = $area;
        }

        echo json_encode(["type" => "FeatureCollection", "features" => $geojson]);
    }
}
