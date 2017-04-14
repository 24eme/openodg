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
            new sfCommandOption('forcecreate', null, sfCommandOption::PARAMETER_REQUIRED, "Force la création", false),
        	new sfCommandOption('forceupdate', null, sfCommandOption::PARAMETER_REQUIRED, "Force la mise à jour de la DR", false),
        	new sfCommandOption('updaterevendique', null, sfCommandOption::PARAMETER_REQUIRED, "Force la mise à jour du volume revendique", false),
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

        if($drev && $drev->isNonRecoltant()) {
            echo sprintf("ERROR;Le DREV est une DREV négoce ou cave coopérative;%s\n", $drev->_id);

            return;
        }

        if($drev && !$drev->isAutomatique() && !$drev->isPapier() && !$drev->hasDR()) {
            echo sprintf("WARNING;La DREV est télédéclaré;%s\n", $drev->_id);

            if(!$options['forceupdate']) {
                return;
            }
        }

        if($drev && $drev->hasDR() && !$options['forceupdate']) {
            echo sprintf("WARNING;La DR a déjà été importée;%s\n", $drev->_id);

            return;
        }

        if($drev && !$drev->validation) {
            echo sprintf("WARNING;La DREV n'est pas validée;%s\n", $drev->_id);

            return;
        }

        if ($drev && $drev->isLectureSeule()) {

            return;
        }


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

            //Juste pour contrôler qu'il n'y a pas volume à revendiquer
            $drev->updateFromCSV(true, false, $csv->getCsvAcheteur($drev->identifiant));

            $drev->add('automatique', 1);
            $drev->add('non_vinificateur', 1);
            $drev->add('non_conditionneur', 1);

            if(($drev->declaration->getTotalVolumeRevendique() > 0 || $drev->declaration->hasVolumeRevendiqueInCepage()) && !$options['forcecreate']) {
                echo sprintf("ERROR;La DR a du volume sur place;%s\n", $etablisement_id);

                return;

            }

            if(!$drev->declaration->getTotalTotalSuperficie() && !$options['forcecreate']) {
                echo sprintf("ERROR;La DR n'a pas de superficie totale;%s\n", $etablisement_id);

                return;
            }

            if($etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)) {
                echo sprintf("WARNING;L'établissement est un vinificateur;%s\n", $etablisement_id);

            }

            if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_PRODUCTEUR)) {
                echo sprintf("WARNING;L'établissement n'est pas un producteur;%s\n", $etablisement_id);
            }

            $drev->remove('declaration');
            $drev->add('declaration');

            $drev->storeDeclarant();

            $drev->validate(true);
            $drev->validateOdg(true);

            $drev->save();
        }

        $drev->storeAttachment($arguments['csv'], "text/csv", "DR.csv");
        $drev->storeAttachment($arguments['pdf'], "application/pdf", "DR.pdf");

        $updateRevendique = $drev->isAutomatique() || $options['updaterevendique'];

    	$drev->updateFromCSV($updateRevendique);
        $drev->declaration->cleanNode();
        $drev->save();

        if(!$drev->isNonVinificateur()) {
            foreach($drev->getProduits() as $produit) {
                if(($produit->superficie_revendique && is_null($produit->superficie_vinifiee)) || ($produit->exist('superficie_revendique_vtsgn') && $produit->superficie_revendique_vtsgn && is_null($produit->superficie_vinifiee_vtsgn))) {
                    echo sprintf("WARNING;Les informations de superficie_vinifiee ne sont pas complètes;%s\n", $drev->_id);
                }

                if(($produit->superficie_revendique && is_null($produit->volume_revendique)) || ($produit->exist('superficie_revendique_vtsgn') && $produit->superficie_revendique_vtsgn && is_null($produit->volume_revendique_vtsgn))) {
                    echo sprintf("WARNING;Les informations de volume_vinifiee ne sont pas complètes;%s\n", $drev->_id);
                }
            }
        }

        echo sprintf("SUCCESS;La DR a bien été importée %s;%s\n", ($updateRevendique) ? "(le volume revendiqué a été mise à jour)" : null, $drev->_id);
    }
}
