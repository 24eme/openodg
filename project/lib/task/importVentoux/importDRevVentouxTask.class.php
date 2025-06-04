<?php

class ImportDRevVentouxTask extends sfBaseTask
{
  const CSV_CVI = 2;
  const CSV_RAISON_SOCIALE = 3;

  const CSV_COULEUR = 7;
  const CSV_VOLUME = 11;
  const CSV_VOLUME_BIO = 12;
  const CSV_VOLUME_CONVERSION = 13;
  const CSV_SUPERFICIE = 14;
  const CSV_SUPERFICIE_BIO = 15;
  const CSV_SUPERFICIE_CONVERSION = 16;

  const CSV_PRODUIT = 9;
  const CSV_AOC = 10;
  const CSV_MENTION_VALORISANTE = 99;

  const CSV_VCI_MILLESIME = 0;
  const CSV_VCI_RAISON_SOCIALE = 1;
  const CSV_VCI_CVI = 2;
  const CSV_VCI_PRODUIT = 3;
  const CSV_VCI_MENTION_VALORISANTE = 4;
  const CSV_VCI_STOCK_PRECEDENT = 6;
  const CSV_VCI_CONSTITUE = 7;
  const CSV_VCI_RAFRAICHI = 8;
  const CSV_VCI_COMPLEMENT = 9;
  const CSV_VCI_SUBSTITUTION = 10;
  const CSV_VCI_DISTILLE_LIES = 11;
  const CSV_VCI_DISTILLE = 12;
  const CSV_VCI_TRANSFERT = 13;
  const CSV_VCI_STOCK = 14;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $produits;
  protected $cepages;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv_drev', sfCommandArgument::REQUIRED, "Fichier csv de la drev"),
            new sfCommandArgument('periode', sfCommandArgument::REQUIRED, "Période"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'drev-ventoux';
        $this->briefDescription = 'Import des déclaration de revendication ventoux';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $periode = $arguments['periode'];

        //$this->initProduitsCepages();

        $ligne = 0;
        foreach(file($arguments['csv_drev']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }

            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CSV_CVI]);

            if (!$etablissement) {
               echo "Error;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import:$line\n";
               continue;
            }

            $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($etablissement->identifiant, $periode);

            if(!$drev) {
                $drev = new DRev();
                $drev->initDoc($etablissement->identifiant, $periode);
                $drev->storeDeclarant();
                try {
                    $drev->resetAndImportFromDocumentDouanier();
                    foreach($drev->getProduits() as $p) {
                        $p->superficie_revendique = null;
                    }
                } catch(Exception $e) {
                    continue;
                }
            }

            if(!$data[self::CSV_VOLUME] && !$data[self::CSV_SUPERFICIE]) {
                continue;
            }

            $volumes = [DRevClient::DENOMINATION_CONVENTIONNEL => 0, DRevClient::DENOMINATION_BIO => 0, DRevClient::DENOMINATION_CONVERSION_BIO => 0];
            $superficies = [DRevClient::DENOMINATION_CONVENTIONNEL => 0, DRevClient::DENOMINATION_BIO => 0, DRevClient::DENOMINATION_CONVERSION_BIO => 0];

            $volumeTotal = round(str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1, 2);
            if($data[self::CSV_VOLUME_BIO]) {
                $volumes[DRevClient::DENOMINATION_BIO] = round(str_replace(',','.',trim($data[self::CSV_VOLUME_BIO])) * 1, 2);
            }
            if($data[self::CSV_VOLUME_CONVERSION]) {
                $volumes[DRevClient::DENOMINATION_CONVERSION_BIO] = round(str_replace(',','.',trim($data[self::CSV_VOLUME_CONVERSION])) * 1, 2);
            }

            $volumes[DRevClient::DENOMINATION_CONVENTIONNEL] = $volumeTotal - $volumes[DRevClient::DENOMINATION_BIO] - $volumes[DRevClient::DENOMINATION_CONVERSION_BIO];

            $superficieTotal = round(str_replace(',','.',trim($data[self::CSV_SUPERFICIE])) * 1, 4);
            if($data[self::CSV_SUPERFICIE_BIO]) {
                $superficies[DRevClient::DENOMINATION_BIO] = round(str_replace(',','.',trim($data[self::CSV_SUPERFICIE_BIO])) * 1, 4);
            }
            if($data[self::CSV_SUPERFICIE_CONVERSION]) {
                $superficies[DRevClient::DENOMINATION_CONVERSION_BIO] = round(str_replace(',','.',trim($data[self::CSV_SUPERFICIE_CONVERSION])) * 1, 4);
            }
            $superficies[DRevClient::DENOMINATION_CONVENTIONNEL] = $superficieTotal - $superficies[DRevClient::DENOMINATION_BIO] - $superficies[DRevClient::DENOMINATION_CONVERSION_BIO];

            if(!$volumeTotal && !$superficieTotal) {
                continue;
            }

            foreach($volumes as $label => $volume) {
                if(!$volume) {
                    continue;
                }
                $libelleDenom = DRevClient::getDenominationsAuto()[$label];

                if($label == DRevClient::DENOMINATION_CONVENTIONNEL) {
                    $libelleDenom = null;
                }

                $drevProduit = $drev->addProduit("/declaration/certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT/couleurs/".strtolower(KeyInflector::slugify($data[self::CSV_COULEUR]))."/cepages/DEFAUT", $libelleDenom);
                $drevProduit->volume_revendique_issu_recolte = $volume;
                $drevProduit->superficie_revendique = $superficies[$label];
                $drevProduit->update();
            }

            try {
                if(!$drev->isValidee()) {
                    $drev->validate($periode."-12-10");
                }
                $drev->validateOdg($periode."-12-10");
            } catch(Exception $e) {
                sleep(60);
                if(!$drev->isValidee()) {
                    $drev->validate($periode."-12-10");
                }
                $drev->validateOdg($periode."-12-10", RegionConfiguration::getInstance()->getOdgRegion($produit->getHash()));
            }
            $drev->save();
        }
    }

}
