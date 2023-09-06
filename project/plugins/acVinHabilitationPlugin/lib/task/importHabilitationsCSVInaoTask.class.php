<?php

class importHabilitationsCSVInaoTask extends sfBaseTask
{

  const CSV_PRODUIT_LIBELLE = 0;
  const CSV_OPERATEUR_RAISON_SOCIALE = 1;
  const CSV_ETABLISSEMENT_RAISON_SOCIALIE = 2;
  const CSV_CVI = 3;
  const CSV_SIRET = 4;
  const CSV_SIEGE_ADRESSE_1 = 5;
  const CSV_SIEGE_ADRESSE_2 = 6;
  const CSV_SIEGE_BOITE_POSTALE = 7;
  const CSV_SIEGE_ADRESSE_3 = 8;
  const CSV_SIEGE_CODE_POSTAL = 9;
  const CSV_SIEGE_COMMUNE = 10;
  const CSV_RESPONSABLE_NOM = 11;
  const CSV_RESPONSABLE_QUALITE = 12;
  const CSV_RESPONSABLE_TELEPHONE = 13;
  const CSV_RESPONSABLE_PORTABLE = 14;
  const CSV_RESPONSABLE_TELECOPIE = 15;
  const CSV_RESPONSABLE_EMAIL = 16;
  const CSV_DI_DATE_DEPOT = 17;
  const CSV_DI_DATE_ENREGISTREMENT = 18;
  const CSV_PRODUCTEUR_RAISINS = 19;
  const CSV_PRODUCTEUR_MOUTS = 20;
  const CSV_VINIFICATEUR = 21;
  const CSV_CONDITIONNEUR = 27;
  const CSV_ACHAT = 23;
  const CSV_ELABORATEUR = 24;
  const CSV_DISTILLATEUR = 26;
  const CSV_METTEUR_EN_MARCHE = 28;
  const CSV_ELEVEUR = 29;
  const CSV_PRESTATAIRE_DE_SERVICE = 30;


  public static $activites = array( self::CSV_PRODUCTEUR_RAISINS => HabilitationClient::ACTIVITE_PRODUCTEUR,
                             self::CSV_PRODUCTEUR_MOUTS => HabilitationClient::ACTIVITE_PRODUCTEUR_MOUTS,
                             self::CSV_VINIFICATEUR => HabilitationClient::ACTIVITE_VINIFICATEUR,
                             self::CSV_CONDITIONNEUR => HabilitationClient::ACTIVITE_CONDITIONNEUR,
                             self::CSV_ACHAT => HabilitationClient::ACTIVITE_VRAC,
                             self::CSV_ELABORATEUR => HabilitationClient::ACTIVITE_ELABORATEUR,
                             self::CSV_DISTILLATEUR => HabilitationClient::ACTIVITE_DISTILLATEUR,
                             self::CSV_METTEUR_EN_MARCHE => HabilitationClient::ACTIVITE_METTEUR_EN_MARCHE,
                             self::CSV_ELEVEUR => HabilitationClient::ACTIVITE_ELEVEUR,
                             self::CSV_PRESTATAIRE_DE_SERVICE => HabilitationClient::ACTIVITE_PRESTATAIRE_DE_SERVICE,
                         );

  public $produits = array( "Muscadet" => "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSAC/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",
                            "Muscadet Sèvre et Maine" => "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSSM/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",
                            "Muscadet Côtes de Grand Lieu" => "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCGL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",
                            "Muscadet Coteaux de la Loire" => "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/MUSCDL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/MEL",
                            "Gros plant" => "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/GPL/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/FBL",
                            "Coteaux d'Ancenis" => "certifications/AOC_INTERLOIRE/genres/TRANQ/appellations/COA/mentions/DEFAUT/lieux/DEFAUT/couleurs/blanc/cepages/DEFAUT",
                            "Alsace" => "certification/genre/appellation_ALSACE",
                            "Alsace Crémant" => "certification/genre/appellation_CREMANT",
                            "Alsace Grand cru" => "certification/genre/appellation_GRDCRU",
                            "Marc d'Alsace Gewurztraminer" => "certification/genre/appellation_MARC",
                          );


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('habilitations_inao', sfCommandArgument::REQUIRED, "Fichier csv INAO pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'habilitations-csv-inao';
        $this->briefDescription = "Import des habilitations via un csv de l'inao";
        $this->detailedDescription = <<<EOF
EOF;
  }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $inaoHabilitationCsvFile = new INAOHabilitationCsvFile($arguments['habilitations_inao']);

        foreach ($inaoHabilitationCsvFile->getLignes() as $key => $data) {

             $cvi = sprintf('%s', $data[self::CSV_CVI]);
             $siren = preg_replace('/^([0-9]{9}).*/', '\1', str_replace(' ', '', $data[self::CSV_SIRET]));
             echo " => import pour  $cvi - $siren \n";
             $eta = EtablissementClient::getInstance()->findByCvi($cvi);
             if (!$eta) {
                 $eta = EtablissementClient::getInstance()->findByCviOrAcciseOrPPMOrSirenOrTVA($siren);
             }
             if (!$eta) {
                 echo "WARNING: établissement non trouvé ".$data[self::CSV_CVI]." - ".$data[self::CSV_SIRET]." : pas d'import\n";
                 continue;
             }

             // ON VIRE LES HABILITATIONS DEJA CREER
             // $habs = HabilitationClient::getInstance()->getHistory($eta->identifiant);
             // foreach ($habs as $key => $hab) {
             //    // echo "/!\ : on vire l'habilitation précédente ".$hab->_id." \n";
             //    // $hab->delete();
             // }

            $produitKey = $this->produits[$data[self::CSV_PRODUIT_LIBELLE]];
            // var_dump($produitKey); exit;
            $dHabilitation = DateTime::createFromFormat("d/m/Y",$this->convertDate($data[self::CSV_DI_DATE_ENREGISTREMENT]));
            $dateHabilitation = ($dHabilitation)? $dHabilitation->format("Y-m-d") : null;

            $dDemande = DateTime::createFromFormat("d/m/Y",$this->convertDate($data[self::CSV_DI_DATE_DEPOT]));
            $dateDemande = ($dDemande)? $dDemande->format("Y-m-d") : $dHabilitation;

            if(!$dateDemande){ continue; }
            $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($eta->identifiant, $dateDemande);
            $hab_activites = $habilitation->addProduit($produitKey)->add('activites');

            foreach (self::$activites as $csvkey => $activite) {
              trim($data[$csvkey]);
              if($data[$csvkey]){
                //demande
                // if($dateDemande){
                //   $hab_activites->add($activite)->updateHabilitation(HabilitationClient::STATUT_ATTENTE_HABILITATION, null, $dateDemande);
                // }
                //statut habilite
                $a = $habilitation->addProduit($produitKey)->add('activites')->add($activite);
                $a->updateHabilitation(HabilitationClient::STATUT_HABILITE, null, $dateDemande);

              }
            }

            $habilitation->save(true);
            echo $habilitation->_id."\n";
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
