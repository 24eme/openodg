<?php

class importDRevMarcTask extends sfBaseTask
{

    const CSV_CVI                     = 0;
    const CSV_ANNEE                   = 1;
    const CSV_DATE_DISTILLATION_DEBUT = 2;
    const CSV_DATE_DISTILLATION_FIN   = 3;
    const CSV_VOLUME_ALCOOL           = 4;
    const CSV_TITRE_ALCOOMETRIQUE     = 5;
    const CSV_QUANTITE_MARC           = 6;

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
        $this->name = 'DRevMarc';
        $this->briefDescription = 'Import des déclarations de revendication de marc';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $doc = null;
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^#/", $line)) {
                
                continue;
            }

            $data = str_getcsv($line, ';');
            
            try{
                $doc = $this->findOrCreateDoc($data);

                $this->importLine($data, $doc);
            } catch (Exception $e) {

                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                continue;
            }

            $this->save($doc);
        }

    }

    public function findOrCreateDoc($data) {

        $cvi = $data[self::CSV_CVI];
        $campagne = $data[self::CSV_ANNEE];

        if(!EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $cvi), acCouchdbClient::HYDRATE_JSON)) {

            throw new sfException(sprintf("Etablissement %s does not exist", $cvi));
        }

        $id = sprintf("DREVMARC-%s-%s", $cvi, $campagne);
        $doc = DRevMarcClient::getInstance()->find($id);
        if(!$doc) {
            $doc = new DRevMarc();
            $doc->initDoc($cvi, $campagne);
        }

        return $doc;
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

    protected function importLine($data, $doc) {
        $doc->debut_distillation = $this->convertDate($data[self::CSV_DATE_DISTILLATION_DEBUT]);
        $doc->fin_distillation = $this->convertDate($data[self::CSV_DATE_DISTILLATION_FIN]);
        $doc->qte_marc = $this->convertToFloat($data[self::CSV_QUANTITE_MARC]);
        $doc->volume_obtenu = $this->convertToFloat($data[self::CSV_VOLUME_ALCOOL]);
        $doc->titre_alcool_vol = $this->convertToFloat($data[self::CSV_TITRE_ALCOOMETRIQUE]);
    }

    protected function convertDate($dateFr) {
        if(!preg_match("|^([0-9]+)/([0-9]+)/([0-9]+) |", $dateFr, $matches)) {

            throw new sfException("Date invalide");
        }

        $date = new DateTime(sprintf("%02d-%02d-%s", $matches[3], $matches[2], $matches[1]));

        return $date->format('Y-m-d');
    }

    protected function convertToFloat($numberFr) {

        return round(str_replace(",", ".", $numberFr), 2);
    }

}