<?php

class importHabilitationCsvTask extends sfBaseTask
{

  const CSV_IDENTIFIANT = 0;

  const CSV_ACTIVITES = 14;
  const CSV_STATUT = 15;
  const CSV_DATE_DEMANDE_HABILITATION = 30;
  const CSV_DATE_HABILITATION = 31;
  const CSV_DATE_ARCHIVAGE = 32;
  const CSV_COMMENTAIRE = 33;


    protected $types_ignore = array();
    protected $dateHabilitation = null;
    protected $dateArchivage = null;
    protected $dateDemande = null;

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

        $this->produitKey = 'certifications/AOP/genres/TRANQ/appellations/CDP';
        $this->dateHabilitation = '2016-08-01';
        $this->dateArchivage = null;

        $this->convert_statut = array();
        $this->convert_statut["En attente d'habilitation"] = HabilitationClient::STATUT_ATTENTE_HABILITATION;
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


            $dateHabilitation = DateTime::createFromFormat("d/m/Y",$this->convertDate($data[self::CSV_DATE_HABILITATION]));
            $this->dateHabilitation = ($dateHabilitation)? $dateHabilitation->format("Y-m-d") : null;

            $dateDemande = DateTime::createFromFormat("d/m/Y",$this->convertDate($data[self::CSV_DATE_DEMANDE_HABILITATION]));
            $this->dateDemande = ($dateDemande)? $dateDemande->format("Y-m-d") : $this->dateHabilitation;
            echo "$eta->identifiant $this->dateDemande\n";

            $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($eta->identifiant, $this->dateDemande);
            $hab_activites = $habilitation->addProduit($this->produitKey)->add('activites');


            $dateArchivageHabilitation = DateTime::createFromFormat("d/m/Y",$this->convertDate($data[self::CSV_DATE_ARCHIVAGE]));
            $this->dateArchivage = ($dateArchivageHabilitation)? $dateArchivageHabilitation->format("Y-m-d") : null;



            $statut = $this->convert_statut[$data[self::CSV_STATUT]];

            //demande
            if($statut == HabilitationClient::STATUT_ATTENTE_HABILITATION){
                $this->updateHabilitationStatut($hab_activites,$data,HabilitationClient::STATUT_ATTENTE_HABILITATION,$this->dateDemande);
            }

            //statut habilite
            if($statut == HabilitationClient::STATUT_HABILITE){
                $this->updateHabilitationStatut($hab_activites,$data,$statut,$this->dateHabilitation);
            }
            //statut archive
            if($statut == HabilitationClient::STATUT_ARCHIVE){
                $this->updateHabilitationStatut($hab_activites,$data,$statut,$this->dateArchivage);
            }
            if(($statut == HabilitationClient::STATUT_RETRAIT) || ($statut == HabilitationClient::STATUT_SUSPENDU)){
                $this->updateHabilitationStatut($hab_activites,$data,$statut,$this->dateHabilitation);
            }
            $habilitation->save(true);
            echo $habilitation->_id."\n";
        }
    }

    protected function updateHabilitationStatut($hab_activites,$data,$statut,$date){
        foreach (explode(";",$data[self::CSV_ACTIVITES]) as $act) {
            if ($activite = $this->convert_activites[trim($act)]) {
                $commentaire = ($data[self::CSV_COMMENTAIRE])? str_replace("#","\n",$data[self::CSV_COMMENTAIRE]) : '';
                $hab_activites->add($activite)->updateHabilitation($statut, $commentaire, $date);
            }
        }
    }

    protected function convertDate($date){
        if(preg_match('/[0-9]{5}/',trim($date))){
            $dateExcel = trim($date);
            $date19000101 = new DateTime("1900-01-01");
            $d = $date19000101->modify("+".$dateExcel." days");
            return $d->format('d/m/Y');
        }else{
            return $date;
        }
    }
}
