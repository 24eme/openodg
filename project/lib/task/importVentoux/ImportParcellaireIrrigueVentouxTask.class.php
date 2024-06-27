<?php

class ImportParcellaireIrrigueVentouxTask extends sfBaseTask
{
    const CSV_CVI = 2;
    const CSV_NOM_COMMUNE = 4;
    const CSV_SECTION = 7;
    const CSV_NUM_PARCELLE = 8;
    const CSV_SURFACE = 9;
    const CSV_CEPAGE = 11;

    const CSV_IRRIGABLE = 16;
    const CSV_MATERIEL = 18;
    const CSV_IRRIGUE = 19;

    const DATE_VALIDATION = "2023-04-15";

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaireirrigue-ventoux';
        $this->briefDescription = 'Import des parcellaires irrigués';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            if (! $data[self::CSV_IRRIGUE] || ! $data[self::CSV_IRRIGABLE] || $data[self::CSV_IRRIGABLE] === 'NON') {
                continue;
            }

            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CSV_CVI]);
            if (!$etablissement) {
                echo "Error: Etablissement ".$data[self::CSV_CVI]." non trouvé\n";
                continue;
            }
            $parcellaireTotal = ParcellaireClient::getInstance()->getLast($etablissement->identifiant);
            if (!$parcellaireTotal) {
                $parcellaireTotal = new Parcellaire();
                echo "Parcellaire non trouvé;".$line;
            }

            $irrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, "2023");
            $irrigue = ParcellaireIrrigueClient::getInstance()->createOrGetDocFromIdentifiantAndDate($etablissement->identifiant, "2023", true, self::DATE_VALIDATION);

            $found = false;
            foreach($parcellaireTotal->getParcelles() as $parcelle) {
                if ($parcelle->getSection() == strtoupper($data[self::CSV_SECTION]) &&
                    $parcelle->numero_parcelle == $data[self::CSV_NUM_PARCELLE]) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $produitHash = '/declaration/certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT/couleurs';
                if(preg_match('/ B$/', $data[self::CSV_CEPAGE])) {
                    $produitHash .= '/blanc/cepages/DEFAUT';
                } else {
                    $produitHash .= '/rouge/cepages/DEFAUT';
                }
                try {
                    $parcelle = $parcellaireTotal->addParcelleWithProduit($produitHash, $data[self::CSV_CEPAGE], null, $data[self::CSV_NOM_COMMUNE], null, $data[self::CSV_SECTION], $data[self::CSV_NUM_PARCELLE]);
                    $parcelle->superficie = (float)($data[self::CSV_SURFACE]);
                } catch (Exception $e) {
                    echo $e->getMessage().";".$line;
                    continue;
                }
            }

            $parcelleIrrigableAjoutee = $this->addParcelleFromParcellaireParcelle($irrigable, $parcelle);
            $parcelleIrrigableAjoutee->materiel = $data[self::CSV_MATERIEL] ?: '';
            $parcelleIrrigableAjoutee->ressource = $parcelleIrrigableAjoutee->materiel;

            $parcelleIrrigueAjoutee = $this->addParcelleFromParcellaireParcelle($irrigue, $parcelle);
            $parcelleIrrigueAjoutee->materiel = $data[self::CSV_MATERIEL] ?: '';
            $parcelleIrrigueAjoutee->ressource = $parcelleIrrigueAjoutee->materiel;
            $parcelleIrrigueAjoutee->irrigation = $data[self::CSV_IRRIGUE] ? 1 : 0;
            $parcelleIrrigueAjoutee->date_irrigation = $parcelleIrrigueAjoutee->irrigation ? self::DATE_VALIDATION : null;

            try {
                if(!$irrigable->isValidee()) {
                    $irrigable->validate(self::DATE_VALIDATION);
                }
                $irrigable->save();
            } catch(Exception $e) {
                sleep(60);
                if(!$irrigable->isValidee()) {
                    $irrigable->validate(self::DATE_VALIDATION);
                }
                $irrigable->save();
            }

            try {
                if(!$irrigue->isValidee()) {
                    $irrigue->validate(self::DATE_VALIDATION);
                }
                $irrigue->save();
            } catch(Exception $e) {
                echo $e->getMessage().";".$line;
            }
        }
    }

    protected function addParcelleFromParcellaireParcelle($doc, $parcelle) {
        $produit = $parcelle->getProduit();
        $item = $doc->declaration->add(str_replace('/declaration/', null, preg_replace('|/couleurs/.*$|', '', $produit->getHash())));
        $item->libelle = $produit->libelle;
        $subitem = $item->detail->add($parcelle->getKey());
        ParcellaireClient::CopyParcelle($subitem, $parcelle);

        return $subitem;
    }
}
