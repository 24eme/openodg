<?php

class importHabilitationIAAOCCsvTask extends importOperateurIACsvTask
{

  const CSV_HABILITATION_IDENTIFIANT = 0;
  const CSV_HABILITATION_RAISON_SOCIALE = 1;
  const CSV_HABILITATION_CODE_POSTAL = 5;
  const CSV_HABILITATION_CVI = 11;
  const CSV_HABILITATION_SIRET = 12;
  const CSV_HABILITATION_NEGOCIANT = 15;
  const CSV_HABILITATION_CAVE_COOPERATIVE = 16;
  const CSV_HABILITATION_CAVE_PARTICULIERE = 17;
  const CSV_HABILITATION_PRODUIT = 18;
  const CSV_HABILITATION_ACTIVITES = 19;
  const CSV_HABILITATION_STATUT = 20;

  const CSV_INAO_PRODUIT = 0;
  const CSV_INAO_ACTIVITE = 1;
  const CSV_INAO_RAISON_SOCIALE = 2;
  const CSV_INAO_CVI = 6;
  const CSV_INAO_CODE_POSTAL = 8;
  const CSV_INAO_DATE_HABILITATION = 13;
  const CSV_INAO_CODE_HABILITATION = 14;
  const CSV_INAO_DATE_DECISION = 15;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $inao;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('fichier_habilitations', sfCommandArgument::REQUIRED, "Fichier csv pour l'import des habilitations"),
            new sfCommandArgument('fichier_habilitations_inao', sfCommandArgument::REQUIRED, "Fichier csv pour l'import des habilitations"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'habilitation-ia-aoc';
        $this->briefDescription = 'Import des habilitation (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;

        $this->convert_statut = array();
        $this->convert_statut['Habilitation'] = HabilitationClient::STATUT_HABILITE;
        $this->convert_statut["Retrait"] = HabilitationClient::STATUT_RETRAIT;
        $this->convert_statut["Habilitation en cours"] = HabilitationClient::STATUT_DEMANDE_HABILITATION;
        $this->convert_statut["Refusé"] = HabilitationClient::STATUT_REFUS;

        $this->convert_activites = array();
        $this->convert_activites['Producteur de raisin'] = HabilitationClient::ACTIVITE_PRODUCTEUR;
        $this->convert_activites['Vinificateur (vente en vrac)'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_activites['Conditionneur'] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        $this->convert_activites['Négoce'] = HabilitationClient::ACTIVITE_NEGOCIANT;
        $this->convert_activites['Mise en bouteille'] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        $this->convert_activites['Pressurage'] = HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS;;
        $this->convert_activites['Producteur de moûts'] = HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS;
        $this->convert_activites['Eleveur'] = HabilitationClient::ACTIVITE_ELEVEUR;


        $this->convert_products = array();
        $this->convert_products['Coteaux du Giennois'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/CDG';
        $this->convert_products['Quincy'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/QCY';
        $this->convert_products['Chateaumeillant'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/CHM';
        $this->convert_products['Reuilly'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/RLY';
        $this->convert_products['Menetou-Salon'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/MTS';
        $this->convert_products['Pouilly sur Loire'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/PSL';
        $this->convert_products['Pouilly Fumé'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/PFM';
        $this->convert_products['Sancerre'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/SCR';

        $this->convert_produits_inao = [];
        $this->convert_produits_inao['A93'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/MTS';
        $this->convert_produits_inao['A101'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/PFM';
        $this->convert_produits_inao['A102'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/PSL';
        $this->convert_produits_inao['A104'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/QCY';
        $this->convert_produits_inao['A105'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/RLY';
        $this->convert_produits_inao['A109'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/SCR';
        $this->convert_produits_inao['A125'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/CDG';
        $this->convert_produits_inao['A836'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/CHM';

        $this->convert_activites_inao = [];
        $this->convert_activites_inao['VINIFICATEUR'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_activites_inao['VENTE EN VRAC'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_activites_inao['VINIFICATEUR - VCI'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_activites_inao['PRODUCTEUR DE MOUTS - VCI'] = HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS;
        $this->convert_activites_inao['PRODUCTEUR DE MOUTS'] = HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS;
        $this->convert_activites_inao['PRODUCTEUR DE MOUT'] = HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS;
        $this->convert_activites_inao['CONDITIONNEUR'] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        $this->convert_activites_inao['PRODUCTEUR DE RAISINS'] = HabilitationClient::ACTIVITE_PRODUCTEUR;
        $this->convert_activites_inao['ELEVEUR'] = HabilitationClient::ACTIVITE_ELEVEUR;
        $this->convert_activites_inao['NEGOCIANT'] = HabilitationClient::ACTIVITE_NEGOCIANT;
        $this->convert_activites_inao['DEFAULT'] = 'DEFAULT';
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->inao = [];

        foreach(file($arguments['fichier_habilitations_inao']) as $line) {

            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');

            $eta = $this->identifyEtablissement(null, $data[self::CSV_INAO_CVI], $data[self::CSV_INAO_CODE_POSTAL]);
            if (!$eta) {
                $eta = $this->identifyEtablissement($data[self::CSV_INAO_RAISON_SOCIALE], $data[self::CSV_INAO_CVI], $data[self::CSV_INAO_CODE_POSTAL]);
            }
            if (!$eta) {
                continue;
            }
            if(!isset($this->convert_activites_inao[$data[self::CSV_INAO_ACTIVITE]])) {
                continue;
            }
            $dateHabilitation = preg_replace("#^([0-9]{2})/([0-9]{2})/([0-9]{4})$#", '\3-\2-\1', trim($data[self::CSV_INAO_DATE_HABILITATION]));

            if(!isset($this->inao[$eta->identifiant][$this->convert_produits_inao[$data[self::CSV_INAO_PRODUIT]]])) {
                @$this->inao[$eta->identifiant][$this->convert_produits_inao[$data[self::CSV_INAO_PRODUIT]]]['DEFAULT'] = $dateHabilitation;
            }
            @$this->inao[$eta->identifiant][$this->convert_produits_inao[$data[self::CSV_INAO_PRODUIT]]][$this->convert_activites_inao[$data[self::CSV_INAO_ACTIVITE]]] = $dateHabilitation;
        }

        $datas = array();
        foreach(file($arguments['fichier_habilitations']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
             if (!$data) {
               continue;
             }
             $eta = $this->identifyEtablissement($data[self::CSV_HABILITATION_RAISON_SOCIALE], $data[self::CSV_HABILITATION_CVI], $data[self::CSV_HABILITATION_CODE_POSTAL]);
             if (!$eta) {
                 //echo "WARNING: établissement non trouvé ".$line." : pas d'import\n";
                 continue;
             }

             $produitKey = (isset($this->convert_products[trim($data[self::CSV_HABILITATION_PRODUIT])]))? trim($this->convert_products[trim($data[self::CSV_HABILITATION_PRODUIT])]) : null;

             if (!$produitKey) {
                 echo "WARNING: produit non trouvé ".$line." : pas d'import\n";
                 continue;
             }

            $statut = $this->convert_statut[trim($data[self::CSV_HABILITATION_STATUT])];

            if (!$statut) {
                echo "WARNING: statut non trouvé ".$line." : pas d'import\n";
                continue;
            }

            $this->updateHabilitationStatut($eta->identifiant, $produitKey, $data, $statut);

            $etablissement = EtablissementClient::getInstance()->find($eta->_id);
            $habilitation = HabilitationClient::getInstance()->getLastHabilitation($eta->identifiant);
            $theoriticalFamille = $habilitation->getTheoriticalFamille();
            $activites = $habilitation->getActivitesHabilites();
            if(!count($activites)) {
                $activites = $habilitation->getActivitesWrongHabilitation();
            }
            if($theoriticalFamille) {
                $etablissement->famille = $habilitation->getTheoriticalFamille();
            } elseif($data[self::CSV_HABILITATION_CAVE_COOPERATIVE] == "oui") {
                $etablissement->famille = EtablissementFamilles::FAMILLE_COOPERATIVE;
            } elseif($data[self::CSV_HABILITATION_NEGOCIANT] == "oui") {
                $etablissement->famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
            } elseif($data[self::CSV_HABILITATION_CAVE_PARTICULIERE] == "oui") {
                $etablissement->famille = EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR;
            } elseif(in_array(HabilitationClient::ACTIVITE_CONDITIONNEUR, $activites) && in_array(HabilitationClient::ACTIVITE_VINIFICATEUR, $activites)) {
                $etablissement->famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
            } elseif(in_array(HabilitationClient::ACTIVITE_CONDITIONNEUR, $activites)) {
                $etablissement->famille = EtablissementFamilles::FAMILLE_NEGOCIANT;
            } else {
                $etablissement->famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
                echo $etablissement->_id."\n";
            }

            $etablissement->save();
        }
    }

    protected function updateHabilitationStatut($identifiant,$produitKey,$data,$statut){
        $date = '2000-08-01';
        foreach (explode(",",$data[self::CSV_HABILITATION_ACTIVITES]) as $act) {
            $activite = $this->convert_activites[trim($act)];
            if (!$activite) {
                continue;
            }
            if(isset($this->inao[$identifiant][$produitKey][$activite])) {
                $date = $this->inao[$identifiant][$produitKey][$activite];
            } elseif(isset($this->inao[$identifiant][$produitKey]['DEFAULT'])) {
                $date = $this->inao[$identifiant][$produitKey]['DEFAULT'];
            }

            $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($identifiant, $date);
            $produit = $habilitation->addProduit($produitKey);
            if (!$produit) {
                echo "WARNING: produit $produitKey (".$data[self::CSV_HABILITATION_PRODUIT].") non trouvé : ligne non importée\n";
                return;
            }

            $produit->add('activites')->add($activite)->updateHabilitation($statut, null);
            $habilitation->save(true);
            echo "SUCCESS: ".$habilitation->_id."\n";
        }
    }
}
