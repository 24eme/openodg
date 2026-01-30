<?php

class DeclarationExportCsvTask extends sfBaseTask
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
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
            new sfCommandOption('no-warnings', null, sfCommandOption::PARAMETER_REQUIRED, 'do not print warnings', false),
            new sfCommandOption('filter-produit', null, sfCommandOption::PARAMETER_REQUIRED, "Produit de destination de l'export (pour les DR/SV11/SV12 de med)", ''),
            new sfCommandOption('bypass-exclude', null, sfCommandOption::PARAMETER_REQUIRED, "Bypass exclusion de document", false),
        ));

        $this->namespace = 'declaration';
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

        if (strpos($arguments['doc_id'], 'DOUANE') !== false) {
            $docid = $arguments['doc_id'];
            foreach (['DR', 'SV11', 'SV12'] as $t) {
                $docid_new = str_replace('DOUANE-', $t.'-', $docid);
                echo "$docid_new\n";
                $doc = DeclarationClient::getInstance()->find($docid_new);
                if ($doc) {
                    break;
                }
            }
        }else {
            $doc = DeclarationClient::getInstance()->find($arguments['doc_id']);
        }

        if(!$doc) {
            throw new sfException(sprintf("Document %s introuvable", $arguments['doc_id']));
        }

        if (!$options['no-warnings'] && $doc->exist('validation') && !$doc->validation) {
            fwrite(STDERR, "WARNING: document non validé, ne sera pas exporté via la tache d'export global\n");
        }

        if(!$options['no-warnings'] && !$options['bypass-exclude'] && method_exists($doc, "isExcluExportCsv") && $doc->isExcluExportCsv()) {
            fwrite(STDERR, "WARNING: document exclu de la tache d'export global (via ".get_class($doc)."::isExcluExportCsv())\n");
            return ;
        }

        if($options["header"]) {
            $className = DeclarationClient::getInstance()->getExportCsvClassName($doc->type);
            echo $className::getHeaderCsv();
        }

        $export = DeclarationClient::getInstance()->getExportCsvObject($doc, false);
        if ($options['filter-produit']) {
            $export->setExtraArgs(array('drev_produit_filter' => $options['filter-produit']));
        }
        echo $export->export();
    }
}