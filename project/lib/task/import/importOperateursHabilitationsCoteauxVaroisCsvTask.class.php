<?php

class importOperateursHabilitationsCoteauxVaroisCsvTask extends sfBaseTask
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
    const CSV_DATE_RECEPTION_ODG = 16;
    const CSV_DATE_COMPLET_ODG = 17;
    const CSV_DATE_AR = 18;
    const CSV_DATE_TRANSMISSION_AVPI = 19;
    const CSV_DATE_HABILITATION = 20;
    const CSV_DATE_ARCHIVAGE = 21;
    const CSV_OBSERVATION = 22;
    const CSV_ETAT_HABILITATION = 23;

    const CSV_CHAIS_NUMERO_OPERATEUR = 1;
    const CSV_CHAIS_ACTIVITES = 3;
    const CSV_CHAIS_ADRESSE_1 = 4;
    const CSV_CHAIS_ADRESSE_2 = 5;
    const CSV_CHAIS_ADRESSE_3 = 6;
    const CSV_CHAIS_CODE_POSTAL = 7;
    const CSV_CHAIS_VILLE = 8;
    const CSV_CHAIS_CONTACT = 9;
    const CSV_CHAIS_CONTACT_TELEPHONE = 10;

    const hash_produit = '/declaration/certifications/AOP/genres/TRANQ/appellations/CVP';

    const activites = [
        'Producteur de raisins' => HabilitationClient::ACTIVITE_PRODUCTEUR,
        'Détenteur de vin en vrac' => HabilitationClient::ACTIVITE_VRAC,
        'Vinificateur' => HabilitationClient::ACTIVITE_VINIFICATEUR,
        'Producteur de moût' => HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS,
        'Conditionneur' => HabilitationClient::ACTIVITE_CONDITIONNEUR,
    ];

    const activitesChais = [
        "Vinification" => EtablissementClient::CHAI_ATTRIBUT_VINIFICATION,
        "VC Stockage" => EtablissementClient::CHAI_ATTRIBUT_STOCKAGE_VIN_CONDITIONNE,
        "VV Stockage" => EtablissementClient::CHAI_ATTRIBUT_STOCKAGE_VRAC,
    ];

    const status = [
        'habilité' => HabilitationClient::STATUT_HABILITE,
        'en cours' => HabilitationClient::STATUT_DEMANDE_HABILITATION
    ];

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('csv_chais', sfCommandArgument::REQUIRED, "Fichier csv pour l'import des chais"),
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
        // xlsx2csv -l '\r\n' -d ";" -n Chais $xlsxfilepath | tr -d "\n" | tr "\r" "\n" > $csvfilepath

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

        $csvfilechais = fopen($arguments['csv_chais'], 'r');

        if (! $csvfilechais) {
            throw new sfException("Impossible d'ouvrir le fichier " . $arguments['csv_chais']);
        }

        $chais = [];

        while(($data = fgetcsv($csvfilechais, 1000, ";")) !== false) {
            $data = array_map('trim', $data);
            $chais[$data[self::CSV_CHAIS_NUMERO_OPERATEUR]][] = $data;
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

            $this->importChais($etablissement, $data, $chais);

            if(!isset($_ENV['DRY_RUN'])) {
                $etablissement->save();
            } else {
                //print_r($etablissement);
            }

            $numInterneEtablissement[$data[self::CSV_NUMERO_ENREGISTREMENT]] = $etablissement;

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
            if(!isset($_ENV['DRY_RUN'])) {
                $societe->save();
            } else {
                //print_r($societe);
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

    private function importChais($etablissement, $data, $chais) {
        if(isset($chais[$data[self::CSV_NUMERO_ENREGISTREMENT]])) {
            foreach($chais[$data[self::CSV_NUMERO_ENREGISTREMENT]] as $chaiData) {
                foreach($etablissement->add('chais') as $c) {
                    if(strpos(KeyInflector::slugify($c->adresse), KeyInflector::slugify($chaiData[self::CSV_CHAIS_ADRESSE_1])) !== false) {
                        continue 2;
                    }
                }
                $chai = $etablissement->add('chais')->add();
                $chai->nom = $chaiData[self::CSV_CHAIS_VILLE];
                $chai->adresse = implode(" - ", array_filter([$chaiData[self::CSV_CHAIS_ADRESSE_1], $chaiData[self::CSV_CHAIS_ADRESSE_2], $chaiData[self::CSV_CHAIS_ADRESSE_3]], 'strlen'));
                $chai->code_postal = $chaiData[self::CSV_CHAIS_CODE_POSTAL];
                $chai->commune = $chaiData[self::CSV_CHAIS_VILLE];
                $activitesData = explode(';', $chaiData[self::CSV_CHAIS_ACTIVITES]);
                $activitesData = array_map('trim', $activitesData);
                $activites = [];
                foreach ($activitesData as $activiteTerm) {
                    if(!array_key_exists($activiteTerm, self::activitesChais)) {
                        echo "Activite \"".$activiteTerm."\" non trouvé;".implode(";", $data)."\n";
                        continue;
                    }
                    $activites[] = self::activitesChais[$activiteTerm];
                }
                $chai->add('attributs', $activites);
            }
        }

        $chaisImported = [];
        if(isset($chais[$data[self::CSV_NUMERO_ENREGISTREMENT]])) {
            foreach($chais[$data[self::CSV_NUMERO_ENREGISTREMENT]] as $chaiData) {
                if(!$chaiData[self::CSV_CHAIS_CONTACT]) {
                    continue;
                }
                if(isset($chaisImported[$etablissement->_id][$chaiData[self::CSV_CHAIS_CONTACT]])) {
                    continue;
                }
                $inter = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($etablissement->getSociete());
                $inter->nom_a_afficher = $chaiData[self::CSV_CHAIS_CONTACT];
                if (preg_match('/^(M. |Mme |MR |)([a-zA-Zéèêîïâ].*) ([a-zA-ZéèêîïâA]+)$/', $chaiData[self::CSV_CHAIS_CONTACT], $m)) {
                    $inter->nom = $m[3];
                    $inter->prenom = $m[2];
                    $inter->civilite = str_replace([".", " ", "MR"], ["", "", "M"], $m[1]);
                }else{
                    $inter->nom = $chaiData[self::CSV_CHAIS_CONTACT];
                }
                $inter->telephone_perso = Phone::format($chaiData[self::CSV_CHAIS_CONTACT_TELEPHONE]);
                $inter->fonction = "Responsable de chai";
                $inter->commentaire = "Import CVP";

                if(!isset($_ENV['DRY_RUN'])) {
                    $inter->save();
                } else {
                    print_r($inter);
                }

                $chaisImported[$etablissement->_id][$chaiData[self::CSV_CHAIS_CONTACT]] = true;
            }
        }

    }

    private function importHabilitation($etablissement, $data, $suspendu = false)
    {
        $data = array_map('trim', $data);
        $dateReceptionODG = ($data[self::CSV_DATE_RECEPTION_ODG]) ? DateTime::createFromFormat('m-d-y', $data[self::CSV_DATE_RECEPTION_ODG])->format('Y-m-d') : null;
        $dateCompletODG = ($data[self::CSV_DATE_COMPLET_ODG]) ? DateTime::createFromFormat('m-d-y', $data[self::CSV_DATE_COMPLET_ODG])->format('Y-m-d') : null;
        $dateAR = ($data[self::CSV_DATE_AR]) ? DateTime::createFromFormat('m-d-y', $data[self::CSV_DATE_AR])->format('Y-m-d') : null;
        $dateTransmissionAVPI = ($data[self::CSV_DATE_TRANSMISSION_AVPI]) ? DateTime::createFromFormat('m-d-y', $data[self::CSV_DATE_TRANSMISSION_AVPI])->format('Y-m-d') : null;
        $dateHabilitation = ($data[self::CSV_DATE_HABILITATION]) ? DateTime::createFromFormat('m-d-y', $data[self::CSV_DATE_HABILITATION])->format('Y-m-d') : null;
        $dateArchivage = ($data[self::CSV_DATE_ARCHIVAGE]) ? DateTime::createFromFormat('m-d-y', $data[self::CSV_DATE_ARCHIVAGE])->format('Y-m-d') : null;

        $activitesData = explode(';', $data[self::CSV_ACTIVITE]);
        $activitesData = array_map('trim', $activitesData);
        $activites = [];
        foreach ($activitesData as $activiteTerm) {
            if(!array_key_exists($activiteTerm, self::activites)) {
                echo "Activite \"".$activiteTerm."\" non trouvé;".implode(";", $data)."\n";
                continue;
            }
            $activites[] = self::activites[$activiteTerm];
        }

        $datesCommentaire = [];

        if($dateReceptionODG) {
            $datesCommentaire[$dateReceptionODG]["DEPOT"] = null;
        }
        if($dateCompletODG) {
            $datesCommentaire[$dateCompletODG]["COMPLET"] = null;
        }
        if($dateTransmissionAVPI) {
            $datesCommentaire[$dateTransmissionAVPI]["TRANSMIS_OC"] = null;
        }
        if($dateAR) {
            $datesCommentaire[$dateAR]["RECUS_OC"] = null;
        }
        if($dateHabilitation && !in_array($data[self::CSV_ETAT_HABILITATION], ["Refus d'habilitation", "Habilité en attente"])) {
            $datesCommentaire[$dateHabilitation]["VALIDE"] = null;
        }
        if($dateHabilitation && $data[self::CSV_ETAT_HABILITATION] == "Habilité en attente") {
            $datesCommentaire[$dateHabilitation]["VALIDE"] = "Habilité en attente";
        }
        if($dateHabilitation && $data[self::CSV_ETAT_HABILITATION] == "Refus d'habilitation") {
            $datesCommentaire[$dateHabilitation]["REFUSE"] = null;
        }

        $demande = null;
        foreach($datesCommentaire as $date => $statuts) {
            foreach($statuts as $statut => $commentaire) {
                if(!$demande) {
                    $demande = HabilitationClient::getInstance()->createDemandeAndSave($etablissement->identifiant, HabilitationClient::DEMANDE_HABILITATION, self::hash_produit, $activites, [], $statut, $date, $commentaire, "Import");
                } else {
                    $demande = HabilitationClient::getInstance()->updateDemandeAndSave($etablissement->identifiant, $demande->getKey(), $date, $statut, $commentaire, "Import");
                }
                if(isset($_ENV['DRY_RUN'])) {
                    $demande = null;
                }
            }
        }

        if($dateArchivage && $data[self::CSV_ETAT_HABILITATION] == "Suspension d'habilitation") {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($etablissement->identifiant, self::hash_produit, $dateArchivage, $activites, [], HabilitationClient::STATUT_SUSPENDU);
        }
        if($dateArchivage && $data[self::CSV_ETAT_HABILITATION] == "Retrait d'habilitation") {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($etablissement->identifiant, self::hash_produit, $dateArchivage, $activites, [], HabilitationClient::STATUT_RETRAIT);
        }
    }
}
