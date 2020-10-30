<?php

class importHabilitationIACsvTask extends sfBaseTask
{

  const CSV_RS = 0;
  const CSV_CVI = 1;
  const CSV_PRODUIT = 2;
  const CSV_ACTIVITES = 3;
  const CSV_STATUT = 4;
  const CSV_ADRESSE = 5;
  const CSV_COMPLEMENT = 6;
  const CSV_CP = 7;
  const CSV_VILLE = 8;
  
  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $etablissements;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('fichier_habilitations', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'habilitation-ia';
        $this->briefDescription = 'Import des habilitation (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;

        $this->date = '2020-08-01';

        $this->convert_statut = array();
        $this->convert_statut['Habilité'] = HabilitationClient::STATUT_HABILITE;
        $this->convert_statut["Retiré"] = HabilitationClient::STATUT_RETRAIT;
        $this->convert_statut["Suspendu"] = HabilitationClient::STATUT_SUSPENDU;

        $this->convert_activites = array();
        $this->convert_activites['Producteur de raisin'] = HabilitationClient::ACTIVITE_PRODUCTEUR;
        $this->convert_activites['Vinificateur'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_activites['Conditionneur'] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        $this->convert_activites['Négociant'] = HabilitationClient::ACTIVITE_NEGOCIANT;
        $this->convert_activites['Vrac export'] = HabilitationClient::ACTIVITE_VRAC;
        

        $this->convert_products = array();
        $this->convert_products['Alpilles'] = 'certifications/IGP/genres/TRANQ/appellations/APL';
        $this->convert_products['Mediterranee'] = 'certifications/IGP/genres/TRANQ/appellations/MED';
        $this->convert_products['Pays des Bouches du Rhône'] = 'certifications/IGP/genres/TRANQ/appellations/D13';
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        
        $this->etablissements = EtablissementAllView::getInstance()->getAll();
        
        $datas = array();
        foreach(file($arguments['fichier_habilitations']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
             if (!$data) {
               continue;
             }
             $eta = $this->identifyEtablissement($data);
             if (!$eta) {
                 echo "WARNING: établissement non trouvé ".$line." : pas d'import\n";
                 continue;
             }

             $produitKey = (isset($this->convert_products[trim($data[self::CSV_PRODUIT])]))? trim($this->convert_products[trim($data[self::CSV_PRODUIT])]) : null;

             if (!$produitKey) {
                 echo "WARNING: produit non trouvé ".$line." : pas d'import\n";
                 continue;
             }

            $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($eta->identifiant, $this->date);
            $hab_activites = $habilitation->addProduit($produitKey)->add('activites');

            $statut = $this->convert_statut[trim($data[self::CSV_STATUT])];

            if (!$produitKey) {
                echo "WARNING: statut non trouvé ".$line." : pas d'import\n";
                continue;
            }

            $this->updateHabilitationStatut($hab_activites, $data, $statut, $this->date);

            $habilitation->save(true);
            //echo "SUCCESS: ".$habilitation->_id."\n";
        }
    }

    protected function identifyEtablissement($data) {
        foreach ($this->etablissements as $etab) {
            if (isset($data[self::CSV_CVI]) && trim($data[self::CSV_CVI]) && $etab->key[EtablissementAllView::KEY_CVI] == trim($data[self::CSV_CVI])) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
            if (isset($data[self::CSV_RS]) && trim($data[self::CSV_RS]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_RS]))) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
            if (isset($data[self::CSV_RS]) && trim($data[self::CSV_RS]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_RS]))) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
        }
        return null;
    }

    protected function updateHabilitationStatut($hab_activites,$data,$statut,$date){
        foreach (explode(",",$data[self::CSV_ACTIVITES]) as $act) {
            if ($activite = $this->convert_activites[trim($act)]) {
                $hab_activites->add($activite)->updateHabilitation($statut, null, $date);
            }
        }
    }
}
