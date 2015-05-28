<?php

class importAbonnementTask extends sfBaseTask
{
    const CSV_CODE_CLIENT           = 0;
    const CSV_TARIF                 = 1;
    const CSV_COMPTE_ID             = 2;

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
        $this->name = 'Abonnement';
        $this->briefDescription = 'Import des abonnements';
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

            try{
                $doc = $this->importLine($data);
                $this->save($doc);
            } catch (Exception $e) {

                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                $doc = null;
                continue;
            }
        }
    }

    protected function save($doc) {
        if($doc->isNew()) {
            echo sprintf("SUCCESS;%s;%s\n", "Création", $doc->_id);
        } else {
            return;
            echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $doc->_id);
        }

        $doc->save();
    }

    protected function importLine($data) {
        $compte = CompteClient::getInstance()->find($data[self::CSV_COMPTE_ID]);

        if(!$compte) {
            throw new sfException(sprintf("Le compte %s n'existe pas.", $data[self::CSV_COMPTE_ID])); 
        }

        $doc = AbonnementClient::getInstance()->findOrCreateDoc($compte->identifiant, $this->getDateDebut($data), $this->getDateFin($data));

        $doc->mouvements = array();
        $doc->generateMouvements();
        $doc->tarif = $this->getTarif($data);

        if($doc->tarif == AbonnementClient::TARIF_GRATUIT) {
            $doc->facturerMouvements();
        }

        if($doc->tarif == AbonnementClient::TARIF_PLEIN) {
            $doc->facturerMouvements();
        }

        if($doc->tarif == AbonnementClient::TARIF_ETRANGER) {
            $doc->facturerMouvements();
        }

        return $doc;
    }

    protected function getTarif($data) {
        if(preg_match("/TARIF MEMBRE/", $data[self::CSV_TARIF])) {

            return AbonnementClient::TARIF_MEMBRE;
        }

        if(preg_match("/GRATUIT/", $data[self::CSV_TARIF])) {

            return AbonnementClient::TARIF_GRATUIT;
        }

        if(preg_match("/TARIF PLEIN/", $data[self::CSV_TARIF])) {

            return AbonnementClient::TARIF_PLEIN;
        }

        if(preg_match("/TARIF ETRANGER/", $data[self::CSV_TARIF])) {

            return AbonnementClient::TARIF_ETRANGER;
        }

        throw new sfException(sprintf("Le tarif n'est pas connu %s", $data[self::CSV_TARIF])); 
    }

    protected function getDateDebut($data) {
        return "2015-01-01";
        
        if(preg_match("/([0-9]+)$/", trim($data[self::CSV_TARIF]), $matches)) {

            return sprintf("2015-%02d-01", $matches[1]);
        }

        return "2015-01-01";
    }

    protected function getDateFin($data) {
        $date_debut = new DateTime($this->getDateDebut($data));

        return $date_debut->modify("+1 year")->modify("-1 day")->format("Y-m-d");
    }

}