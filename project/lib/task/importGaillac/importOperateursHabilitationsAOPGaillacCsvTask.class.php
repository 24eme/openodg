<?php

class importOperateursHabilitationsAOPGaillacCsvTask extends sfBaseTask
{

    const CSV_NUMERO_ENREGISTREMENT = 0;
    const CSV_RAISON_SOCIALE =  1;
    const CSV_CVI = 2;
    const CSV_SIRET = 3;
    const CSV_ADRESSE_1 = 4;
    const CSV_ADRESSE_2 = 5;
    const CSV_CODE_POSTAL = 6;
    const CSV_COMMUNE = 7;
    const CSV_DATE_DEPOT_DI = 8;
    const CSV_PRODUCTEUR_DE_RAISINS = 9;
    const CSV_PRODUCTEUR_DE_MOUTS = 10;
    const CSV_VINIFICATION = 11;
    const CSV_ELABORATION_DE_MOUSSEUX = 12;
    const CSV_ELEVAGE = 13;
    const CSV_ACHAT_DE_VINS_EN_VRAC = 14;
    const CSV_TRANSACTIONS_VRAC_VENTE_ENTRE_OPERATEURS = 15;
    const CSV_MISE_EN_MARCHÉ_VRAC_A_DESTINATION_CONSOMMATEUR = 16;
    const CSV_CONDITIONNEMENT = 17;
    const CSV_COOOPERATIVE_COOPERATEUR = 18;
    const CSV_OBSERVATIONS = 19;


    const hash_produit = 'certifications/AOP/genres/TRANQ/appellations/GLC';

    const activites = [
        self::CSV_PRODUCTEUR_DE_RAISINS => HabilitationClient::ACTIVITE_PRODUCTEUR,
        self::CSV_PRODUCTEUR_DE_MOUTS => HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS,
        self::CSV_VINIFICATION => HabilitationClient::ACTIVITE_VINIFICATEUR,
        self::CSV_ACHAT_DE_VINS_EN_VRAC => HabilitationClient::ACTIVITE_VRAC,
        self::CSV_CONDITIONNEMENT => HabilitationClient::ACTIVITE_CONDITIONNEUR,
        self::CSV_ELABORATION_DE_MOUSSEUX => HabilitationClient::ACTIVITE_ELABORATEUR,
        self::CSV_ELEVAGE => HabilitationClient::ACTIVITE_ELEVEUR,
        self::CSV_TRANSACTIONS_VRAC_VENTE_ENTRE_OPERATEURS => HabilitationClient::ACTIVITE_VRAC,
        self::CSV_MISE_EN_MARCHÉ_VRAC_A_DESTINATION_CONSOMMATEUR => HabilitationClient::ACTIVITE_VRAC,
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
        $this->name = 'operateur-habilitation-aopgaillac';
        $this->briefDescription = 'Import des opérateurs et habilitations de ventoux (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $csvfile = fopen($arguments['csv'], 'r');

        if (! $csvfile) {
            throw new sfException("Impossible d'ouvrir le fichier " . $arguments['csv']);
        }

        while(($data = fgetcsv($csvfile, 1000, ";")) !== false) {
            if (is_numeric($data[self::CSV_NUMERO_ENREGISTREMENT]) === false) {
                continue;
            }

            $etablissement = $this->importSocieteEtablissement($data, (bool)$options['suspendu']);
            if ($etablissement === false) {
                continue;
            }
            $this->importLien($etablissement, $data);
            $this->importHabilitation($etablissement, $data, (bool)$options['suspendu']);
        }
    }

    private function importLien($etablissement, $data) {
        if (!$data[self::CSV_COOOPERATIVE_COOPERATEUR]) {
            return;
        }
        if ($data[self::CSV_COOOPERATIVE_COOPERATEUR] === "1") {
            $etablissement->famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
            $etablissement->save();
            return;
        }
        $coop = EtablissementClient::getInstance()->findByCVI($data[self::CSV_COOOPERATIVE_COOPERATEUR]);
        $coop->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATEUR, $etablissement, !isset($_ENV['DRY_RUN']));
    }

    private function importSocieteEtablissement($data, $suspendu = false)
    {
        $e = null;
        if ($data[self::CSV_CVI]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_CVI]));
        }
        if (!$e && $data[self::CSV_SIRET]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_SIRET]));
        }
        if ($e) {
            echo("Etablissement existe " . $e->_id . ", ". $data[self::CSV_CVI]." ".$data[self::CSV_SIRET]."\n");
            if ($e->region == 'IGPTARN') {
                if ($e->num_interne) {
                    $e->num_interne .= "|";
                }
                $e->num_interne .= $data[self::CSV_NUMERO_ENREGISTREMENT];
                $e->addCommentaire("Etablissement partagé IGP - AOP (".date('d/m/Y').")");
                $e->region = 'IGPTARN|AOCGAILLAC';
            } else {
                $e->region = 'AOCGAILLAC';
            }
            if (!isset($_ENV['DRY_RUN'])) {
                $e->save();
            }
            return $e;
        }


        $raison_sociale = $data[self::CSV_RAISON_SOCIALE];
        $newSociete = SocieteClient::getInstance()->createSociete($raison_sociale, SocieteClient::TYPE_OPERATEUR, $data[self::CSV_NUMERO_ENREGISTREMENT]);

        $societe = SocieteClient::getInstance()->find($newSociete->_id);

        if($societe) {
            return false;
        }

        $data = array_map('trim', $data);

        $societe = $newSociete;
        $societe->statut = SocieteClient::STATUT_ACTIF;
        $societe->siege->adresse = $data[self::CSV_ADRESSE_1] ?? null;
        $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2] ?? null;
        $societe->siege->code_postal = $data[self::CSV_CODE_POSTAL] ?? null;
        $societe->siege->commune = $data[self::CSV_COMMUNE] ?? null;
        //$societe->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE] ?? null);
        //$societe->telephone_mobile = Phone::format($data[self::CSV_PORTABLE] ?? null);
        //$societe->email = KeyInflector::unaccent($data[self::CSV_EMAIL] ?? null);
        $societe->siret = str_replace(" ", "", $data[self::CSV_SIRET] ?? null);

        try {
            if (!isset($_ENV['DRY_RUN'])) {
                $societe->save();
            }
        } catch (Exception $e) {
            echo "$societe->_id save error :".$e->getMessage()."\n";
            return false;
        }

        $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;

        if ($data[self::CSV_PRODUCTEUR_DE_RAISINS] === "1" || $data[self::CSV_PRODUCTEUR_DE_MOUTS] === "1") {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;

            if ($data[self::CSV_VINIFICATION] === "1") {
                $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
            }
        }

        if ($data[self::CSV_ACHAT_DE_VINS_EN_VRAC] === "1" || $data[self::CSV_CONDITIONNEMENT] === "1") {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;

            if ($data[self::CSV_VINIFICATION] === "1") {
                $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
            }
        }

        if ($data[self::CSV_COOOPERATIVE_COOPERATEUR] === "1") {
            $famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
        }

        $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, $famille);
        $etablissement->nom = $raison_sociale;

        $cvi = null;
        if (isset($data[self::CSV_CVI])){
            $cvi = EtablissementClient::repairCVI($data[self::CSV_CVI]);
        }

        $etablissement->cvi = $cvi;
        $etablissement->num_interne = $data[self::CSV_NUMERO_ENREGISTREMENT];
        $etablissement->commentaire = trim($data[self::CSV_OBSERVATIONS]) ? $data[self::CSV_OBSERVATIONS] : null;
        $etablissement->region = 'AOCGAILLAC';

        $societe->pushAdresseTo($etablissement);
        $societe->pushContactTo($etablissement);
        if (!isset($_ENV['DRY_RUN'])) {
            $etablissement->save();
        }

        if($suspendu) {
            if (!isset($_ENV['DRY_RUN'])) {
                $societe->switchStatusAndSave();
            }
            $etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
        }

        return $etablissement;
    }

    const mois = ['janv' => '01', 'févr' => '02', 'mars' => '03', 'avr' => '04', 'mai' => '05', 'juin' => '06', 'juil' => '07', 'août' => '08', 'sept' => '09', 'oct' => '10', 'nov' => '11', 'déc' => '12'];

    private function importHabilitation($etablissement, $data, $suspendu = false)
    {
        $identifiant = $etablissement->identifiant;
        $dates = trim($data[self::CSV_DATE_DEPOT_DI]);
        $dates = preg_replace('/([0-9][0-9]) ([0-9][0-9]) ([0-9][0-9][0-9][0-9])/', '\1\/\2\/\3', $dates);
        $dates = explode(" ", $dates);
        $date_decision = end($dates);
        if (preg_match('/([0-9]+)-([^-]*[a-z][^-\.]*)\.?-([0-9][0-9][0-9][0-9])/', $date_decision, $m) && isset(self::mois[$m[2]])) {
            $date_decision = sprintf('%04d-%02d-%02d', $m[3], self::mois[$m[2]], $m[1]);
        }elseif (preg_match('/([0-9]+)-([^-]*[a-z][^-\.]*)\.?-([0-9][0-9])/', $date_decision, $m) && isset(self::mois[$m[2]])) {
            $date_decision = sprintf('20%02d-%02d-%02d', $m[3], self::mois[$m[2]], $m[1]);
        }elseif (preg_match('/([0-9]+)\/([0-9]+)\/([0-9][0-9][0-9][0-9])/', $date_decision, $m)) {
            $date_decision = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }elseif (preg_match('/^([0-9]+)\/([0-9]+)\/([0-9][0-9])$/', $date_decision, $m)) {
            $date_decision = sprintf('20%02d-%02d-%02d', $m[3], $m[2], $m[1]);
        }else{
            echo "unsupported date format : ".$date_decision."\n";
            return;
        }

        $statut = HabilitationClient::STATUT_HABILITE;
        $activites = [];

        foreach ([
            self::CSV_PRODUCTEUR_DE_RAISINS => $data[self::CSV_PRODUCTEUR_DE_RAISINS],
            self::CSV_PRODUCTEUR_DE_MOUTS => $data[self::CSV_PRODUCTEUR_DE_MOUTS],
            self::CSV_VINIFICATION => $data[self::CSV_VINIFICATION],
            self::CSV_ACHAT_DE_VINS_EN_VRAC => $data[self::CSV_ACHAT_DE_VINS_EN_VRAC],
            self::CSV_CONDITIONNEMENT => $data[self::CSV_CONDITIONNEMENT],
        ] as $key => $activite) {
            if ($activite === "1") {
                $activites[] = self::activites[$key];
            }
        }

        if (!isset($_ENV['DRY_RUN'])) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_decision, $activites, [], $statut);
        }
        /*
        if($suspendu) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, date('Y-m-d'), $activites, [], HabilitationClient::STATUT_RETRAIT, $data[self::CSV_OBSERVATION]);
        }
        */
    }
}
