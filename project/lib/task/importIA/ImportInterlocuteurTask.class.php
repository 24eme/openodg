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

            $civilite = null;
            if(isset($data[self::CSV_CIVILITE]) && $data[self::CSV_CIVILITE] == "Madame") {
                $civilite = "Mme";
            }
            if(isset($data[self::CSV_CIVILITE]) && $data[self::CSV_CIVILITE] == "Mademoiselle") {
                $civilite = "Mme";
            }
            if(isset($data[self::CSV_CIVILITE]) && $data[self::CSV_CIVILITE] == "Monsieur") {
                $civilite = "M";
            }

            $raisonSociale = $data[self::CSV_RAISON_SOCIALE];
            if(!$raisonSociale) {
                $raisonSociale = trim($civilite." ".trim($data[self::CSV_NOM])." ".trim($data[self::CSV_PRENOM]));
            }

            $resultat = SocieteClient::matchSociete($societes, $raisonSociale, 1);
            if($resultat && count($resultat) >= 1 && $raisonSociale) {
                $societe = SocieteClient::getInstance()->find(key($resultat));
            }
            if(!$societe) {
                $societe = SocieteClient::getInstance()->createSociete($raisonSociale, SocieteClient::TYPE_OPERATEUR);
                if (isset($data[self::CSV_ADRESSE_1])){
                  $societe->siege->adresse = $data[self::CSV_ADRESSE_1];
                }
                if (isset($data[self::CSV_ADRESSE_2])){
                  $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2];
                }
                if (isset($data[self::CSV_CODE_POSTAL])){
                  $societe->siege->code_postal = $data[self::CSV_CODE_POSTAL];
                }
                if (isset($data[self::CSV_VILLE])){
                  $societe->siege->commune = $data[self::CSV_VILLE];
                }
                if (isset($data[self::CSV_TELEPHONE])){
                  $societe->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
                }
                if (isset($data[self::CSV_PORTABLE])){
                  $societe->telephone_mobile = Phone::format($data[self::CSV_PORTABLE]);
                }
                if(isset($data[self::CSV_FAX])){
                  $societe->fax = Phone::format($data[self::CSV_FAX]);
                }
                if (isset($data[self::CSV_EMAIL])){
                  $societe->email = $data[self::CSV_EMAIL];
                }
                $societe->save();
            }

            $compte = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
            $compte->civilite = $civilite;

            if (isset($data[self::CSV_NOM])){
              $compte->nom = trim($data[self::CSV_NOM]);
            }
            if (isset($data[self::CSV_PRENOM])){
              $compte->prenom = trim($data[self::CSV_PRENOM]);
            }
            if (isset($data[self::CSV_ADRESSE_1])){
              $compte->adresse = trim($data[self::CSV_ADRESSE_1]);
            }
            if (isset($data[self::CSV_ADRESSE_2])){
              $compte->adresse_complementaire = trim($data[self::CSV_ADRESSE_2]);
            }
            if (isset($data[self::CSV_CODE_POSTAL])){
              $compte->code_postal = trim($data[self::CSV_CODE_POSTAL]);
            }
            if (isset($data[self::CSV_VILLE])){
              $compte->commune = trim($data[self::CSV_VILLE]);
            }
            if (isset($data[self::CSV_TELEPHONE])){
              $compte->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
            }
            if (isset($data[self::CSV_PORTABLE])){
              $compte->telephone_mobile = Phone::format($data[self::CSV_PORTABLE]);
            }
            if (isset($data[self::CSV_FAX])){
              $compte->fax = Phone::format($data[self::CSV_FAX]);
            }
            if (isset($data[self::CSV_EMAIL])){
              $compte->email = $data[self::CSV_EMAIL];
            }
            if (isset($data[self::CSV_COLLEGE])){
              if(preg_match('/Porteur de mémoire/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:porteur_de_memoire');
              }
              if(preg_match('/Observateur/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:porteur_de_memoire');
              }
              if(preg_match('/Technicien/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:technicien');
              }
              if(preg_match('/Usager du produit/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:usager_du_produit');
              }
            }
            if ($data[self::CSV_FORMATION] == "Oui") {
                $compte->tags->add("manuel")->add("degustateur_formation");
            }
            if ($data[self::CSV_COMPETENCES]) {
                $competence = trim($data[self::CSV_COMPETENCES]);
                $competence = "degustateur_competence_".preg_replace('/[\(\) ]/', '_', $competence);
                $compte->tags->add("manuel")->add(null, $competence);
            }
            $compte->save();
        }
    }
}
