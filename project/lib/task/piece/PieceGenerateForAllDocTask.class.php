<?php

class PieceGenerateForAllDocTask extends sfBaseTask
{

    protected function configure()
    {

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'piece';
        $this->name = 'generate-all';
        $this->briefDescription = "Génère les pièces de tous les documents";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        $docs = acCouchdbManager::getClient()->getAllDocs();
        $ids = array();
        foreach ($docs->rows as $doc) {
        	if (preg_match('/^(DREV|FACTURE|CONSTATS|DEGUSTATION|PARCELLAIRE|TIRAGE)/', $doc->id)) {
        		$ids[] = $doc->id;
        	}
        }
        $nbdocs = count($ids);
        $i = 0;
        foreach ($ids as $id) {
        	$i++;
        	$doc = acCouchdbManager::getClient()->find($id);
        	$percent = round(($i/$nbdocs)*100,1);
        	if(!$doc) {
        		echo sprintf("ERROR;%01.1f;Document introuvable %s\n", $percent, $id);
        		continue;
        	}
        	if (!($doc instanceof InterfacePieceDocument)) {
        		echo sprintf("ERROR;%01.1f;Document non piecable %s\n", $percent, $id);
        		continue;
        	}
        	try {
        		$doc->generatePieces();
        		$doc->save();
        	} catch (Exception $e) {
        		echo sprintf("ERROR;$percent%;".$e->getMessage());
        		continue;
        	}
        	echo sprintf("SUCCESS;%01.1f;Les pièces du document ont bien été générées;%s\n", $percent, $doc->_id);
        }
    }
}
