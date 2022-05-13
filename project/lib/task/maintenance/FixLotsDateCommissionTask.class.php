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

        $mouvements = LotsClient::getInstance()->getHistory($doc->identifiant, $doc->changement_origine_lot_unique_id);
        $ordre = array();
        $degust = null;
        foreach($mouvements as $m) {
            if (strpos($m->id, 'DEGUSTATION') !== false) {
                $degust = $m->id;
            }
            $ordre[explode(' ', $m->value->date)[0]] = $m->id;
        }

        if ($degust) {
            $doc->changement_origine_id_document = $degust;
        }

        $last = array_pop($ordre);

        if($lotOrigine) {
            $doc->changement_date_commission = $lotOrigine->date_commission;
            if ( $last == $doc->_id && ('20220401' - str_replace('-', '', $doc->validation_odg)) > 10000) {
                $doc->changement_affectable = false;
            }
        }

        if ( $last == $doc->_id && ('20220401' - str_replace('-', '', $doc->validation_odg)) > 10000) {
            $doc->origine_affectable = false;
        }

        if($doc->changement_specificite == "UNDEFINED") {
            $doc->changement_specificite = "";
        }

        $doc->updateStatut();
        $doc->generateLots();

       try {
           if($doc->save()) {
               echo $doc->_id.": saved\n";
           }
       } catch(Error $e) {
           echo $doc->_id.": error\n";
       }
    }
  }
}