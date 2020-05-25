<?php

class DouaneImportTask extends sfBaseTask
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

        $this->namespace = 'douane';
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
            $cvis[$data[DouaneCsvFile::CSV_RECOLTANT_CVI]."_".$data[DouaneCsvFile::CSV_CAMPAGNE].'_'.$data[DouaneCsvFile::CSV_TYPE]][] = $ligne;
        }

        foreach($cvis as $cviCampagne => $lignes) {
                $cviParts = explode('_', $cviCampagne);
                $cvi = $cviParts[0];
                $campagne = $cviParts[1];
                $type = $cviParts[2];

                $etablissement = EtablissementClient::getInstance()->findByCvi($cvi,true);

                if(!$etablissement) {
                    echo "$type;ERREUR;$cvi;cvi non trouvé\n";

                    continue;
                }

                if(is_array($etablissement) && count($etablissement) > 1) {
                    echo "$type;ERREUR;$cvi;plusieurs établissements ont ce cvi\n";

                    continue;
                }

                if($etablissement->isSuspendu()){
                  echo "$type;ERREUR;$cvi;cvi opérateur archivé, pas de reprise\n";
                  continue;
                }
                $fichier = FichierClient::getInstance()->findByArgs($type, $etablissement->identifiant, $campagne);

                if($fichier) {

                    continue;
                }

                if(!$fichier) {
                    $fichier = FichierClient::getClientFromType($type)->createDoc($etablissement->identifiant, $campagne);
                }

                foreach($lignes as $ligne) {
                    $fichier->addDonnee($csv[$ligne]);
                }

                $fichier->save();

                echo "IMPORTE;$fichier->_id\n";
        }
    }
}
