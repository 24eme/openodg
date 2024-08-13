<?php

class LotLeverConformiteTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Identifiant de l'établissement"),
            new sfCommandArgument('lot_unique_id', sfCommandArgument::REQUIRED, "lot id"),
            new sfCommandArgument('commentaire', sfCommandArgument::OPTIONAL, "Commentaire"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'lot';
        $this->name = 'lever-convormite';
        $this->briefDescription = "Lever la conformité d'un lot";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $lot = LotsClient::getInstance()->findByUniqueId($arguments['identifiant'], $arguments['lot_unique_id']);
        $lot->leverNonConformite($lot->date);
        if(isset($arguments['commentaire'])) {
            $lot->observation = $arguments['commentaire'];
        }
        $lot->getDocument()->save();
    }
}
