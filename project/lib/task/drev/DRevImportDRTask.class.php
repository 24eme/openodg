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

        if(!file_exists($arguments['csv'])) {
            echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $arguments['doc_id']);

            return;
        }

        if(!file_exists($arguments['pdf'])) {
            echo sprintf("ERROR;Le fichier PDF n'existe pas;%s\n", $arguments['doc_id']);

            return;
        }

        $drev = DRevClient::getInstance()->find($arguments['doc_id']);
        
        if(!$drev) {
            $etablisement_id = preg_replace("/^DREV-([0-9]+)-[0-9]+$/", 'ETABLISSEMENT-\1', $arguments['doc_id']);
            $etablissement = EtablissementClient::getInstance()->find($etablisement_id);

            if(!$etablissement) {
                echo sprintf("ERROR;L'établissement n'existe pas;%s\n", $etablisement_id);

                return;
            }

            $campagne = preg_replace("/^DREV-[0-9]+-([0-9]+)$/", '\1', $arguments['doc_id']);

            $drev = new DRev();
            $drev->initDoc($etablissement->identifiant, $campagne);
            $csv = new DRCsvFile($arguments['csv']);

            $drev->updateFromCSV($csv->getCsvAcheteur($drev->identifiant));
            $drev->updateRevendiqueFromDetail();
            $drev->updateProduitsFromCepage();
            $drev->add('automatique', 1);
            $drev->add('non_vinificateur', 1);
            $drev->add('non_conditionneur', 1);

            if($drev->declaration->getTotalVolumeRevendique() > 0) {
                echo sprintf("ERROR;La DR a du volume sur place;%s\n", $etablisement_id);

                return;
            }

            if(!$drev->declaration->getTotalTotalSuperficie()) {
                echo sprintf("ERROR;La DR n'a pas de superficie totale;%s\n", $etablisement_id);

                return;
            }

            if($etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)) {
                echo sprintf("WARNING;L'établissement est un vinificateur;%s\n", $etablisement_id);

                return;
            }

            if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_PRODUCTEUR)) {
                echo sprintf("WARNING;L'établissement n'est pas un producteur;%s\n", $etablisement_id);

                return;
            }

            $drev->storeDeclarant();
            $drev->validation = true;
            $drev->validation_odg = true;
            $drev->save();
        }

        if($drev->hasDR()) {
            echo sprintf("WARNING;La DR a déjà été importée;%s\n", $drev->_id);

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

        if(!$drev->isAutomatique() && !$drev->isPapier()) {
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