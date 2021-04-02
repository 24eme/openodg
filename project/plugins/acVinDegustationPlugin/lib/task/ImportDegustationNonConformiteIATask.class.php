<?php

class ImportDegustationNonConformiteIATask extends ImportLotsIATask
{

    const CSV_CODE_NC = 0;
    const CSV_STATUT = 1;
    const CSV_DATE_STATUT = 2;
    const CSV_DEFAUT = 3;
    const CSV_GRAVITE = 4;
    const CSV_APPELLATION = 5;
    const CSV_COULEUR = 6;
    const CSV_MILLESIME = 7;
    const CSV_VOLUME = 8;
    const CSV_CUVE = 9;
    const CSV_NUM_LOT_OPERATEUR = 10;
    const CSV_RAISON_SOCIALE = 11;
    const CSV_CVI = 12;
    const CSV_ADRESSE_1 = 13;
    const CSV_ADRESSE_2 = 14;
    const CSV_CODE_POSTAL = 15;
    const CSV_VILLE = 16;
    const CSV_FACTURE = 17;

    const STATUT_DECLASSE = 'declassé';
    const STATUT_RECOURS_OC = 'recours_oc';
    const STATUT_LEVEE = "levée";

    protected static $statut_libelle = array(
        "Constatée" => null,
        "Déclassement du lot" => self::STATUT_DECLASSE,
        "Deuxième Passage" => null,
        "Deuxième Passage - Commission" => null,
        "Levée" => self::STATUT_LEVEE,
        "Notifiée" => null,
        "Traitée OC" => self::STATUT_RECOURS_OC,
        "Transmise OC" => self::STATUT_RECOURS_OC,
    );

    protected static $libelle2gravite = array(
        "grave" => Lot::CONFORMITE_NONCONFORME_GRAVE,
        "majeure" => Lot::CONFORMITE_NONCONFORME_MAJEUR,
        "mineure" => Lot::CONFORMITE_NONCONFORME_MINEUR
    );

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'degustations-non-conformite-ia';
        $this->briefDescription = 'Import des non conformités dans les dégustations (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        foreach(file($arguments['csv']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line,';');
            if (!$data) {
                continue;
            }

            $produitKey=null;
            if (isset($data[self::CSV_APPELLATION])){
                $produitKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_APPELLATION])." ".trim($data[self::CSV_COULEUR])));
            }

            if (!isset($this->produits[$produitKey])) {
                echo "WARNING;produit non trouvé;pas d'import;$line\n";
                continue;
            }
            $produit = $this->produits[$produitKey];

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], $data[self::CSV_CODE_POSTAL]);
            if (!$etablissement) {
                echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
                continue;
            }

            $numeroCuve = $data[self::CSV_NUM_LOT_OPERATEUR];
            $volume = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;
            $statut_date = $this->formatDate($data[self::CSV_DATE_STATUT]);

            $lots = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant, array('volume' => $volume, 'numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_NONCONFORME));

            if(!$lots) {
                $lots = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant, array('numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_NONCONFORME));
            }
            if(!$lots) {
                $lots = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant, array('volume' => $volume, 'numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_CONFORME));
            }
            if(!$lots) {
                $lots = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant, array('numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_CONFORME));
            }


            if(!count($lots)) {
                echo "ERROR;mouvement de lot d'origin non trouvé;$line\n";
                continue;
            }
            if(count($lots) > 1) {
                $lots_date = array();
                foreach($lots as $l) {
                    if(!isset($lots_date[$l->unique_id])) {
                        $lots_date[$l->unique_id] = $l;
                    }elseif($lots_date[$l->unique_id]->date < $l->date) {
                        $lots_date[$l->unique_id] = $l;
                    }
                }
                if (count($lots_date) != 1) {
                    echo "ERROR;plusieurs lots d'origin trouvés;$line\n";
                    continue;
                }
                $lot = array_shift($lots_date);
            }else {
                $lot = $lots[0];
            }
            $date_lot_securite =  date("Y-m-d",strtotime($lot->date." -6 months"));
            if ($date_lot_securite > $statut_date) {
                echo "ERROR: La Date d'un lot (".$lot->date." - $date_lot_securite) ne peut être suppérieure à la date de dégustation ($statut_date);$line\n";
                continue;
            }

            $degust = DegustationClient::getInstance()->find($lot->id_document);
            $lot = $degust->getLot($lot->unique_id);
            $lot->motif = $data[self::CSV_DEFAUT];
            $lot->conformite = self::$libelle2gravite[strtolower($data[self::CSV_GRAVITE])];

            if (self::$statut_libelle[$data[self::CSV_STATUT]] == self::STATUT_DECLASSE) {
                $degust->save();
                $declassmt = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, $statut_date, true);
                $declassmt->setLotOrigine($lot);
                $declassmt->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
                $declassmt->constructId();
                $declassmt->validate();
                $declassmt->validateOdg();
                try {
                    $declassmt->save();
                } catch(Exception $e) {
                    echo "ERROR;save error ".$e->getMessage().";$line\n";
                }
                continue;
            }elseif (self::$statut_libelle[$data[self::CSV_STATUT]] == self::STATUT_RECOURS_OC) {
                $lot->recoursOc($statut_date);
                $degust->generateMouvementsLots();
            }elseif (self::$statut_libelle[$data[self::CSV_STATUT]] == self::STATUT_LEVEE) {
                if (! $lot->id_document_affectation) {
                    $lot->conformeAppel($statut_date);
                    $degust->generateMouvementsLots();
                }
            }

            $degust->save();
        }
    }

    public function saveDegustation($degustation) {
        if($degustation->date > date('Y-m-d H:i:s')) {
            $degustation->etape = DegustationEtapes::ETAPE_LOTS;
        } else {
            $degustation->etape = DegustationEtapes::ETAPE_NOTIFICATIONS;
        }
        $degustation->save();
    }

    public function formatDate($date){
        if(!$date) {
            return null;
        }
        if(!isset($date[9])) {
            return null;
        }
        $jour=$date[0].$date[1];
        $mois=$date[3].$date[4];
        $annee=$date[6].$date[7].$date[8].$date[9];
        $d= $annee.'-'.$mois.'-'.$jour;
        return $d;
    }

}
