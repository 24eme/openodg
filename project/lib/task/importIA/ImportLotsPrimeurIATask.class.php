<?php

class ImportLotsPrimeurIATask extends ImportLotsIATask
{

    protected function configure()
    {
        parent::configure();
        $this->namespace = 'import';
        $this->name = 'lots-primeur-ia';
        $this->briefDescription = 'Identification des lots primeur (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $this->initProduitsCepages();
        $this->etablissements = EtablissementAllView::getInstance()->getAll();
        $document = null;
        $ligne = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }
            $type = trim($data[self::CSV_TYPE]);
            if (!in_array($type, self::$typeAllowed)) {
                echo "SQUEEZE;lot non issu de la revendication, type : ".$type.";pas d'import;$line\n";
                continue;
            }
            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], $data[self::CSV_CODE_POSTAL]);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $produitKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_APPELLATION])." ".trim($data[self::CSV_COULEUR])));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_APPELLATION].' '.$data[self::CSV_COULEUR].";pas d'import;$line\n";
              continue;
            }
            $produit = $this->produits[$produitKey];
            $volume = str_replace(',','.',trim($data[self::CSV_VOLUME_INITIAL])) * 1;
            $periode = preg_replace('/\/.*/', '', trim($data[self::CSV_CAMPAGNE]));
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : $periode;
            $numeroDossier = sprintf("%05d", trim($data[self::CSV_NUM_DOSSIER]));
            $numeroLot = sprintf("%05d", trim($data[self::CSV_NUM_LOT_ODG]));
            $numero = trim($data[self::CSV_NUM_LOT_OPERATEUR]);
            $destinationDate = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_TRANSACTION_DATE]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $date = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_VALIDATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

            $mouvementLots = MouvementLotHistoryView::getInstance()->getMouvements($etablissement->identifiant, $periode."-".($periode+1), $numeroDossier, $numeroLot);
            if(!count($mouvementLots->rows)) {
                echo "WARNING;lot non trouvé ".$numeroLot.";pas d'import;$line\n";
                continue;
            }

            $document = DeclarationClient::getInstance()->find($mouvementLots->rows[0]->id);

            $lot = $document->getLot($mouvementLots->rows[0]->value->lot_unique_id);

            $lot->specificite = Lot::SPECIFICITE_PRIMEUR;
            $document->save();
        }
    }

}
