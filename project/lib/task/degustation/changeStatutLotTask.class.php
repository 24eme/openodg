<?php

class changeStatutLotTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('etablissement_id', sfCommandArgument::REQUIRED, "Etablissement ID"),
            new sfCommandArgument('lot_unique_id', sfCommandArgument::REQUIRED, "lot id"),
            new sfCommandArgument('affectable', sfCommandArgument::REQUIRED, "affectable (true/false)")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'lot';
        $this->name = 'change-statut';
        $this->briefDescription = "Corrige les dégustations après une petite refonte";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $affecte = false;
        if ($arguments['affectable'] == 1 || strtolower($arguments['affectable']) == "true") {
            $affecte = true;
        }

        $this->lot = LotsClient::getInstance()->findByUniqueId($arguments['etablissement_id'], $arguments['lot_unique_id']);
        $this->lot->affectable = $affecte;
        $this->lot->getDocument()->save();

    }
}
