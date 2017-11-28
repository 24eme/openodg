<?php

class DRevNegoceImportDRTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV des DR"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'negoce-import-dr';
        $this->briefDescription = "Import des superficies d'une DRev négoce";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if(!file_exists($arguments['csv'])) {
            echo sprintf("ERREUR;Le fichier CSV n'existe pas;%s\n", $arguments['doc_id']);

            return;
        }

        $drev = DRevClient::getInstance()->find($arguments['doc_id']);

        if(!$drev) {
            echo "ERREUR;La DREV n'existe pas ".$arguments['doc_id']."\n";
            return;
        }

        if($drev && !$drev->validation) {
            echo sprintf("ERREUR;La DREV n'est pas validée;%s\n", $drev->_id);

            return;
        }

        if($drev && !$drev->isNonRecoltant()) {
            echo sprintf("ERROR;Le DREV est une DREV producteur;%s\n", $drev->_id);

            return;
        }

        $csv = new DRCIVACsvFile($arguments['csv']);

        $lines = array();
        foreach($csv->getCsv() as $line) {
            if($line[DRCIVACsvFile::CSV_ACHETEUR_CVI] != $drev->identifiant) {
                continue;
            }

            if($line[DRCIVACsvFile::CSV_CEPAGE] != "TOTAL") {
                continue;
            }
            $lines[] = $line;
        }

        foreach($lines as $line) {
            if($line[DRCIVACsvFile::CSV_SUPERFICIE] == "") {
                echo "ERREUR;Toutes les superficies ne sont pas renseignés;".$drev->_id."\n";

                return;
            }
            $line[DRCIVACsvFile::CSV_HASH_PRODUIT] = preg_replace("/(mentionVT|mentionSGN)/", "mention", $line[DRCIVACsvFile::CSV_HASH_PRODUIT]);
            if (!$drev->getConfiguration()->exist(preg_replace('|/recolte.|', '/declaration/', $line[DRCIVACsvFile::CSV_HASH_PRODUIT]))) {
                continue;
            }
        }

        foreach($drev->getProduits() as $produit) {
            foreach($produit->getProduitsCepage() as $detail) {
                $detail->superficie_vinifiee_total = null;
                $detail->superficie_vinifiee = null;
                $detail->superficie_vinifiee_vt = null;
                $detail->superficie_vinifiee_sgn = null;
            }
        }

        foreach($lines as $line) {
            //var_dump($line);
            $superficie = $line[DRCIVACsvFile::CSV_SUPERFICIE];
            //var_dump($superficie)."\n";
            $hash = preg_replace("/(mentionVT|mentionSGN)/", "mention", $line[DRCIVACsvFile::CSV_HASH_PRODUIT]);
            if (!$drev->getConfiguration()->exist(preg_replace('|/recolte.|', '/declaration/', $hash))) {
                continue;
            }
            $config = $drev->getConfiguration()->get($hash)->getNodeRelation('revendication');
            $configHash = $config->getHash();
            if(!$drev->exist($configHash) && preg_match("/appellation_PINOTNOIRROUGE/", $configHash)) {
                $configHash = preg_replace("|/appellation_PINOTNOIRROUGE/|", '/appellation_PINOTNOIR/', $configHash);
            }
            if(!$drev->exist($configHash) && preg_match("|/appellation_PINOTNOIR/|", $configHash)) {
                $configHash = preg_replace("|/appellation_PINOTNOIR/|", '/appellation_PINOTNOIRROUGE/', $configHash);
            }
            if(!$drev->exist($configHash)) {
                echo "ERREUR;Ce produit ".$configHash." n'a pas de volume;".$drev->_id."\n";
                return;
            }
            $produit = $drev->get($configHash);

            $vtsgn = null;
            if((preg_match("/mentionVT/",$line[DRCIVACsvFile::CSV_HASH_PRODUIT]))) {
                $vtsgn = "VT";
            }

            if((preg_match("/mentionSGN/",$line[DRCIVACsvFile::CSV_HASH_PRODUIT]))) {
                $vtsgn = "SGN";
            }

            foreach($produit->getProduitsCepage() as $detail) {
                if($vtsgn && $detail->getConfig()->hasVtsgn()) {
                    break;
                } elseif($vtsgn) {
                    continue;
                }

                break;
            }

            if($vtsgn) {
                $key = 'superficie_vinifiee_'.strtolower($vtsgn);
                $detail->set($key, $detail->get($key) + (float) $superficie);
            } else {
                $detail->superficie_vinifiee += (float) $superficie;
            }
        }

        $drev->updateProduitRevendiqueFromCepage();

        foreach($drev->getProduits() as $produit) {
            if(($produit->superficie_revendique && !$produit->superficie_vinifiee) || ($produit->exist('superficie_revendique_vtsgn') && $produit->superficie_revendique_vtsgn && !$produit->superficie_vinifiee_vtsgn)) {
                echo sprintf("ERROR;Les informations de superficie_vinifiee ne sont pas complètes;%s\n", $drev->_id);

                return;
            }

            if(($produit->superficie_revendique && !$produit->volume_revendique) || ($produit->exist('superficie_revendique_vtsgn') && $produit->superficie_revendique_vtsgn && !$produit->volume_revendique_vtsgn)) {
                echo sprintf("ERROR;Les informations de volume_vinifiee ne sont pas complètes;%s\n", $drev->_id);

                return;
            }
        }

        $drev->save();



        echo sprintf("SUCCESS;Les données de superficies ont bien été importées;%s\n", $drev->_id);
    }
}
