<?php

class importOperateursHabilitationsIGPTarnCsvTask extends sfBaseTask
{
    const CSV_NUMERO_OPERATEUR = 0;
    const CSV_NOM_OPERATEUR = 1;
    const CSV_ADRESSE_1 = 2;
    const CSV_ADRESSE_2 = 3;
    const CSV_COMMUNE = 4;
    const CSV_CP = 5;
    const CSV_DEPARTEMENT = 6;
    const CSV_TEL = 7;
    const CSV_NOCHEPTEL = 8;
    const CSV_NOCVI = 9;
    const CSV_SIRET = 10;
    const CSV_NOCDC = 11;
    const CSV_CDC = 12;
    const CSV_NODOSSIER = 13;
    const CSV_TYPE_OPERATEUR = 14;
    const CSV_DATE_PRESTATION = 15;
    const CSV_ETAT_PRESTATION = 16;
    const CSV_DATE_ENGAGEMENT = 17;
    const CSV_DATE_DEMARRAGE = 18;
    const CSV_GROUPEMENT = 19;
    const CSV_PORTEUR_DEMARCHE = 20;

    const hash_produit = 'certifications/IGP/genres/TRANQ/appellations/CDT';

    const status = [
        'H' => HabilitationClient::STATUT_HABILITE,
        'HSIRE' => HabilitationClient::STATUT_DEMANDE_HABILITATION
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
        $this->name = 'operateur-habilitation-igptarn';
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
            if (is_numeric($data[self::CSV_NUMERO_OPERATEUR]) === false) {
                continue;
            }

            $etablissement = $this->importSocieteEtablissement($data, (bool)$options['suspendu']);
            if ($etablissement === false) {
                continue;
            }

            $this->importHabilitation($etablissement, $data, (bool)$options['suspendu']);
        }
    }

    private function importSocieteEtablissement($data, $suspendu = false)
    {
        $e = null;
        if ($data[self::CSV_NOCVI]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_NOCVI]));
        }
        if (!$e && $data[self::CSV_SIRET]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_SIRET]));
        }
        if ($e) {
            echo("Etablissement existe " . $e->_id . ", ". $data[self::CSV_NOCVI]." ".$data[self::CSV_SIRET]."\n");
            return $e;
        }

        $societe = SocieteClient::getInstance()->findBySiret(str_replace(' ', '', $data[self::CSV_SIRET]));

        if (!$societe) {

            $raison_sociale = trim(implode(' ', array_map('trim', [$data[self::CSV_NOM_OPERATEUR]])));
            $newSociete = SocieteClient::getInstance()->createSociete($raison_sociale, SocieteClient::TYPE_OPERATEUR, $data[self::CSV_NUMERO_OPERATEUR]);

            $societe = SocieteClient::getInstance()->find($newSociete->_id);

            if($societe) {
                return false;
            }

            $data = array_map('trim', $data);

            $societe = $newSociete;
            $societe->statut = SocieteClient::STATUT_ACTIF;
            $societe->siege->adresse = $data[self::CSV_ADRESSE_1] ?? null;
            $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2] ?? null;
            $societe->siege->code_postal = $data[self::CSV_CP] ?? null;
            $societe->siege->commune = $data[self::CSV_COMMUNE] ?? null;
            $tel = Phone::format($data[self::CSV_TEL] ?? null);
            if ($tel && strpos($tel, '06') === 0) {
                $societe->telephone_mobile = $tel;
            }else{
                $societe->telephone_bureau = $tel;
            }
            $societe->siret = str_replace(" ", "", $data[self::CSV_SIRET] ?? null);

            try {
                $societe->save();
            } catch (Exception $e) {
                echo "$societe->_id save error :".$e->getMessage()."\n";
                return false;
            }

        }

        if (strpos($data[self::CSV_TYPE_OPERATEUR], 'Producteur et transformateur viticole') !== false) {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
        } elseif (strpos($data[self::CSV_TYPE_OPERATEUR], 'Producteur Viticole') !== false) {
                $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
        } elseif (strpos($data[self::CSV_TYPE_OPERATEUR], 'Transformateur viticole') !== false) {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
        } elseif (strpos($data[self::CSV_TYPE_OPERATEUR], 'Opérateur non transformateur') !== false) {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
        } else {
            echo "ERROR: Famille non reconnue : ".$data[self::CSV_TYPE_OPERATEUR]."\n";
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
        }

        $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, $famille);
        $etablissement->nom = $raison_sociale;
        if (strpos('hors zone', $data[self::CSV_TYPE_OPERATEUR]) !== false) {
            $etablissement->region = 'HORS_REGION_IGPTARN';
        } else {
            $etablissement->region = 'IGPTARN';
        }

        $cvi = null;
        if (isset($data[self::CSV_NOCVI])){
            $cvi = EtablissementClient::repairCVI($data[self::CSV_NOCVI]);
        }

        $etablissement->cvi = $cvi;
        $etablissement->commentaire = trim($data[self::CSV_NOCHEPTEL]) ? "Cheptel numéro : ".$data[self::CSV_NOCHEPTEL] : null;
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
        if (strpos($data[self::CSV_TYPE_OPERATEUR], 'H') !== false) {
            return;
        }

        if (!isset(self::status[trim(strtoupper($data[self::CSV_ETAT_PRESTATION]))])) {
            return;
        }
        $statut = self::status[trim(strtoupper($data[self::CSV_ETAT_PRESTATION]))];

        $identifiant = $etablissement->identifiant;
        $date_demande  = ($data[self::CSV_DATE_ENGAGEMENT]) ? DateTime::createFromFormat('d/m/Y', explode(" ", $data[self::CSV_DATE_ENGAGEMENT])[0])->format('Y-m-d') : null;
        $date_decision = ($data[self::CSV_DATE_DEMARRAGE]) ? DateTime::createFromFormat('d/m/Y', explode(" ", $data[self::CSV_DATE_DEMARRAGE])[0])->format('Y-m-d') : null;

        $activites = [];
        if (strpos($data[self::CSV_TYPE_OPERATEUR], ' de raisins') !== false) {
            $activites[] = HabilitationClient::ACTIVITE_PRODUCTEUR;
        }
        if (strpos($data[self::CSV_TYPE_OPERATEUR], 'inificat') !== false) {
            $activites[] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        }
        if (strpos($data[self::CSV_TYPE_OPERATEUR], 'vrac') !== false) {
            $activites[] = HabilitationClient::ACTIVITE_VRAC;
        }
        if (strpos($data[self::CSV_TYPE_OPERATEUR], 'onditionne') !== false) {
            $activites[] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        }

        if($date_demande && $date_demande < $date_decision) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_demande, $activites, [], HabilitationClient::STATUT_DEMANDE_HABILITATION, "Cheptel numéro : ".$data[self::CSV_NOCHEPTEL]);
        }

        HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_decision, $activites, [], $statut);

        if($suspendu) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, date('Y-m-d'), $activites, [], HabilitationClient::STATUT_RETRAIT);
        }
    }
}
