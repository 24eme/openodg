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
            new sfCommandOption('sleep_step', null, sfCommandOption::PARAMETER_REQUIRED, 'nb doc before wait', 1000),
            new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, "region de l'ODG (si non renseignée toutes les régions sont utilisées)", null),
            new sfCommandOption('extra_fields', null, sfCommandOption::PARAMETER_REQUIRED, 'Add extra fields (organisme, doc id, item id, hash produit) in CSV', false),
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
        $sleepStep = $options['sleep_step']*1;
        $step = 0;

        $region = $options['region'];

        $extraFields = $options['extra_fields'];

        foreach($ids as $id) {
            $tobeexported = true;
            while ($tobeexported) {
                try {
                    $doc = null;
                    try{
                      $doc = DeclarationClient::getInstance()->find($id);
                      if(method_exists($doc,'getMaster') && $doc->getMaster()->_id != $doc->_id){
                        continue 2;
                      }
                    }catch(sfException $e){
                      continue 2;
                    }
                    $export = DeclarationClient::getInstance()->getExportCsvObject($doc, false, $region, $extraFields);

                    if($arguments['validation'] && $doc->exist('validation') && !$doc->validation) {
                        continue 2;
                    }

                    if(method_exists($doc, "isExcluExportCsv") && $doc->isExcluExportCsv()) {
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
