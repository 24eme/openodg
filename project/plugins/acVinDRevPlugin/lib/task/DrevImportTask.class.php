<?php

class DrevImportTask extends sfBaseTask
{

    protected $byLots = false;
    protected $configurationProduits = array();
    protected $csv = null;
    protected static $destinationsTypes = array(
                                            "CONDITIONNEMENT" => DRevClient::LOT_DESTINATION_CONDITIONNEMENT,
                                            "VRAC_FRANCE" => DRevClient::LOT_DESTINATION_VRAC_FRANCE,
                                            "VRAC_EXPORT" => DRevClient::LOT_DESTINATION_VRAC_EXPORT,
                                            "VRAC_FRANCE_ET_CONDITIONNEMENT" => DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_CONDITIONNEMENT,
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
            if(!$data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]) {
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
                  echo "DREV;ERREUR;$cvi;cvi opérateur archivé, pas de reprise\n";
                  continue;
                }

                $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($etablissement->identifiant, $campagne);

                if($this->byLots){
                    $this->importDRevByLots($drev,  $lignes, $etablissement->identifiant, $campagne);
                    continue;
                }

                if($drev) {
                    continue;
                }

                $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne, false, false);
                if(!$drev->getDocumentDouanier()) {
                    echo "ERREUR;$etablissement->_id ($etablissement->cvi);pas de document douanier\n";
                    continue;
                }

                $drev->resetAndImportFromDocumentDouanier();
                foreach($drev->getProduits() as $produit) {
                    $produit->superficie_revendique = null;
                }

                foreach($lignes as $ligne) {
                    $data = $this->csv[$ligne];
                    $hash = "/declaration/certifications/".$data[ExportDRevCSV::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[ExportDRevCSV::CSV_PRODUIT_GENRE]."/appellations/".$data[ExportDRevCSV::CSV_PRODUIT_APPELLATION]."/mentions/".$data[ExportDRevCSV::CSV_PRODUIT_MENTION]."/lieux/".$data[ExportDRevCSV::CSV_PRODUIT_LIEU]."/couleurs/".$data[ExportDRevCSV::CSV_PRODUIT_COULEUR]."/cepages/".$data[ExportDRevCSV::CSV_PRODUIT_CEPAGE];

                    if(!$drev->getConfiguration()->exist($hash)) {
                        $code_inao = trim($data[ExportDRevCSV::CSV_PRODUIT_INAO]);
                        $produit_line = null;

                        foreach ($this->configurationProduits as $key => $produit) {
                            if($produit->getCodeDouane() == $code_inao){
                                $hash = $produit->getHash();
                                break;
                            }
                        }
                    }
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
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_PRECEDENT]) > 0) {
                        $produit->vci->stock_precedent += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_PRECEDENT]);
                    }
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_DESTRUCTION]) > 0) {
                        $produit->vci->destruction += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_DESTRUCTION]);
                    }
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_COMPLEMENT]) > 0) {
                        $produit->vci->complement += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_COMPLEMENT]);
                    }
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_SUBSTITUTION]) > 0) {
                        $produit->vci->substitution += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_SUBSTITUTION]);
                    }
                    if($this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_RAFRAICHI]) > 0) {
                        $produit->vci->rafraichi += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_RAFRAICHI]);
                    }
                    if(!$drev->hasDR() && $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_CONSTITUE]) > 0) {
                        $produit->vci->constitue += $this->formatFloat($data[ExportDRevCSV::CSV_VCI_STOCK_CONSTITUE]);
                    }
                }
                $drev->update();
                $dateValidation = null;
                if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]){
                      $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);
                      $dateValidation = $dt->modify('+1 minute')->format('c');
                }
                $drev->validate($dateValidation);
                $drev->save();
                if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]){
                      $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]);
                      $dateValidationODG = $dt->modify('+1 minute')->format('c');
                      $drev->validateOdg($dateValidation);
                }
                $drev->save();

                echo "IMPORTE;$drev->_id;".Organisme::getInstance()->getUrl()."/drev/visualisation/".$drev->_id."\n";

        }
    }

    protected function importDRevByLots($drev, $lignes, $identifiant, $campagne){
	    if($drev && !$drev->isModifiable()) {
            	echo "ERREUR;$drev->_id;la drev est en cours de saisie\n";
            	return;
            }

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

        $revision = $drev->_rev;

        foreach($lignes as $ligne) {
            $data = $this->csv[$ligne];
            if(!trim($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]) && !trim($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG])){
                $cvi = $data[ExportDRevCSV::CSV_CVI];
                $campagne = $data[ExportDRevCSV::CSV_CAMPAGNE];
                echo "ERREUR;$cvi;$campagne;pas d'import, pas de validation declarant et odg\n";
            }
            $volume = trim($data[ExportDRevCSV::CSV_VOLUME_REVENDIQUE]);
            $millesime = trim($data[ExportDRevCSV::CSV_LOT_MILLESIME]);
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


            if($this->formatFloat($volume)){
                if($this->isLotInDrev($drev, $data)){
                    $libelleProduit = $produit_line->getLibelleComplet();
                    //echo "WARNING;PAS D'IMPORT lot existe : $drev->_id;$campagne;$libelleProduit;$volume;$numero_cuve;$type_destination;$date_destination\n";
                    continue;
                }
                $lot = $drev->addLot();
                $lot->date = trim($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);
                $lot->numero_logement_operateur = $numero_cuve;
                $lot->destination_type = $type_destination;
                $lot->destination_date = $date_destination;
                $lot->affectable = false;
                $lot->volume = $this->formatFloat($volume);
                $lot->produit_hash = $produit_line->getHash();
                $lot->millesime = $millesime;
                $libelleProduit = $produit_line->getLibelle();
                $lotsAdded = true;
                echo "Ajout d'un lot;$drev->_id;$libelleProduit;$volume;$numero_cuve;$type_destination;$date_destination\n";
            }
        }
        $papier = ($data[ExportDRevCSV::CSV_TYPE_DREV] != "TELEDECLARATION");
        if($papier){
            $drev->add('papier', 1);
        }
        $dateValidationDeclarant = null;
        if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]){
            $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);
            $dateValidationDeclarant = $dt->modify('+1 minute')->format('c');
        }
        if ($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]){
            $dt = new DateTime($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]);
            $dateValidation = $dt->modify('+1 minute')->format('c');
        }

        if($lotsAdded || $revision != $drev->_rev) {
            $drev->validate($dateValidationDeclarant);
	    if($data[ExportDRevCSV::CSV_DATE_VALIDATION_ODG]) {
		    $drev->validateOdg($dateValidation);
	    }

            $drev->save();
            echo "IMPORTE;$drev->_id;".Organisme::getInstance()->getUrl()."/drev/visualisation/".$drev->_id."\n";
        }
    }

    protected function isLotInDrev($drev, $ligne){
        $volume = $this->formatFloat(trim($ligne[ExportDRevCSV::CSV_VOLUME_REVENDIQUE]));
        $numero_cuve = trim($ligne[ExportDRevCSV::CSV_LOT_NUMERO_CUVE]);
        $type_destination = self::$destinationsTypes[preg_replace("/([A-Z_]+).+/","$1",$ligne[ExportDRevCSV::CSV_LOT_DESTINATION])];
        $date_destination = preg_replace("/([A-Z_]* )?([0-9\/]+)/","$2",$ligne[ExportDRevCSV::CSV_LOT_DESTINATION]);
        $code_inao = trim($ligne[ExportDRevCSV::CSV_PRODUIT_INAO]);
        $date = trim($ligne[ExportDRevCSV::CSV_DATE_VALIDATION_DECLARANT]);

        // Check si le Volume est le même que celui d'un autre Lot
        foreach ($drev->getLots() as $lot) {
            if (trim($numero_cuve) == trim($lot->numero_logement_operateur)) {
                return true;
            }
            if ($lot->volume == $volume && KeyInflector::slugify(trim($numero_cuve)) == KeyInflector::slugify(trim($lot->numero_logement_operateur))) {
                return true;
            }
        }

        $lotFindByVolume = null;
        foreach ($drev->getLots() as $lot) {
            if (!$lot->numero_logement_operateur && $numero_cuve && $lot->volume == $volume && $lot->getConfigProduit()->getCodeDouane() == $code_inao) {
                if($lotFindByVolume) {
                    throw new sfException("Le lot semble être déjà importé mais il y a un doute");
                }
                $lotFindByVolume = $lot;
            }
        }

        if($lotFindByVolume) {
            $lotFindByVolume->numero_logement_operateur = $numero_cuve;
            echo "mise à jour du numéro de cuve;$drev->_id;$numero_cuve;\n";
            $drev->save();
            return true;
        }

        return false;
    }

    protected function formatFloat($value) {

        return str_replace(',', '.', $value)*1.0;
    }
}
