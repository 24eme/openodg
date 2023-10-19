<?php

class ImportParcellaireManquantIAAOCTask extends importOperateurIACsvTask
{
  const CSV_RAISON_SOCIALE = 0;
  const CSV_CVI = 6;
  const CSV_VOLUME = 7;
  const CSV_SUPERFICIE = 8;
  const CSV_PRODUIT = 9;
  const CSV_MENTION_VALORISANTE = 10;
  const CSV_AOC = 12;
  const CSV_MILLESIME = 11;
  const CSV_MOIS_PRESENTATION = 7;
  const CSV_DATE_DECLARATION = 8;
  const CSV_DATE_COMMISSION = 9;
  const CSV_LOGEMENT = 10;
  const CSV_NUM_LOT = 11;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv de la drev")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellairemanquant-ia-aoc';
        $this->briefDescription = 'Import des déclarations de pieds manquants';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $ligne = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }
            print_r($data);
            continue;

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

            $drevProduit = $drev->addProduit($produit->getHash(), $data[self::CSV_MENTION_VALORISANTE]);
            $drevProduit->volume_revendique_issu_recolte = $volume;
            $drevProduit->superficie_revendique = $superficie;
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
    }

    protected function alias($produit) {
        $produit = preg_replace('/^Pouilly sur Loire$/', 'Pouilly sur Loire Blanc', $produit);
        return $produit;
    }

}
