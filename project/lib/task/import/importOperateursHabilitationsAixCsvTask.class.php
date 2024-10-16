<?php

class importOperateursHabilitationsAixCsvTask extends sfBaseTask
{

    const CSV_AIX_OPERATEUR_NUMERO_ENREGISTREMENT = 0;
    const CSV_AIX_OPERATEUR_RAISON_SOCIALE = 1;
    const CSV_AIX_OPERATEUR_DI_DATE = 2;
    const CSV_AIX_OPERATEUR_SIRET = 3;
    const CSV_AIX_OPERATEUR_CVI = 4;
    const CSV_AIX_OPERATEUR_ADRESSE1 = 5;
    const CSV_AIX_OPERATEUR_ADRESSE2 = 6;
    const CSV_AIX_OPERATEUR_CODE_POSTAL = 7;
    const CSV_AIX_OPERATEUR_VILLE = 8;
    const CSV_AIX_OPERATEUR_TELEPHONE = 9;
    const CSV_AIX_OPERATEUR_FAX = 10;
    const CSV_AIX_OPERATEUR_MAIL = 11;
    const CSV_AIX_OPERATEUR_NOM_RESPONSABLE = 12;
    const CSV_AIX_OPERATEUR_FONCTION = 13;
    const CSV_AIX_OPERATEUR_ACTIVITE_PRODUCTEUR_DE_RAISINS = 14;
    const CSV_AIX_OPERATEUR_ACTIVITE_TRANSFORMATEUR_VINIFICATEUR = 15;
    const CSV_AIX_OPERATEUR_ACTIVITE_CONDITIONNEUR = 16;
    const CSV_AIX_OPERATEUR_ACTIVITE_PRESTATAIRE_DE_SERVICE = 17;
    const CSV_AIX_OPERATEUR_ACTIVITE_VENTE_DE_VRAC = 18;
    const CSV_AIX_OPERATEUR_ACTIVITE_NEGOCIANT = 19;
    const CSV_AIX_OPERATEUR_ACTIVITE_AUTRES = 20;
    const CSV_AIX_OPERATEUR_HABILITATION_OUI = 21;
    const CSV_AIX_OPERATEUR_HABILITATION_NON = 22;

    const hash_produit = 'certifications/AOP/genres/TRANQ/appellations/CAP';

    const activites = [
        self::CSV_AIX_OPERATEUR_ACTIVITE_PRODUCTEUR_DE_RAISINS => HabilitationClient::ACTIVITE_PRODUCTEUR,
        self::CSV_AIX_OPERATEUR_ACTIVITE_TRANSFORMATEUR_VINIFICATEUR => HabilitationClient::ACTIVITE_VINIFICATEUR,
        self::CSV_AIX_OPERATEUR_ACTIVITE_CONDITIONNEUR => HabilitationClient::ACTIVITE_CONDITIONNEUR,
        self::CSV_AIX_OPERATEUR_ACTIVITE_PRESTATAIRE_DE_SERVICE => HabilitationClient::ACTIVITE_PRESTATAIRE_DE_SERVICE,
        self::CSV_AIX_OPERATEUR_ACTIVITE_VENTE_DE_VRAC => HabilitationClient::ACTIVITE_VRAC,
        self::CSV_AIX_OPERATEUR_ACTIVITE_NEGOCIANT => HabilitationClient::ACTIVITE_NEGOCIANT,
    ];

    const status = [
        'habilité' => HabilitationClient::STATUT_HABILITE,
        'en cours' => HabilitationClient::STATUT_DEMANDE_HABILITATION
    ];

    private $numintern2etablissement = array();

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
            new sfCommandOption('dryrun', null, sfCommandOption::PARAMETER_REQUIRED, "Dont save", false),
        ));

        $this->namespace = 'import';
        $this->name = 'operateur-habilitation-aix';
        $this->briefDescription = 'Import des opérateurs et habilitations de ventoux (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {

        if ($options['dryrun']) {
            $_ENV['DRY_RUN'] = true;
        }
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $csvfile = fopen($arguments['csv'], 'r');

        if (! $csvfile) {
            throw new sfException("Impossible d'ouvrir le fichier " . $arguments['csv']);
        }

        while(($data = fgetcsv($csvfile, 1000, ";")) !== false) {
            if (is_numeric($data[self::CSV_AIX_OPERATEUR_NUMERO_ENREGISTREMENT]) === false) {
                continue;
            }
            $suspendu = !($data[self::CSV_AIX_OPERATEUR_CVI]) && !($data[self::CSV_AIX_OPERATEUR_SIRET]);
            $etablissement = $this->importSocieteEtablissement($data, $suspendu);
            if ($etablissement === false) {
                continue;
            }
            $this->importHabilitation($etablissement, $data, $suspendu);
        }
    }

    private function importSocieteEtablissement($data, $suspendu = false)
    {
        $e = null;

        if ($data[self::CSV_AIX_OPERATEUR_CVI]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_AIX_OPERATEUR_CVI]));
        }
        if (!$e && $data[self::CSV_AIX_OPERATEUR_SIRET]) {
            $e = EtablissementClient::getInstance()->findByCVI(str_replace(' ', '', $data[self::CSV_AIX_OPERATEUR_SIRET]));
        }
        if ($e) {
            echo("Etablissement existe " . $e->_id . ", ". $data[0]." ".$data[1]."\n");
            $this->numintern2etablissement[$data[self::CSV_AIX_OPERATEUR_NUMERO_ENREGISTREMENT]] = $e;
            return;
        }

        if ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_PRODUCTEUR_DE_RAISINS] == "Oui" || $data[self::CSV_AIX_OPERATEUR_ACTIVITE_TRANSFORMATEUR_VINIFICATEUR] == "Oui") {
            if (!$data[self::CSV_AIX_OPERATEUR_CVI]) {
                echo("WARNING: CVI non dispo pour ".$data[0]." ".$data[1]."\n");
            }
        }

        $q = $data[self::CSV_AIX_OPERATEUR_RAISON_SOCIALE];
        $societes = SocieteAllView::getInstance()->findByInterproAndStatut("INTERPRO-declaration", SocieteClient::STATUT_ACTIF, $q, 10);
        $json = SocieteClient::matchSociete($societes, $q, 10);
        if ($json) {
            $soc = array_key_first($json);
            $s = SocieteClient::getInstance()->find($soc);
            if (substr($s->siret, 0, 9) == substr(str_replace(' ', '', $data[self::CSV_AIX_OPERATEUR_SIRET]), 0, 9)) {
                $e = $s->getEtablissementPrincipal();
                if ($e) {
                    $this->numintern2etablissement[$data[self::CSV_AIX_OPERATEUR_NUMERO_ENREGISTREMENT]] = $e;
                }
                return;
            }
        }

        $data = array_map('trim', $data);

        $societe = SocieteClient::getInstance()->createSociete($data[self::CSV_AIX_OPERATEUR_RAISON_SOCIALE], SocieteClient::TYPE_OPERATEUR);
        $societe->statut = SocieteClient::STATUT_ACTIF;
        $societe->siege->adresse = $data[self::CSV_AIX_OPERATEUR_ADRESSE1] ?? null;
        $societe->siege->adresse_complementaire = $data[self::CSV_AIX_OPERATEUR_ADRESSE2] ?? null;
        $societe->siege->code_postal = $data[self::CSV_AIX_OPERATEUR_CODE_POSTAL] ?? null;
        $societe->siege->commune = $data[self::CSV_AIX_OPERATEUR_VILLE] ?? null;
        $societe->siret = str_replace(" ", "", $data[self::CSV_AIX_OPERATEUR_SIRET] ?? null);

        $emails = explode('|', $data[self::CSV_AIX_OPERATEUR_MAIL]);
        $telephones = explode('|', $data[self::CSV_AIX_OPERATEUR_TELEPHONE]);

        foreach($telephones as $p) {
            if (!$societe->telephone_bureau && preg_match('/^0[1-5]/', $p)) {
                $societe->telephone_bureau = Phone::format($p);
            }elseif (!$societe->telephone_mobile && preg_match('/^0[76]/', $p)) {
                $societe->telephone_mobile = Phone::format($p);
            }
        }
        foreach(explode('|', $data[self::CSV_AIX_OPERATEUR_FAX]) as $p) {
            if (!$societe->fax && preg_match('/^0[1-5]/', $p)) {
                $societe->telephone_bureau = Phone::format($p);
            }
        }

        if (!$societe->email && $emails) {
            $societe->email = KeyInflector::unaccent($emails[0]);
        }

        $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
        if ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_NEGOCIANT] == 'Oui') {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
            if ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_TRANSFORMATEUR_VINIFICATEUR] == 'Oui') {
                $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
            }
        }elseif ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_TRANSFORMATEUR_VINIFICATEUR] == 'Oui') {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
        }elseif ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_PRODUCTEUR_DE_RAISINS] == 'Oui') {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
        }elseif ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_VENTE_DE_VRAC] == 'Oui') {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
        }elseif ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_CONDITIONNEUR] == 'Oui') {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
        }elseif ($data[self::CSV_AIX_OPERATEUR_ACTIVITE_PRESTATAIRE_DE_SERVICE] == 'Oui') {
            $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
        }

        if (strpos($data[self::CSV_AIX_OPERATEUR_RAISON_SOCIALE], 'oop') !== false || strpos($data[self::CSV_AIX_OPERATEUR_MAIL], 'oop') !== false || strpos($data[self::CSV_AIX_OPERATEUR_ADRESSE1], 'oop') !== false) {
            $famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
        }

        $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, $famille);
        $etablissement->nom = $data[self::CSV_AIX_OPERATEUR_RAISON_SOCIALE];
        $etablissement->num_interne = $data[self::CSV_AIX_OPERATEUR_NUMERO_ENREGISTREMENT];

        $cvi = null;
        if (isset($data[self::CSV_AIX_OPERATEUR_CVI])){
            $cvi = EtablissementClient::repairCVI($data[self::CSV_AIX_OPERATEUR_CVI]);
        }
        $etablissement->cvi = $cvi;
        $societe->pushAdresseTo($etablissement);
        $societe->pushContactTo($etablissement);

        $interlocuteurs = [];
        $i = 0;
        $responsables = explode('|', $data[self::CSV_AIX_OPERATEUR_NOM_RESPONSABLE]);
        $fonctions = explode('|', $data[self::CSV_AIX_OPERATEUR_FONCTION]);
        $emails = explode('|', $data[self::CSV_AIX_OPERATEUR_MAIL]);
        $telephones = explode('|', $data[self::CSV_AIX_OPERATEUR_TELEPHONE]);
        foreach($responsables as $r) {
            if (!$r) {
                continue;
            }
            $inter = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
            $inter->nom_a_afficher = $r;
            if (preg_match('/^(M[mer]*) ([A-Z].*) ([A-Z]+)$/', $r, $m)) {
                $inter->nom = $m[3];
                $inter->prenom = $m[2];
                $inter->civilite = $m[1];
            }else{
                $inter->nom = $r;
            }
            if (count($fonctions) == count($responsables) && $fonctions[$i]) {
                $inter->fonction = $fonctions[$i];
            }
            if (count($emails) == count($responsables) && $emails[$i]) {
                $inter->email = KeyInflector::unaccent($emails[$i]);
            }
            if (count($telephones) == count($responsables) && $telephones[$i]) {
                $inter->telephone_perso = Phone::format($telephones[$i]);
            }
            $interlocuteurs[] = $inter;
            $i++;
        }
        echo($etablissement->num_interne."\n");
        //print_r([$societe, $etablissement, $interlocuteurs]);
        return $etablissement;
    }

    function importHabilitation($etablissement, $data, $suspendu = false) {
        if (!$etablissement) {
            print_r([$data]);
            throw new sfException("etablissement empty");
        }
        $dates = [];
        foreach(explode('|', $data[self::CSV_AIX_OPERATEUR_DI_DATE]) as $date) {
            $dates[] = DateTime::createFromFormat('d/m/Y', explode(" ", $date)[0])->format('Y-m-d');
        }
        sort($dates);

        $activites = [];
        foreach (self::activites as $csv_activite => $hab_activite) {
            if (strtoupper($data[$csv_activite]) === "OUI") {
                $activites[] = $hab_activite;
            }
        }

        $date_demande = array_pop($dates);
        $identifiant = $etablissement->identifiant;

        $commentaires = "Import";
        if (count($dates)) {
            $commentaires .= " (Autres dates : ".array_shift($dates);
            foreach($dates as $d) {
                $commentaires .= ", ".$d;
            }
            $commentaires .= ")";
        }
        if ($suspendu) {
            $h = HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_demande, $activites, [], HabilitationClient::STATUT_RETRAIT, $commentaires." : suspendu faute de SIRET/CVI");
        }elseif ($data[self::CSV_AIX_OPERATEUR_HABILITATION_NON] === 'X') {
            $h = HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_demande, $activites, [], HabilitationClient::STATUT_RETRAIT, $commentaires);
        }elseif ($data[self::CSV_AIX_OPERATEUR_HABILITATION_OUI] === 'X') {
            $h = HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_demande, $activites, [], HabilitationClient::STATUT_HABILITE, $commentaires);
        }else{
            $h = HabilitationClient::getInstance()->updateAndSaveHabilitation($identifiant, self::hash_produit, $date_demande, $activites, [], HabilitationClient::STATUT_DEMANDE_HABILITATION, $commentaires);
        }
    }
}
