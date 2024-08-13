<?php

class importEtablissementsAssvasTask extends sfBaseTask
{
    const CSV_IDENTIFIANT           = 0;
    const CSV_NOUVEL_IDENTIFIANT    = 1;
    const CSV_SIRET                 = 3;
    const CSV_CVI                   = 4;
    const CSV_OBSERVATION           = 5;
    const CSV_INTITULE              = 6;
    const CSV_RAISON_SOCIALE        = 7;
    const CSV_NOM_INTERLOCUTEUR     = 8;
    const CSV_ADRESSE               = 9;
    const CSV_CODE_POSTAL           = 10;
    const CSV_COMMUNE               = 11;
    const CSV_TEL                   = 13;
    const CSV_PORTABLE_1            = 14;
    const CSV_PORTABLE_2            = 15;
    const CSV_EMAIL_1               = 16;
    const CSV_EMAIL_2               = 17;
    const CSV_HABILITATION_ANJOU_SAUMUR = 20;
    const CSV_HABILITATION_IGP = 24;
    const CSV_HABILITATION_HAUT_POITOU = 28;
    const CSV_HABILITATION_FIEFS_VENDEENS = 32;
    public static $tags = [self::CSV_HABILITATION_ANJOU_SAUMUR => 'Habilitation Anjou Saumur',
             self::CSV_HABILITATION_IGP => 'Habilitation IGP',
             self::CSV_HABILITATION_HAUT_POITOU => 'Habilitation Haut-Poitou', self::CSV_HABILITATION_FIEFS_VENDEENS => 'Habilitation Fiefs Vendeens'];

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'etablissements-assvas';
        $this->briefDescription = 'Import des etablissements';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $csv = fopen($arguments['file'], 'r');
        while(($line = fgetcsv($csv, 0, ';')) !== false) {
            if(!$line[self::CSV_IDENTIFIANT]) {
                continue;
            }
            if(!preg_match('/^[0-9]+$/', $line[self::CSV_IDENTIFIANT]) && preg_match('/^[0-9]+$/', $line[self::CSV_NOUVEL_IDENTIFIANT])) {
                $line[self::CSV_IDENTIFIANT] = $line[self::CSV_NOUVEL_IDENTIFIANT];
            }
            if(!preg_match('/^[0-9]+$/', $line[self::CSV_IDENTIFIANT])) {
                echo "Erreur identifiant non valide : ".$line[self::CSV_IDENTIFIANT].PHP_EOL;
                continue;
            }
            $line[self::CSV_SIRET] = trim(str_replace(" ", "", $line[self::CSV_SIRET]));
            $line[self::CSV_CVI] = trim(str_replace(" ", "", $line[self::CSV_CVI]));

            $statut = SocieteClient::STATUT_SUSPENDU;
            foreach(self::$tags as $tagKey => $tagValue) {
                if(strtoupper($line[$tagKey]) == "OUI") {
                    $statut = SocieteClient::STATUT_ACTIF;
                }
                if(strtoupper($line[$tagKey]) == "EN COURS") {
                    $statut = SocieteClient::STATUT_ACTIF;
                }
            }

            $initialRevisionSociete = null;
            $initialRevisionEtablissement = null;
            $initialRevisionCompteEtablissement = null;

            $societe = new Societe();
            $societe->identifiant = sprintf(sfConfig::get('app_societe_format_identifiant'), $line[self::CSV_IDENTIFIANT]);
            $societe->type_societe = SocieteClient::TYPE_OPERATEUR;
            $societe->constructId();

            if(SocieteClient::getInstance()->find($societe->_id, acCouchdbClient::HYDRATE_JSON)) {
                $societe = SocieteClient::getInstance()->find($societe->_id);
                $initialRevisionSociete = $societe->_rev;
            }

            if(!$line[self::CSV_INTITULE]) {
                $line[self::CSV_INTITULE] = null;
            }
            $societe->raison_sociale = trim(implode(" ", [$line[self::CSV_INTITULE], $line[self::CSV_RAISON_SOCIALE]]));
            $societe->siret = $line[self::CSV_SIRET];

            $societe->interpro = 'INTERPRO-declaration';
            $societe->statut = $statut;

            $societe->adresse = trim(preg_replace('/(^-|-$)/', '', trim($line[self::CSV_ADRESSE])));
            $societe->code_postal = $line[self::CSV_CODE_POSTAL];
            $societe->commune = trim($line[self::CSV_COMMUNE]);
            $societe->setPays('FR');

            $societe->telephone_bureau = Phone::clean($line[self::CSV_TEL]);
            $societe->telephone_mobile = Phone::clean($line[self::CSV_PORTABLE_1]);
            $societe->telephone_perso = Phone::clean($line[self::CSV_PORTABLE_2]);

            foreach (['telephone_bureau', 'telephone_mobile', 'telephone_perso'] as $tel) {
                if (strlen($societe->$tel) === 9) {
                    $societe->$tel = "0".$societe->$tel;
                }
            }

            $societe->email = (trim($line[self::CSV_EMAIL_1])) ?: null;
            $societe->save();

            $compte = $societe->getMasterCompte();
            $compte->statut = $societe->statut;
            $compte->save();

            if(EtablissementClient::getInstance()->find("ETABLISSEMENT-".$societe->identifiant.'01', acCouchdbClient::HYDRATE_JSON)) {
                $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$societe->identifiant.'01');
                $initialRevisionEtablissement = $etablissement->_rev;
            } else {
                $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR);
                $etablissement->save();
            }

            $etablissement->statut = $societe->getStatut();
            $etablissement->nom = $societe->getRaisonSociale();
            $societe->pushAdresseTo($etablissement);
            $societe->pushContactTo($etablissement);
            $etablissement->cvi = $line[self::CSV_CVI];
            $etablissement->siret = $line[self::CSV_SIRET];
            $etablissement->commentaire = $line[self::CSV_OBSERVATION];
            $etablissement->save();

            $compte = $etablissement->getMasterCompte();
            $initialRevisionCompteEtablissement = $compte->_rev;
            $actif = false;
            $compte->removeTags('manuel', self::$tags);
            foreach(self::$tags as $tagKey => $tagValue) {
                if($line[$tagKey] == "OUI") {
                    $compte->addTag('manuel', $tagValue);
                    $actif = true;
                }
            }
            $compte->statut = $etablissement->statut;
            $compte->save();

            if(!$actif) {

            }

            $societe = SocieteClient::getInstance()->find($societe->_id);
            $etablissement = EtablissementClient::getInstance()->find($etablissement->_id);

            if(!preg_match("/^[0-9]{10}$/", $etablissement->cvi)) {
                echo "Warning cvi non valide : $etablissement->cvi ($etablissement->_id)".PHP_EOL;
            }

            if($etablissement->siret && !preg_match("/^[0-9]{9,14}$/", $etablissement->siret)) {
                echo "Warning siret non valide : ".$etablissement->_get('siret')." ($etablissement->_id)".PHP_EOL;
            }

            if ($societe->email && filter_var($societe->email, FILTER_VALIDATE_EMAIL) === false) {
                echo "Warning email non valide : $societe->email ($societe->_id)".PHP_EOL;
            }

            if($societe->_rev != $initialRevisionSociete) {
                echo "Success société enregistrée : $societe->_id ($initialRevisionSociete => $societe->_rev)".PHP_EOL;
            }

            if($etablissement->_rev != $initialRevisionEtablissement) {
                echo "Success etablissement enregistrée : $etablissement->_id ($initialRevisionEtablissement => $etablissement->_rev)".PHP_EOL;
            }

            if($initialRevisionCompteEtablissement != $compte->_rev) {
                echo "Success compte enregistrée : $compte->_id ($initialRevisionCompteEtablissement => $compte->_rev)".PHP_EOL;
            }


        }
    }

}
