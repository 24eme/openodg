<?php

class DrevImportTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV de la DRev"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'import';
        $this->briefDescription = "Import de la DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if(!file_exists($arguments['csv'])) {
            //echo sprintf("ERROR;Le fichier CSV n'existe pas;%s\n", $arguments['doc_id']);

            //return;
        }

        $csvFile = new CsvFile($arguments['csv']);
        $csv = $csvFile->getCsv();
        $cvis = array();
        foreach($csv as $ligne => $data) {
            if(!$data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]) {
                continue;
            }
            $cvi = $data[ExportDRevCSV::CSV_CVI];
            $campagne = $data[ExportDRevCSV::CSV_CAMPAGNE];
            $cvis[$cvi."_".$campagne][] = $ligne;
        }

        foreach($cvis as $cviCampagne => $lignes) {
                $cviParts = explode('_', $cviCampagne);
                $cvi = $cviParts[0];
                $campagne = $cviParts[1];

                $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);

                if(!$etablissement) {
                    echo "DREV;ERREUR;$cvi;cvi non trouvé\n";

                    continue;
                }

                if(is_array($etablissement) && count($etablissement) > 1) {
                    echo "DREV;ERREUR;$type;$cvi;plusieurs établissements ont ce cvi\n";

                    continue;
                }
                if($etablissement->isSuspendu()){
                  echo "DREV;ERROR;$cvi;cvi opérateur archivé, pas de reprise\n";
                  continue;
                }
                $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($etablissement->identifiant, $campagne);

                if($drev) {
                    continue;
                }

                $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne, false, false);
                $drev->constructId();
                if(!$drev->getDocumentDouanier()) {
                    echo "ERROR;$etablissement->_id ($etablissement->cvi);pas de document douanier\n";
                    continue;
                }

                foreach($lignes as $ligne) {
                    $data = $csv[$ligne];
                    $hash = "/declaration/certifications/".$data[ExportDRevCSV::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[ExportDRevCSV::CSV_PRODUIT_GENRE]."/appellations/".$data[ExportDRevCSV::CSV_PRODUIT_APPELLATION]."/mentions/".$data[ExportDRevCSV::CSV_PRODUIT_MENTION]."/lieux/".$data[ExportDRevCSV::CSV_PRODUIT_LIEU]."/couleurs/".$data[ExportDRevCSV::CSV_PRODUIT_COULEUR]."/cepages/".$data[ExportDRevCSV::CSV_PRODUIT_CEPAGE];

                    if(!$drev->getConfiguration()->exist($hash)) {
                        continue;
                    }

                    $produit = $drev->addProduit($hash, $data[ExportDRevCSV::CSV_PRODUIT_DENOMINATION_COMPLEMENTAIRE]);
                    $produit->superficie_revendique += $this->formatFloat($data[ExportDRevCSV::CSV_SUPERFICIE_REVENDIQUE]);
                    $produit->volume_revendique_issu_recolte += $this->formatFloat($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE_ISSU_RECOLTE]);
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE_ISSU_VCI]) > 0 && $produit->exist('volume_revendique_issu_vci')) {
                        $produit->volume_revendique_issu_vci = $this->formatFloat($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE_ISSU_VCI]);
                    }
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE_ISSU_MUTAGE]) > 0 && $produit->exist('volume_revendique_issu_mutage')) {
                        $produit->volume_revendique_issu_mutage = $this->formatFloat($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE_ISSU_MUTAGE]);
                    }
                    $produit->vci->stock_precedent += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_PRECEDENT]);
                    $produit->vci->destruction += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_DESTRUCTION]);
                    $produit->vci->complement += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_COMPLEMENT]);
                    $produit->vci->substitution += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_SUBSTITUTION]);
                    $produit->vci->rafraichi += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_RAFRAICHI]);
                    $produit->vci->constitue += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_CONSTITUE]);
                }

                $drev->update();
                $dateValidation = null;
                if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]){
                      $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);
                      $dateValidation = $dt->modify('+1 minute')->format('c');
                }
                $drev->validate($dateValidation);
                $dateValidation = null;
                if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]){
                      $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]);
                      $dateValidation = $dt->modify('+1 minute')->format('c');
                }
                $drev->validateOdg($dateValidation);
                $drev->save();

                echo "IMPORTE;$drev->_id\n";
        }
    }

    protected function formatFloat($value) {

        return str_replace(',', '.', $value)*1.0;
    }
}
