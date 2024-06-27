<?php

class ParcellaireImport
{

    const CSV_CVI = 2;
    const CSV_NOM_COMMUNE = 4;
    const CSV_LIEUDIT = 5;
    const CSV_SECTION = 7;
    const CSV_NUM_PARCELLE = 8;
    const CSV_SURFACE = 9;
    const CSV_ANNEE_PLANTATION = 10;
    const CSV_CEPAGE = 11;

    protected $datasByCVI = [];
    protected $datas = [];

    public function __construct($file) {
        foreach(file($file) as $numLigne => $line) {
            $data = str_getcsv($line, ';');
            $this->datas[$numLigne] = $data;
            $this->datasByCVI[$data[self::CSV_CVI]][$numLigne] = $data;
        }
    }

    public function getCsv()  {

        return $this->datas;
    }

    public function verification() {

        $erreurs = [];

        foreach($this->datasByCVI as $cvi => $datas) {
            $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);
            if (!$etablissement) {
                foreach($datas as $numLigne => $data) {
                    $erreurs[$numLigne] = ['message' => "Etablissement non trouvé",  'numLigne' => $numLigne, 'ligne' => $data];
                }
                continue;
            }
            $parcellaireTotal = ParcellaireClient::getInstance()->getLastByCampagne($etablissement->identifiant, '2024-2025');
            if (!$parcellaireTotal) {
                foreach($datas as $numLigne => $data) {
                    $erreurs[$numLigne] = ['message' => "Pas de parcellaire",  'numLigne' => $numLigne, 'ligne' => $data];
                }
                continue;
            }

            foreach($datas as $numLigne => $data) {
                $parcellesFound = [];

                $data[self::CSV_SURFACE] = str_replace(',', '.', trim($data[self::CSV_SURFACE]));
                if(!preg_match('/^[0-9\.]+$/', $data[self::CSV_SURFACE])) {
                    $erreurs[$numLigne] = ['message' => "Surface invalide",  'numLigne' => $numLigne];
                    continue;
                }
                $parcelleToFind = ParcellaireParcelle::freeInstance($parcellaireTotal);
                $parcelleToFind->lieu = strtoupper(trim($data[self::CSV_LIEUDIT]));
                $parcelleToFind->section = strtoupper(trim($data[self::CSV_SECTION]));
                $parcelleToFind->numero_parcelle = trim($data[self::CSV_NUM_PARCELLE]);
                $parcelleToFind->superficie = round(floatval($data[self::CSV_SURFACE]), 4);
                $parcelleToFind->cepage = trim($data[self::CSV_CEPAGE]);
                $parcelleToFind->campagne_plantation =  intval(explode("/", $data[self::CSV_ANNEE_PLANTATION])[0]);
                $parcelleFindedStrict = $parcellaireTotal->findParcelle($parcelleToFind, 1);
                $parcelleFindedLaxiste = $parcellaireTotal->findParcelle($parcelleToFind, 0.75);

                if($parcelleFindedStrict) {
                    continue;
                }

                if (!$parcelleFindedLaxiste) {
                    $erreurs[$numLigne] = ['message' => "Parcelle non trouvé",  'numLigne' => $numLigne, 'ligne' => $data];
                }

                if ($parcelleFindedLaxiste) {
                    $erreurs[$numLigne] = ['message' => "Parcelle ambigües",  'numLigne' => $numLigne, 'ligne' => $data];
                }
            }
        }

        return $erreurs;
    }

}
