<?php

class importDouaneTauxTask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('configuration_id', sfCommandArgument::REQUIRED, "ID couchdb du document à importer"),
           new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Répertoire contenant les fichiers"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
        ));

        $this->namespace = 'import';
        $this->name = 'douane-taux';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [importCVO|INFO] task does things.
Call it with:

  [php symfony importCVO|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $configuration = ConfigurationClient::getInstance()->find($arguments['configuration_id']);

        foreach(file($arguments['file']) as $line) {
            if (preg_match('/^#/', $line)) {
                continue;
            }
            $douaneTaxe = split(';', $line);

            $a = array();
            $a[ProduitCsvFile::CSV_PRODUIT_INTERPRO] = 'declaration';
            $a[ProduitCsvFile::CSV_PRODUIT_DOUANE_DATE] = $douaneTaxe[1];
            $a[ProduitCsvFile::CSV_PRODUIT_DOUANE_TAXE] = $douaneTaxe[2];
            $a[ProduitCsvFile::CSV_PRODUIT_DOUANE_NOEUD] = 'import';
            
            $configuration->declaration->get($douaneTaxe[0])->setDroitDouaneCsv($a, 'import');
        }

        $configuration->save();

    }

}
