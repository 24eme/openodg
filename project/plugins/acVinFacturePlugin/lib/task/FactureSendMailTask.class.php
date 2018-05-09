<?php

class FactureSendMailTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
			    new sfCommandArgument('compte_id', sfCommandArgument::REQUIRED, 'Compte to send mail'),
			    new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, 'Campagne de facturation'),
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

    $compte = CompteClient::getInstance()->find($arguments['compte_id']);

    if(!$compte) {
        echo $arguments['compte_id'].";ERROR;Compte non trouvé\n";
        return;
    }

    if(!$compte->email) {
        return;
    }

    try {
        $message = FactureEmailManager::getInstance()->send($compte, $arguments['campagne']);
    } catch(Exception $e) {
        echo $arguments['compte_id'].";ERROR;".$e->getMessage()."\n";
        return;
    }

    if($message === false) {
        return;
    }

    if(!$message) {
        echo $arguments['compte_id'].";ERROR;Mail non envoyé\n";
        return;
    }

    echo $arguments['compte_id'].";".$compte->email.";SUCCESS\n";

    @mkdir(sfConfig::get('sf_log_dir')."/mails_factures");
    file_put_contents(sfConfig::get('sf_log_dir')."/mails_factures/".date('YmdHis')."_".$compte->_id.".eml", $message->toString());
  }
}
