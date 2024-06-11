<?php

class ImportParcellaireAffectationVentouxTask extends sfBaseTask
{
    const CSV_CVI = 2;
    const CSV_NOM_COMMUNE = 4;
    const CSV_SECTION = 7;
    const CSV_NUM_PARCELLE = 8;
    const CSV_SURFACE = 9;
    const CSV_CEPAGE = 11;

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
        $this->name = 'parcellaireaffectation-ventoux';
        $this->briefDescription = 'Import des affectations parcellaire';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

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
            $affectation = ParcellaireAffectationClient::getInstance()->findOrCreate($etablissement->identifiant, "2023");
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
                $parcelle = $parcellaireTotal->addParcelle($produitHash, $data[self::CSV_CEPAGE], null, $data[self::CSV_NOM_COMMUNE], null, $data[self::CSV_SECTION], $data[self::CSV_NUM_PARCELLE]);
                $parcelle->superficie = (float)($data[self::CSV_SURFACE]);
            }
            $affectationParcelle = $this->addParcelleFromParcellaireParcelle($affectation, $parcelle);

            $affectationParcelle->affectee = 1;
            $affectationParcelle->superficie_affectation = (float) $data[self::CSV_SURFACE];
            $affectationParcelle->date_affectation = "2023-04-30";

            try {
                if(!$affectation->isValidee()) {
                    $affectation->validate("2023-04-30");
                }
                $affectation->save();
            } catch(Exception $e) {
                sleep(60);
                if(!$affectation->isValidee()) {
                    $affectation->validate("2023-04-30");
                }
                $affectation->save();
            }
        }
    }

    protected function addParcelleFromParcellaireParcelle($affectation, $detail) {
        $produit = $detail->getProduit();
        $item = $affectation->declaration->add(str_replace('/declaration/', null, preg_replace('|/couleurs/.*$|', '', $produit->getHash())));
        $item->libelle = $produit->libelle;
        $subitem = $item->detail->add($detail->getKey());

            $subitem->superficie = $detail->superficie;
            $subitem->commune = $detail->commune;
            $subitem->code_commune = $detail->code_commune;
            $subitem->prefix = $detail->prefix;
            $subitem->section = $detail->section;
            $subitem->numero_parcelle = $detail->numero_parcelle;
            $subitem->idu = $detail->idu;
            $subitem->lieu = $detail->lieu;
            $subitem->cepage = $detail->cepage;
            $subitem->active = 1;
            $subitem->remove('vtsgn');
            if($detail->exist('vtsgn')) {
                $subitem->add('vtsgn', (int)$detail->vtsgn);
            }
            $subitem->campagne_plantation = ($detail->exist('campagne_plantation'))? $detail->campagne_plantation : null;

        return $subitem;
    }
}
