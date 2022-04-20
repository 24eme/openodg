<?php

class LotsExportHistoriqueCsvTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
      // add your own options here
    ));

    $this->namespace        = 'lots';
    $this->name             = 'export-historique-csv';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $context = sfContext::createInstance($this->configuration);

    $appName = $this->configuration->getApplication();

    echo "Origine;Id Opérateur;Nom Opérateur;Campagne;Date commission;Date lot;Num Dossier;Num Lot;Doc Ordre;Doc Type;Libellé du lot;Volume;Statut;Details;Organisme;Doc Id;Lot unique Id;Declarant Lot unique Id\n";

    foreach(MouvementLotHistoryView::getInstance()->getAllLotsWithHistorique()->rows as $lot) {
      $values = (array)$lot->value;
      $statut = (isset(Lot::$libellesStatuts[$values['statut']]))? Lot::$libellesStatuts[$values['statut']] : $values['statut'];
      $date = preg_split('/( |T)/', $values['date'], -1, PREG_SPLIT_NO_EMPTY);
      printf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
          $values['initial_type'],
          $values['declarant_identifiant'],
          VarManipulator::protectStrForCsv($values['declarant_nom']),
          $values['campagne'],
          (isset($date['date_commission']) && $date['date_commission']) ? $date['date_commission'] : $date[0],
          $date[0],
          $values['numero_dossier'],
          $values['numero_archive'],
          $values['document_ordre'],
          $values['document_type'],
          VarManipulator::protectStrForCsv($values['libelle']),
          VarManipulator::floatizeForCsv($values['volume']),
          VarManipulator::protectStrForCsv($statut),
          VarManipulator::protectStrForCsv($values['detail']),
          $appName,
          $values['document_id'],
          $values['lot_unique_id'],
          $values['declarant_identifiant'].'-'.$values['lot_unique_id']
      );
    }
  }
}
