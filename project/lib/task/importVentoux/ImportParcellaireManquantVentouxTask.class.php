<?php

class ImportParcellaireManquantVentouxTask extends sfBaseTask
{
    const CSV_CVI = 2;
    const CSV_NOM_COMMUNE = 4;
    const CSV_SECTION = 7;
    const CSV_NUM_PARCELLE = 8;
    const CSV_SURFACE = 9;
    const CSV_CEPAGE = 11;
    const CSV_POURCENTAGE_MANQUANT = 15;
    const CSV_DENSITE = 12;

    const DATE_VALIDATION = "04-15";

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv"),
            new sfCommandArgument('periode', sfCommandArgument::REQUIRED, "Période")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellairemanquant-ventoux';
        $this->briefDescription = 'Import des déclarations de pieds manquants';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $periode = $arguments['periode'];

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            if(!$data[self::CSV_POURCENTAGE_MANQUANT]) {
                continue;
            }

            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CSV_CVI]);
            if (!$etablissement) {
                echo "Error: Etablissement ".$data[self::CSV_CVI]." non trouvé\n";
                continue;
            }
            $parcellaireTotal = ParcellaireClient::getInstance()->getLast($etablissement->identifiant);
            if (!$parcellaireTotal) {
                $parcellaireTotal = new Parcellaire();
                echo "Parcellaire non trouvé;".$line;
            }
            $manquant = ParcellaireManquantClient::getInstance()->findOrCreate($etablissement->identifiant, $periode);
            $found = false;
            foreach($parcellaireTotal->getParcelles() as $parcelle) {
                if ($parcelle->getSection() == strtoupper($data[self::CSV_SECTION]) &&
                    $parcelle->numero_parcelle == $data[self::CSV_NUM_PARCELLE]) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $produitHash = '/declaration/certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT/couleurs';
                if(preg_match('/ B$/', $data[self::CSV_CEPAGE])) {
                    $produitHash .= '/blanc/cepages/DEFAUT';
                } else {
                    $produitHash .= '/rouge/cepages/DEFAUT';
                }
                try {
                $parcelle = $parcellaireTotal->addParcelleWithProduit($produitHash, $data[self::CSV_CEPAGE], null, $data[self::CSV_NOM_COMMUNE], null, $data[self::CSV_SECTION], $data[self::CSV_NUM_PARCELLE]);
                } catch (Exception $e) {
                    echo $e->getMessage().";".$line;
                    continue;
                }
            }
            $manquantParcelle = $manquant->addParcelleFromParcellaireParcelle($parcelle);
            $manquantParcelle->densite = (int)$data[self::CSV_DENSITE];
            $manquantParcelle->superficie = (float)($data[self::CSV_SURFACE]);
            $manquantParcelle->pourcentage = (int)$data[self::CSV_POURCENTAGE_MANQUANT];

            try {
                if(!$manquant->isValidee()) {
                    $manquant->validate($periode.'-'.self::DATE_VALIDATION);
                }
                $manquant->save();
            } catch(Exception $e) {
                sleep(60);
                if(!$manquant->isValidee()) {
                    $manquant->validate($periode.'-'.self::DATE_VALIDATION);
                }
                $manquant->save();
            }
        }
    }
}
