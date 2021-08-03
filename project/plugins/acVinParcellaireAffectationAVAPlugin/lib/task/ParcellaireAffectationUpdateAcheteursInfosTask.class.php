<?php

class ParcellaireAffectationUpdateAcheteursInfosTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'update-acheteurs-infos';
        $this->briefDescription = "Envoi d'un mail de rappel des pièces non recus";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $parcellaire = ParcellaireAffectationClient::getInstance()->find($arguments['doc_id']);
        $parcellaire->updateAcheteursInfos();

        if($parcellaire->isModified()) {
            echo $parcellaire->_id.":".json_encode($parcellaire->getModifications())."\n";
            $parcellaire->save();
        }


    }
}
