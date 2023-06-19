<?php

class ParcellaireAffectationCreateTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('cvi', sfCommandArgument::REQUIRED, "CVI"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'parcellaireaffectation';
        $this->name = 'create';
        $this->briefDescription = "Creation des affectations parcellaire";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $parcellaire = ParcellaireAffectationClient::getInstance()->findOrCreate($arguments['cvi'], $arguments['campagne']);
        $parcellaire->updateParcelles();

        $parcellaire->save();
    }
}
