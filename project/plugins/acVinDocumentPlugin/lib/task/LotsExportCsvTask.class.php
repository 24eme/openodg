<?php

class LotsExportCsvTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('with-historique', null, sfCommandOption::PARAMETER_REQUIRED, 'Export avec l\'historique', 0),
      // add your own options here
    ));

    $this->namespace        = 'lots';
    $this->name             = 'export-csv';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $e = new ExportLotsCSV(true, $this->configuration->getApplication(), $options['with-historique']);
    print $e->exportAll();
  }
}
