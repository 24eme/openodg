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
  const CSV_FONCTION = 17;

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
            new sfCommandOption('nocreatesociete', null, sfCommandOption::PARAMETER_REQUIRED, 'Ne créé pas de nouvelle société', false),
            new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, 'Région pour les tags dégustateurs', false),
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

        $region = $options['region'];

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
                $resultat = SocieteClient::matchSociete($societes, trim(CompteGenerique::extractIntitule(trim($raisonSociale))[1]), 1);
                if($resultat && count($resultat) >= 1 && $raisonSociale) {
                    $societe = SocieteClient::getInstance()->find(key($resultat));
                }
            }

            if(!$societe) {
                $resultat = SocieteClient::matchSociete($societes, trim(implode(" ", array_reverse(explode(" ", CompteGenerique::extractIntitule(trim($raisonSociale))[1])))), 1);
                if($resultat && count($resultat) >= 1 && $raisonSociale) {
                    $societe = SocieteClient::getInstance()->find(key($resultat));
                }
            }

            if(!$societe && $options['nocreatesociete']) {
                echo "Société pas trouvée : $raisonSociale\n";
                continue;
            }

            if(!$societe) {
                echo "Société créé : $raisonSociale\n";
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
                  $societe->email = KeyInflector::unaccent($data[self::CSV_EMAIL]);
                }
                $societe->save();
                $rowSociete = new stdClass();
                $rowSociete->id = $societe->_id;
                $rowSociete->key = [$societe->interpro, $societe->statut, $societe->type_societe, $societe->_id, $societe->raison_sociale, $societe->identifiant, $societe->siret, $societe->siege->commune, $societe->siege->code_postal];
                $rowSociete->value = null;
                $societes[] = $rowSociete;
            }

            $societe = SocieteClient::getInstance()->find($societe->_id);

            $compte = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);

            if (isset($data[self::CSV_NOM])){
              $compte->nom = trim($data[self::CSV_NOM]);
            }
            if (isset($data[self::CSV_PRENOM])){
              $compte->prenom = trim($data[self::CSV_PRENOM]);
            }

            foreach($societe->getComptesInterlocuteurs() as $c) {
                if(KeyInflector::slugify(strtolower(str_replace([" ", "-"], "", $c->prenom.$c->nom))) == KeyInflector::slugify(strtolower(str_replace([" ", "-"], "", $compte->prenom.$compte->nom)))) {
                    $compte = $c;
                    break;
                }
            }

            $compte->civilite = $civilite;

            if (isset($data[self::CSV_ADRESSE_1]) && trim($data[self::CSV_ADRESSE_1])) {
              $compte->adresse = trim($data[self::CSV_ADRESSE_1]);
            }
            if (isset($data[self::CSV_ADRESSE_2]) && trim($data[self::CSV_ADRESSE_2])){
              $compte->adresse_complementaire = trim($data[self::CSV_ADRESSE_2]);
            }
            if (isset($data[self::CSV_CODE_POSTAL]) && trim($data[self::CSV_CODE_POSTAL])){
              $compte->code_postal = trim($data[self::CSV_CODE_POSTAL]);
            }
            if (isset($data[self::CSV_VILLE]) && trim($data[self::CSV_VILLE])){
              $compte->commune = trim($data[self::CSV_VILLE]);
            }
            if (isset($data[self::CSV_TELEPHONE]) && trim($data[self::CSV_TELEPHONE])){
              $compte->telephone_bureau = Phone::format($data[self::CSV_TELEPHONE]);
            }
            if (isset($data[self::CSV_PORTABLE]) && trim($data[self::CSV_PORTABLE])){
              $compte->telephone_mobile = Phone::format($data[self::CSV_PORTABLE]);
            }
            if (isset($data[self::CSV_FAX]) && trim($data[self::CSV_FAX])){
              $compte->fax = Phone::format($data[self::CSV_FAX]);
            }
            if (isset($data[self::CSV_EMAIL]) && trim($data[self::CSV_EMAIL])){
              $compte->email = KeyInflector::unaccent($data[self::CSV_EMAIL]);
            }

            if (isset($data[self::CSV_COLLEGE]) && $data[self::CSV_COLLEGE]){
              if(preg_match('/Porteur de mémoire/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:porteur_de_memoire'.($region ? ":".$region:null));
              }
              if(preg_match('/Observateur/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:porteur_de_memoire'.($region ? ":".$region:null));
              }
              if(preg_match('/Technicien/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:technicien'.($region ? ":".$region:null));
              }
              if(preg_match('/Usager du produit/', $data[self::CSV_COLLEGE])) {
                  $compte->add('droits')->add(null, 'degustateur:usager_du_produit'.($region ? ":".$region:null));
              }

              $droits = $compte->add('droits')->toArray(true, false);
              $compte->remove('droits');
              $compte->add('droits');
              foreach(array_unique($droits) as $droit) {
                  $compte->add('droits')->add(null, $droit);
              }
            }
            if ($data[self::CSV_FORMATION] == "Oui") {
                $compte->tags->add("manuel")->add(null, "degustateur_formation".($region ? "_".$region:null));
            }
            if ($data[self::CSV_COMPETENCES]) {
                $competence = trim($data[self::CSV_COMPETENCES]);
                $competence = "degustateur_competence_".preg_replace('/[\(\) ]/', '_', $competence);
                $compte->tags->add("manuel")->add(null, $competence);
            }

            $manuels = $compte->tags->add('manuel')->toArray(true, false);
            $compte->tags->remove('manuel');
            $compte->tags->add('manuel');
            foreach(array_unique($manuels) as $manuel) {
                $compte->tags->add('manuel')->add(null, $manuel);
            }

            if(isset($data[self::CSV_FONCTION]) && $data[self::CSV_FONCTION]) {
                $compte->fonction = $data[self::CSV_FONCTION];
            }
            $compte->updateNomAAfficher();
            if($compte->isNew()) {
                //echo "Compte créé : ".$compte->nom_a_afficher." (".$societe->_id." ".$societe->raison_sociale.")\n";
            }

            $compte->save();
        }
    }
}
