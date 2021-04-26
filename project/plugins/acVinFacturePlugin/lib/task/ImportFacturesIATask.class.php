<?php

class ImportFacturesIATask extends importOperateurIACsvTask
{

    const CSV_FACTURE_NUM_DOSSIER = 0;
    const CSV_FACTURE_NUM_FACTURE = 1;
    const CSV_FACTURE_DATE_FACTURE = 2;
    const CSV_FACTURE_CAMPAGNE = 3;
    const CSV_FACTURE_TYPE = 4;
    const CSV_FACTURE_RAISON_SOCIALE = 5;
    const CSV_FACTURE_CODE_POSTAL = 6;
    const CSV_FACTURE_VILLE = 7;
    const CSV_FACTURE_IGP = 8;
    const CSV_FACTURE_VOLUME = 9;
    const CSV_FACTURE_MONTANT_FACTURE = 10;
    const CSV_FACTURE_TOTAL_REGLE = 11;


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
            $etablissement = $this->identifyEtablissement($data[self::CSV_FACTURE_RAISON_SOCIALE], null, $data[self::CSV_FACTURE_CODE_POSTAL]);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_FACTURE_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $date = strtok($data[self::CSV_FACTURE_DATE_FACTURE], '/');
            $date = strtok('/').'-'.$date;
            $date = strtok('/').'-'.$date;
            $mouvements = array();
            $facture = FactureClient::getInstance()->createEmptyDoc($etablissement, $date, "Facture importée", strtoupper($options['application']), null, $date);
            echo $facture->_id."\n";
            $f = FactureClient::getInstance()->find($facture->_id);
            if ($f) {
                $facture = $f;
            }
            $data[self::CSV_FACTURE_MONTANT_FACTURE] = str_replace(',', '.', $data[self::CSV_FACTURE_MONTANT_FACTURE]);
            $data[self::CSV_FACTURE_VOLUME] = str_replace(',', '.', $data[self::CSV_FACTURE_VOLUME]);
            $data[self::CSV_FACTURE_TOTAL_REGLE] = str_replace(',', '.', $data[self::CSV_FACTURE_TOTAL_REGLE]);
            $line = $facture->add('lignes')->add('ligne_import_'.count($facture->lignes));
            $line->libelle = 'Import dossier '.$data[self::CSV_FACTURE_NUM_DOSSIER];
            $detail = $line->add('details')->add();
            $detail->quantite = $data[self::CSV_FACTURE_VOLUME] * 1;
            $detail->montant_ht = $data[self::CSV_FACTURE_MONTANT_FACTURE] / 1.2;
            $detail->taux_tva = 0.2;
            $detail->montant_tva = ($data[self::CSV_FACTURE_MONTANT_FACTURE] * 1) - $detail->montant_ht;
            $detail->prix_unitaire = $detail->montant_ht / $detail->quantite;
            $detail->libelle = $data[self::CSV_FACTURE_IGP];
            $facture->updateTotaux();
            $facture->montant_paiement += 0;
            $facture->versement_comptable_paiement = 0;
            if ($data[self::CSV_FACTURE_TOTAL_REGLE]) {
                $paiement  = $facture->add("paiements")->add();
                $paiement->montant = $data[self::CSV_FACTURE_TOTAL_REGLE] * 1;
                $paiement->commentaire = "Paiement importé";
                $facture->versement_comptable_paiement = 1;
                $facture->montant_paiement += $data[self::CSV_FACTURE_TOTAL_REGLE] * 1;
            }
            $facture->versement_comptable = 1;
            $facture->campagne = $data[self::CSV_FACTURE_CAMPAGNE];
            $facture->numero_archive = $data[self::CSV_FACTURE_NUM_FACTURE];
            $facture->numero_odg = str_replace('_', '', $data[self::CSV_FACTURE_NUM_FACTURE]);
            $facture->date_emission = $date;
            $facture->save();
        }
    }

}
