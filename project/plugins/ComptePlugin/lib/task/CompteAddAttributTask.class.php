<?php

class CompteAddAttributTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Compte doc id"),
            new sfCommandArgument('attribut', sfCommandArgument::REQUIRED, "Argument"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'add-attribut';
        $this->briefDescription = "Permet d'ajouter un attribut à un compte";
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

        $attributs = CompteClient::getInstance()->getAttributsForType($compte->type_compte);
        $attribut = $arguments['attribut'];

        if(!array_key_exists($attribut, $attributs)) {
            echo sprintf("ERROR;L'attribut %s n'existe pas pour ce compte %s\n", $attribut, $arguments['doc_id']);
            return;
        }

        $compte->infos->attributs->add($attribut, $attributs[$attribut]);
        $compte->save();

        echo sprintf("SUCCESS;L'attribut %s a bien été ajouté au compte %s\n", $attribut, $arguments['doc_id']);
    }
}