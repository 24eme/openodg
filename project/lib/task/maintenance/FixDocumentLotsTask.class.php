<?php

class FixDocumentLotsTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
       new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, 'ID du document'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'fix';
    $this->name             = 'document-lots';
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

    $doc = acCouchdbManager::getClient()->find($arguments['doc_id']);

    if($doc->exist('changement_origine_lot_unique_id')) {
        $doc->campagne = null;
        $doc->getCampagne();
        $doc->changement_origine_lot_unique_id = preg_replace('/a+$/', '', $doc->changement_origine_lot_unique_id);
    }

    if($doc->exist('lots')) {
        foreach($doc->lots as $lot) {
            if(!$lot->unique_id) {
                continue;
            }
            if($doc->type == ChgtDenomClient::TYPE_MODEL) {
                $lot->campagne = $doc->campagne;
            }
            $date = $lot->date;
            $lot->date = $date;
            $lot->getDate();
            $lot->origine_type = null;
            $documentOrdreCalcule = $lot->getDocumentOrdreCalcule();
            if($documentOrdreCalcule != $lot->document_ordre) {
                echo $doc->_id.":".$lot->unique_id.";Le document d'ordre passe de ".$lot->document_ordre." à ".$documentOrdreCalcule."\n";
            }
            $lot->document_ordre = $documentOrdreCalcule;
            $lot->numero_archive = preg_replace('/a+$/', '', $lot->numero_archive);
        }
    }

    if($doc->save(false)) {
        echo "Document ".$doc->_id."@".$doc->_rev." saved\n";
    }
  }
}