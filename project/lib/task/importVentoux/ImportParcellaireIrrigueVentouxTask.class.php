<?php

class ImportParcellaireIrrigueVentouxTask extends ImportParcellaireAffectationVentouxTask
{
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

            $etablissement = $this->findEtablissement($data);

            if(!$etablissement) {
                echo "Error: Etablissement ".$data[self::CSV_CVI]." non trouvé;".implode(";", $data)."\n";
                continue;
            }

            $irrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, $periode);
            if(!$irrigable->isValidee()) {
                $irrigable->validate($periode.'-'.self::DATE_VALIDATION);
            }
            try {
                $irrigable->save();
            } catch(Exception $e) {
                sleep(60);
                $irrigable->save();
            }
            if (! $data[self::CSV_IRRIGUE] || ! $data[self::CSV_IRRIGABLE] || $data[self::CSV_IRRIGABLE] === 'NON') {
                continue;
            }

            $irrigableParcelle = $this->addParcelleFromData($irrigable, $data);
            if(!$irrigableParcelle) {
                continue;
            }

            $irrigableParcelle->materiel = $this->parseRessource($data[self::CSV_MATERIEL]);
            $irrigableParcelle->ressource = $this->parseRessource($data[self::CSV_MATERIEL]);
            try {
                $irrigable->save();
            } catch(Exception $e) {
                sleep(60);
                $irrigable->save();
            }

            $irrigue = ParcellaireIrrigueClient::getInstance()->createOrGetDocFromIdentifiantAndDate($etablissement->identifiant, $periode, true, $periode.'-'.self::DATE_VALIDATION);
            $irrigueParcelle = $this->addParcelleFromData($irrigue, $data);
            if(!$irrigueParcelle) {
                continue;
            }
            if(!$data[self::CSV_IRRIGUE]) {
                continue;
            }
            $irrigueParcelle->materiel = $this->parseRessource($data[self::CSV_MATERIEL]);
            $irrigueParcelle->ressource = $this->parseRessource($data[self::CSV_MATERIEL]);
            $irrigueParcelle->irrigation = 1;
            $irrigueParcelle->date_irrigation = $periode.'-'.self::DATE_VALIDATION;
            try {
                $irrigue->save();
            } catch(Exception $e) {
                sleep(60);
                $irrigue->save();
            }
        }
    }

    protected function addParcelleFromParcellaireParcelle($doc, $parcelle) {
        $produit = $parcelle->getProduit();
        $item = $doc->declaration->add('certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT');
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
