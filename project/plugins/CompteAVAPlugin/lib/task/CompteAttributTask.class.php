<?php

class CompteAttributTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Compte doc id"),
            new sfCommandArgument('action', sfCommandArgument::REQUIRED, "Action (add or remove)"),
            new sfCommandArgument('type_attribut', sfCommandArgument::REQUIRED, "Type d'attribut (attributs, produits, syndicats"),
            new sfCommandArgument('attribut', sfCommandArgument::REQUIRED, "Clé de l'attribut"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'attribut';
        $this->briefDescription = "Permet d'ajouter ou supprimer l'attribut d'un compte";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $action = $arguments['action'];
        $actions_autorisees = array('add', 'remove');

        if(!in_array($action, $actions_autorisees)) {
            echo sprintf("ERROR;L'action doit être %s, et non %s\n", implode(",", $actions_autorisees), $arguments['action']);
            return;
        }

        $type_attribut = $arguments['type_attribut'];
        $types_attribut_autorises = array('attributs', 'produits', 'syndicats', 'manuels');

        $attribut = trim($arguments['attribut']);

        if(!in_array($type_attribut, $types_attribut_autorises)) {
            echo sprintf("ERROR;Le type d'attribut doit être %s, et non %s\n", implode(",", $types_attribut_autorises), $arguments['type_attribut']);
            return;
        }

        $compte = CompteClient::getInstance()->find($arguments['doc_id']);

        if(!$compte) {
            echo sprintf("ERROR;COMPTE %s inexistant\n", $arguments['doc_id']);
            return;
        }

        if($action == 'add') {
            if($compte->existInfo($type_attribut, $attribut)) {
                echo sprintf("WARNING;L'attribut %s existe déjà pour ce compte %s\n", $attribut, $arguments['doc_id']);
                return;
            }

            @$compte->addInfo($type_attribut, $attribut);

            if(!$compte->getInfo($type_attribut, $attribut)) {
                echo sprintf("ERROR;L'attribut %s n'existe pas\n", $attribut, $arguments['doc_id']);
                return;
            }

            echo sprintf("SUCCESS;L'attribut %s a bien été ajouté au compte %s\n", $attribut, $arguments['doc_id']);
        } elseif($action == 'remove') {
            if(!$compte->existInfo($type_attribut, $attribut)) {
                echo sprintf("WARNING;L'attribut %s n'existe pas pour ce compte %s\n", $attribut, $arguments['doc_id']);
                return;
            }
            $compte->removeInfo($type_attribut, $attribut);
            echo sprintf("SUCCESS;L'attribut %s a bien été supprimé du compte %s\n", $attribut, $arguments['doc_id']);
        }

        $compte->save();

        
    }
}