<?php

class DrevPDFTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('drev_id', sfCommandArgument::REQUIRED, "Id de la DRev"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'pdf';
        $this->briefDescription = "Genere le pdf d'une DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        $drev = DRevClient::getInstance()->find($arguments['drev_id']);

        if(!$drev) {
            die("DREV ".$arguments['drev_id']." non trouvée");
        }
        $region = null;
        $document = new ExportDRevPdf($drev, $region, 'pdf', false);
        $document->setPartialFunction(array($this, 'getPartial'));
        $document->generate();
        echo "DREV;".$drev->campagne.";".$drev->identifiant.";".$drev->declarant->cvi.";".$drev->_id.";".$document->getFile()."\n";
    }

    public function getPartial($templateName, $vars = null) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $vars = null !== $vars ? $vars : $this->varHolder->getAll();

        return get_partial($templateName, $vars);
    }

}
