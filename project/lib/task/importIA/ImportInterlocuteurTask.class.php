<?php

class importInterlocuteurIACsvTask extends sfBaseTask
{
  const CSV_CIVILITE = 0;
  const CSV_NOM = 1;
  const CSV_PRENOM = 2;
  const CSV_RAISON_SOCIALE = 3;
  const CSV_COLLEGE = 4;
  const CSV_COMPETENCES = 5;
  const CSV_FORMATION = 6;
  const CSV_ADRESSE_1 = 7;
  const CSV_ADRESSE_2 = 8;
  const CSV_CODE_POSTAL = 9;
  const CSV_VILLE = 10;
  const CSV_TELEPHONE = 11;
  const CSV_FAX = 12;
  const CSV_PORTABLE = 13;
  const CSV_EMAIL = 14;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $etablissements;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'interlocuteur-ia';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $societes = SocieteAllView::getInstance()->findByInterpro("INTERPRO-declaration");

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");
            $societe = null;
            $resultat = SocieteClient::matchSociete($societes, $data[self::CSV_RAISON_SOCIALE], 1);
            if($resultat && count($resultat) >= 1 && $data[self::CSV_RAISON_SOCIALE]) {
                $societe = SocieteClient::getInstance()->find(key($resultat));
            }
            if(!$societe) {
                $societe = SocieteClient::getInstance()->createSociete($data[self::CSV_RAISON_SOCIALE], SocieteClient::TYPE_OPERATEUR);
                $societe->siege->adresse = $data[self::CSV_ADRESSE_1];
                $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2];
                $societe->siege->code_postal = $data[self::CSV_CODE_POSTAL];
                $societe->siege->commune = $data[self::CSV_VILLE];
                $societe->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
                $societe->telephone_mobile = Phone::format($data[self::CSV_PORTABLE]);
                $societe->fax = Phone::format($data[self::CSV_FAX]);
                $societe->email = $data[self::CSV_EMAIL];
                $societe->save();
            }

            $compte = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
            if($data[self::CSV_CIVILITE] == "Madame") {
                $compte->civilite = "Mme";
            }
            if($data[self::CSV_CIVILITE] == "Mademoiselle") {
                $compte->civilite = "Mme";
            }
            if($data[self::CSV_CIVILITE] == "Monsieur") {
                $compte->civilite = "M";
            }
            $compte->nom = $data[self::CSV_NOM];
            $compte->prenom = $data[self::CSV_PRENOM];
            $compte->adresse = $data[self::CSV_ADRESSE_1];
            $compte->adresse_complementaire = $data[self::CSV_ADRESSE_2];
            $compte->code_postal = $data[self::CSV_CODE_POSTAL];
            $compte->commune = $data[self::CSV_VILLE];
            $compte->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
            $compte->telephone_mobile = Phone::format($data[self::CSV_PORTABLE]);
            $compte->fax = Phone::format($data[self::CSV_FAX]);
            $compte->email = $data[self::CSV_EMAIL];
            if(preg_match('/Porteur de mémoire/', $data[self::CSV_COLLEGE])) {
                $compte->add('droits')->add(null, 'degustateur:porteur_de_memoire');
            }
            if(preg_match('/Technicien/', $data[self::CSV_COLLEGE])) {
                $compte->add('droits')->add(null, 'degustateur:technicien');
            }
            if(preg_match('/Usager du produit/', $data[self::CSV_COLLEGE])) {
                $compte->add('droits')->add(null, 'degustateur:usager_du_produit');
            }
            $compte->save();

        }
    }
}
