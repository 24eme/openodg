<?php

class DeclarationTransfertTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant_from', sfCommandArgument::REQUIRED, "Identifiant à transferer"),
            new sfCommandArgument('identifiant_to', sfCommandArgument::REQUIRED, "Nouvelle identifiant"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
        ));

        $this->namespace = 'declaration';
        $this->name = 'transfert';
        $this->briefDescription = "Permet de transférer les documents d\"un identifiant vers un autre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $transfert = new DeclarationTransfert($arguments['identifiant_from'], $arguments['identifiant_to']);
        $ids = $transfert->transfert();

        foreach($ids as $idFrom => $idTo) {
            echo $idFrom . " ----> " . $idTo . "\n";
        }

    }
}
