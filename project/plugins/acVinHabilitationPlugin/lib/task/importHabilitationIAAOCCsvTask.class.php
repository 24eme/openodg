<?php

class importHabilitationIAAOCCsvTask extends importOperateurIACsvTask
{

  const CSV_HABILITATION_IDENTIFIANT = 0;
  const CSV_HABILITATION_RAISON_SOCIALE = 1;
  const CSV_HABILITATION_CODE_POSTAL = 5;
  const CSV_HABILITATION_CVI = 11;
  const CSV_HABILITATION_SIRET = 12;
  const CSV_HABILITATION_NEGOCIANT = 15;
  const CSV_HABILITATION_PRODUIT = 18;
  const CSV_HABILITATION_ACTIVITES = 19;
  const CSV_HABILITATION_STATUT = 20;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('fichier_habilitations', sfCommandArgument::REQUIRED, "Fichier csv pour l'import des habilitations"),
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
        $this->convert_products['Quincy'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/QCY';
        $this->convert_products['Chateaumeillant'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/CHM';
        $this->convert_products['Reuilly'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/RLY';
        $this->convert_products['Menetou-Salon'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/MTS';
        $this->convert_products['Pouilly'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/PSL';
        $this->convert_products['Pouilly Fumé'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/PFM';
        $this->convert_products['Sancerre'] = '/declaration/certifications/AOC/genres/TRANQ/appellations/SCR';
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $datas = array();
        foreach(file($arguments['fichier_habilitations']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
             if (!$data) {
               continue;
             }
             $eta = $this->identifyEtablissement($data[self::CSV_HABILITATION_RAISON_SOCIALE], $data[self::CSV_HABILITATION_CVI], $data[self::CSV_HABILITATION_CODE_POSTAL]);
             if (!$eta) {
                 echo "WARNING: établissement non trouvé ".$line." : pas d'import\n";
                 continue;
             }

             $produitKey = (isset($this->convert_products[trim($data[self::CSV_HABILITATION_PRODUIT])]))? trim($this->convert_products[trim($data[self::CSV_HABILITATION_PRODUIT])]) : null;

             if (!$produitKey) {
                 echo "WARNING: produit non trouvé ".$line." : pas d'import\n";
                 continue;
             }

             $date = '2000-08-01';
             if (isset($cvi2di[$data[self::CSV_HABILITATION_CVI]]) &&
                    isset($cvi2di[$data[self::CSV_HABILITATION_CVI]][$data[self::CSV_HABILITATION_PRODUIT]]) &&
                    isset($cvi2di[$data[self::CSV_HABILITATION_CVI]][$data[self::CSV_HABILITATION_PRODUIT]]['DATEDECISION'])
                ) {
                    $date = $cvi2di[$data[self::CSV_HABILITATION_CVI]][$data[self::CSV_HABILITATION_PRODUIT]]['DATEDECISION'];
             }

            $statut = $this->convert_statut[trim($data[self::CSV_HABILITATION_STATUT])];

            if (!$produitKey) {
                echo "WARNING: statut non trouvé ".$line." : pas d'import\n";
                continue;
            }
            if($data[self::CSV_HABILITATION_NEGOCIANT] == "oui") {
                $etablissement = EtablissementClient::getInstance()->find($eta->_id);
                $etablissement->famille = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
                $etablissement->save();
            }

            if (($statut == HabilitationClient::STATUT_HABILITE) && isset($cvi2di[$data[self::CSV_HABILITATION_CVI]]) && isset($cvi2di[$data[self::CSV_HABILITATION_CVI]][$data[self::CSV_HABILITATION_PRODUIT]])) {
                $di = $cvi2di[$data[self::CSV_HABILITATION_CVI]][$data[self::CSV_HABILITATION_PRODUIT]];
                if (isset($di['DATEDEMANDE'])) {
                    $this->updateHabilitationStatut($eta->identifiant, $produitKey, $data, HabilitationClient::STATUT_DEMANDE_HABILITATION, $di['DATEDEMANDE']);
                }
                if (isset($di['NOTIFIEEOC'])) {
                    $this->updateHabilitationStatut($eta->identifiant, $produitKey, $data, HabilitationClient::STATUT_ATTENTE_HABILITATION, $di['NOTIFIEEOC']);
                }
                $this->updateHabilitationStatut($eta->identifiant, $produitKey, $data, HabilitationClient::STATUT_HABILITE, $date);
            }else{
                $this->updateHabilitationStatut($eta->identifiant, $produitKey, $data, $statut, $date);
            }
        }
    }

    protected function updateHabilitationStatut($identifiant,$produitKey,$data,$statut,$date){
        $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($identifiant, $date);
        $produit = $habilitation->addProduit($produitKey);
        if (!$produit) {
            echo "WARNING: produit $produitKey (".$data[self::CSV_HABILITATION_PRODUIT].") non trouvé : ligne non importée\n";
            return;
        }
        $hab_activites = $produit->add('activites');
        foreach (explode(",",$data[self::CSV_HABILITATION_ACTIVITES]) as $act) {
            if ($activite = $this->convert_activites[trim($act)]) {
                $hab_activites->add($activite)->updateHabilitation($statut, null, $date);
            }
        }
        $habilitation->save(true);
        echo "SUCCESS: ".$habilitation->_id."\n";
    }
}
