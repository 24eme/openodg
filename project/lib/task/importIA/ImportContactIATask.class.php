<?php

class importContactIACsvTask extends sfBaseTask
{
  const CSV_NUM_SOCIETE = 0;
  const CSV_RAISON_SOCIALE = 1;
  const CSV_ADRESSE_1 = 2;
  const CSV_ADRESSE_2 = 3;
  const CSV_CODE_POSTAL = 4;
  const CSV_VILLE = 5;
  const CSV_TELEPHONE = 6;
  const CSV_FAX = 7;
  const CSV_EMAIL = 8;
  const CSV_NOM = 9;
  const CSV_PRENOM = 10;
  const CSV_CIVILITE = 11;



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
        $this->name = 'contact-ia';
        $this->briefDescription = 'Import des contacts (via un csv)';
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
            $id_ia = intval(preg_replace("/^ENT/","",$data[self::CSV_NUM_SOCIETE]));
            if (!$id_ia) {
                echo "WARNING: societe non trouvée : ".$data[self::CSV_NUM_SOCIETE]."\n";
                continue;
            }
            $idSociete=sprintf(SocieteClient::getSocieteFormatIdentifiant(), $id_ia);
            $societe = SocieteClient::getInstance()->find('SOCIETE-'.$idSociete);
            if(!$societe) {
                $societe = SocieteClient::getInstance()->createSociete($data[self::CSV_RAISON_SOCIALE], SocieteClient::TYPE_OPERATEUR, $id_ia);
                if (isset($data[self::CSV_ADRESSE_1])){
                  $societe->siege->adresse = $data[self::CSV_ADRESSE_1];
                }
                if (isset($data[self::CSV_ADRESSE_2])){
                  $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2];
                }

                if( isset($data[self::CSV_CODE_POSTAL])){
                  $societe->siege->code_postal = $data[self::CSV_CODE_POSTAL];
                }

                if ( isset($data[self::CSV_VILLE])){
                  $societe->siege->commune = $data[self::CSV_VILLE];
                }

                if (isset($data[self::CSV_TELEPHONE])){
                  $societe->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
                }

                if (isset($data[self::CSV_FAX])){
                  $societe->fax = Phone::format($data[self::CSV_FAX]);
                }

                if (isset($data[self::CSV_EMAIL])){
                  $societe->email = $data[self::CSV_EMAIL];
                }

                $societe->save();
            }

            $compte = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);

            if (isset($data[self::CSV_ADRESSE_1])){
              $compte->adresse = $data[self::CSV_ADRESSE_1];
            }

            if (isset($data[self::CSV_ADRESSE_2])){
              $compte->adresse_complementaire = $data[self::CSV_ADRESSE_2];
            }
            if (isset($data[self::CSV_CODE_POSTAL])){
              $compte->code_postal = $data[self::CSV_CODE_POSTAL];
            }
            if (isset($data[self::CSV_VILLE])){
              $compte->commune = $data[self::CSV_VILLE];
            }
            if (isset($data[self::CSV_TELEPHONE])){
              $compte->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
            }
            if (isset($data[self::CSV_FAX])){
              $compte->fax = Phone::format($data[self::CSV_FAX]);
            }
            if (isset($data[self::CSV_EMAIL])){
              $compte->email = $data[self::CSV_EMAIL];
            }
            if (isset($data[self::CSV_NOM])){
              $compte->nom = $data[self::CSV_NOM];
            }
            if (isset($data[self::CSV_PRENOM])){
                $compte->prenom = $data[self::CSV_PRENOM];
            }
            if (isset($data[self::CSV_CIVILITE])){
              if($data[self::CSV_CIVILITE] == "Madame") {
                  $compte->civilite = "Mme";
              }
              if($data[self::CSV_CIVILITE] == "Mademoiselle") {
                  $compte->civilite = "Mme";
              }
              if($data[self::CSV_CIVILITE] == "Monsieur") {
                  $compte->civilite = "M";
              }
            }
            $compte->statut = $societe->statut;
            $compte->save();

        }
    }
}
