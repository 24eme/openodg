<?php

class DegustateursExportCsvTask extends sfBaseTask
{

    protected function configure()
    {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'degustations';
        $this->name = 'export-degustateurs-csv';
        $this->briefDescription = "Export CSV des dégustateurs";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $degustations = DeclarationExportView::getInstance()->getDeclarations(DegustationClient::TYPE_MODEL)->rows;
        $headers = true;
        foreach($degustations as $item) {
          $export = new ExportDegustateursCSV(DegustationClient::getInstance()->find($item->id), $headers);
          echo $export->export();
          $headers = false;
        }
    }
}
