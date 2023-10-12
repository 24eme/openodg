<?php

class DRImportRelationBailleurTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "DR document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'dr';
        $this->name = 'import-relation-bailleur';
        $this->briefDescription = "Importe les relations bailleurs / metayer à partir d'une dr";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $dr = DRClient::getInstance()->find($arguments['doc_id']);
        $etablissement_source = EtablissementClient::getInstance()->findAny($dr->getDeclarant()->cvi);
        $etablissement_dst = $dr->getBailleurs();
        foreach ($etablissement_dst as $etablissement) {
            if ($etablissement['relation_exist'] === false)
                $etablissement_source->addLiaison("BAILLEUR", EtablissementClient::getInstance()->find($etablissement['etablissement_id']), true);
        }
        $etablissement_source->save();
    }
}
