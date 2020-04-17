<?php

class exportFactureTask extends sfBaseTask
{
    protected function configure()
    {
      // // add your own arguments here
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
        ));

        $this->namespace        = 'export';
        $this->name             = 'facture';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [testFacture|INFO] task does things.
Call it with:

    [php symfony export:facture|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if(!$options['application']){
          throw new sfException("Le choix de l'application est obligatoire");

        }
        $app = $options['application'];
        $classExportFactureCsv = 'ExportFactureCSV_'.$app;

        echo $classExportFactureCsv::getHeaderCsv();
        $all_factures = acCouchdbManager::getClient()
                    ->startkey(array("Facture"))
                    ->endkey(array("Facture", array()))
                    ->reduce(false)
                    ->getView('declaration', 'export')->rows;
        foreach($all_factures as $vfacture) {

          $facture = FactureClient::getInstance()->find($vfacture->id);
          if(!$facture) {
              throw new sfException(sprintf("Document %s introuvable", $vfacture->key[FactureEtablissementView::KEYS_FACTURE_ID]));
          }
          $export = new $classExportFactureCsv($facture, false);
          echo $export->exportFacture();
        }
    }
}
