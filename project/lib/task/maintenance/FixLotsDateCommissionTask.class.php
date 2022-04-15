<?php

class FixLotsDateCommissionTask extends sfBaseTask
{
  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      new sfCommandOption('doc_id', null, sfCommandOption::PARAMETER_OPTIONAL, "Permet de lancer la tâche pour un doc", null),
    ));

    $this->namespace        = 'fix';
    $this->name             = 'lots-date-comission';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [fix:lots-date-comission|INFO] task does things.
Call it with:

  [php symfony fix:lots-date-comission|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if(isset($options['doc_id']) && $options['doc_id']) {
        $ids = [$options['doc_id']];
    }

    if(!isset($ids)) {
        $ids = DeclarationClient::getInstance()->getIds(ChgtDenomClient::TYPE_MODEL);
    }

    rsort($ids);

    foreach($ids as $id) {
        $doc = DeclarationClient::getInstance()->find($id);
        $lotOrigine = $doc->getLotOrigine();
        if($lotOrigine) {
            $doc->changement_date_commission = $lotOrigine->date_commission;
        }

        if($doc->changement_specificite == "UNDEFINED") {
            $doc->changement_specificite = "";
        }

        if($doc->save()) {
            echo $doc->_id.": saved\n";
        }
    }
  }
}