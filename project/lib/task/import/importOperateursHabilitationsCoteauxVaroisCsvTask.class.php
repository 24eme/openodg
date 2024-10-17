<?php

class importOperateursHabilitationsVentouxCsvTask extends sfBaseTask
{

    const CSV_NUMERO_ENREGISTREMENT = 0;
    const CSV_INTITULE = 1;
    const CSV_NOM = 2;
    const CSV_ADRESSE_1 = 3;
    const CSV_ADRESSE_2 = 4;
    const CSV_ADRESSE_3 = 5;
    const CSV_CODE_POSTAL = 6;
    const CSV_VILLE = 7;
    const CSV_CVI = 9;
    const CSV_SIRET = 10;
    const CSV_TELEPHONE = 11;
    const CSV_FAX = 12;
    const CSV_PORTABLE = 13;
    const CSV_EMAIL = 14;
    const CSV_ACTIVITE = 15;
    const CSV_OBSERVATION = 22;

    const CSV_DATE_SAISIE_IDENTIFICATION = 12;
    const CSV_PRODUCTION_RAISINS = 15;
    const CSV_VINIFICATION = 16;
    const CSV_ACHAT_VENTE_VRAC = 17;
    const CSV_CONDITIONNEMENT = 18;
    const CSV_TIREUSE = 19;
    const CSV_ETAT_HABILITATION = 20;
    const CSV_DATE_HABILITATION = 21;

    const hash_produit = 'certifications/AOC/genres/TRANQ/appellations/VTX';

    const activites = [
        self::CSV_PRODUCTION_RAISINS => HabilitationClient::ACTIVITE_PRODUCTEUR,
        self::CSV_VINIFICATION => HabilitationClient::ACTIVITE_VINIFICATEUR,
        self::CSV_ACHAT_VENTE_VRAC => HabilitationClient::ACTIVITE_VRAC,
        self::CSV_CONDITIONNEMENT => HabilitationClient::ACTIVITE_CONDITIONNEUR,
        self::CSV_TIREUSE => HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE,
    ];

    const status = [
        'habilité' => HabilitationClient::STATUT_HABILITE,
        'en cours' => HabilitationClient::STATUT_DEMANDE_HABILITATION
    ];

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('suspendu', null, sfCommandOption::PARAMETER_REQUIRED, "L'opérateur est suspendu", false),
        ));

        $this->namespace = 'import';
        $this->name = 'operateur-habilitation-coteaux-varois';
        $this->briefDescription = 'Import des opérateurs et habilitations de coteaux varois (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;

        // Conversion du fichier original en csv
        // xlsx2csv -l '\r\n' -d ";" $xlsxfilepath | tr -d "\n" | tr "\r" "\n" > $csvfilepath

    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $_ENV['DRY_RUN'] = true;

        $csvfile = fopen($arguments['csv'], 'r');

        if (! $csvfile) {
            throw new sfException("Impossible d'ouvrir le fichier " . $arguments['csv']);
        }

        while(($data = fgetcsv($csvfile, 1000, ";")) !== false) {
            if (!preg_match('/[0-9]+/', $data[self::CSV_NUMERO_ENREGISTREMENT])) {
                continue;
            }

            $etablissement = $this->findEtablissement($data);

            if(!$etablissement) {
                $etablissement = $this->importSocieteEtablissement($data, (bool)$options['suspendu']);
            }

            if ($etablissement === false) {
                echo "L'établissement n'a pas été créé ".implode(";", $data)."\n";
                continue;
            }

            if(!preg_match('/Opérateur Coteaux du Varois/', $etablissement->commentaire)) {
                $etablissement->addCommentaire("Opérateur Coteaux du Varois (n°".$data[self::CSV_NUMERO_ENREGISTREMENT].")");
            }

            if(trim($data[self::CSV_OBSERVATION])) {
                $etablissement->addCommentaire(trim($data[self::CSV_OBSERVATION]));
            }

            if(!isset($_ENV['DRY_RUN'])) {
                $etablissement->save();
            } else {
                print_r($etablissement);
            }

            continue;
            $this->importHabilitation($etablissement, $data, (bool)$options['suspendu']);
        }
    }

    protected function findEtablissement($data) {
        if($data[self::CSV_CVI]) {
            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CSV_CVI]);
            if($etablissement) {
                return $etablissement;
            }
        }
        if($data[self::CSV_SIRET]) {
            $etablissement = EtablissementClient::getInstance()->findByCvi($data[self::CSV_SIRET]);
            if($etablissement) {
                return $etablissement;
            }
        }

        return null;
    }

    private function importSocieteEtablissement($data, $suspendu = false)
    {
        $raison_sociale = trim(implode(' ', array_map('trim', [$data[self::CSV_INTITULE], $data[self::CSV_NOM]])));
        $newSociete = SocieteClient::getInstance()->createSociete($raison_sociale, SocieteClient::TYPE_OPERATEUR);

        $data = array_map('trim', $data);

        $societe = $newSociete;
        $societe->statut = SocieteClient::STATUT_ACTIF;
        $societe->siege->adresse = $data[self::CSV_ADRESSE_1] ?? null;
        $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2] ?? null;
        if($data[self::CSV_ADRESSE_3]) {
            $societe->siege->adresse_complementaire .= ", ".$data[self::CSV_ADRESSE_3];
        }
        $societe->siege->code_postal = $data[self::CSV_CODE_POSTAL] ?? null;
        $societe->siege->commune = $data[self::CSV_VILLE] ?? null;
        $societe->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE] ?? null);
        $societe->fax = Phone::format($data[self::CSV_FAX] ?? null);
        $societe->telephone_mobile = Phone::format($data[self::CSV_PORTABLE] ?? null);
        $societe->email = KeyInflector::unaccent($data[self::CSV_EMAIL] ?? null);
        $societe->siret = str_replace(" ", "", $data[self::CSV_SIRET] ?? null);

        try {
            if(isset($_ENV['DRY_RUN'])) {
                print_r($societe);
            } else {
                $societe->save();
            }
        } catch (Exception $e) {
            echo "$societe->_id save error :".$e->getMessage()."\n";
            return false;
        }

        $famille = null;

        if(strpos($data[self::CSV_ACTIVITE], 'Producteur de raisins') !== false) {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
        }

        if($famille == EtablissementFamilles::FAMILLE_PRODUCTEUR && strpos($data[self::CSV_ACTIVITE], 'Vinificateur') !== false) {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
        }

        if(!$famille && strpos($data[self::CSV_ACTIVITE], 'Vinificateur') !== false) {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
        }

        if(!$famille) {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
        }

        $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, $famille);
        $etablissement->nom = $raison_sociale;

        $cvi = null;
        if (isset($data[self::CSV_CVI])){
            $cvi = EtablissementClient::repairCVI($data[self::CSV_CVI]);
        }

        $etablissement->cvi = $cvi;
        $societe->pushAdresseTo($etablissement);
        $societe->pushContactTo($etablissement);

        if(!isset($_ENV['DRY_RUN'])) {
            $etablissement->save();
        }

        if($suspendu && !isset($_ENV['DRY_RUN'])) {
            $societe->switchStatusAndSave();
            $etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
        }

        return $etablissement;
    }

    private function importHabilitation($etablissement, $data, $suspendu = false)
    {
        $identifiant = $etablissement->identifiant;
        $date_demande  = ($data[self::CSV_DATE_SAISIE_IDENTIFICATION]) ? DateTime::createFromFormat('d/m/Y', explode(" ", $data[self::CSV_DATE_SAISIE_IDENTIFICATION])[0])->format('Y-m-d') : null;
        $date_decision = ($data[self::CSV_DATE_HABILITATION]) ? DateTime::createFromFormat('d/m/Y', explode(" ", $data[self::CSV_DATE_HABILITATION])[0])->format('Y-m-d') : null;

        $statut = self::status[trim(strtolower($data[self::CSV_ETAT_HABILITATION]))];
        $activites = [];

        foreach ([
            self::CSV_PRODUCTION_RAISINS => $data[self::CSV_PRODUCTION_RAISINS],
            self::CSV_VINIFICATION => $data[self::CSV_VINIFICATION],
            self::CSV_ACHAT_VENTE_VRAC => $data[self::CSV_ACHAT_VENTE_VRAC],
            self::CSV_CONDITIONNEMENT => $data[self::CSV_CONDITIONNEMENT],
            self::CSV_TIREUSE => $data[self::CSV_TIREUSE]
        ] as $key => $activite) {
            if (strtoupper($activite) === "X") {
                $activites[] = self::activites[$key];
            }
        }

        if($date_demande && $date_demande < $date_decision) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_demande, $activites, [], HabilitationClient::STATUT_DEMANDE_HABILITATION);
        }

        HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_decision, $activites, [], $statut);

        if($suspendu) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, date('Y-m-d'), $activites, [], HabilitationClient::STATUT_RETRAIT, $data[self::CSV_OBSERVATION]);
        }
    }
}
