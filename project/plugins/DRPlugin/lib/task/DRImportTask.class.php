<?php

class DRImportTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV de la DR"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'dr';
        $this->name = 'import';
        $this->briefDescription = "Import de la DR";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if(!file_exists($arguments['csv'])) {
            //echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $arguments['doc_id']);

            //return;
        }

        $csvFile = new CsvFile($arguments['csv']);
        $csv = $csvFile->getCsv();
        $cvis = null;
        foreach($csv as $ligne => $data) {
            $cvi = $data[DouaneCsvFile::CSV_RECOLTANT_CVI];
            $campagne = $data[DouaneCsvFile::CSV_CAMPAGNE];
            $cvis[$cvi."_".$campagne][] = $ligne;
        }

        foreach($cvis as $cviCamapagne => $lignes) {
                $cviParts = explode('_', $cviCamapagne);
                $cvi = $cviParts[0];
                $campagne = $cviParts[1];

                $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);

                if(!$etablissement) {
                    echo "ERREUR;$cvi;cvi non trouvé\n";

                    continue;
                }

                $dr = DRClient::getInstance()->findByArgs($etablissement->identifiant, $campagne);

                if(!$dr) {
                    $dr = DRClient::getInstance()->createDoc($etablissement->identifiant, $campagne);
                }

                $dr->remove('donnees');
                $dr->add('donnees');
                $dr->remove('mouvements');
                $dr->add('mouvements');

                foreach($lignes as $ligne) {
                    $dr->addDonnee($csv[$ligne]);
                }

                $dr->save();

                echo "IMPORTE;$dr->_id\n";
        }
    }
}
