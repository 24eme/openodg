<?php

class DeclarationsLotsExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
            new sfCommandOption('sleep_second', null, sfCommandOption::PARAMETER_REQUIRED, 'secont to wait', false),
            new sfCommandOption('sleep_step', null, sfCommandOption::PARAMETER_REQUIRED, 'nb doc before wait', 1000),
            new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, "region de l'ODG (si non renseignée toutes les régions sont utilisées)", null),
        ));

        $this->namespace = 'declarations';
        $this->name = 'lots-export-csv';
        $this->briefDescription = "Export CSV d'une declaration";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if($options["header"]) {
            echo ExportDeclarationLotsCSV::getHeaderCsv();
        }

        $ids = array_merge(
            DeclarationClient::getInstance()->getIds(DRevClient::TYPE_MODEL),
            DeclarationClient::getInstance()->getIds(ConditionnementClient::TYPE_MODEL),
            DeclarationClient::getInstance()->getIds(TransactionClient::TYPE_MODEL)
        );

        $sleepSecond = false;
        if($options['sleep_second']) {
            $sleepSecond = $options['sleep_second']*1;
        }
        $sleepStep = $options['sleep_step']*1;
        $step = 0;

        $region = $options['region'];

        foreach($ids as $id) {
            $tobeexported = true;
            while ($tobeexported) {
                try {
                    $doc = null;
                    try{
                      $doc = DeclarationClient::getInstance()->find($id);
                    }catch(sfException $e){
                      continue 2;
                    }
                    $export = new ExportDeclarationLotsCSV($doc, false, $region);

                    if(!$doc->validation) {
                        continue 2;
                    }

                    echo $export->export();

                }catch(InvalidArgumentException $e) {
                    sleep(60);
                    continue;
                }

                $tobeexported = false;

            }
            $step++;
            if($sleepStep && $sleepSecond && $step > $sleepStep) {
                sleep($sleepSecond);
                $step = 0;
            }
        }
    }
}
