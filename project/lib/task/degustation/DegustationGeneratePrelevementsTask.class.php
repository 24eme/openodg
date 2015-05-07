<?php

class DegustationGeneratePrelevementsTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Degustation ID")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'degustation';
        $this->name = 'generate-prelevements';
        $this->briefDescription = "Corrige le parcellaire passé en parametre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $degustation = DegustationClient::getInstance()->find($arguments['doc_id']);

        $degustation->updateFromDRev();

        $degustation->prelevements = array();
        foreach($degustation->lots as $lot) {
            $degustation->addPrelevementFromLot($lot);
        }
        //$degustation->generatePrelevements();
        $degustation->save();
    }
}