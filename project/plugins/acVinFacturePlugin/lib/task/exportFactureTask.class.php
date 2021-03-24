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
            new sfCommandOption('factureid', null, sfCommandOption::PARAMETER_REQUIRED, 'L\'id de la facture', null),
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
        $ids = array();
        if (!$options['factureid']) {
            $all_factures = acCouchdbManager::getClient()
                    ->startkey(array("Facture"))
                    ->endkey(array("Facture", array()))
                    ->reduce(false)
                    ->getView('declaration', 'export')->rows;
            foreach($all_factures as $vfacture) {
                $ids[] = $vfacture->id;
            }
        }else{
            $ids[] = $options['factureid'];
        }
        foreach($ids as $id) {
          $facture = FactureClient::getInstance()->find($id);
          if(!$facture) {
              throw new sfException(sprintf("Document %s introuvable", $id));
          }
          $export = new $classExportFactureCsv($facture, false);
          echo $export->exportFacture();
        }
    }
}
