<?php

class FactureSetexportedTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
			    new sfCommandArgument('factureid', null, sfCommandOption::PARAMETER_REQUIRED, 'Facture id'),
    ));

    $this->addOptions(array(
			    new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
			    new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
			    new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
			    new sfCommandOption('directory', null, sfCommandOption::PARAMETER_REQUIRED, 'Output directory', '.'),
          new sfCommandOption('deversement', null, sfCommandOption::PARAMETER_REQUIRED, 'Déversement', false),
      // add your own options here
    ));

    $this->namespace        = 'facture';
    $this->name             = 'setexported';
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
    $paiement = false;
    if(preg_match('/PAIEMENT-/', $arguments['factureid'])) {
        $paiement = true;
    }

    $id = str_replace("PAIEMENT-", "FACTURE-", $arguments['factureid']);
    $facture = FactureClient::getInstance()->find($id);
    if(!$options['deversement']) {
      $facture->setVerseEnCompta($paiement);
    } else {
      $facture->setDeVerseEnCompta($paiement);
    }
    $facture->save();
  }
}
