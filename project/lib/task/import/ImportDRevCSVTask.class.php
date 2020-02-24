<?php

class importDRevCSVTask extends sfBaseTask
{

    const CSV_TYPE                  = -1; //VCI ou DREV

    const CSV_MILLESIME             = 0; // = campagne
    const CSV_DATE_RECEPTION        = 1;
    const CSV_CVI_OP                = 2;
    const CSV_PRODUIT               = 3;

    const CSV_SURFACE               = 5;
    const CSV_VOLUME                = 6;
    const CSV_VOLUME_BRUT           = 7;


    const CSV_VOLUME_REPLIE         = 8; // Ca sert à quoi?
    const CSV_VOLUME_DECLASSE       = 9; // Ca sert à quoi?
    const CSV_VOLUME_SUPLEMENTAIRE  = 10; // Ca sert à quoi?



    const CSVVCI_ID_OP                = 0;
    const CSVVCI_PRODUIT              = 1;
    const CSVVCI_CAMPAGNE             = 2;
    const CSVVCI_VCICONSTITUE         = 5;


    const SOCIETE_INCONNUE = "inconnu";

    protected $stockVCI2016 = array();

    protected static $produitsKey = array(
        "C.A Cab rose" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/CBF",""),
        "C.A Cab Rouge" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/CBF",""),
        "C.A Gam Rosé" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT",""),
        "C.A Gam Rouge" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT",""),
        "C.A Malvoisie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MALVOISIE",""),
        "C.A Pineau Blanc" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT",""),
        // qualifié en "Coteaux d'Ancenis Blanc Chenin"
        "Gros Plant" =>array( "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/GPL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/FBL",""),
        "Gros Plant S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/GPL/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/FBL",""),
        "Mus AC" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus AC Cru Communal" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","Cru Communal"),
        // qualifié en "Muscadet AC"
        "Mus  AC S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Muscadet Appellation Communale" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","Appellation Communale"),
        // qualifié en "Muscadet Appellation Communale"
        "Muscadet S/ Maine CHATEAU THEBAUD" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","CHATEAU THEBAUD"),
        "Muscadet S/Maine MAISDON" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","MAISDON"),
        "Muscadet S/Maine RUBIS DE SANGUEZE" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","RUBIS DE SANGUEZE"),
        "Muscadet S/Maine VERTOU" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","VERTOU"),
        "Mus CL" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus CL CHAMPTOCEAUX" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","CHAMPTOCEAUX"),
        "Mus CL S/ Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus  G/ Lieu" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCGL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus G/Lieu cru communal" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCGL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","cru communal"),
        // qualifié en "Muscadet Côtes de Grand Lieu"
        "Mus G/Lieu S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCGL/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus Primeur" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/PRI/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        // qualifié en "Muscadet AC Primeur"
        "Mus S/ Maine" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus S/M CH. THEBAUD" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","CH. THEBAUD"),
        "Mus S/M CLISSON" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/CLISSON/couleurs/blanc/cepages/MEL",""),
        "Mus S/M GORGES" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/GORGES/couleurs/blanc/cepages/MEL",""),
        "Mus S/M GOULAINE" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","GOULAINE"),
         // qualifié en "Muscadet Sèvre et Maine" dénomination "GOULAINE"
        "Mus S/M H. FOUASSIERE" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","H. FOUASSIERE"),
        // qualifié en "Muscadet Sèvre et Maine" dénomination "H. FOUASSIERE"
        "Mus S/M LE PALLET" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/LEPALLET/couleurs/blanc/cepages/MEL",""),
        "Mus S/M MON.ST FIACRE" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","MON.ST FIACRE"),
        // qualifié en "Muscadet Sèvre et Maine" dénomination "MON.ST FIACRE"
        "Mus S/M MOUZ.TILLIERES" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","MOUZ.TILLIERES"),
        // qualifié en "Muscadet Sèvre et Maine" dénomination "MOUZ.TILLIERES"
        "Mus S/M S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus S/M VALLET" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","VALLET"),
        // qualifié en "Muscadet Sèvre et Maine" dénomination "VALLET"
        "Mus S/M VERTOU" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","VERTOU"),
        // qualifié en "Muscadet Sèvre et Maine" dénomination "VERTOU"
        "Sèvre et Maine Cru Communal" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","Cru Communal"),
        // qualifié en "Muscadet Sèvre et Maine" dénomination "Cru Communal"
    );

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('fileVci', sfCommandArgument::REQUIRED, "Fichier csv pour l'import de la repartition vci seule")
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
            if(preg_match("/^Campagne/", $line)) {
                continue;
            }
            $data = str_getcsv($line, ';');

            $this->importLineDrev($data);
        }

        foreach(file($arguments['fileVci']) as $line) {
            $line = str_replace("\n", "", $line);

            $data = str_getcsv($line, ';');
            $this->importLineDrevCVI($data);
        }
    }

    public function importLineDrev($data) {

        $cviEtb = strtoupper($data[self::CSV_CVI_OP]);
        $etablissement = EtablissementClient::getInstance()->findByCvi($cviEtb);

        if(!$etablissement) {
          echo sprintf("!!! Etablissement %s does not exist \n", $cviEtb);
          return null;
          // $this->createEtablissementAndSociete($data);
          // $etablissement = EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $idEtb));
        }

        $idEtb = $etablissement->getIdentifiant();
        $campagne = $data[self::CSV_MILLESIME];

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
            echo "probleme : ".$idEtb." ".$campagne."\n";
        }
        $produit_file = trim($data[self::CSV_PRODUIT]);
            if(!self::$produitsKey[$produit_file] || !self::$produitsKey[$produit_file][0]){
              echo sprintf("!!! Etablissement %s => produit %s non trouvé \n", $idEtb,$produit_file);
              return "";
            }
            $produit = $drev->addProduit(self::$produitsKey[$produit_file][0],self::$produitsKey[$produit_file][1]);

            echo "Ajout d'une revendication produit ".self::$produitsKey[$produit_file][0]." à la drev $drev->_id \n";

            $surface = $data[self::CSV_SURFACE] / 100.0;
            $produit->superficie_revendique += $this->convertFloat($surface);
            $produit->recolte->superficie_total += $this->convertFloat($surface);

            $volume_brut = $data[self::CSV_VOLUME_BRUT] / 100.00;

            $produit->recolte->volume_total += $this->convertFloat($volume_brut);

            $produit->recolte->recolte_nette += $this->convertFloat($volume_brut);
            $produit->recolte->volume_sur_place += $this->convertFloat($volume_brut);

            $volume_rev = $data[self::CSV_VOLUME] / 100.00;

            $produit->volume_revendique_total += $this->convertFloat($volume_rev);

            $produit->volume_revendique_issu_recolte += $this->convertFloat($volume_rev);

        $date_reception = DateTime::createFromformat("d/m/Y",$data[self::CSV_DATE_RECEPTION]);
        $drev->add('validation',$date_reception->format('Y-m-d'));
        $drev->add('validation_odg',$date_reception->format('Y-m-d'));
        $drev->validate($date_reception->format('Y-m-d'));
        $drev->save();
    }

    public function importLineDrevCVI($data) {
        $cviEtb = strtoupper($data[self::CSVVCI_ID_OP]);
        $etablissement =  EtablissementClient::getInstance()->findByCvi($cviEtb);
        $idEtb = $etablissement->getIdentifiant();
        $produitFile = trim($data[self::CSVVCI_PRODUIT]);
        $campagne = $data[self::CSVVCI_CAMPAGNE];

        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,$campagne);
        if($drev){
          $nodeKey = (self::$produitsKey[$produitFile][1])? self::$produitsKey[$produitFile][1] : "DEFAUT";
          $produitNode = $drev->declaration->get(self::$produitsKey[$produitFile][0]."/".$nodeKey);

          $constitue = $data[self::CSVVCI_VCICONSTITUE];
          $produitNode->vci->constitue = $this->convertFloat($constitue);
          $drev->update();
          $drev->save();
          }else{
              echo $idEtb." ".$campagne." pas de DREV \n";
          }
    }


    public function convertFloat($value){
        return floatval(str_replace(',','.',$value));
    }

}
