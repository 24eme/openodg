<?php

class exportSocieteTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('all', null, sfCommandOption::PARAMETER_OPTIONAL, 'Display all societé (suspendu included)', ''),
      // add your own options here
    ));

    $this->namespace        = 'export';
    $this->name             = 'societe';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [testFacture|INFO] task does things.
Call it with:

    [php symfony export:societe|INFO]
EOF;
  }
  
  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $this->includeSuspendu = false;
    if (isset($options['all']) && $options['all']) {
	$this->includeSuspendu = true;
    }

    $this->routing = clone ProjectConfiguration::getAppRouting();

    echo ExportSocieteCSV::getHeaderCsv();

    foreach(SocieteAllView::getInstance()->findByInterpro('INTERPRO-declaration') as $socdata) {
        $soc = SocieteClient::getInstance()->find($socdata->id);

        $export = new ExportSocieteCSV($soc, false, $this->routing);
        echo $export->export();
    }
  }
}
