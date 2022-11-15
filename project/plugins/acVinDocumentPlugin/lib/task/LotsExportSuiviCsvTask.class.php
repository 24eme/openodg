<?php

class LotsExportSuiviCsvTask extends sfBaseTask
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
    $this->name             = 'export-suivi-csv';
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

    $lots = array();
    foreach(MouvementLotView::getInstance()->getByStatut(Lot::STATUT_DEGUSTE)->rows as $lot) {
        $lots[$lot->value->declarant_identifiant.$lot->value->unique_id] = $lot->value;
    }

    echo "Organisme;Declarant Identifiant;Lot unique id;Type;Origine Type;Declarant Nom;Produit Libelle;Millesime;Volume;Origine Date;1ere Degustation Date;1ere Degustation Statut;1ere Degustation Statut libelle;2eme Degustation Date;2eme Degustation Statut;2eme Degustation Statut Libelle;Issue Date;Issue Statut;Issue Statut libelle\n";

    foreach($lots as $lot) {
        $suivi = LotsClient::getInstance()->getSuivi($lot->declarant_identifiant, $lot->unique_id);

        if(!$suivi) {
            continue;
        }
        echo $appName.";".$lot->declarant_identifiant.";".$lot->unique_id.";".$suivi['ORIGINE']['TYPE'].";".$suivi['ORIGINE']['INITIAL_TYPE'].";".$lot->declarant_nom.";".$lot->produit_libelle.";".$lot->millesime.";".$lot->volume.";".$suivi['ORIGINE']['DATE'].";".$suivi['DEGUSTATION'][0]['DATE'].";".$suivi['DEGUSTATION'][0]['STATUT'].";".$suivi['DEGUSTATION'][0]['STATUT_LIBELLE'].";".$suivi['DEGUSTATION'][1]['DATE'].";".$suivi['DEGUSTATION'][1]['STATUT'].";".$suivi['DEGUSTATION'][1]['STATUT_LIBELLE'].";".$suivi['ISSUE']['DATE'].";".$suivi['ISSUE']['STATUT'].";".$suivi['ISSUE']['STATUT_LIBELLE']."\n";
    }
  }
}
