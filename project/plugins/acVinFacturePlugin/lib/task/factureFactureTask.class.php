<?php

class factureFactureTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'vinsdeloire'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('compteid', null, sfCommandOption::PARAMETER_REQUIRED, 'Compte id'),
      new sfCommandOption('templateid', null, sfCommandOption::PARAMETER_REQUIRED, 'Template id'),
      new sfCommandOption('date_facturation', null, sfCommandOption::PARAMETER_REQUIRED, 'Date de la facturation id'),
      // add your own options here
    ));

    $this->namespace        = 'facture';
    $this->name             = 'facturer';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [testFacture|INFO] task does things.
Call it with:

    [php symfony test:Facture|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $compte = CompteClient::getInstance()->find($options['compteid']);
    if(!$compte) {
        throw new sfException('existing compteid neeeded');
    }

    $template = TemplateFactureClient::getInstance()->find($options['templateid']);
    if(!$template) {
        throw new sfException('existing templateid neeeded');
    }
    if (!$options['date_facturation']) {
        throw new sfException('date_facturation neeeded');
    }
    $facture = FactureClient::getInstance()->createFactureByTemplate($template, $compte, $options['date_facturation']);
    $facture->save();
    echo $facture->_id." created\n";
  }
}
