<?php

class DRevSauvegardeTask extends sfBaseTask
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

        $this->namespace = 'drev';
        $this->name = 'sauvegarde';
        $this->briefDescription = "Sauvegarde de la DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();


        $drev = DRevClient::getInstance()->find($arguments['doc_id']);

        if(!$drev) {
            echo sprintf("ERROR;DRev introuvable %s\n", $arguments['doc_id']);
            return;
        }

        if(!$drev->validation) {
        	echo sprintf("WARNING;La DREV n'est pas validée;%s\n", $drev->_id);
        	return;
        }
        
        if ($drev->isSauvegarde()) {
        	return;
        }
        
        if(DRevClient::getInstance()->find($arguments['doc_id'].'0')) {
        	echo sprintf("WARNING;La DREV est déjà sauvegardée;%s\n", $drev->_id);
        	return;
		}
		
        $sauvegarde = clone $drev;
        
        $sauvegarde->remove('mouvements');
        $sauvegarde->add('mouvements');
        
        if ($sauvegarde->exist('_attachments')) {
        	$sauvegarde->remove('_attachments');
        }
        
        $sauvegarde->setComplementId(0);
        echo $arguments['doc_id']."\n";
        $sauvegarde->save();
        
        if ($drev->exist('_attachments')) {
        	$sauvegarde->add('_attachments');
        
	        umask(0002);
	        $cache_dir = sfConfig::get('sf_cache_dir') . '/dr';
	        if (!file_exists($cache_dir)) {
	        	mkdir($cache_dir);
	        }
	        file_put_contents($cache_dir . "/DR.csv", $drev->getAttachmentUri('DR.csv'));
	        $sauvegarde->storeAttachment($cache_dir . "/DR.csv", "text/csv");
	        
	        file_put_contents($cache_dir . "/DR.pdf", $drev->getAttachmentUri('DR.pdf'));
	        $sauvegarde->storeAttachment($cache_dir . "/DR.pdf", "application/pdf");
        
        }
        
        
        echo sprintf("SUCCESS;La DRev a bien été sauvegardée;%s\n", $drev->_id);
    }
}
