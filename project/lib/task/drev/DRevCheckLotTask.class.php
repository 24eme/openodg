<?php

class DRevCheckLotTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'check-lot';
        $this->briefDescription = "Vérification des lots d'une DREV (cas des lots supprimés)";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $drev_id = $arguments['doc_id'];

        $drev = DRevClient::getInstance()->find($drev_id);

        if(!$drev) {
            echo sprintf("ERROR;DRev introuvable %s\n", $arguments['doc_id']);
            return;
        }
        $need_saving = false;
        for($i = count($drev->lots) - 1; $i >= 0 ; $i-- ) {
            $lots = LotsClient::getInstance()->getHistory($drev->identifiant, $drev->lots[$i]->unique_id);
            if (!count($lots)) {
                echo "$drev_id: lot $i sans doc origine\n";
                unset($drev->lots[$i]);
                $need_saving = true;
            }
        }
        if ($need_saving) {
            $drev->save();
        }
    }
}
