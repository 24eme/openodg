<?php 

class ReserveInterproExportCSVTask extends sfBaseTask 
{
    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('drev_id', sfCommandArgument::OPTIONAL, "Id de la DRev"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace        = "drev";
        $this->name             = "reserve-interpro";
        $this->briefDescription = "Export de la reserve interprofessionnelle depuis la DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) 
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        if(isset($arguments['drev_id']) && $arguments['drev_id']) {
            $drev = DRevClient::getInstance()->find($arguments['drev_id']);
            $export = new ExportReserveInterproCSV($drev);

            $csv = $export->exportForOneDRev($drev); 
            
            print($csv);
            return;
        }

        $export = new ExportReserveInterproCSV();
        $csv = $export->export();
        print($csv);
        return;
    }
}