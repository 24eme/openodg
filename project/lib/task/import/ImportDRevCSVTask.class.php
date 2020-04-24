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
    const CSV_VOLUME_DR           = 7;


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
            $this->importLineDrevVCI($data);
        }
    }

    public function importLineDrev($data) {

        $cviEtb = strtoupper($data[self::CSV_CVI_OP]);
        $etablissement = EtablissementClient::getInstance()->findByCvi($cviEtb, true);

        if(!$etablissement) {
          echo sprintf("!!! Etablissement %s does not exist \n", $cviEtb);
          return null;
          // $this->createEtablissementAndSociete($data);
          // $etablissement = EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $idEtb));
        }


        if(is_array($etablissement) && count($etablissement) > 1) {
          echo sprintf("!!! plusieurs établissements ont ce cvi %s \n", $cviEtb);
          return null;
        }

        $idEtb = $etablissement->getIdentifiant();
        $campagne = $data[self::CSV_MILLESIME];

        if(!$campagne) {
            throw new sfException(sprintf("campagne %s non renseignée", $data[self::CSV_MILLESIME]));
        }

        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,$campagne);

        if(!$drev){
            $drev = DRevClient::getInstance()->createDoc($idEtb,$campagne,true, false);
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

            $surface = $data[self::CSV_SURFACE] / 10000.0;
            $volume_net = $data[self::CSV_VOLUME_DR] / 100.00;
            $volume_rev = $data[self::CSV_VOLUME] / 100.00;
            $hashProduit = self::$produitsKey[$produit_file][0];
            $complement = self::$produitsKey[$produit_file][1];

            $produit = $drev->addProduit($hashProduit, $complement);

            echo "Ajout d'une revendication produit ".self::$produitsKey[$produit_file][0]." à la drev $drev->_id \n";

            if($v_net = $this->convertFloat($volume_net)){
              $produit->recolte->recolte_nette += $v_net;
            }
            if($sur = $this->convertFloat($surface)){
              $produit->recolte->superficie_total += $sur;
              $produit->superficie_revendique += $sur;
            }
            if($v_rev = $this->convertFloat($volume_rev)){
              $produit->volume_revendique_issu_recolte += $v_rev;
            }

        $date_reception = DateTime::createFromformat("d/m/Y",$data[self::CSV_DATE_RECEPTION]);
        $drev->update();

        $volume_supplementaire = $data[self::CSV_VOLUME_SUPLEMENTAIRE] / 100.00;
        if($volume_supplementaire > 0) {
            $complement .= ' Achat ';
            $complement = trim($complement);
            $produit_suppl = $drev->addProduit($hashProduit, $complement);
            $produit_suppl->volume_revendique_issu_recolte = $volume_supplementaire;
            $drev->update();
        }


        $drev->validate($date_reception->format('Y-m-d'));
        $drev->validateOdg($date_reception->format('Y-m-d'));
        $drev->save();
    }

    public function importLineDrevVCI($data) {
        $cviEtb = strtoupper($data[self::CSVVCI_ID_OP]);
        $etablissement =  EtablissementClient::getInstance()->findByCvi($cviEtb);
        if(!$etablissement){
          return;
        }
        $idEtb = $etablissement->getIdentifiant();
        $produitFile = trim($data[self::CSVVCI_PRODUIT]);
        $campagne = $data[self::CSVVCI_CAMPAGNE];

        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,$campagne);
        if($drev){
          $hashProduit = self::$produitsKey[$produitFile][0];
          $complement = self::$produitsKey[$produitFile][1];
          $produit = $drev->addProduit($hashProduit, $complement);

          $constitue = $data[self::CSVVCI_VCICONSTITUE];
          $produit->vci->constitue = $this->convertFloat($constitue);
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
