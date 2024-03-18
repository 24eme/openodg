<?php

class LotChangeStatutTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Doc ID"),
            new sfCommandArgument('lot_unique_id', sfCommandArgument::REQUIRED, "lot id"),
            new sfCommandArgument('affectable', sfCommandArgument::REQUIRED, "affectable (true/false)"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('statut', null, sfCommandOption::PARAMETER_OPTIONAL, "statut"),
            new sfCommandOption('elevage', null, sfCommandOption::PARAMETER_OPTIONAL, "elevage", "-1"),
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

        $doc = acCouchdbManager::getClient()->find($arguments['doc_id']);
        $lot = $doc->getLot($arguments['lot_unique_id']);
        $lot->affectable = $affecte;
        if (isset($options['statut']) && $options['statut']) {
            $lot->statut = $arguments['statut'];
        }
        if ($options['elevage'] != '-1') {
            if ($options['elevage'] == 1 || strtolower($options['elevage']) == "true") {
                $lot->elevage = true;
            }else{
                $lot->elevage = false;
            }

        }
        $doc->save();

    }
}
