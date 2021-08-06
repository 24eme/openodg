<?php

class DocumentPDFTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('document_id', sfCommandArgument::REQUIRED, "Id du document"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'document';
        $this->name = 'pdf';
        $this->briefDescription = "Genere le pdf d'un document";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        $doc = acCouchdbManager::getClient()->find($arguments['document_id']);

        if(!$doc) {
            die($arguments['document_id']." non trouvée");
        }
        $export_class = 'Export'.$doc->type.'Pdf';
        $document = new $export_class($doc, 'pdf', false);
        $document->setPartialFunction(array($this, 'getPartial'));
        $document->generate();
        echo $doc->type.";".$doc->campagne.";".$doc->identifiant.";".$doc->declarant->cvi.";".$doc->_id.";".$document->getFile()."\n";
    }

    public function getPartial($templateName, $vars = null) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $vars = null !== $vars ? $vars : $this->varHolder->getAll();

        return get_partial($templateName, $vars);
    }

}
