<?php

class importDRevCSVTask extends sfBaseTask
{

    const CSV_TYPE                  = -1; //VCI ou DREV

    const CSV_MILLESIME             = 0; // = campagne
    const CSV_DATE_RECEPTION        = 1;
    const CSV_CVI_OP                 = 2;
    const CSV_PRODUIT               = 3;

    const CSV_SURFACE               = 5;
    const CSV_VOLUME                = 6;
    const CSV_VOLUME_BRUT           = 7; //SI vide = volume?


    const CSV_VOLUME_REPLIE         = 8; // Ca sert à quoi?
    const CSV_VOLUME_DECLASSE       = 9; // Ca sert à quoi?
    const CSV_VOLUME_SUPLEMENTAIRE  = 10; // Ca sert à quoi?



    const CSVVCI_NOM_OP               = 0;
    const CSVVCI_ID_OP                = 1;
    const CSVVCI_CAMPAGNE             = 2;
    const CSVVCI_VCICONSTITUE         = 3;
    const CSVVCI_VCICOMPLEMENT        = 4;
    const CSVVCI_VCIADETRUIRE         = 5;
    const CSVVCI_VCIRAFRAICHI         = 6;
    const CSVVCI_VCISTOCKNOUVEAU      = 7;

    const SOCIETE_INCONNUE = "inconnu";

    protected $stockVCI2016 = array();

    protected static $produitsKey = array(
        "C.A Cab rose" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/CBF",""),
        "C.A Cab Rouge" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/CBF",""),
        "C.A Gam Rosé" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT",""),
        "C.A Gam Rouge" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT",""),
        "C.A Malvoisie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MALVOISIE",""),
        "C.A Pineau Blanc" => array("",""), // ???
        "Gros Plant" =>array( "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/GPL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/FBL",""),
        "Gros Plant S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/GPL/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/FBL",""),
        "Mus AC" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus AC Cru Communal" => array("",""), // ??? = Mus AC
        "Mus  AC S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Muscadet Appellation Communale" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Muscadet S/ Maine CHATEAU THEBAUD" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","CHATEAU THEBAUD"),
        "Muscadet S/Maine MAISDON" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","MAISDON"),
        "Muscadet S/Maine RUBIS DE SANGUEZE" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","RUBIS DE SANGUEZE"),
        "Muscadet S/Maine VERTOU" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","VERTOU"),
        "Mus CL" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus CL CHAMPTOCEAUX" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","CHAMPTOCEAUX"),
        "Mus CL S/ Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus  G/ Lieu" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCGL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus G/Lieu cru communal" => array("",""), // ??? = Mus  G/ Lieu classique
        "Mus G/Lieu S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCGL/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus Primeur" => array("",""), // est-ce du Muscadet AC Primeur ???
        "Mus S/ Maine" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus S/M" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL","CH. THEBAUD"),
        "Mus S/M CLISSON" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/CLISSON/couleurs/blanc/cepages/MEL",""),
        "Mus S/M GORGES" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/GORGES/couleurs/blanc/cepages/MEL",""),
        "Mus S/M GOULAINE" => array("",""), // ancien cru?
        "Mus S/M H. FOUASSIERE" => array("",""), // ancien cru?
        "Mus S/M LE PALLET" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/LEPALLET/couleurs/blanc/cepages/MEL",""),
        "Mus S/M MON.ST FIACRE" => array("",""), // ancien cru?
        "Mus S/M MOUZ.TILLIERES" => array("",""), // ancien cru?
        "Mus S/M S/Lie" => array("certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/LIE/lieux/DEFAUT/couleurs/blanc/cepages/MEL",""),
        "Mus S/M VALLET" => array("",""), // ancien cru?
        "Mus S/M VERTOU" => array("",""), // ancien cru?
        "Sèvre et Maine Cru Communal" => array("",""),
    );

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        //    new sfCommandArgument('fileVci', sfCommandArgument::REQUIRED, "Fichier csv pour l'import de la repartition vci seule")
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
        /*foreach(file($arguments['fileVci']) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^\"RECAPITULATIF VCI/", $line) || preg_match("/^\"Nom/", $line)) {

                continue;
            }

            $data = str_getcsv($line, ';');
            $this->importLineDrevCVI($data);
        }*/
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
            echo "WTF? ".$idEtb." ".$campagne."\n";
        }
        $produit_file = trim($data[self::CSV_PRODUIT]);
            if(!self::$produitsKey[$produit_file] || !self::$produitsKey[$produit_file][0]){
              echo sprintf("!!! Etablissement %s => produit %s non trouvé \n", $idEtb,$produit_file);
              return "";
            }
            $produit = $drev->addProduit(self::$produitsKey[$produit_file][0],self::$produitsKey[$produit_file][1]);

            echo "Ajout d'une revendication produit ".self::$produitsKey[$produit_file][0]." à la drev $drev->_id \n";

            $surface = $data[self::CSV_SURFACE] / 100.0;
            $produit->superficie_revendique = $this->convertFloat($surface);
            $produit->recolte->superficie_total = $this->convertFloat($surface);
            $volume_brut = $data[self::CSV_VOLUME_BRUT] / 100.00;

            $produit->recolte->volume_total = $this->convertFloat($volume_brut);

            $produit->recolte->recolte_nette = $this->convertFloat($volume_brut);
            $produit->recolte->volume_sur_place = $this->convertFloat($volume_brut);

            $volume_rev = $data[self::CSV_VOLUME] / 100.00;

            $produit->volume_revendique_total = $this->convertFloat($volume_rev);
            $produit->volume_revendique_issu_recolte = $this->convertFloat($volume_rev);



        /*
        if($data[self::CSV_TYPE] == "VCI"){
            $bio = ($data[self::CSV_AB])? "AB" : null;
            $produit = $drev->addProduit(self::$produitsKey[$data[self::CSV_PRODUIT]],$bio);
            echo "Ajout du vci produit ".self::$produitsKey[$data[self::CSV_PRODUIT]]." à la drev $drev->_id \n";
            $produit->vci->stock_precedent = 0;
            $volumeVci = $this->convertFloat($data[self::CSV_VOLUME]);
            $produit->vci->constitue = $volumeVci;
            $produit->vci->stock_final = $volumeVci;
            $this->stockVCI2016[$idEtb.$produit->getHash()] = $volumeVci;

        }
        */
        $date_reception = DateTime::createFromformat("d/m/Y",$data[self::CSV_DATE_RECEPTION]);
        $drev->add('validation',$date_reception->format('Y-m-d'));
        $drev->add('validation_odg',$date_reception->format('Y-m-d'));
        $drev->validate($date_reception->format('Y-m-d'));
        $drev->save();
    }

    public function importLineDrevCVI($data) {
        $idEtb = strtoupper($data[self::CSVVCI_ID_OP])."01";
        $campagne = $data[self::CSVVCI_CAMPAGNE];

        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,$campagne);
        if($drev){
            $produitsVCI = array();
            $campagnes = array("2013","2014","2015","2016","2017","2018");
            foreach ($campagnes as $c) {
                $drevLocale = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,$c);
                if($drevLocale){
                    foreach ($drevLocale->getProduits() as $key => $produit) {
                        if($produit->hasVci()){
                            $produitsVCI[$produit->getLibelleComplet()] = $produit->getHash();
                        }
                    }
                }
            }


            if(count(array_keys($produitsVCI)) > 1){
                echo "/!\ DREV ".$drev->_id." intraitable => VCI sur 2 produits\n";
            }
            $constitue = $data[self::CSVVCI_VCICONSTITUE];
            $adetruire = $data[self::CSVVCI_VCIADETRUIRE];
            $rafraichi = $data[self::CSVVCI_VCIRAFRAICHI];
            $complement = $data[self::CSVVCI_VCICOMPLEMENT];

            $stockNouveau = $data[self::CSVVCI_VCISTOCKNOUVEAU];

            $drevPrec = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($idEtb,"".(intval($campagne)-1));

            $vciProduit = $drev->declaration->add("certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT")->getOrAdd("DEFAUT")->vci;

            if($drevPrec){
                $vciPrecProduit = $drevPrec->declaration->add("certifications/AOP/genres/TRANQ/appellations/CDP/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT")->getOrAdd("DEFAUT")->vci;
                if($vciPrecProduit->stock_final){
                    $vciProduit->stock_precedent = $vciPrecProduit->stock_final;
                }
            }
            $vciProduit->constitue = $this->convertFloat($constitue);
            $vciProduit->complement = $this->convertFloat($complement);
            $vciProduit->destruction = $this->convertFloat($adetruire);
            $vciProduit->rafraichi = $this->convertFloat($rafraichi);
            $drev->update();

            echo "DREV ".$drev->_id." le stock final de VCI est de : ".$vciProduit->stock_final." [ $constitue | $adetruire | $rafraichi | $complement ]\n";
            $coherent = ($vciProduit->stock_final == $this->convertFloat($data[self::CSVVCI_VCISTOCKNOUVEAU]));
            if($coherent){
                echo "GOOD : le stock final ".$this->convertFloat($data[self::CSVVCI_VCISTOCKNOUVEAU])." des données correscpond au stock calculé après UPDATE.\n";
            }else{
                echo "WRONG ".$drev->_id.": le stock final ".$this->convertFloat($data[self::CSVVCI_VCISTOCKNOUVEAU])." est différent de ".$vciProduit->stock_final." après UPDATE.\n";
            }
            $drev->save();
        }else{
            echo $idEtb." ".$campagne." pas de DREV \n";
        }

    }

    private function createEtablissementAndSociete($data){

        $cdp = strtoupper($data[self::CSV_ID_OP]);
        $newSoc = SocieteClient::getInstance()->find("SOCIETE-".$cdp);
        if(!$newSoc){
            $rs = self::SOCIETE_INCONNUE." ".$cdp;
            $newSoc = SocieteClient::getInstance()->createSociete($rs);
            $newSoc->identifiant = $cdp;
            $newSoc->_id = "SOCIETE-".$cdp;
            $newSoc->save();
        }

        echo "Creation de la société ".self::SOCIETE_INCONNUE." ".$cdp."\n";

        $soc = SocieteClient::getInstance()->find($newSoc->_id);
        $etb = $soc->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR);
        $etb->save();
        echo "Creation de l'etablissement ".$etb->_id."\n";

        $soc = SocieteClient::getInstance()->find($newSoc->_id);
        $soc->switchStatusAndSave();
        $soc = SocieteClient::getInstance()->find($newSoc->_id);
        $compte = $soc->getMasterCompte();
        $compte->addTag('manuel',"Création import inconnu");
        $compte->save();

        $etb = EtablissementClient::getInstance()->find($etb->_id);
        $compte = $etb->getMasterCompte();
        $compte->addTag('manuel',"Création import inconnu");
        $compte->save();
    }

    public function convertFloat($value){
        return floatval(str_replace(',','.',$value));
    }

}
