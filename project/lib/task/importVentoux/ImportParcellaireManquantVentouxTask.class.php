<?php

class ImportParcellaireManquantVentouxTask extends ImportParcellaireAffectationVentouxTask
{

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

            $etablissement = $this->findEtablissement($data);

            if(!$etablissement) {
                echo "Error: Etablissement ".$data[self::CSV_CVI]." non trouvé;".implode(";", $data)."\n";
                continue;
            }

            $manquant = ParcellaireManquantClient::getInstance()->findOrCreate($etablissement->identifiant, $periode);
            if(!$manquant->isValidee()) {
                $manquant->validate($periode.'-'.self::DATE_VALIDATION);
            }
            try {
                $manquant->save();
            } catch(Exception $e) {
                sleep(60);
                $manquant->save();
            }

            if(!$data[self::CSV_POURCENTAGE_MANQUANT]) {
                continue;
            }

            $manquantParcelle = $this->addParcelleFromData($manquant, $data);
            if(!$manquantParcelle) {
                continue;
            }

            $manquantParcelle->densite = (int)$data[self::CSV_DENSITE];
            $manquantParcelle->superficie = (float)($data[self::CSV_SURFACE]);
            $manquantParcelle->pourcentage = round((float)($data[self::CSV_POURCENTAGE_MANQUANT])*100, 2);

            try {
                $manquant->save();
            } catch(Exception $e) {
                sleep(60);
                $manquant->save();
            }
        }
    }
}
