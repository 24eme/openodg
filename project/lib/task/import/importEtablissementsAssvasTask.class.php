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
    const CSV_ZONE_AGENT            = 18;

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

            $societe = new Societe();
            $societe->identifiant = sprintf(sfConfig::get('app_societe_format_identifiant'), $line[self::CSV_IDENTIFIANT]);
            $societe->constructId();

            $societe->type_societe = SocieteClient::TYPE_OPERATEUR;
            $societe->raison_sociale = implode(" ", [$line[self::CSV_INTITULE], $line[self::CSV_RAISON_SOCIALE]]);
            $societe->siret = $line[self::CSV_SIRET];

            $societe->interpro = 'INTERPRO-declaration';
            $societe->statut = SocieteClient::STATUT_ACTIF;

            $societe->adresse = $line[self::CSV_ADRESSE];
            $societe->code_postal = $line[self::CSV_CODE_POSTAL];
            $societe->commune = $line[self::CSV_COMMUNE];
            $societe->setPays('FR');

            $societe->telephone_bureau = $line[self::CSV_TEL];
            $societe->telephone_mobile = $line[self::CSV_PORTABLE_1];
            $societe->telephone_perso = $line[self::CSV_PORTABLE_2];

            $societe->save();

            $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR);
            $etablissement->save();
            $etablissement->cvi = $line[self::CSV_CVI];
            $etablissement->siret = $line[self::CSV_SIRET];
            $etablissement->commentaire = $line[self::CSV_OBSERVATION];

            $etablissement->save();
        }
    }

}
