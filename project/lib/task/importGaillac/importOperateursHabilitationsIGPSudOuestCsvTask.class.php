<?php

class importOperateursHabilitationsIGPSudOuestCsvTask extends sfBaseTask
{
    const CSV_TYPE_ENREGISTREMENT = 0;
    const CSV_NUMERO_OPERATEUR = 1;
    const CSV_HABILITATION_RAISONSOCIALE = 2;
    const CSV_HABILITATION_NOMENTREPRISE = 3;
    const CSV_HABILITATION_SIRET = 4;
    const CSV_HABILITATION_CVI = 5;
    const CSV_HABILITATION_PPM = 6;
    const CSV_HABILITATION_ACCISE = 7;
    const CSV_HABILITATION_TVA = 8;
    const CSV_HABILITATION_ADRESSE = 9;
    const CSV_HABILITATION_CODEPOSTAL = 10;
    const CSV_HABILITATION_VILLE = 11;
    const CSV_HABILITATION_TEL = 12;
    const CSV_HABILITATION_FAX = 13;
    const CSV_HABILITATION_PORTABLE = 14;
    const CSV_HABILITATION_COURRIEL = 15;
    const CSV_HABILITATION_PRODUIT = 16;
    const CSV_HABILITATION_ACTIVITE = 17;
    const CSV_HABILITATION_STATUT = 18;
    const CSV_CHAIS_ACTIVITE = 2;
    const CSV_CHAIS_SITE = 3;
    const CSV_CHAIS_ADRESSE = 4;
    const CSV_CHAIS_CODEPOSTAL = 5;
    const CSV_CHAIS_VILLE = 6;
    const CSV_CHAIS_TELS = 7;


    private $habilitation_hash_produits =  [
        "Ariège" => "certifications/IGP/genres/TRANQ/appellations/ARG",
        "Aveyron" => "certifications/IGP/genres/TRANQ/appellations/AVR",
        "Comté Tolosan" => "certifications/IGP/genres/TRANQ/appellations/COT",
        "Lavilledieu" => "certifications/IGP/genres/TRANQ/appellations/LVD",
        "Thézac-Perricard" => "certifications/IGP/genres/TRANQ/appellations/TZP"
    ];


    private $habilitation_activite = [
        "Achat et vente de vin en vrac" => [HabilitationClient::ACTIVITE_VRAC],
        "Conditionnement" => [HabilitationClient::ACTIVITE_CONDITIONNEUR],
        "Elevage" => [HabilitationClient::ACTIVITE_ELEVEUR],
        "Négociant" => [HabilitationClient::ACTIVITE_NEGOCIANT],
        "Production de raisin" => [HabilitationClient::ACTIVITE_PRODUCTEUR],
        "Vinification" => [HabilitationClient::ACTIVITE_VINIFICATEUR],
        "Négociant conditionneur" => [HabilitationClient::ACTIVITE_NEGOCIANT, HabilitationClient::ACTIVITE_CONDITIONNEUR],
        "Vinificateur conditionneur" => [HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_CONDITIONNEUR],
        "Apporteur au négoce vinificateur" => [HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS],
        "Apporteur en cave coopérative" => [HabilitationClient::ACTIVITE_PRODUCTEUR],
        "Négociant vrac" => [HabilitationClient::ACTIVITE_NEGOCIANT, HabilitationClient::ACTIVITE_VRAC],
    ];

    private $habilitation_statut = [
        "Habilitation" => HabilitationClient::STATUT_HABILITE,
        "Retrait" => HabilitationClient::STATUT_RETRAIT,
        "Habilitation en cours" => HabilitationClient::STATUT_DEMANDE_HABILITATION,
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
        $this->name = 'operateur-habilitation-igpsudouest';
        $this->briefDescription = 'Import des opérateurs et habilitations de Sud-Ouest (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    private $etablissements = [];

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
            if (isset($data[self::CSV_NUMERO_OPERATEUR])) {
                $data[self::CSV_NUMERO_OPERATEUR] = str_replace('ENT', '', $data[self::CSV_NUMERO_OPERATEUR]);
                if (is_numeric($data[self::CSV_NUMERO_OPERATEUR]) === false) {
                    continue;
                }
            } else {
                continue;
            }
            if (isset($this->etablissements[$data[self::CSV_NUMERO_OPERATEUR]])) {
                $etablissement = EtablissementClient::getInstance()->find($this->etablissements[$data[self::CSV_NUMERO_OPERATEUR]]->_id);
            }else{
                $etablissement = $this->importSocieteEtablissement($data, (bool)$options['suspendu']);
                $this->etablissements[$data[self::CSV_NUMERO_OPERATEUR]] = $etablissement;
            }

            if (!$etablissement) {
                print_r(["pas d'etablissement pour ", $data]);
                continue;
            }

            $this->importHabilitation($etablissement, $data, (bool)$options['suspendu']);
            $this->importChais($etablissement, $data);

        }
    }

    private function importSocieteEtablissement($data, $suspendu = false)
    {
        $e = null;
        if ($data[self::CSV_TYPE_ENREGISTREMENT] != 'HABILITATION') {
            return;
        }
        if ($data[self::CSV_HABILITATION_CVI]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_HABILITATION_CVI]));
        }
        if (!$e && $data[self::CSV_HABILITATION_SIRET]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_HABILITATION_SIRET]));
        }
        if ($e) {
            echo("Etablissement existe " . $e->_id . ", ". $data[self::CSV_HABILITATION_CVI]." ".$data[self::CSV_HABILITATION_SIRET]."\n");
            return $e;
        }

        $societe = SocieteClient::getInstance()->findBySiret(str_replace(' ', '', $data[self::CSV_HABILITATION_SIRET]));

        if (!$societe) {

            $raison_sociale = trim(implode(' ', array_map('trim', [$data[self::CSV_HABILITATION_RAISONSOCIALE]])));
            $newSociete = SocieteClient::getInstance()->createSociete($raison_sociale, SocieteClient::TYPE_OPERATEUR, $data[self::CSV_NUMERO_OPERATEUR]);

            $societe = SocieteClient::getInstance()->find($newSociete->_id);

            if($societe) {
                return $societe->getEtablissementPrincipal();
            }

            $data = array_map('trim', $data);

            $societe = $newSociete;
            $societe->statut = SocieteClient::STATUT_ACTIF;

            $societe->siret = str_replace(" ", "", $data[self::CSV_HABILITATION_SIRET] ?? null);
            $societe->no_tva_intracommunautaire = $data[self::CSV_HABILITATION_TVA];

            $societe->siege->adresse = $data[self::CSV_HABILITATION_ADRESSE] ?? null;
            $societe->siege->code_postal = $data[self::CSV_HABILITATION_CODEPOSTAL] ?? null;
            $societe->siege->commune = $data[self::CSV_HABILITATION_VILLE] ?? null;
            $societe->telephone_bureau = Phone::format($data[self::CSV_HABILITATION_TEL] ?? null);
            $societe->fax = Phone::format($data[self::CSV_HABILITATION_FAX] ?? null);
            $societe->telephone_mobile = Phone::format($data[self::CSV_HABILITATION_PORTABLE] ?? null);
            $societe->email = KeyInflector::unaccent($data[self::CSV_HABILITATION_COURRIEL] ?? null);

            try {
                $societe->save();
            } catch (Exception $e) {
                echo "$societe->_id save error :".$e->getMessage()."\n";
                return false;
            }

        }

        $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;

        $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, $famille);
        $etablissement->nom = $raison_sociale;

        $cvi = null;
        if (isset($data[self::CSV_HABILITATION_CVI])){
            $cvi = EtablissementClient::repairCVI($data[self::CSV_HABILITATION_CVI]);
        }

        $etablissement->cvi = $cvi;
        $etablissement->ppm = $data[self::CSV_HABILITATION_PPM];
        $etablissement->no_accises = $data[self::CSV_HABILITATION_ACCISE];

        $societe->pushAdresseTo($etablissement);
        $societe->pushContactTo($etablissement);
        $etablissement->save();

        if($suspendu) {
            $societe->switchStatusAndSave();
            $etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
        }

        return $etablissement;
    }

    private function importHabilitation($etablissement, $data, $suspendu = false)
    {
        if ($data[self::CSV_TYPE_ENREGISTREMENT] != 'HABILITATION') {
            return;
        }
        $identifiant = $etablissement->identifiant;
        $hash_produit = $this->habilitation_hash_produits[$data[self::CSV_HABILITATION_PRODUIT]];
        $date_decision = '2000-01-01';
        $activites = $this->habilitation_activite[$data[self::CSV_HABILITATION_ACTIVITE]];
        if (!strlen($data[self::CSV_HABILITATION_STATUT]) < 3) {
            return;
        }
        $statut = $this->habilitation_statut[$data[self::CSV_HABILITATION_STATUT]];

        $hab = HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, $hash_produit, $date_decision, $activites, [], $statut);

        if($suspendu) {
            $hab = HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, $hash_produit, date('Y-m-d'), $activites, [], HabilitationClient::STATUT_RETRAIT);
        }
        if (!$hab) {
            print_r(["Erreur habilitation", $identifiant, $hash_produit, $date_decision, $activites, [], $statut]);
            return;
        }

        $is_prod = false;
        $is_vinif = false;
        foreach ($hab->declaration as $hash => $h) {
            foreach($h->activites as $activite_key => $activite) {
                if ($activite->statut != HabilitationClient::STATUT_HABILITE) {
                    continue;
                }
                switch ($activite->activite) {
                    case HabilitationClient::ACTIVITE_PRODUCTEUR:
                        $is_prod = true;
                        break;
                    case HabilitationClient::ACTIVITE_VINIFICATEUR:
                        $is_vinif = true;
                        break;
                    default:
                        // code...
                        break;
                }
            }
        }
        if ($is_prod) {
            if ($is_vinif) {
                if ($etablissement->famille != EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR) {
                    $etablissement->famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
                    $etablissement->save();
                }
            } else {
                if ($etablissement->famille != EtablissementFamilles::FAMILLE_PRODUCTEUR) {
                    $etablissement->famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
                    $etablissement->save();
                }
            }
        } else {
            if ($is_vinif) {
                if ($etablissement->famille != EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR) {
                    $etablissement->famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
                    $etablissement->save();
                }
            } else {
                if ($etablissement->famille != EtablissementFamilles::FAMILLE_NEGOCIANT) {
                    $etablissement->famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
                    $etablissement->save();
                }
            }
        }
        return $etablissement;
    }

    private function importChais($etablissement, $data)
    {
        if ($data[self::CSV_TYPE_ENREGISTREMENT] != 'CHAIS') {
            return;
        }
        if (!$data[self::CSV_CHAIS_ADRESSE]) {
            return;
        }

        $chais = $etablissement->getOrAdd('chais')->add();

        $nom_chais = strtolower($data[self::CSV_CHAIS_SITE]);
        $nom_reversed = implode(' ', array_reverse(explode(' ', $nom_chais)));
        if ( ($nom_chais != strtolower($etablissement->raison_sociale) && $nom_reversed != strtolower($etablissement->raison_sociale)) ||
             strtolower($data[self::CSV_CHAIS_ADRESSE]) != strtolower($etablissement->adresse) ||
             strtolower($data[self::CSV_CHAIS_CODEPOSTAL]) != strtolower($etablissement->code_postal) ||
             strtolower($data[self::CSV_CHAIS_VILLE]) != strtolower($etablissement->commune) )
        {

            $chais->nom = $data[self::CSV_CHAIS_SITE];
            $chais->adresse = $data[self::CSV_CHAIS_ADRESSE];
            $chais->code_postal = $data[self::CSV_CHAIS_CODEPOSTAL];
            $chais->commune = $data[self::CSV_CHAIS_VILLE];
        }
        $chais->archive = false;

        switch ($data[self::CSV_CHAIS_ACTIVITE]) {
            case 'EntreposageConditionnementVinification':
                $chais->attributs->add("STOCKAGE_VRAC", "Stockage Vin en Vrac");
            case 'ConditionnementVinification':
                $chais->attributs->add("VINIFICATION", "Chai de vinification");
            case 'Conditionnement':
                $chais->attributs->add("CONDITIONNEMENT", "Centre de conditionnement");
                break;
            case 'EntreposageVinification':
                $chais->attributs->add("VINIFICATION", "Chai de vinification");
            case 'Entreposage':
                $chais->attributs->add("STOCKAGE_VRAC", "Stockage Vin en Vrac");
                break;
            default:
                // code...
                break;
        }
        $etablissement->save();
    }

}
