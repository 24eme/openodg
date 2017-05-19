<?php

class PieceGenerateForDocTask extends sfBaseTask
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

        $this->namespace = 'piece';
        $this->name = 'generate';
        $this->briefDescription = "Génère les pièces d'un document";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        $doc = acCouchdbManager::getClient()->find($arguments['doc_id']);
        if(!$doc) {
            echo sprintf("ERROR;Document introuvable %s\n", $arguments['doc_id']);
            return;
        }
        try {
        	$doc->generatePieces();
        	$doc->save();
        } catch (Exception $e) {
        	echo sprintf("ERROR;".$e->getMessage());
        	return;
        }
        echo sprintf("SUCCESS;Les pièces du document ont bien été générées;%s\n", $doc->_id);
    }
}
