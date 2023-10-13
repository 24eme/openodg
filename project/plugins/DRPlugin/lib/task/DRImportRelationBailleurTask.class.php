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
        $etablissement_source = $dr->getEtablissementObject();
        $etablissement_dst = $dr->getBailleurs();
        foreach ($etablissement_dst as $etablissement) {
            $etablissement_id = $etablissement['etablissement_id'];
            if ($etablissement_id == null) {
                $etab = EtablissementClient::getInstance()->find(EtablissementClient::getInstance()->findByRaisonSociale($etablissement['raison_sociale']));
                $etab->ppm = $etablissement['ppm'];
                $etablissement_id = $etab->_id;
                $etab->save();
            }
            $etablissement_source->addLiaison("BAILLEUR", EtablissementClient::getInstance()->find($etablissement_id), true);
        }
        $etablissement_source->save();
    }
}
