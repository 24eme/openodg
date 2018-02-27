<?php

class importHabilitationCsvTask extends sfBaseTask
{

  const CSV_IDENTIFIANT = 0;

  const CSV_ACTIVITES = 14;
  const CSV_STATUT = 15;


    protected $types_ignore = array();

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
        $this->name = 'habilitation-from-csv';
        $this->briefDescription = 'Import des habilitation (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;

        $this->produitKey = 'certifications/AOP/genres/TRANQ/appellations/CDR';
        $this->defaultDateHabilitation = '2016-08-01';

        $this->convert_statut = array();
        $this->convert_statut["En attente d'habilitation"] = HabilitationClient::STATUT_DEMANDE_HABILITATION;
        $this->convert_statut["Archivé"] = HabilitationClient::STATUT_ARCHIVE;
        $this->convert_statut['Habilité'] = HabilitationClient::STATUT_HABILITE;
        $this->convert_statut['Refus'] = HabilitationClient::STATUT_REFUS;
        $this->convert_statut["Retrait d'habilitation"] = HabilitationClient::STATUT_RETRAIT;
        $this->convert_statut["Suspension d'habilitation"] = HabilitationClient::STATUT_SUSPENDU;

        $this->convert_activites = array();
        $this->convert_activites['Producteur de raisins'] = HabilitationClient::ACTIVITE_PRODUCTEUR;
        $this->convert_activites['Vinificateur'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_activites['Détenteur de vin en vrac'] = HabilitationClient::ACTIVITE_VRAC;
        $this->convert_activites['Conditionneur'] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        $this->convert_activites['Producteur de moût'] = HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS;
        $this->convert_activites['Eleveur de DGC'] = HabilitationClient::ACTIVITE_ELEVEUR_DGC;

        $this->convert_activites['elaborateur'] = HabilitationClient::ACTIVITE_ELABORATEUR;
        $this->convert_activites['vente tireuse'] = HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $datas = array();
        $date_dossiers = array();
        foreach(file($arguments['fichier_habilitations']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^\"tbl_CDPOps/", $line)) {
                continue;
            }
            $data = str_getcsv($line, ';');

             if (!$data) {
               continue;
             }

             $identifiant = sprintf('%s', $data[self::CSV_IDENTIFIANT]);
             echo "trying $identifiant \n";
             $eta = EtablissementClient::getInstance()->findByIdentifiant($identifiant."01");
            if (!$eta) {
              echo "WARNING: établissement non trouvé ".$identifiant." : pas d'import\n";
              continue;
            }

            $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($eta->identifiant, $this->defaultDateHabilitation);
            $hab_activites = $habilitation->addProduit($this->produitKey)->add('activites');

            $statut = $this->convert_statut[$data[self::CSV_STATUT]];

            foreach (explode(";",$data[self::CSV_ACTIVITES]) as $act) {
                if ($activite = $this->convert_activites[trim($act)]) {
                  $hab_activites->add($activite)->updateHabilitation($statut, '', $this->defaultDateHabilitation);
                }
            }
            $habilitation->save(true);
            echo $habilitation->_id."\n";
        }
    }
}
