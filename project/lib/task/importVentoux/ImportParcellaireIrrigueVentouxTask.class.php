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

    const DATE_VALIDATION = "04-15";

    protected $materiels;
    protected $ressources;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv"),
            new sfCommandArgument('periode', sfCommandArgument::REQUIRED, "Période")
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

        $periode = $arguments['periode'];

        $this->materiels = sfConfig::get('app_parcellaire_irrigable_materiels');
        $this->ressources = sfConfig::get('app_parcellaire_irrigable_ressources');

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

            $irrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, $periode);
            $irrigue = ParcellaireIrrigueClient::getInstance()->createOrGetDocFromIdentifiantAndDate($etablissement->identifiant, $periode, true, $periode.'-'.self::DATE_VALIDATION);

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
                    $parcelle = $parcellaireTotal->addParcelleWithProduit($produitHash, 'Ventoux', $data[self::CSV_CEPAGE], null, $data[self::CSV_NOM_COMMUNE], null, $data[self::CSV_SECTION], $data[self::CSV_NUM_PARCELLE]);
                    $parcelle->superficie = (float)($data[self::CSV_SURFACE]);
                } catch (Exception $e) {
                    echo $e->getMessage().";".$line;
                    continue;
                }
            }

            $parcelleIrrigableAjoutee = $this->addParcelleFromParcellaireParcelle($irrigable, $parcelle);
            $parcelleIrrigableAjoutee->materiel = $this->parseRessource($data[self::CSV_MATERIEL]);
            $parcelleIrrigableAjoutee->ressource = $this->parseRessource($data[self::CSV_MATERIEL]);

            $parcelleIrrigueAjoutee = $this->addParcelleFromParcellaireParcelle($irrigue, $parcelle);
            $parcelleIrrigueAjoutee->materiel = $this->parseRessource($data[self::CSV_MATERIEL]);
            $parcelleIrrigueAjoutee->ressource = $this->parseRessource($data[self::CSV_MATERIEL]);
            $parcelleIrrigueAjoutee->irrigation = $data[self::CSV_IRRIGUE] ? 1 : 0;
            $parcelleIrrigueAjoutee->date_irrigation = $parcelleIrrigueAjoutee->irrigation ? $periode.'-'.self::DATE_VALIDATION : null;

            try {
                if(!$irrigable->isValidee()) {
                    $irrigable->validate($periode.'-'.self::DATE_VALIDATION);
                }
                $irrigable->save();
            } catch(Exception $e) {
                sleep(60);
                if(!$irrigable->isValidee()) {
                    $irrigable->validate($periode.'-'.self::DATE_VALIDATION);
                }
                $irrigable->save();
            }

            try {
                if(!$irrigue->isValidee()) {
                    $irrigue->validate($periode.'-'.self::DATE_VALIDATION);
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

    protected function parseRessource($value)
    {
        if (! $value) {
            return null;
        }

        if ($value === "SCP" || $value === "scp") {
            $value = "Canal de Provence";
        }

        if (strpos($value, " SS ") !== false) {
            $value = str_replace(" SS ", " SOUS ", $value);
        }

        if (strpos(strtoupper($value), "CANAL D") === false && strlen($value) > 5 && strpos(strtoupper($value), "CANAL") !== strlen($value) - 5) {
            $value = str_replace("CANAL", "CANAL DE", strtoupper($value));
        }

        $value = str_replace('GOUTTE A', 'GOUTTE À', strtoupper($value));

        // Si match exact
        foreach ($this->ressources as $ressource) {
            if (mb_strtoupper($ressource) === mb_strtoupper($value)) {
                return $ressource;
            }
        }

        foreach ($this->materiels as $ressource) {
            if (mb_strtoupper($ressource) === mb_strtoupper($value)) {
                return $ressource;
            }
        }

        $value = ucfirst(mb_strtolower($value));

        return str_replace(
            ['ouveze', 'ventoux', 'Asa', 'Reseau', 'prive', 'Neant'],
            ['Ouveze', 'Ventoux', 'ASA', 'Réseau', 'privé', 'Néant'],
            $value
        );
    }
}
