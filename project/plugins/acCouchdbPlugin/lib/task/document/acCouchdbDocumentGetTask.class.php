<?php

class acCouchdbDocumentGetTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'ID du document'),
       new sfCommandArgument('doc_revision', sfCommandArgument::OPTIONAL, 'Revision'),
    ));

    $this->addOptions(array(
      new sfCommandOption('hash', null, sfCommandOption::PARAMETER_REQUIRED, 'Hash', null),
      new sfCommandOption('format', null, sfCommandOption::PARAMETER_REQUIRED, 'Format of return (json, php, flatten)', 'json'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'document';
    $this->name             = 'get';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [maintenanceCompteStatut|INFO] task does things.
Call it with:

  [php symfony maintenanceCompteStatut|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $hydrate = acCouchdbClient::HYDRATE_JSON;
    if(isset($options['hash']) && $options['hash']) {
        $hydrate = acCouchdbClient::HYDRATE_DOCUMENT;
    }

    if(isset($arguments['doc_revision'])) {
      $doc = acCouchdbManager::getClient()->rev($arguments['doc_revision'])->find($arguments['doc_id'], $hydrate);
    } else {
      $doc = acCouchdbManager::getClient()->find($arguments['doc_id'], $hydrate);
    }

    if(isset($options['hash']) && $options['hash']) {
        $hashes = json_decode($options['hash']);
        if (!$hashes) {
            $hashes = array($options['hash']);
        }
        $values = array();
        foreach($hashes as $h) {
            $value = $doc->get($h, acCouchdbClient::HYDRATE_JSON);
            $values[] = $value;
        }
        $doc = $values;
    }

    if($options['format'] == "json") {
      echo json_encode($doc)."\n";
      return ;
    }

    if($options['format'] == "php") {
      return print_r($doc);
    }

    if($options['format'] == "flatten") {
      return print_r(acCouchdbToolsJson::json2FlatenArray($doc));
    }
  }
}
