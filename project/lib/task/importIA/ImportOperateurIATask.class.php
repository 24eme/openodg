<?php

class importOperateurIACsvTask extends sfBaseTask
{
  const CSV_IDENTIFIANT = 0;
  const CSV_RAISON_SOCIALE = 1;
  const CSV_NOM = 2;
  const CSV_ACTIVITE = 3;
  const CSV_ADRESSE_1 = 5;
  const CSV_ADRESSE_2 = 6;
  const CSV_CODE_POSTAL = 7;
  const CSV_VILLE = 8;
  const CSV_TELEPHONE = 9;
  const CSV_FAX = 10;
  const CSV_PORTABLE = 11;
  const CSV_EMAIL = 12;
  const CSV_CVI = 13;
  const CSV_SIRET = 14;
  const CSV_TVA_INTRA = 15;
  const CSV_CODE_COMPTABLE = 17;
  const CSV_NEGOCIANT = 19;
  const CSV_CAVE_COOPERATIVE = 20;
  const CSV_PRODUCTEUR = 21;
  const CSV_STATUT = 22;
  const CSV_ACHETEUR = 23;

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
        $this->name = 'operateur-ia';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");

            $newSociete = SocieteClient::getInstance()->createSociete($data[self::CSV_RAISON_SOCIALE], SocieteClient::TYPE_OPERATEUR, preg_replace("/^ENT/", "", $data[self::CSV_IDENTIFIANT]));

            $societe = SocieteClient::getInstance()->find($newSociete->_id);
            if($societe && isset($data[self::CSV_ACHETEUR]) && $data[self::CSV_ACHETEUR]) {
                $this->importLiaison(EtablissementClient::getInstance()->find("ETABLISSEMENT-".$societe->identifiant."01"), $data[self::CSV_ACHETEUR]);
            }

            if($societe) {
                continue;
            }

            $societe = $newSociete;

            if(isset($data[self::CSV_STATUT]) && $data[self::CSV_STATUT] == "SUSPENDU") {
                $societe->statut = SocieteClient::STATUT_SUSPENDU;
            }
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
            if (isset($data[self::CSV_FAX])){
              $societe->fax = Phone::format($data[self::CSV_FAX]);
            }
            if (isset($data[self::CSV_EMAIL])){
              $societe->email = $data[self::CSV_EMAIL];
            }
            if (isset($data[self::CSV_CODE_COMPTABLE])){
              $societe->code_comptable_client = $data[self::CSV_CODE_COMPTABLE];
            }
            if (isset($data[self::CSV_SIRET])){
              $societe->siret = str_replace(" ", "", $data[self::CSV_SIRET]);
            }
            if (isset($data[self::CSV_TVA_INTRA])){
              $societe->no_tva_intracommunautaire = $data[self::CSV_TVA_INTRA];
            }
            try {
                $societe->save();
            } catch (Exception $e) {
                echo "$societe->_id save error :".$e->getMessage()."\n";
                continue;
            }

            if(isset($data[self::CSV_CAVE_COOPERATIVE]) && $data[self::CSV_CAVE_COOPERATIVE] == "Oui") {
              $famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
            } elseif(preg_match("/Producteur de raisin/", $data[self::CSV_ACTIVITE]) && preg_match("/Vinificateur/", $data[self::CSV_ACTIVITE])) {
              $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
            } elseif(preg_match("/Producteur de raisin/", $data[self::CSV_ACTIVITE])) {
              $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
            } elseif(preg_match("/Négociant/", $data[self::CSV_ACTIVITE]) && preg_match("/Vinificateur/", $data[self::CSV_ACTIVITE])) {
              $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
            } elseif(preg_match("/Vinificateur/", $data[self::CSV_ACTIVITE])) {
              $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
            } else {
              $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
            }

            $etablissement = EtablissementClient::getInstance()->createEtablissementFromSociete($societe, $famille);
            if(isset($data[self::CSV_STATUT]) && $data[self::CSV_STATUT] == "SUSPENDU") {
                $etablissement->statut = EtablissementClient::STATUT_SUSPENDU;
            }
            if (isset($data[self::CSV_NOM]) || isset($data[self::CSV_RAISON_SOCIALE])){
                $nom = '';
                if ($data[self::CSV_RAISON_SOCIALE]) {
                    $nom = $data[self::CSV_RAISON_SOCIALE];
                    if (($data[self::CSV_RAISON_SOCIALE] != $data[self::CSV_NOM]) && $data[self::CSV_NOM]) {
                        $nom .= ' - '.$data[self::CSV_NOM];
                    }
                }elseif($data[self::CSV_NOM]) {
                    $nom = $data[self::CSV_NOM];
                }
              $etablissement->nom = $nom;
            }
            if (isset($data[self::CSV_CVI])){
              $cvi = preg_replace('/[^A-Z0-9]+/', "", $data[self::CSV_CVI]);
              if (strlen($cvi) < 10) {
                  for($i = 0 ; $i < ( 10 - strlen($cvi) ) ;  $i++) {
                    $cvi = $cvi."0";
                  }
              }
            }
            $etablissement->cvi = ($cvi) ? str_pad($cvi, 10, "0", STR_PAD_LEFT) : null;
            $societe->pushAdresseTo($etablissement);
            $societe->pushContactTo($etablissement);
            $etablissement->save();

            if(isset($data[self::CSV_ACHETEUR]) && $data[self::CSV_ACHETEUR]) {
                $this->importLiaison($etablissement, $data[self::CSV_ACHETEUR]);
            }
        }
    }

    protected function importLiaison($etablissement, $acheteur) {
        $etablissementAcheteurId = $this->identifyEtablissement($acheteur);
        if(!$etablissementAcheteurId) {
            echo "Établissement cooperative non identifié :".$acheteur."\n";
            return;
        }

        $etablissement->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $etablissementAcheteurId);
        $etablissement->save();
    }

    protected function identifyEtablissement($nom) {

        if(!$this->etablissements) {
            $this->etablissements = EtablissementAllView::getInstance()->getAll();
        }

        $key = KeyInflector::slugify($nom);

        if(isset($this->etablissementsCache[$key])) {
            return $this->etablissementsCache[$key];
        }

        foreach ($this->etablissements as $etab) {
            if (KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($nom))) {
                $this->etablissementsCache[$key] = $etab->id;
                return $this->etablissementsCache[$key];
            }

            if (KeyInflector::slugify($etab->value[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($nom))) {
                $this->etablissementsCache[$key] = $etab->id;
                return $this->etablissementsCache[$key];
            }
        }
        return null;
    }
}
