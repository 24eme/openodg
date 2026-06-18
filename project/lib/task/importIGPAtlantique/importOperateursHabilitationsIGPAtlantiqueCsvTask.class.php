<?php

class importOperateursHabilitationsIGPAtlantiqueCsvTask extends sfBaseTask
{

    const CSV_DATE_CREATION = 0;
    const CSV_NUM_OPERATEUR = 1;
    const CSV_DATE_HABILITATION = 2;
    const CSV_NOM_OPERATEUR = 3;
    const CSV_CHAI = 4;
    const CSV_ADRESSE = 5;
    const CSV_CP = 6;
    const CSV_COMMUNE = 7;
    const CSV_EMAIL = 8;
    const CSV_TEL = 9;
    const CSV_MOBILE = 10;
    const CSV_RESPONSABLE_NOM = 11;
    const CSV_RESPONSABLE_PRENOM = 12;
    const CSV_NOCVI = 13;
    const CSV_SIRET = 14;
    const CSV_AUTRE = 15;
    const CSV_HABILITATION = 16;
    const CSV_OBSERVATION = 17;
    const CSV_EXTRA_TYPE_OPERATEUR = 18;

    const hash_produit = 'certifications/IGP/genres/TRANQ/appellations/ATL';

    private static $idPrefixe = [
        'PVC' => '2',
        'VC' => '3',
        'C' => '4',
        'P' => '',
    ];

    private static $familles = [
        'PVC' => EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR,
        'VC' => EtablissementFamilles::FAMILLE_COOPERATIVE,
        'C' => EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR,
        'P' => EtablissementFamilles::FAMILLE_PRODUCTEUR,
    ];

    private static $activites = [
        'PVC' => [HabilitationClient::ACTIVITE_PRODUCTEUR, HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_CONDITIONNEUR],
        'VC' => [HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_CONDITIONNEUR],
        'C' => [HabilitationClient::ACTIVITE_CONDITIONNEUR],
        'P' => [HabilitationClient::ACTIVITE_PRODUCTEUR],
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
        $this->name = 'operateur-habilitation-igpatlantique';
        $this->briefDescription = 'Import des opérateurs et habilitations igp atlantique (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);$this->configuration->loadMultiDatabases(null, $databaseManager);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $csvfile = fopen($arguments['csv'], 'r');

        if (! $csvfile) {
            throw new sfException("Impossible d'ouvrir le fichier " . $arguments['csv']);
        }

        $typeOperateur = basename($arguments['csv'], ".csv");

        while(($data = fgetcsv($csvfile, null, ";")) !== false) {
            $data[self::CSV_EXTRA_TYPE_OPERATEUR] = $typeOperateur;
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
            $e = EtablissementClient::getInstance()->findByCvi(str_replace(' ', '', $data[self::CSV_NOCVI]));
        }
        if (!$e && !$data[self::CSV_NOCVI] && $data[self::CSV_SIRET]) {
            $e = EtablissementClient::getInstance()->findByCvi(str_replace(' ', '', $data[self::CSV_SIRET]));
            if ($e && $e->cvi)  {
                $e = null;
            }
        }

        if($data[self::CSV_NOCVI] && strlen($data[self::CSV_NOCVI]) != 10) {
            echo "Warning CVI != 10 caractères pour CVI = " . $data[self::CSV_NOCVI]." ".(implode(";", $data))."\n";
        }
        if ($e) {
            echo("Etablissement existe " . $e->_id . ", ". $data[self::CSV_NOCVI]." ".$data[self::CSV_SIRET]."\n");
            return $e;
        }

        $societe = SocieteClient::getInstance()->findBySiret(str_replace(' ', '', $data[self::CSV_SIRET]));

        if (!$societe) {
            $raison_sociale = trim(implode(' ', array_map('trim', [$data[self::CSV_NOM_OPERATEUR]])));

            $id = intval($data[self::CSV_NUM_OPERATEUR])."";
            if(strlen($id) < 3) {
                $id = str_pad(intval($data[self::CSV_NUM_OPERATEUR]), 3, '0', STR_PAD_LEFT);
            }
            $id = self::$idPrefixe[$data[self::CSV_EXTRA_TYPE_OPERATEUR]].$id;

            $newSociete = SocieteClient::getInstance()->createSociete($raison_sociale, SocieteClient::TYPE_OPERATEUR, $id);

            $societe = SocieteClient::getInstance()->find($newSociete->_id);

            if($societe) {
                return false;
            }

            $data = array_map('trim', $data);

            $societe = $newSociete;
            $societe->statut = SocieteClient::STATUT_ACTIF;
            $societe->siege->adresse = $data[self::CSV_ADRESSE] ?? null;
            $societe->siege->code_postal = $data[self::CSV_CP] ?? null;
            $societe->siege->commune = $data[self::CSV_COMMUNE] ?? null;
            $societe->telephone_mobile = Phone::format($data[self::CSV_TEL] ?? null);
            $societe->telephone_bureau = Phone::format($data[self::CSV_MOBILE] ?? null);
            $societe->siret = str_replace(" ", "", $data[self::CSV_SIRET] ?? null);

            $cvi = EtablissementClient::repairCVI($data[self::CSV_NOCVI]);
            $societe->email = $data[self::CSV_EMAIL] ?? null;
            try {
                $societe->save();
            } catch (Exception $e) {
                echo "$societe->_id save error :".$e->getMessage()."\n";
                return false;
            }
        }
        $this->importContactAssocie($societe, $data);

        if(!isset(self::$familles[$data[self::CSV_EXTRA_TYPE_OPERATEUR]])) {
            echo "ERROR: Famille non reconnue : ".$data[self::CSV_EXTRA_TYPE_OPERATEUR]."\n";
            return false;
        }

        $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, self::$familles[$data[self::CSV_EXTRA_TYPE_OPERATEUR]]);
        $etablissement->nom = trim(implode(' ', array_map('trim', [$data[self::CSV_NOM_OPERATEUR]])));
        $etablissement->region = 'IGPATLANTIQUE';

        $cvi = null;
        if (isset($data[self::CSV_NOCVI])){
            $cvi = EtablissementClient::repairCVI($data[self::CSV_NOCVI]);
        }

        $etablissement->cvi = $cvi;
        $etablissement->num_interne = trim($data[self::CSV_NUM_OPERATEUR]) ?? null;
        $etablissement->commentaire = trim($data[self::CSV_OBSERVATION]) ?? null;
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

        $date = DateTime::createFromFormat('d/m/Y',$data[self::CSV_DATE_HABILITATION]);
        if (!$date) {
            $date = DateTime::createFromFormat('d/m/Y',$data[self::CSV_DATE_CREATION]);
        }
        if (!$date) {
            echo "ERROR: Aucune date pour habilitation : ".implode(';',$data)."\n";
            return false;
        }

        $activites = self::$activites[$data[self::CSV_EXTRA_TYPE_OPERATEUR]] ?? [];

        $habilitation = KeyInflector::unaccent($data[self::CSV_HABILITATION]);
        $hasRetrait = stripos($habilitation, 'retrait') !== false || stripos($habilitation, 'arret') !== false;
        if($suspendu||$hasRetrait) {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($etablissement->identifiant, self::hash_produit, date('Y-m-d'), $activites, [], HabilitationClient::STATUT_RETRAIT);
        } else {
            HabilitationClient::getInstance()->updateAndSaveHabilitation($etablissement->identifiant, self::hash_produit, $date->format('Y-m-d'), $activites, [], HabilitationClient::STATUT_HABILITE);
        }
    }

    private function importContactAssocie($societe, $data)
    {
        if (trim($data[self::CSV_RESPONSABLE_NOM])||trim($data[self::CSV_RESPONSABLE_PRENOM])) {
            $contact = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
            $contact->nom = trim($data[self::CSV_RESPONSABLE_NOM]);
            $contact->prenom = trim($data[self::CSV_RESPONSABLE_PRENOM]);
            $contact->fonction = 'Responsable';
            $contact->save();
        }
    }
}
