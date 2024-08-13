<?php

class ImportDRevIAAOCTask extends importOperateurIACsvTask
{
  const CSV_RAISON_SOCIALE = 0;
  const CSV_CVI = 4;
  const CSV_MILLESIME = 5;
  const CSV_VOLUME = 6;
  const CSV_SUPERFICIE = 7;
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
            new sfCommandArgument('csv_vci', sfCommandArgument::REQUIRED, "Fichier csv du vci"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'drev-ia-aoc';
        $this->briefDescription = 'Import des déclaration de revendication';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $ligne = 0;
        foreach(file($arguments['csv_drev']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], null);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }

            if(strpos($data[self::CSV_PRODUIT], " (VCI)") !== false) {
                continue;
            }

            $produitKey = $this->clearProduitKey(KeyInflector::slugify($this->alias($data[self::CSV_PRODUIT])));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_PRODUIT]." ($produitKey);pas d'import;$line\n";
              continue;
            }
            $produit = $this->produits[$produitKey];
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : null;
            $periode = $millesime."";
            $volume = round(str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1, 2);
            $superficie = round(str_replace(',','.',trim($data[self::CSV_SUPERFICIE])) * 1, 4);

            $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($etablissement->identifiant, $periode);
            if(!$drev) {
                $drev = new DRev();
                $drev->initDoc($etablissement->identifiant, $millesime);
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

            $drevProduit = $drev->addProduit($produit->getHash(), isset($data[self::CSV_MENTION_VALORISANTE]) ? $data[self::CSV_MENTION_VALORISANTE] : null);
            $drevProduit->volume_revendique_issu_recolte += $volume;
            $drevProduit->superficie_revendique += round($superficie / 10000, 4);
            $drevProduit->update();

            try {
                if(!$drev->isValidee()) {
                    $drev->validate($periode."-12-10");
                }
                $drev->validateOdg($periode."-12-10", RegionConfiguration::getInstance()->getOdgRegion($produit->getHash()));
            } catch(Exception $e) {
                sleep(60);
                if(!$drev->isValidee()) {
                    $drev->validate($periode."-12-10");
                }
                $drev->validateOdg($periode."-12-10", RegionConfiguration::getInstance()->getOdgRegion($produit->getHash()));
            }
            $drev->save();

            echo $drev."\n";
        }

        $ligne = 0;
        foreach(file($arguments['csv_vci']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }

            $etablissement = $this->identifyEtablissement($data[self::CSV_VCI_RAISON_SOCIALE], $data[self::CSV_VCI_CVI], null);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_VCI_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }

            $produitKey = $this->clearProduitKey(KeyInflector::slugify($this->alias($data[self::CSV_VCI_PRODUIT])));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_VCI_PRODUIT]." ($produitKey);pas d'import;$line\n";
              continue;
            }
            $produit = $this->produits[$produitKey];
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_VCI_MILLESIME]))? trim($data[self::CSV_VCI_MILLESIME])*1 : null;
            $periode = $millesime."";
            $volume_stock_precedent = round(str_replace(',','.',trim($data[self::CSV_VCI_STOCK_PRECEDENT])) * 1, 2);
            $volume_constitue = round(str_replace(',','.',trim($data[self::CSV_VCI_CONSTITUE])) * 1, 2);
            $volume_rafraichi = round(str_replace(',','.',trim($data[self::CSV_VCI_RAFRAICHI])) * 1, 2);
            $volume_complement = round(str_replace(',','.',trim($data[self::CSV_VCI_COMPLEMENT])) * 1, 2);
            $volume_substitution = round(str_replace(',','.',trim($data[self::CSV_VCI_SUBSTITUTION])) * 1, 2);
            $volume_distille_lies = round(str_replace(',','.',trim($data[self::CSV_VCI_DISTILLE_LIES])) * 1, 2);
            $volume_distille = round(str_replace(',','.',trim($data[self::CSV_VCI_DISTILLE])) * 1, 2);
            $volume_transfert = round(str_replace(',','.',trim($data[self::CSV_VCI_TRANSFERT])) * 1, 2);
            $volume_stock = round(str_replace(',','.',trim($data[self::CSV_VCI_STOCK])) * 1, 2);

            $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($etablissement->identifiant, $periode);
            if(!$drev) {
                echo "WARNING;aucune drev trouvée;pas d'import;$line\n";
                continue;
            }

            $drevProduit = $drev->addProduit($produit->getHash(), $data[self::CSV_VCI_MENTION_VALORISANTE]);
            $drevProduit->vci->stock_precedent = $volume_stock_precedent;
            $drevProduit->vci->constitue = $volume_constitue;
            $drevProduit->vci->rafraichi = $volume_rafraichi;
            $drevProduit->vci->complement = $volume_complement;
            $drevProduit->vci->substitution = $volume_substitution;
            $drevProduit->vci->destruction = $volume_distille_lies + $volume_distille;
            $drevProduit->update();
            if(round($drevProduit->vci->stock_final, 2) != $volume_stock) {
                echo "WARNING;Le stock final calculé n'est pas identique à celui du csv;$line\n";
                continue;
            }
            if(!$drev->isValidee()) {
                $drev->validate($periode."-12-10");
            }
            $drev->validateOdg($periode."-12-10", RegionConfiguration::getInstance()->getOdgRegion($produit->getHash()));
            $drev->save();
        }
    }

    protected function alias($produit) {
        $produit = preg_replace('/^Pouilly sur Loire$/', 'Pouilly sur Loire Blanc', $produit);
        return $produit;
    }

}
