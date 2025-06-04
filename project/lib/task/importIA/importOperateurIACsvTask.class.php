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
  protected $etablissements = null;
  protected $etablissementsCache = array();

    public static $correspondancesCepages = array(
      "Cabernet sauvignon N" => "CAB-SAUV-N",
      "Chardonnay B" => "CHARDONN.B",
      "Cinsault N" => "CINSAUT N",
      "Clairette B" => "CLAIRET.B",
      "Mourvèdre N" => "MOURVED.N",
      "Muscat à petits grains B" => "MUS.PT.G.B",
      "Muscat à petits grains Rs" => "MUS.P.G.RS",
      "Muscat d'Hambourg N" => "MUS.HAMB.N",
      "Muscat PG B" => "MUS.PT.G.B",
      "Nielluccio N" => "NIELLUC.N",
      "Sauvignon B" => "SAUVIGN.B",
      "Savagnin Blanc B" => "SAVAGN.B",
      "Vermentino B" => "VERMENT.B"
    );

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
              $societe->email = KeyInflector::unaccent($data[self::CSV_EMAIL]);
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
            } elseif(preg_match("/Négociant/", $data[self::CSV_ACTIVITE])) {
              $famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
            } else {
              $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
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
              for($i = strlen($cvi) ; $i < 10 ;  $i++) {
                  $cvi = $cvi."0";
              }
              if(!intval($cvi)) {
                  $cvi = '';
              }
            }
            $etablissement->cvi = $cvi;
            $societe->pushAdresseTo($etablissement);
            $societe->pushContactTo($etablissement);
            $etablissement->save();

            if(isset($data[self::CSV_ACHETEUR]) && $data[self::CSV_ACHETEUR]) {
                $this->importLiaison($etablissement, $data[self::CSV_ACHETEUR]);
            }
        }
    }

    protected function importLiaison($etablissement, $acheteur) {
        $etablissementAcheteur = $this->identifyEtablissement($acheteur);
        if(!$etablissementAcheteur) {
            echo "Établissement cooperative non identifié :".$acheteur."\n";
            return;
        }

        $etablissement->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $etablissementAcheteur->_id);
        $etablissement->save();
    }

    protected function identifyEtablissement($raisonSociale, $cvi = null, $codePostal = null, $hydrate = acCouchdbClient::HYDRATE_JSON) {
        if(!$this->etablissements) {
            $this->etablissements = EtablissementAllView::getInstance()->getAll();
        }

        $CSV_HABILITATION_CVI = preg_replace('/[^0-9]/', '', $cvi);
        for($i = strlen($CSV_HABILITATION_CVI) ; $i < 10 ; $i++) {
            $CSV_HABILITATION_CVI .= '0';
        }
        if (!intval($CSV_HABILITATION_CVI)){
            $CSV_HABILITATION_CVI = '';
        }
        $CSV_HABILITATION_RS = KeyInflector::slugify(trim($raisonSociale));
        $CSV_HABILITATION_RS_WITHOUT_INTITULE = KeyInflector::slugify(trim(CompteGenerique::extractIntitule(trim($raisonSociale))[1]));
        $key_raisonsociale_cvi_codepostal = KeyInflector::slugify($CSV_HABILITATION_RS.$CSV_HABILITATION_CVI.str_replace(' ', '', $codePostal));

        if(isset($this->etablissementsCache[$key_raisonsociale_cvi_codepostal])) {

            return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
        }

        $key_raisonsociale_codepostal = KeyInflector::slugify($CSV_HABILITATION_RS.trim($codePostal));
        foreach ($this->etablissements as $etab) {
            if (KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM].$etab->key[EtablissementAllView::KEY_CVI].$etab->value[EtablissementAllView::VALUE_CODE_POSTAL]) == $key_raisonsociale_cvi_codepostal) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
            if (KeyInflector::slugify($etab->key[EtablissementAllView::VALUE_RAISON_SOCIALE].$etab->key[EtablissementAllView::KEY_CVI].$etab->value[EtablissementAllView::VALUE_CODE_POSTAL]) == $key_raisonsociale_cvi_codepostal) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
            if (KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM].$etab->value[EtablissementAllView::VALUE_CODE_POSTAL]) == $key_raisonsociale_codepostal) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
            if (KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE].$etab->value[EtablissementAllView::VALUE_CODE_POSTAL]) == $key_raisonsociale_codepostal) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
        }
        foreach ($this->etablissements as $etab) {
            if ($CSV_HABILITATION_CVI && $etab->key[EtablissementAllView::KEY_CVI] == $CSV_HABILITATION_CVI ) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
            if (KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == $CSV_HABILITATION_RS) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
            if (KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == $CSV_HABILITATION_RS) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }

            $currentNomWithoutIntitule = KeyInflector::slugify(trim(CompteGenerique::extractIntitule($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE])[1]));
            if ($currentNomWithoutIntitule == $CSV_HABILITATION_RS_WITHOUT_INTITULE) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
            $currentNomWithoutIntitulInverse = implode("-", array_reverse(explode("-", $currentNomWithoutIntitule)));
            if ($currentNomWithoutIntitulInverse == $CSV_HABILITATION_RS_WITHOUT_INTITULE) {
                $this->etablissementsCache[$key_raisonsociale_cvi_codepostal] = EtablissementClient::getInstance()->find($etab->id, $hydrate);

                return $this->etablissementsCache[$key_raisonsociale_cvi_codepostal];
            }
        }
        return null;
    }


        public function initProduitsCepages() {
          $this->produits = array();
          $this->cepages = array();
          $produits = ConfigurationClient::getInstance()->getConfiguration()->declaration->getProduits();
          foreach ($produits as $key => $produit) {
            $this->produits[KeyInflector::slugify($produit->getLibelleFormat())] = $produit;
            foreach($produit->getCepagesAutorises() as $ca) {
              $this->cepages[KeyInflector::slugify($ca)] = $ca;
            }
          }
        }

    protected function identifyCepage($key) {
      $key = trim($key);
      if (isset($this->cepages[KeyInflector::slugify($key)])) {
        return $this->cepages[KeyInflector::slugify($key)];
      }
      $correspondances = self::$correspondancesCepages;
      return (isset($correspondances[$key]))? $correspondances[$key] : strtoupper(str_replace(' ', '.', $key));
    }

    public function clearProduitKey($key) {
      $key = implode("-", array_unique(explode("-", $key)));
      $key = preg_replace('/[\-]*[0-9]+$/', '', $key);
      $key = str_replace('MAURE-VAR-', 'MAURE-', $key);
      $key = str_replace('CHATEAUMEILLANT-GRIS', 'CHATEAUMEILLANT-ROSE', $key);
      $key = str_replace('MONT-CAUME-VAR-', 'MONT-CAUME-', $key);
      $key = str_replace('MEDITERRANEE-VAR-', 'MEDITERRANEE-', $key);
      $key = str_replace('VAR-VAR-', 'VAR-', $key);
      $key = str_replace('IGP-BDR-', 'PAYS-DES-BOUCHES-DU-RHONE-', $key);
      $key = str_replace('NORD-', '', $key);
      $key = str_replace('PRINCIPAUTE-ORANGE', 'VAUCLUSE-PRINCIPAUTE-D-ORANGE', $key);
      $key = preg_replace('/^COTEAUX-DE-MONTELIMAR/', 'DROME-COTEAUX-DE-MONTELIMAR', $key);
      $key = preg_replace('/^COMTE-DE-GRIGNAN/', 'DROME-COMTE-DE-GRIGNAN', $key);
      $key = preg_replace('/^LOIRE-ATLANTIQUE/', 'VAL-DE-LOIRE-LOIRE-ATLANTIQUE', $key);
      $key = preg_replace('/^INDRE-ET-LOIRE/', 'VAL-DE-LOIRE-INDRE-ET-LOIRE', $key);
      $key = preg_replace('/^MAINE-ET-LOIRE/', 'VAL-DE-LOIRE-MAINE-ET-LOIRE', $key);
      $key = preg_replace('/^LOIR-ET-CHER/', 'VAL-DE-LOIRE-LOIR-ET-CHER', $key);
      $key = preg_replace('/^CHER/', 'VAL-DE-LOIRE-CHER', $key);
      $key = preg_replace('/^SARTHE/', 'VAL-DE-LOIRE-SARTHE', $key);
      $key = preg_replace('/^VENDEE/', 'VAL-DE-LOIRE-VENDEE', $key);
      $key = preg_replace('/^VIENNE/', 'VAL-DE-LOIRE-VIENNE', $key);
      $key = preg_replace('/^ALLIER/', 'VAL-DE-LOIRE-ALLIER', $key);
      return $key;
    }
}
