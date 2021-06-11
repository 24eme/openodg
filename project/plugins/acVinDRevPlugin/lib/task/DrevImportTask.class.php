<?php

class DrevImportTask extends sfBaseTask
{

    protected $byLots = false;
    protected $configurationProduits = array();
    protected $csv = null;
    protected static $destinationsTypes = array("VRAC_FRANCE_ET_CONDITIONNEMENT" => DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_CONDITIONNEMENT,
                                           "VRAC_FRANCE_ET_VRAC_EXPORT" => DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_VRAC_EXPORT,
                                           "VRAC_EXPORT_VRAC_FRANCE_ET_CONDITIONNEMENT" => DRevClient::LOT_DESTINATION_VRAC_FRANCE_VRAC_EXPORT_CONDITIONNEMENT);

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "CSV de la DRev"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('byLots', null, sfCommandOption::PARAMETER_OPTIONAL, 'Import de la DRev avec un ensemble de lots', false),
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

        $this->byLots = $options['byLots'];

        $this->configurationProduits = ConfigurationClient::getInstance()->getConfiguration()->getProduits();


        $csvFile = new CsvFile($arguments['csv']);
        $this->csv = $csvFile->getCsv();
        $cvis = array();
        foreach($this->csv as $ligne => $data) {
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
                if($cvi == "CVI Opérateur"){
                    continue;
                }

                $campagne = $cviParts[1];

                $etablissement = EtablissementClient::getInstance()->findByCvi($cvi,true);

                if(!$etablissement) {
                    echo "DREV;ERREUR;$cvi;cvi non trouvé\n";

                    continue;
                }

                if(is_array($etablissement) && count($etablissement) > 1) {
                    echo "DREV;ERREUR;$type;$cvi;plusieurs établissements ont ce cvi\n";

                    continue;
                }
                if($etablissement->isSuspendu()){
                  echo "DREV;ERREUR;$cvi;cvi opérateur archivé, pas de reprise\n";
                  continue;
                }

                $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($etablissement->identifiant, $campagne);
                $hash = "/declaration/certifications/".$data[ExportDRevCSV::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[ExportDRevCSV::CSV_PRODUIT_GENRE]."/appellations/".$data[ExportDRevCSV::CSV_PRODUIT_APPELLATION]."/mentions/".$data[ExportDRevCSV::CSV_PRODUIT_MENTION]."/lieux/".$data[ExportDRevCSV::CSV_PRODUIT_LIEU]."/couleurs/".$data[ExportDRevCSV::CSV_PRODUIT_COULEUR]."/cepages/".$data[ExportDRevCSV::CSV_PRODUIT_CEPAGE];

                if($this->byLots){

                    $this->importDRevByLots($drev,  $lignes, $etablissement->identifiant, $campagne);

                }else{

                if($drev) {
                    continue;
                }
                $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne, false, false);
                if(!$drev->getDocumentDouanier()) {
                    echo "ERREUR;$etablissement->_id ($etablissement->cvi);pas de document douanier\n";
                    continue;
                }
                foreach($lignes as $ligne) {
                    $data = $this->csv[$ligne];

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

                echo "IMPORTE;$drev->_id;".Organisme::getInstance()->getUrl()."/drev/visualisation/".$drev->_id."\n";
            }

        }
    }

    protected function importDRevByLots($drev, $lignes, $identifiant, $campagne){
        if(!$drev) {
            $drev = DRevClient::getInstance()->createDoc($identifiant, $campagne, false, false);
            $drev->cleanLots();
        }else{
            $drev = $drev->getDocument()->generateModificative();
        }

        $lotsAdded = false;

        $volumesbyCouleur = array();
        foreach ($lignes as $ligne) {
            $data = $this->csv[$ligne];
            $couleur = $data[ExportDRevCSV::CSV_PRODUIT_COULEUR];
            if(!isset($volumesbyCouleur[$couleur])){
                $volumesbyCouleur[$couleur] = 0.0;
            }
            $volumesbyCouleur[$couleur] = $volumesbyCouleur[$couleur] + floatval(str_replace(",", ".",trim($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE])));
        }

        foreach($lignes as $ligne) {
            $data = $this->csv[$ligne];
            if(!trim($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]) || !trim($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG])){
                $cvi = $data[ExportDRevCSV::CSV_CVI];
                $campagne = $data[ExportDRevCSV::CSV_CAMPAGNE];
                echo "ERREUR;$cvi;$campagne;pas d'import, pas de validation declarant ou odg\n";
            }
            $volume = trim($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE]);
            $numero_cuve = trim($data[ExportDRevCSV::CSV_LOT_NUMERO_CUVE]);
            $type_destination = self::$destinationsTypes[preg_replace("/([A-Z_]+).+/","$1",$data[ExportDRevCSV::CSV_LOT_DESTINATION])];
            $date_destination = preg_replace("/([A-Z_]* )?([0-9\/]+)/","$2",$data[ExportDRevCSV::CSV_LOT_DESTINATION]);
            $code_inao = trim($data[ExportDRevCSV::CSV_PRODUIT_INAO]);
            $produit_line = null;

            foreach ($this->configurationProduits as $key => $produit) {
                if($produit->getCodeDouane() == $code_inao){
                    $produit_line = $produit;
                    break;
                }
            }
            if(!$code_inao || !$produit_line){
                echo "DREV;ERREUR;$code_inao;$produit_line;Produit non trouvé\n";
                continue;
            }


            if($volume){
                if($this->isLotInDrev($drev, $volume, $volumesbyCouleur)){
                    $libelleProduit = $produit_line->getLibelleComplet();
                    echo "WARNING;PAS D'IMPORT lot existe : $drev->_id;$campagne;$libelleProduit;$volume;$numero_cuve;$type_destination;$date_destination\n";
                    continue;
                }
                $lot = $drev->addLot();
                $lot->date = trim($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);
                $lot->numero_logement_operateur = $numero_cuve;
                $lot->destination_type = $type_destination;
                $lot->destination_date = $date_destination;
                $lot->volume = $this->formatFloat($volume);
                $lot->produit_hash = $produit_line->getHash();
                $libelleProduit = $produit_line->getLibelle();
                $lotsAdded = true;
                echo "Ajout d'un lot;$drev->_id;$libelleProduit;$volume;$numero_cuve;$type_destination;$date_destination\n";
            }
        }
        $papier = ($data[ExportDRevCSV::CSV_TYPE_DREV] != "TELEDECLARATION");
        if($papier){
            $drev->add('papier', 1);
        }
        $dateValidation = null;
        if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]){
            $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);
            $dateValidation = $dt->modify('+1 minute')->format('c');
        }
        $dateValidation = null;
        if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]){
            $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]);
            $dateValidation = $dt->modify('+1 minute')->format('c');
        }

        if($lotsAdded){
            $drev->validate($dateValidation);
            $drev->validateOdg($dateValidation);
            $drev->save();
            echo "IMPORTE;$drev->_id;".Organisme::getInstance()->getUrl()."/drev/visualisation/".$drev->_id."\n";
        }
    }

    protected function isLotInDrev($drev, $volume, $volumesbyCouleur){

        // Check si le Volume est le même que celui d'un autre Lot
        foreach ($drev->getLots() as $lot) {
            $sameVolume = ($this->formatFloat($volume) == $lot->getVolume());

            if($sameVolume){
                return true;
            }
        }

        // Check le Volume couleur est le même que celui de l'ensemble de la couleur de la Drev existante
        foreach ($drev->getLotsByCouleur() as $couleur => $lots) {
            $somme = 0.0;
            foreach ($lots as $lot) {
                $somme += $lot->volume;
            }
            $couleurProduitDrev = $lot->getConfig()->getCouleur()->getKey();

            if($volumesbyCouleur[$couleurProduitDrev] == $somme){
                return true;
            }
        }

        return false;
    }

    protected function formatFloat($value) {

        return str_replace(',', '.', $value)*1.0;
    }
}
