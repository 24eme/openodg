<?php

class CompteUpdateInfosTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Compte doc id"),
            new sfCommandArgument('noeud', sfCommandArgument::REQUIRED, "Noeud de l'info à mettre à jours"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'update-infos';
        $this->briefDescription = "Permet de mettre à jour les libellés des infos";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $compte = CompteClient::getInstance()->find($arguments['doc_id']);

        if(!$compte) {
            echo sprintf("ERROR;COMPTE %s inexistant\n", $arguments['doc_id']);
            return;
        }

        if($arguments['noeud'] == 'produits') {
            $compte->updateLocalTagsProduits(array_keys($compte->infos->get('produits')->toArray(true, false)));
        }

        $compte->save();


    }
}
