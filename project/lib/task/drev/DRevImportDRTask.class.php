<?php

class DRevImportDRTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV de la DR"),
            new sfCommandArgument('pdf', sfCommandArgument::REQUIRED, "PDF de la DR"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'import-dr';
        $this->briefDescription = "Import de la DR";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drev = DRevClient::getInstance()->find($arguments['doc_id']);
        
        if(!$drev) {
            echo sprintf("WARNING;La DREV n'existe pas;%s\n", $arguments['doc_id']);

            return;
        }

        if(!$drev->validation) {
            echo sprintf("WARNING;La DREV n'est pas validée;%s\n", $drev->_id);

            return;
        }

        if($drev->isNonRecoltant()) {
            echo sprintf("WARNING;Le DREV est une DREV négoce ou cave coopérative;%s\n", $drev->_id);

            return;
        }

        if(!file_exists($arguments['csv'])) {
            echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $drev->_id);

            return;
        }

        if(!file_exists($arguments['pdf'])) {
            echo sprintf("ERROR;Le fichier PDF n'existe pas;%s\n", $drev->_id);

            return;
        }

        if($drev->hasDR()) {
            echo sprintf("WARNING;La DR a déjà été importée;%s\n", $drev->_id);

            return;
        }

        if(!$drev->isPapier()) {
            echo sprintf("WARNING;La DREV n'est pas une déclaration papier;%s\n", $drev->_id);

            return;
        }

        $drev->storeAttachment($arguments['csv'], "text/csv", "DR.csv");
        $drev->storeAttachment($arguments['pdf'], "application/pdf", "DR.pdf");
        $drev->updateFromCSV();
        $drev->declaration->cleanNode();
        $drev->save();

        echo sprintf("SUCCESS;La DR a bien été importée;%s\n", $drev->_id);


    }
}