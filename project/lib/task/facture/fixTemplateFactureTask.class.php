<?php

class fixTemplateFactureTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'templatefacture';
        $this->briefDescription = "";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $t = TemplateFactureClient::getInstance()->find($arguments['doc_id'], acCouchdbClient::HYDRATE_JSON);

        foreach($t->cotisations as $key => $c) {
            if($c->modele == "Cotisation") {
                $c->modele = "CotisationsCollection";
            }

            if($c->modele == "CotisationSelection") {
                $c->modele = "CotisationsCollectionSelection";
            }

            if($c->modele == "CotisationCondition") {
                $c->modele = "CotisationsCollectionCondition";
            }

            foreach($c->details as $detail) {
                if($key == "syndicat_viticole" && $detail->modele == "CotisationIntervalles") {
                    $detail->modele = "CotisationIntervallesFixe";
                }
                if($key == "syndicat_viticole" && $detail->modele == "CotisationTranche") {
                    $detail->modele = "CotisationTrancheFixe";
                }
                if($key == "syndicat_viticole" && $detail->modele == "CotisationFacture") {
                    $detail->modele = "CotisationCotisationFixe";
                }
            }
        }

        TemplateFactureClient::getInstance()->storeDoc($t);
    }
}
