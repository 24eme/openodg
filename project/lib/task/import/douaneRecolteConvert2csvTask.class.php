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
        try{
          $csvfile = Fichier::convertXlsFile($file);

        }catch(sfException $e){
          echo "convertion impossible\n";
        }
        $fichier = DouaneImportCsvFile::getNewInstanceFromType(DouaneImportCsvFile::getTypeFromFile($file), $csvfile);
        print $fichier->convert();
    }

}
