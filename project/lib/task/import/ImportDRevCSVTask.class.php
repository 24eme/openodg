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
            if(preg_match("/^\"IdDrev/", $line)) {
                continue;
            }

            $data = str_getcsv($line, ';');

            $this->importLineDrev($data);
        }
        foreach(file($arguments['fileVci']) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^\"RECAPITULATIF VCI/", $line) || preg_match("/^\"Nom/", $line)) {

                continue;
            }

            $data = str_getcsv($line, ';');
            $this->importLineDrevCVI($data);
        }
    }

    public function importLineDrev($data) {

        $idEtb = strtoupper($data[self::CSV_ID_OP]).'01';
        $etablissement = EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $idEtb));
        $campagne = $data[self::CSV_MILLESIME];

        if(!$etablissement) {
            echo sprintf("!!! Etablissement %s does not exist \n", $idEtb);
            $this->createEtablissementAndSociete($data);
            $etablissement = EtablissementClient::getInstance()->find(sprintf("ETABLISSEMENT-%s", $idEtb));
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



        if($data[self::CSV_TYPE] == "DREV"){
            $bio = ($data[self::CSV_AB])? "AB" : null;
            $produit = $drev->addProduit(self::$produitsKey[$data[self::CSV_PRODUIT]],$bio);
            if(!$bio || ($data[self::CSV_AB_VOl] == $data[self::CSV_L15])){
                echo "Ajout d'une revendication produit ".self::$produitsKey[$data[self::CSV_PRODUIT]]." à la drev $drev->_id \n";

                $produit->superficie_revendique = $this->convertFloat($data[self::CSV_SURFACE]);
                $produit->recolte->superficie_total = $this->convertFloat($data[self::CSV_SURFACE]);
                if($this->convertFloat($data[self::CSV_VOLUME_BRUT])){
                    $produit->recolte->volume_total = $this->convertFloat($data[self::CSV_VOLUME_BRUT]);
                }else{
                    $produit->recolte->volume_total = $this->convertFloat($data[self::CSV_L15]);
                }
                $produit->recolte->recolte_nette = $this->convertFloat($data[self::CSV_L15]);
                $produit->recolte->volume_sur_place = $this->convertFloat($data[self::CSV_L15]);

                $produit->volume_revendique_total = $this->convertFloat($data[self::CSV_VOLUME]);
                $produit->volume_revendique_issu_recolte = $this->convertFloat($data[self::CSV_VOLUME]);
            }else{
                $produitPasBio = $drev->addProduit(self::$produitsKey[$data[self::CSV_PRODUIT]]);
                echo "Ajout d'une revendication produit ".self::$produitsKey[$data[self::CSV_PRODUIT]]." à la drev $drev->_id \n";
                $produitPasBio->superficie_revendique = $this->convertFloat($data[self::CSV_SURFACE]);
                $produitPasBio->recolte->superficie_total = $this->convertFloat($data[self::CSV_SURFACE]);
                $produitPasBio->volume_revendique_total = $this->convertFloat($data[self::CSV_VOLUME]) - $this->convertFloat($data[self::CSV_AB_VOl]);

                if($this->convertFloat($data[self::CSV_VOLUME_BRUT])){
                    $produitPasBio->recolte->volume_total = $this->convertFloat($data[self::CSV_VOLUME_BRUT]);
                }else{
                    $produitPasBio->recolte->volume_total = $this->convertFloat($data[self::CSV_L15]);
                }

                $produitPasBio->volume_revendique_issu_recolte = $this->convertFloat($data[self::CSV_VOLUME]);

                $produitPasBio->recolte->recolte_nette = $this->convertFloat($data[self::CSV_L15]);
                $produitPasBio->recolte->volume_sur_place = $this->convertFloat($data[self::CSV_L15]);


                $produit->volume_revendique_issu_recolte = $this->convertFloat($data[self::CSV_AB_VOl]);
                $produit->volume_revendique_total = $this->convertFloat($data[self::CSV_AB_VOl]);

            }

        }
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
