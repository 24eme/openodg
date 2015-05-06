<?php

class FacturationGenerationTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('template_id', sfCommandArgument::REQUIRED, "Template doc id"),
            new sfCommandArgument('compte_id', sfCommandArgument::REQUIRED, "Compte doc id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'facturation';
        $this->name = 'generation';
        $this->briefDescription = "Génére les factures pour un modèle de template";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $template = TemplateFactureClient::getInstance()->find($arguments['template_id']);
    
        if(!$template) {

            throw new sfException(sprintf("Template %s not found", $arguments['template_id']));
        }

        $generation = FactureClient::getInstance()->createFactureByCompte($template, $arguments['compte_id']);
        $generation->save();
    }
}