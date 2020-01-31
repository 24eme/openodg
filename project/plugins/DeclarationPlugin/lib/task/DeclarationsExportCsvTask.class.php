<?php

class DeclarationsExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('type', sfCommandArgument::REQUIRED, "Type du document"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne"),
            new sfCommandArgument('validation', sfCommandArgument::OPTIONAL, "Document validé uniquement", true),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
            new sfCommandOption('sleep_second', null, sfCommandOption::PARAMETER_REQUIRED, 'secont to wait', false),
            new sfCommandOption('sleep_step', null, sfCommandOption::PARAMETER_REQUIRED, 'nb doc before wait', false),
            new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, "region de l'ODG (si non renseignée toutes les régions sont utilisées)", null),
        ));

        $this->namespace = 'declarations';
        $this->name = 'export-csv';
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
            $className = DeclarationClient::getInstance()->getExportCsvClassName($arguments['type']);
            echo $className::getHeaderCsv();
        }

        $ids = DeclarationClient::getInstance()->getIds($arguments['type'], $arguments['campagne']);

        $sleepSecond = false;
        if($options['sleep_second']) {
            $sleepSecond = $options['sleep_second']*1;
        }

        $sleepStep = false;
        if($options['sleep_step']) {
            $sleepStep = $options['sleep_step']*1;
        }

        $step = 0;

        $region = null;
        if($options["region"]) {
            $region = $options['region'];
        }

        foreach($ids as $id) {
            $doc = null;
            try{
              $doc = DeclarationClient::getInstance()->find($id);
            }catch(sfException $e){
              continue;
            }
            $export = DeclarationClient::getInstance()->getExportCsvObject($doc, false, $region);

            if($arguments['validation'] && $doc->exist('validation') && !$doc->validation) {
                continue;
            }

            if(method_exists($doc, "isExcluExportCsv") && $doc->isExcluExportCsv()) {
                continue;
            }

            echo $export->export();
            $step++;
            if($sleepStep && $sleepSecond && $step > $sleepStep) {
                sleep($sleepSecond);
                $step = 0;
            }
        }


    }
}
