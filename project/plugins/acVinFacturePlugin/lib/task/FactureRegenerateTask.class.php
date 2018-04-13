<?php

class FactureRegenerateTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addArguments(array(
	    new sfCommandArgument('doc_id', null, sfCommandOption::PARAMETER_REQUIRED, 'Facture id'),
    ));

    $this->addOptions(array(
	    new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
	    new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
	    new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
    ));

    $this->namespace        = 'facture';
    $this->name             = 'regenerate';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [generatePDF|INFO] task does things.
Call it with:

  [php symfony generatePDF|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $f = FactureClient::getInstance()->find($arguments['doc_id']);

    if(!$f) {
        return;
    }

    $fRegenere = FactureClient::getInstance()->regenerate($f);
    $fRegenere->save();

    echo "Facture ".$fRegenere->_id." regénéré @".$f->_rev." -> @".$fRegenere->_rev."\n";

  }
}
