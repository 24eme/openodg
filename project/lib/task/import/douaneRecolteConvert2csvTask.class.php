<?php

class DouaneRecolteConvert2csvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
        	new sfCommandArgument('fichier', sfCommandArgument::REQUIRED, "Chemin du fichier"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add CSV header', false),
        ));

        $this->namespace = 'douaneRecolte';
        $this->name = 'convert2csv';
        $this->briefDescription = "Convertion des documents douaniers";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $file = $arguments['fichier'];

        if (!file_exists($file) || !is_file($file)) {
          return;
        }

        $csvfile = $file;
        $infos = pathinfo($file);
        $extension = (isset($infos['extension']) && $infos['extension'])? strtolower($infos['extension']): '';
        if ($extension == 'xls') {
          $csvfile = Fichier::convertXlsFile($file);
        } elseif ( ! DouaneImportCsvFile::getTypeFromFile($file) ) {
          throw new sfException("extention de ".$file." non géré");
        }
        if (isset($options['header']) && $options['header']) {
            echo DouaneCsvFile::CSV_ENTETES;
        }
        if (preg_match('/(sv|production)-[0-9]*-([0-9A-Z]{10})\./', $csvfile,$m)) {
            $cvi = $m[2];
        }
        $fichier = DouaneImportCsvFile::getNewInstanceFromType(DouaneImportCsvFile::getTypeFromFile($file), $csvfile, null, null, $cvi);
        $m = array();
        preg_match("/[a-zA-Z0-9]+-([0-9]{4})-.+/",$file,$m);
        if(count($m) > 1){
          $fichier->setCampagne($m[1]);
        }
        print $fichier->convert();
    }

}
