<?php

class ImportParcellaireManquantIAAOCTask extends importOperateurIACsvTask
{
  const CSV_ANNEE_DECLARATION = 0;
  const CSV_NOM_COMMUNE = 1;
  const CSV_RAISON_SOCIALE = 2;
  const CSV_CVI = 3;
  const CSV_SECTION = 4;
  const CSV_NUM_PARCELLE = 5;
  const CSV_ODG = 6;
  const CSV_PRODUIT = 7;
  const CSV_CEPAGE = 8;
  const CSV_SURFACE = 9;
  const CSV_SURFACE_HA = 10;
  const CSV_SURFACE_A = 11;
  const CSV_SURFACE_CA = 12;
  const CSV_DENSITE = 13;
  const CSV_RATIO_PIED_MANQUANT = 14;


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Identifiant etablissement"),
            new sfCommandArgument('periode', sfCommandArgument::REQUIRED, "Annee de la declaration de pieds manquants"),
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv de la declaration de pieds manquants")
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

        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($arguments['identifiant']);
        if (!$etablissement) {
            echo "Error: Etablissement ".$arguments['identifiant']." non trouvé\n";
            exit;
        }

        $parcellaireTotal = ParcellaireClient::getInstance()->getLast($etablissement->identifiant);
        if (!$parcellaireTotal) {
            $parcellaireTotal = new Parcellaire();
        }
        $manquant = ParcellaireManquantClient::getInstance()->findOrCreate($etablissement->identifiant, $arguments['periode']);
        foreach(file($arguments['csv']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }
            $found = false;
            if ($data[self::CSV_CVI] == $parcellaireTotal['declarant']->cvi) {
                foreach($parcellaireTotal->getParcelles() as $parcelle) {
                if ($parcelle->getSection() == strtoupper($data[self::CSV_SECTION]) &&
                        $parcelle->numero_parcelle == $data[self::CSV_NUM_PARCELLE]) {
                    $found = true;
                    break;
                }
            }
                if (!$found) {
                    $produitKey = $this->clearProduitKey(KeyInflector::slugify($this->alias($data[self::CSV_PRODUIT])));
                    if (!isset($this->produits[$produitKey])) {
                      echo "WARNING;produit non trouvé ".$data[self::CSV_PRODUIT]." ($produitKey);pas d'import;$line\n";
                      continue;
                    }
                    $produit = $this->produits[$produitKey];
                    $parcelle = $parcellaireTotal->addParcelleWithProduit($produit->getHash(), $data[self::CSV_PRODUIT], $data[self::CSV_CEPAGE], null, null, $data[self::CSV_NOM_COMMUNE], null, $data[self::CSV_SECTION], $data[self::CSV_NUM_PARCELLE]);
                }

                $manquantParcelle = $manquant->addParcelleFromParcellaireParcelle($parcelle);
                $manquantParcelle->densite = (float)$data[self::CSV_DENSITE];
                $manquantParcelle->superficie = (float)($data[self::CSV_SURFACE] / 10000);
                $manquantParcelle->pourcentage = $data[self::CSV_RATIO_PIED_MANQUANT];
            }
        }
        try {
            if(!$manquant->isValidee()) {
                $manquant->validate($arguments['periode']."-12-10");
            }
            $manquant->save();
        } catch(Exception $e) {
            sleep(60);
            if(!$manquant->isValidee()) {
                $manquant->validate($arguments['periode']."-12-10");
            }
            $manquant->save();
        }
    }

    protected function alias($produit) {
        $produit = preg_replace('/^Pouilly sur Loire$/', 'Pouilly sur Loire Blanc', $produit);
        return $produit;
    }

}
