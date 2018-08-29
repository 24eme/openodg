<?php

class importDRevCSVTask extends sfBaseTask
{

    const CSV_ID_DREV               = 0;
    const CSV_ID_OP                 = 1;
    const CSV_DATE_RECEPTION        = 2;

    const CSV_TYPE                  = 3; //VCI ou DREV
    const CSV_MILLESIME             = 4; // = campagne


    const CSV_PRODUIT               = 5;
    const CSV_VOLUME                = 6;
    const CSV_SURFACE               = 7;
    const CSV_VOLUME_BRUT           = 8; //SI vide = volume?
    const CSV_COHERENCE_DREC        = 9; // Ca sert à quoi?
    const CSV_AB                    = 10; // BIO
    const CSV_L15                   = 11;
    const CSV_AB_VOl                = 12; // volume de BIO

    protected static $produitsKey = array(
        "CDP BL" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT",
        "CDP RG" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT",
        "CDP RS" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT",
        "FR RG" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/FRE/couleurs/rouge/cepages/DEFAUT",
        "FR RS" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/FRE/couleurs/rose/cepages/DEFAUT",
        "LL BL" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/LLO/couleurs/blanc/cepages/DEFAUT",
        "LL RG" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/LLO/couleurs/rouge/cepages/DEFAUT",
        "LL RS" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/LLO/couleurs/rose/cepages/DEFAUT",
        "PF RG" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/PIE/couleurs/rouge/cepages/DEFAUT",
        "PF RS" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/PIE/couleurs/rose/cepages/DEFAUT",
        "SV RG" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/SVI/couleurs/rouge/cepages/DEFAUT",
        "SV RS" => "certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/SVI/couleurs/rose/cepages/DEFAUT"
    );

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
        $this->name = 'drev-csv';
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

            if(preg_match("/^\"IdDrev/", $line)) {

                continue;
            }

            $data = str_getcsv($line, ';');

            $this->importLineDrev($data);
        }
    }

    public function importLineDrev($data) {

        $idEtb = $data[self::CSV_ID_OP].'01';
        $etablissement = EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $idEtb));
        $campagne = $data[self::CSV_MILLESIME];

        if(!$etablissement) {
            echo sprintf("!!! Etablissement %s does not exist \n", $idEtb);
            return;
        }
        if(!$campagne) {
            throw new sfException(sprintf("campagne %s non renseignée", $data[self::CSV_MILLESIME]));
        }

        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,$campagne);

        if(!$drev){
            $drev = DRevClient::getInstance()->createDoc($idEtb,$campagne,true);
            echo "Création de la drev $drev->_id \n";
        }
        try {
            $drev->storeDeclarant();
        } catch (sfException $e) {
            echo "WTF? ".$idEtb." ".$campagne."\n";
        }


        $bio = ($data[self::CSV_AB])? "Agriculture Biologique" : null;

        $produit = $drev->addProduit(self::$produitsKey[$data[self::CSV_PRODUIT]],$bio);
        if($data[self::CSV_TYPE] == "DREV"){
            echo "Ajout d'une revendication produit ".self::$produitsKey[$data[self::CSV_PRODUIT]]." à la drev $drev->_id \n";

            $produit->superficie_revendique = $this->convertFloat($data[self::CSV_SURFACE]);
            $produit->recolte->superficie_total = $this->convertFloat($data[self::CSV_SURFACE]);

            $produit->volume_revendique_total = $this->convertFloat($data[self::CSV_VOLUME]);

            $volume_revendique_issu_recolte = $this->convertFloat($data[self::CSV_VOLUME_BRUT]);
            if(!$volume_revendique_issu_recolte){
                $volume_revendique_issu_recolte = $this->convertFloat($data[self::CSV_VOLUME]);
            }
            $produit->volume_revendique_issu_recolte = $volume_revendique_issu_recolte;

            $produit->recolte->volume_sur_place = $this->convertFloat($data[self::CSV_L15]);

        }
        if($data[self::CSV_TYPE] == "VCI"){
            echo "Ajout du vci produit ".self::$produitsKey[$data[self::CSV_PRODUIT]]." à la drev $drev->_id \n";
            $produit->vci->stock_precedent = 0;
            $produit->vci->complement = $this->convertFloat($data[self::CSV_VOLUME]);
        }

        $date_reception = DateTime::createFromformat("d/m/Y",$data[self::CSV_DATE_RECEPTION]);
        $drev->add('validation',$date_reception->format('Y-m-d'));
        $drev->add('validation_odg',$date_reception->format('Y-m-d'));
        $drev->save();
    }

    public function convertFloat($value){
        return floatval(str_replace(',','.',$value));
    }

}
