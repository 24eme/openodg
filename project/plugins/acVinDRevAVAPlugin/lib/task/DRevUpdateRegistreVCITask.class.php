<?php

class DRevUpdateRegistreVCITask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document DRev ID"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'update-registre-vci';
        $this->briefDescription = 'Mise à jour du registre VCI à partir de la DRev';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drev = DRevClient::getInstance()->find($arguments['doc_id']);

        if(!$drev) {

            echo sprintf("ERROR;DRev introuvable %s\n", $arguments['doc_id']);
            return;
        }

        if(!$drev->validation) {
            echo sprintf("WARNING;DRev non validé\n", $arguments['doc_id']);
            return;
        }

        $drev->updateRegistreVCI();
    }
}
