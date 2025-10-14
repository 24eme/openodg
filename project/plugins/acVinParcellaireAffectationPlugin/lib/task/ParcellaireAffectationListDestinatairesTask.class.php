<?php

class ParcellaireAffectationListDestinataires extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne"),
            new sfCommandArgument('validation', sfCommandArgument::OPTIONAL, "Document validé uniquement", true),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'affectations';
        $this->name = 'liste-destinataires';
        $this->briefDescription = "Liste les destinataires d'une affectation";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $results = DeclarationTousView::getInstance()->getByTypeCampagne("Affectation", $arguments['campagne']);

        foreach($results->rows as $row) {
            try {
                $doc = DeclarationClient::getInstance()->find($row->id);

                if (! $doc->getValidationOdg()) {
                    continue;
                }

                $doc->checkDestinatairesAreSet();

                echo PHP_EOL;
            } catch (Exception $e) {
                echo $e->getMessage().PHP_EOL;
            }
        }
    }
}
