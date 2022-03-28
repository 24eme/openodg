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
        $ids = array_merge(
            DeclarationClient::getInstance()->getIds(DRevClient::TYPE_MODEL),
            DeclarationClient::getInstance()->getIds(ConditionnementClient::TYPE_MODEL),
            DeclarationClient::getInstance()->getIds(TransactionClient::TYPE_MODEL)
        );
    }

    rsort($ids);

    $ids_master = array();
    foreach($ids as $id) {
        $key = preg_replace('/-M[0-9]+$/', '', $id);

        if(array_key_exists($key, $ids_master)) {
            continue;
        }

        $ids_master[$key] = $id;
    }

    $numDossierDateCommission = array();

    foreach($ids_master as $id) {
        $doc = DeclarationClient::getInstance()->find($id);
        foreach($doc->getLots() as $lot) {
            $key = $lot->campagne."_".$lot->numero_dossier;
            if(!$lot->date_commission) {
                continue;
            }
            if(isset($numDossierDateCommission[$key]) && $numDossierDateCommission[$key] === false) {
                continue;
            }
            if(isset($numDossierDateCommission[$key]) && $numDossierDateCommission[$key] != $lot->date_commission) {
                $numDossierDateCommission[$key] = false;
            }
            $numDossierDateCommission[$key] = $lot->date_commission;
        }
    }

    ksort($numDossierDateCommission);

    $keyPrevious = null;
    $campagnePrevious = null;
    $dossierPrevious  = null;
    $keyCurrent = null;
    $campagneCurrent = null;
    $dossierCurrent = null;
    foreach($numDossierDateCommission as $key => $date_commission) {
        $campagneCurrent = explode("_", $key)[0];
        $dossierCurrent = explode("_", $key)[1];
        $keyCurrent = $key;

        if($campagneCurrent != $campagnePrevious || $numDossierDateCommission[$keyPrevious] != $date_commission) {
                $keyPrevious = $keyCurrent;
                $campagnePrevious = $campagneCurrent;
                $dossierPrevious = $dossierCurrent;
                continue;
        }

        for($i=$dossierPrevious*1+1; $i < $dossierCurrent*1; $i++) {
                $numDossierDateCommission[$campagneCurrent."_".sprintf("%05d", $i)] = $date_commission;
        }

        $keyPrevious = $keyCurrent;
        $campagnePrevious = $campagneCurrent;
        $dossierPrevious = $dossierCurrent;
    }

    ksort($numDossierDateCommission);

    foreach($ids_master as $id) {
        $doc = DeclarationClient::getInstance()->find($id);
        foreach($doc->getLots() as $lot) {
            if(!$doc->validation && $lot->isCurrent()) {
                continue;
            }
            if(!isset($numDossierDateCommission[$lot->campagne."_".$lot->numero_dossier])) {
                echo "aucune date trouvé : ".$lot->campagne."_".$lot->numero_dossier."\n";
                continue;
            }
            if($lot->getDateCommission()) {
                continue;
            }
            $lotOrigine = $lot->getLotOrigine();
            if($lotOrigine->getDateCommission()) {
                continue;
            }
            $docOrigine = $lotOrigine->getDocument();
            $lotOrigine->date_commission = $numDossierDateCommission[$lot->campagne."_".$lot->numero_dossier];
            if($docOrigine->save()) {
                echo "Document ".$docOrigine->_id."@".$docOrigine->_rev." saved\n";
            }

        }
    }
  }
}