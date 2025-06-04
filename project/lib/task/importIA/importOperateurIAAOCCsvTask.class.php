<?php

class importOperateurIAAOCCsvTask extends sfBaseTask
{
  const CSV_IDENTIFIANT = 0;
  const CSV_RAISON_SOCIALE = 1;
  const CSV_NOM = 2;
  const CSV_ADRESSE_1 = 3;
  const CSV_ADRESSE_2 = 4;
  const CSV_ADRESSE_3 = 5;
  const CSV_CODE_POSTAL = 6;
  const CSV_VILLE = 7;
  const CSV_PAYS = 8;
  const CSV_TELEPHONE = 9;
  const CSV_FAX = 10;
  const CSV_PORTABLE = 11;
  const CSV_EMAIL = 12;
  const CSV_CVI = 13;
  const CSV_SIRET = 14;
  const CSV_TVA_INTRA = 15;
  const CSV_CODE_COMPTABLE = 17;
  const CSV_TYPE_DECLARATION = 18;
  const CSV_PPM = 20;
  const CSV_CODE_INTERNE = 21;
  const CSV_NEGOCIANT = 21;
  const CSV_CAVE_COOPERATIVE = 22;
  const CSV_CAVE_PARTICULIERE = 23;
  const CSV_STATUT = 24;

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
            new sfCommandArgument('csv_commentaire', sfCommandArgument::OPTIONAL, "Fichier csv contenant les commentaires"),
            new sfCommandArgument('csv_categorie', sfCommandArgument::OPTIONAL, "Fichier csv contenant les catégories/familles"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'operateur-ia-aoc';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $commentaires = [];
        if(isset($arguments['csv_commentaire'])) {
            foreach(file($arguments['csv_commentaire']) as $line) {
                $data = str_getcsv($line, ";");
                if(!isset($commentaires[trim($data[0])])) {
                    $commentaires[trim($data[0])] = null;
                }
                $commentaires[trim($data[0])] .= $data[1]." (par ".$data[2]." le $data[3])\n";
            }
        }

        $categories = [];
        if(isset($arguments['csv_categorie'])) {
            foreach(file($arguments['csv_categorie']) as $line) {
                $data = str_getcsv($line, ";");
                $categories[trim($data[1])] = $data[0];
            }
        }

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");
            $identifiant = sprintf("%06d", str_replace("ENT", "", $data[self::CSV_IDENTIFIANT]));
            if(!$identifiant) {
                echo "pas de numéro interne ".$line;
                continue;
            }

            $newSociete = SocieteClient::getInstance()->createSociete($data[self::CSV_RAISON_SOCIALE], SocieteClient::TYPE_OPERATEUR, $identifiant);
            $societe = SocieteClient::getInstance()->find($newSociete->_id);

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

            $categorie = @$categories[trim($data[self::CSV_CODE_INTERNE])];

            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
            if(count($categories)) {
                if(strpos($categorie, "Cave Coopérative Viticole") !== false) {
                    $famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
                } elseif(strpos($categorie, "Négociant") !== false) {
                    $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
                } elseif(strpos($categorie, "Cave Particulière") !== false) {
                    $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
                }
            } else {
                if($data[self::CSV_NEGOCIANT] == "oui") {
                    $famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
                }
                if($data[self::CSV_CAVE_COOPERATIVE] == "oui") {
                    $famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
                }
                if($data[self::CSV_CAVE_PARTICULIERE] == "oui") {
                    $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
                }
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
            $ppm = null;
            if(isset($data[self::CSV_PPM]) && $data[self::CSV_PPM]) {
                $ppm = trim($data[self::CSV_PPM]);
            }
            $etablissement->cvi = $cvi;
            $etablissement->ppm = strtoupper($ppm);
            $societe->pushAdresseTo($etablissement);
            $societe->pushContactTo($etablissement);
            if(isset($commentaires[trim($data[self::CSV_CODE_INTERNE])])) {
                $etablissement->commentaire = $commentaires[trim($data[self::CSV_CODE_INTERNE])];
            }
            $etablissement->save();
        }
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
      $key = str_replace('MAURE-VAR-', 'MAURE-', $key);
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
