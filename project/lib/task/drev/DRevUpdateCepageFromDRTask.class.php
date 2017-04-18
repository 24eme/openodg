<?php

class DRevUpdateCepageFromDRTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document DRev ID"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'update-cepage-from-dr';
        $this->briefDescription = 'Mise à jour des infos cépage depuis la DR';
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

            echo sprintf("ERROR;DRev introuvable %s\n", $arguments['doc_id']);
            return;
        }

        if(!$drev->hasDR()) {

            echo sprintf("ERROR;DRev pas repris depuis une DR %s\n", $arguments['doc_id']);
            return;
        }

        if(!$drev->validation) {
            echo sprintf("WARNING;DRev non validé\n", $arguments['doc_id']);
            return;
        }

        $drev_origin = clone $drev;
        if($drev->getCSV()) {
            $drev->updateCepageFromCSV($drev->getCSV());
        }
        $drev->declaration->cleanNode();

        foreach($drev->declaration->getProduitsCepage() as $hash => $produit) {
            if(!$drev_origin->exist($hash)) {
                echo sprintf("CORRECTION;%s;%s;%s\n", $drev->_id, $hash, "INEXISTANT");
                continue;
            }
            $diff = array_diff_assoc($produit->toArray(true, false), $drev_origin->get($hash)->toArray(true, false));

            if(count($diff) == 0) {

                continue;
            }

            foreach($diff as $dk => $dv) {
                echo sprintf("CORRECTION;%s;%s;%s;%s\n", $drev->_id, $hash, $dk, $dv);
            }
        }

        foreach($drev_origin->declaration->getProduitsCepage() as $hash => $produit) {
            if(!$drev->exist($hash)) {
                echo sprintf("CORRECTION;%s;%s;%s\n", $drev->_id, $hash, "NOUVELLE_INEXISTANT_ALORS_QUE_EXIST_ANCIENNE");
                continue;
            }
        }

        $drev->save();

    }
}
