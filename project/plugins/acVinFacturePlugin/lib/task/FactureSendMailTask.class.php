<?php

class FactureSendMailTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
			    new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'Document id de la facture'),
    ));

    $this->addOptions(array(
			    new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
			    new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
			    new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'facture';
    $this->name             = 'send-mail';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [generatePDF|INFO] task does things.
Call it with:

  [php symfony generatePDF|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $routing = clone ProjectConfiguration::getAppRouting();
    $context = sfContext::createInstance($this->configuration);
    $context->set('routing', $routing);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $facture = FactureClient::getInstance()->find($arguments['doc_id']);
    if(!$facture) {
        echo $arguments['doc_id'].";ERROR;Doc non trouvé\n";
        return;
    }

    try {
        $resultat = FactureEmailManager::getInstance()->send($facture);
    } catch(Exception $e) {
        echo $arguments['doc_id'].";ERROR;".$e->getMessage()."\n";
        return;
    }

    if(!$resultat) {
        echo $arguments['doc_id'].";ERROR;Mail non envoyé\n";
        return;
    }

    echo $arguments['doc_id'].";SUCCESS\n";
  }
}
