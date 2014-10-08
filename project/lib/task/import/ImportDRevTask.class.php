<?php

class importDRevTask extends sfBaseTask
{

    const CSV_CVI                   = 0;
    const CSV_ANNEE                 = 1;
    const CSV_TYPE_LIGNE            = 2;
    const CSV_REV_NUM_LIGNE         = 3;
    const CSV_REV_TYPE_ID           = 4;
    const CSV_REV_TYPE_LIBELLE      = 5;
    const CSV_REV_VALEUR            = 6;
    const CSV_AOC                   = 7;
    const CSV_GRDCRU                = 8;
    const CSV_CEPAGE                = 9;
    const CSV_NB_LOT                = 10;
    const CSV_ANNEE_PRELEVEMENT     = 11;
    const CSV_SEMAINE_PRELEVEMENT   = 12;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'DRev';
        $this->briefDescription = 'Import des déclarations de revendication';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $doc = null;
        $object = null;
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^#/", $line)) {
                
                continue;
            }

            $data = str_getcsv($line, ';');

            if($doc && $doc->identifiant != $data[self::CSV_CVI]) {
                $this->save($doc);
                $doc = null;
            }

            try{
                if(!$doc) {
                    $doc = $this->findOrCreateDoc($data);
                }
            
                $object = $this->importLine($data, $doc, $object);
            } catch (Exception $e) {

                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                $doc = null;
                continue;
            }
        }

        if($doc) {
            $this->save($doc);
        }
    }

    protected function save($doc) {
         $doc->constructId();
        if($doc->isNew()) {
            echo sprintf("SUCCESS;%s;%s\n", "Création", $doc->_id);
        } else {
            echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $doc->_id);
        }
        $doc->save();
    }

    public function findOrCreateDoc($data) {

        $cvi = $data[self::CSV_CVI];
        $campagne = $data[self::CSV_ANNEE];

        if(!EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $cvi)), acCouchdbClient::HYDRATE_JSON) {

            throw new sfException(sprintf("Etablissement %s does not exist", $cvi));
        }

        $id = sprintf("DREV-%s-%s", $cvi, $campagne);
        $doc = DRevClient::getInstance()->find($id);
        if(!$doc) {
            $doc = new DRev();
            $doc->initDoc($cvi, $campagne);
        }

        $doc->remove('declaration');
        $doc->add('declaration');
        $doc->remove('prelevements');
        $doc->add('prelevements');

        return $doc;
    }

    protected function importLine($data, $doc, $object) {
        if($data[self::CSV_TYPE_LIGNE] == "1.REVE") {
            if($data[self::CSV_REV_TYPE_ID] == "030") {
                return $this->importLineRevendicationProduit($data, $doc);
            }

            return $this->importLineRevendication($data, $doc, $object);
        }

        if($data[self::CSV_TYPE_LIGNE] == "2.LOT ") {
            
            return $this->importLineLot($data, $doc);
        }

        if($data[self::CSV_TYPE_LIGNE] == "3.PREL") {
            
            return $this->importLinePrelevement($data, $doc);
        }
    }

    protected function importLineRevendicationProduit($data, $doc) {
        
        $hash = $this->convertAOCNumberToHash($data[self::CSV_REV_VALEUR]);

        if(!$hash) {
            throw new sfException(sprintf("Produit %s not found", $data[self::CSV_REV_VALEUR]));
        } 

        $produit = $doc->addProduit($hash);

        if(!$produit) {

            throw new sfException(sprintf("Product not created", $hash));
        }

        return $produit;
    }

    protected function importLineRevendication($data, $doc, $produit) {

        if(!$produit) {

            throw new sfException(sprintf("Product not created"));
        }

        if($data[self::CSV_REV_TYPE_LIBELLE] == "Hl") {
            $produit->volume_revendique += $this->convertRevValueToNumber($data[self::CSV_REV_VALEUR]) * 1.0;
        }

        if($data[self::CSV_REV_TYPE_LIBELLE] == "L") {
            $produit->volume_revendique += $this->convertRevValueToNumber($data[self::CSV_REV_VALEUR]) * 0.01 * 1.0;
        }

        if($data[self::CSV_REV_TYPE_LIBELLE] == "Ha") {
            $produit->superficie_revendique += $this->convertRevValueToNumber($data[self::CSV_REV_VALEUR]) * 100;
        }

        if($data[self::CSV_REV_TYPE_LIBELLE] == "a") {
            $produit->superficie_revendique += $this->convertRevValueToNumber($data[self::CSV_REV_VALEUR]);
        }

        if($data[self::CSV_REV_TYPE_LIBELLE] == "ca") {
            $produit->superficie_revendique += $this->convertRevValueToNumber($data[self::CSV_REV_VALEUR]) * 0.01;
        }

        return $produit;
    }

    protected function importLineLot($data, $doc) {
        $hash = $this->convertLotToHashProduit($data);
        $lot = $doc->addLotProduit($hash, DREV::CUVE);

        if(!$lot) {

            return;
        }
        $lot->nb_hors_vtsgn += $data[self::CSV_NB_LOT];
    }

    protected function importLinePrelevement($data, $doc) {
        $key = $this->convertAOCNumberToPrelevementKey($data[self::CSV_AOC]);

        if(!$key) {
            
            throw new sfException(sprintf("AOC introuvable %s", $num));
        }

        $prelevement = $doc->addPrelevement($key);
        $date = new DateTime(sprintf("%s-01-01", $data[self::CSV_ANNEE_PRELEVEMENT]));
        $date->modify("-1 week");
        $date->modify("Previous Monday");
        $date->modify(sprintf("+%s week", $data[self::CSV_SEMAINE_PRELEVEMENT]));
        $prelevement->date = $date->format("Y-m-d");

        if($data[self::CSV_NB_LOT]) {
            $prelevement->total_lots = $data[self::CSV_NB_LOT];
        }
    }

    protected function convertRevValueToNumber($value) {

        return round(str_replace(",", ".", $value));
    }

    protected function convertLotToHashProduit($data) {
        if(in_array($data[self::CSV_AOC], array("1", "3", "4"))) {

            return sprintf("/declaration/certification/genre/appellation_ALSACE/mention/lieu/couleur/cepage_%s", $this->convertCepage($data[self::CSV_CEPAGE]));
        }

        if($data[self::CSV_AOC] == "2") {
            
            return sprintf("/declaration/certification/genre/appellation_GRDCRU/mention/lieu%s/couleur/cepage_%s", sprintf("%02d", $this->convertGrdCruNum($data[self::CSV_GRDCRU])),  $this->convertCepage($data[self::CSV_CEPAGE]));
        }

        throw new sfException(sprintf("Appellation %s not found", $data[self::CSV_AOC]));
    }

    protected function convertAOCNumberToPrelevementKey($num) {
        $keys = array(
            "0" => DRev::BOUTEILLE_ALSACE,
            "1" => DRev::BOUTEILLE_GRDCRU,
            "2" => DRev::BOUTEILLE_VTSGN,
        );

        return (isset($keys[$num])) ? $keys[$num] : null; 
    }

    protected function convertCepage($key) {
        $conversions = array(
            "Edel" => "ED",
            "PN Rosé" => "PR",
            "PN Rouge" => "PN",
            "AUX" => "AU",
            "ASS" => "ED",
            "K de H" => "KL",
            "MU OTT" => "MO",
            );


        return (isset($conversions[$key])) ? $conversions[$key] : $key;
    }

    protected function convertAOCNumberToHash($num) {
        $num = $this->convertRevValueToNumber($num);

        $hash = null;

        $hashs = array(
            "11" => "/declaration/certification/genre/appellation_PINOTNOIRROUGE/mention/lieu/couleur",
            "12" => "/declaration/certification/genre/appellation_PINOTNOIR/mention/lieu/couleur",
            "13" => "/declaration/certification/genre/appellation_ALSACEBLANC/mention/lieu/couleur",
            "23" => "/declaration/certification/genre/appellation_GRDCRU/mention/lieu/couleur",
            "103361" => "/declaration/certification/genre/appellation_COMMUNALE/mention/lieu/couleurRouge",
            "103363" => "/declaration/certification/genre/appellation_COMMUNALE/mention/lieu/couleurBlanc",
            "103371" => "/declaration/certification/genre/appellation_LIEUDIT/mention/lieu/couleurRouge",
            "103373" => "/declaration/certification/genre/appellation_LIEUDIT/mention/lieu/couleurBlanc",
            "100943" => "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur",
        );

        if(isset($hashs[$num])) {
            $hash = $hashs[$num];
        }

        return $hash;
    }

    protected function convertGrdCruNum($num) {
        $grdcru = array("1" => "01", "2" => "02", "3" => "26", "4" => "03", "5" => "49", "6" => "04", "7" => "27", "8" => "50", "9" => "28", "10" => "29", "11" => "48", "12" => "05", "13" => "06", "14" => "07", "15" => "08", "16" => "09", "17" => "51", "18" => "10", "19" => "11", "20" => "12", "21" => "13", "22" => "14", "23" => "15", "24" => "30", "25" => "31", "26" => "32", "27" => "16", "28" => "17", "29" => "34", "30" => "35", "31" => "37", "32" => "18", "33" => "19", "34" => "20", "35" => "21", "36" => "38", "37" => "22", "38" => "23", "39" => "24", "40" => "39", "41" => "40", "42" => "41", "43" => "42", "44" => "43", "45" => "25", "46" => "44", "47" => "45", "48" => "46", "49" => "47", "50" => "33", "52" => "36",
        );

        if(!isset($num)) {

            throw new sfException(sprintf("Numero de grand cru %s", $num));
        }

        return $grdcru[$num];
    }
}