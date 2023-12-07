<?php
class ImportDSCSVTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add CSV header', false),
        ));

        $this->namespace = 'import';
        $this->name = 'ds-csv';
        $this->briefDescription = 'Import des déclarations de stock';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $file = $arguments['file'];

        $this->file_path = $file;

        if (!file_exists($file) || !is_file($file)) {
          return;
        }

        $csvfile = $file;

        $fichier = new DSDouaneCSVFile($file);

        if (isset($options['header']) && $options['header'] && $options['header'] != "false") {
            echo DSDouaneCSVFile::CSV_ENTETES;
        }

        print_r($fichier->convert());
    }

}
