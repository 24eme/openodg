<?php

class importChaisIACsvTask extends importOperateurIAAOCCsvTask
{
    const CSV_RAISON_SOCIALE_OPERATEUR = 0;
    const CSV_NOM_SITE = 1;
    const CSV_ADRESSE_SITE = 2;
    const CSV_CAPACITE = 3;
    const CSV_CODE_POSTAL_SITE = 4;
    const CSV_COMMUNE_SITE = 5;
    const CSV_CODE_POSTAL_OPERATEUR = 6;
    const CSV_COMMUNE_OPERATEUR = 7;

    protected $etablissements = null;
    protected $etablissementsCache = array();

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('secteurs', sfCommandArgument::REQUIRED, "Fichier csv des secteurs"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'chais-ia';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $secteurs = [];
        foreach(file($arguments['secteurs']) as $line) {
            $data = str_getcsv($line, ";");
            $secteurs[KeyInflector::slugify(preg_replace("/[ \-_',]*/", "", $data[2].$data[5].$data[6]))] = trim(str_replace(' ', '_', strtoupper($data[0])));
        }

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE_OPERATEUR], null, $data[self::CSV_CODE_POSTAL_OPERATEUR]);


            if(!$etablissement) {
                echo "Établissement non trouvé : ".$data[self::CSV_RAISON_SOCIALE_OPERATEUR]."\n";
                continue;
            }

            $etablissement = EtablissementClient::getInstance()->find($etablissement->_id);

            $data[self::CSV_NOM_SITE] = trim($data[self::CSV_NOM_SITE]);
            $data[self::CSV_ADRESSE_SITE] = trim($data[self::CSV_ADRESSE_SITE]);
            $data[self::CSV_CODE_POSTAL_SITE] = trim($data[self::CSV_CODE_POSTAL_SITE]);
            $data[self::CSV_COMMUNE_SITE] = trim($data[self::CSV_COMMUNE_SITE]);

            if(!$data[self::CSV_NOM_SITE] && !$data[self::CSV_ADRESSE_SITE] && !$data[self::CSV_CODE_POSTAL_SITE] && !$data[self::CSV_COMMUNE_SITE]) {
                continue;
            }

            $found = false;
            if($etablissement->exist('chais')) {
                foreach($etablissement->chais as $chai) {
                    if($chai->nom == $data[self::CSV_NOM_SITE] && $chai->adresse == $data[self::CSV_ADRESSE_SITE] && $chai->commune == $data[self::CSV_COMMUNE_SITE] && $chai->code_postal == $data[self::CSV_CODE_POSTAL_SITE]) {
                        $found = true;
                        break;
                    }
                }
            }

            if($found) {
                continue;
            }

            $chai = $etablissement->add('chais')->add();
            $chai->nom = $data[self::CSV_NOM_SITE];
            $chai->adresse = $data[self::CSV_ADRESSE_SITE];
            $chai->commune = $data[self::CSV_COMMUNE_SITE];
            $chai->code_postal = $data[self::CSV_CODE_POSTAL_SITE];
            $keyAdresse = KeyInflector::slugify(preg_replace("/[ \-_',]*/", "", $chai->adresse.$chai->code_postal.$chai->commune));
            if(isset($secteurs[$keyAdresse])) {
                $chai->secteur = $secteurs[$keyAdresse];
            }

            $habilitation = HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant);
            if(!$chai->secteur && $habilitation && $habilitation->isHabiliteFor("/declaration/certifications/AOC/genres/TRANQ/appellations/MTS", HabilitationClient::ACTIVITE_VINIFICATEUR)) {
                $chai->secteur = "MENETOUSALON";
            }

            $habilitation = HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant);
            if(!$chai->secteur && $habilitation && $habilitation->isHabiliteFor("/declaration/certifications/AOC/genres/TRANQ/appellations/CHM", HabilitationClient::ACTIVITE_VINIFICATEUR)) {
                $chai->secteur = "CHATEAUMEILLANT";
            }

            $etablissement->save();
            echo "chai ".$chai->nom." ajouté à l'établissement ".$etablissement->raison_sociale." (".$etablissement->_id.")\n";
        }
    }
}
