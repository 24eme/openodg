<?php

class ImportAntsysDRevTask extends sfBaseTask
{
  protected $types_ignore = array();

  const CSV_ID = 0;
  const CSV_PRODUIT = 1;
  const CSV_SURFACE = 2;
  const CSV_VOLUME = 3;
  const CSV_VOLUME_SANS_VCI = 4;
  const CSV_VCI_ANNEE_PRECEDENTE = 5;
  const CSV_VCI_RAFRAICHI = 6;
  const CSV_VCI_COMPLEMENT = 7;
  const CSV_VCI_DETRUIT = 8;

  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
    ));

    $this->namespace = 'import';
    $this->name = 'AntsysDRev';
    $this->briefDescription = 'Import des DRev (via le csv issu de scrapping)';
    $this->detailedDescription = "";

    $this->convert_produits = array();
    //awk -F ';' '{print $9}' data/habilitation.csv | sort | uniq -c | wc -l   =====> 17
    //$this->convert_produits['chateau grillet'] = '';
    $this->convert_produits['CAIRANNE BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CAR/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CAIRANNE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CAR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV CAIRANNE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/CAI/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV CHUSCLAN RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/CHU/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV CHUSCLAN RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/CHU/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV GADAGNE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/GAD/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDR VILLAGES BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVS/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDR VILLAGES RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVS/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDR VILLAGES RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVS/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV LAUDUN BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/LAU/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV LAUDUN RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/LAU/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV LAUDUN RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/LAU/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV MAS UCHAUX RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/MAS/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV PLAN DE DIEU RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/PLA/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV PUYMERAS RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/PUY/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV ROAIX BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/ROX/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV ROAIX RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/ROX/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV ROAIX RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/ROX/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV ROCHEGUDE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/ROC/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV ROUSSET RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/RLV/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV SABLET BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SAB/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV SABLET RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SAB/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV SABLET RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SAB/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV SEGURET BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SEG/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV SEGURET RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SEG/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV SEGURET RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SEG/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV SIGNARGUES RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SIG/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV STE CECILE Rouge'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SCE/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV ST GERVAIS BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/STG/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV ST GERVAIS RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/STG/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV ST MAURICE BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/STM/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV ST MAURICE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/STM/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV ST MAURICE RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/STM/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CDRV ST PANTALE. RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SPV/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV SUZE LA ROUSSE Rouge'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/SLR/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV VAISON LA ROMAINE Rouge'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/VLR/couleurs/DEFAUT/cepages/DEFAUT';
    $this->convert_produits['CDRV VALREAS RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/VAL/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV VISAN BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/VIS/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CDRV VISAN RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/VIS/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['CDRV VISAN RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CVG/mentions/DEFAUT/lieux/VIS/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CONDRIEU BLC'] = 'certifications/AOP/genres/TRANQ/appellations/COD/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CORNAS RGE'] = 'certifications/AOP/genres/TRANQ/appellations/COR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['COTE ROTIE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CRO/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['COTES DU RHONE BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CDR/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['COTES DU RHONE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CDR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['COTES DU RHONE RSE'] = 'certifications/AOP/genres/TRANQ/appellations/CDR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['CROZES HERMITAGE BLC'] = 'certifications/AOP/genres/TRANQ/appellations/CRH/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['CROZES HERMITAGE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/CRH/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['GIGONDAS RGE'] = 'certifications/AOP/genres/TRANQ/appellations/GIG/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['GIGONDAS RSE'] = 'certifications/AOP/genres/TRANQ/appellations/GIG/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['HERMITAGE BLC'] = 'certifications/AOP/genres/TRANQ/appellations/HER/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['HERMITAGE RGE'] = 'certifications/AOP/genres/TRANQ/appellations/HER/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['LIRAC RGE'] = 'certifications/AOP/genres/TRANQ/appellations/LIR/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['ST JOSEPH BLC'] = 'certifications/AOP/genres/TRANQ/appellations/SJO/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['ST JOSEPH RGE'] = 'certifications/AOP/genres/TRANQ/appellations/SJO/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['ST PERAY BLC MOUS.'] = 'certifications/AOP/genres/EFF/appellations/SPE/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['ST PERAY BLC TRAN'] = 'certifications/AOP/genres/TRANQ/appellations/SPT/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['VACQUEYRAS BLC'] = 'certifications/AOP/genres/TRANQ/appellations/VAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT';
    $this->convert_produits['VACQUEYRAS RGE'] = 'certifications/AOP/genres/TRANQ/appellations/VAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    $this->convert_produits['VACQUEYRAS RSE'] = 'certifications/AOP/genres/TRANQ/appellations/VAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/rose/cepages/DEFAUT';
    $this->convert_produits['VINSOBRES RGE'] = 'certifications/AOP/genres/TRANQ/appellations/VBR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';
    //        $this->convert_produits['VDT INT AIRE BLC'] = '';
    //        $this->convert_produits['VDT INT AIRE RGE'] = '';
    //        $this->convert_produits['VDT INT AIRE RSE'] = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    $datas = array();
    foreach(file($arguments['file']) as $line) {
      $line = str_replace("\n", "", $line);
      if(preg_match("/^000000#/", $line)) {
        continue;
      }

      $data = str_getcsv($line, ';');
      if ($data[self::CSV_PRODUIT] == 'produit') {
        continue;
      }
      if (isset($lastid) && $lastid != $data[self::CSV_ID]) {
        $this->saveRows($rows);
        $rows = [];
      }
      $lastid = $data[self::CSV_ID];
      $rows[] = $data;
    }
    $this->saveRows($rows);
  }

  private function saveRows($rows) {
    $campagne = "2016";
    $id = sprintf('%06d', $rows[0][self::CSV_ID]);
    echo "trying $id \n";
    $soc = SocieteClient::getInstance()->find($id);
    if (!$soc) {
      echo "ERROR: pas de société trouvée pour : ".$id."\n";
      return false;
    }
    $eta = $soc->getEtablissementPrincipal();
    if (!$eta) {
      echo "ERROR: pas d'établissement trouvé pour la société ".$id."\n";
      return false;
    }
    if($drev = DRevClient::getInstance()->find('DREV-'.$eta->identifiant."-".$campagne, acCouchdbClient::HYDRATE_JSON)) {
        DRevClient::getInstance()->deleteDoc($drev);
    }
    $drev = DRevClient::getInstance()->createDoc($eta->identifiant, $campagne, true);
    $drev->importFromDR();
    foreach($rows as $r) {
      if (!isset($this->convert_produits[$r[self::CSV_PRODUIT]])) {
        echo "ERROR: produit " . $r[self::CSV_PRODUIT] . " non trouvé\n";
        continue;
      }
      if(!$drev->declaration->getConfig()->exist($this->convert_produits[$r[self::CSV_PRODUIT]])) {
        echo "ERROR: produit " . $this->convert_produits[$r[self::CSV_PRODUIT]] . " non trouvé dans la conf\n";
        continue;
      }

      $produit = $drev->addProduit($this->convert_produits[$r[self::CSV_PRODUIT]]);
      $produit->superficie_revendique = $r[self::CSV_SURFACE] * 1;
      $produit->volume_revendique_issu_recolte = $r[self::CSV_VOLUME] * 1;
      //$produit->volume_revendique_avec_vci = $r[self::CSV_VOLUME] * 1;

      //$produit-> = $r[self::CSV_VCI_ANNEE_PRECEDENTE];
      if (($r[self::CSV_VCI_RAFRAICHI]*1) + ($r[self::CSV_VCI_COMPLEMENT] * 1) + ($r[self::CSV_VCI_DETRUIT] * 1)) {
        //$produit->vci->stock_precedent = $r[self::CSV_VCI_RAFRAICHI] - $r[self::CSV_VCI_DETRUIT] * 1;
        $produit->vci->rafraichi = $r[self::CSV_VCI_RAFRAICHI]  * 1;
        $produit->vci->complement = $r[self::CSV_VCI_COMPLEMENT] * 1;
        $produit->vci->destruction = $r[self::CSV_VCI_DETRUIT] * 1;
      }
    }

    $drev->validate($campagne."-12-10");
    $drev->validateOdg($campagne."-12-10");
    $drev->save();
  }
}
