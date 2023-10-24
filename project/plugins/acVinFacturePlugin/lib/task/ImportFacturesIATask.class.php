<?php

class ImportFacturesIATask extends importOperateurIACsvTask
{

    const CSV_FACTURE_NUM_FACTURE = 0;
    const CSV_FACTURE_TYPE = 1;
    const CSV_FACTURE_RAISON_SOCIALE = 2;
    const CSV_FACTURE_AOC = 3;
    const CSV_FACTURE_VOLUME = 4;
    const CSV_FACTURE_MONTANT_HT = 5;
    const CSV_FACTURE_MONTANT_TVA = 6;
    const CSV_FACTURE_MONTANT_FACTURE = 7;
    const CSV_FACTURE_IS_REGLE = 8;
    const CSV_FACTURE_ANNEE = 9;
    const CSV_FACTURE_DATE_FACTURE = 10;
    const CSV_FACTURE_MODE_REGLEMENT = 11;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, 'Region'),
        ));

        $this->namespace = 'import';
        $this->name = 'factures-ia';
        $this->briefDescription = 'Import des factures (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        sfContext::createInstance($this->configuration);
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $line = str_replace("\n", "", $line);
            $line = str_replace('SCEA DES FUMEES BLANCHES Prod', 'SAS DES FUMEES BLANCHES Prod', $line);
            $line = str_replace('SCEA DOMAINE DES FUMEES BLANCHES NV', 'SAS DOMAINE DES FUMEES BLANCHES NV', $line);
            $data = str_getcsv($line, ';');
            if (!$data || $data[self::CSV_FACTURE_RAISON_SOCIALE] == "Raison Sociale") {
              continue;
            }
            $etablissement = $this->identifyEtablissement($data[self::CSV_FACTURE_RAISON_SOCIALE], null, null);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_FACTURE_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            if (!$data[self::CSV_FACTURE_MONTANT_HT]) {
                echo "WARNING;MONTANT HT vide ou à 0;".$data[self::CSV_FACTURE_RAISON_SOCIALE].";pas d'import;$line\n";
                continue;
            }
            $date = strtok($data[self::CSV_FACTURE_DATE_FACTURE], '/');
            $date = strtok('/').'-'.$date;
            $date = strtok('/').'-'.$date;
            $mouvements = array();
            $facture = FactureClient::getInstance()->createEmptyDoc($etablissement, $date, "Facture importée - se référer au document originel et non à cette copie partielle", strtoupper($options['application']), null, $date);
            echo $facture->_id."\n";
            $f = FactureClient::getInstance()->find($facture->_id);
            if ($f) {
                $facture = $f;
            }
            $data[self::CSV_FACTURE_MONTANT_FACTURE] = str_replace(',', '.', $data[self::CSV_FACTURE_MONTANT_FACTURE]) * 1;
            $data[self::CSV_FACTURE_MONTANT_HT] = str_replace(',', '.', $data[self::CSV_FACTURE_MONTANT_HT]) * 1;
            $data[self::CSV_FACTURE_MONTANT_TVA] = str_replace(',', '.', $data[self::CSV_FACTURE_MONTANT_TVA]) *1 ;
//            $data[self::CSV_FACTURE_VOLUME] = str_replace(',', '.', $data[self::CSV_FACTURE_VOLUME]);
            $facture->add('taux_tva', 1 - $data[self::CSV_FACTURE_MONTANT_FACTURE] /  $data[self::CSV_FACTURE_MONTANT_HT]);
            $data[self::CSV_FACTURE_IS_REGLE] = ($data[self::CSV_FACTURE_IS_REGLE] == 'O');
            $line = $facture->add('lignes')->add('ligne_import_'.count($facture->lignes));
            $line->libelle = 'Import '.$data[self::CSV_FACTURE_AOC].' '.$data[self::CSV_FACTURE_ANNEE].' ';
            $detail = $line->add('details')->add();
            $detail->quantite = 1;
            $detail->montant_ht = $data[self::CSV_FACTURE_MONTANT_HT];
            $detail->montant_tva = $data[self::CSV_FACTURE_MONTANT_TVA];
            $detail->taux_tva = 1 - $data[self::CSV_FACTURE_MONTANT_FACTURE] /  $data[self::CSV_FACTURE_MONTANT_HT];
            $detail->prix_unitaire = $detail->montant_ht;
            $detail->libelle = $data[self::CSV_FACTURE_TYPE].' ('.$data[self::CSV_FACTURE_VOLUME].')';
            $facture->updateTotaux();
            $facture->montant_paiement += 0;
            $facture->versement_comptable_paiement = 0;
            if ($data[self::CSV_FACTURE_IS_REGLE]) {
                $paiement  = $facture->add("paiements")->add();
                $paiement->montant = $data[self::CSV_FACTURE_MONTANT_FACTURE] * 1;
                $paiement->commentaire = "Paiement importé";
                $facture->versement_comptable_paiement = 1;
                $facture->montant_paiement += $data[self::CSV_FACTURE_MONTANT_FACTURE] * 1;
            }
            $facture->versement_comptable = 1;
            $facture->numero_archive = $data[self::CSV_FACTURE_NUM_FACTURE];
            $facture->numero_odg = str_replace('_', '', $data[self::CSV_FACTURE_NUM_FACTURE]);
            $facture->date_emission = $date;
            if ($options['region']) {
                $facture->add('region', $options['region']);
            }
            $facture->save();
        }
    }

}
