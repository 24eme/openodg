<?php

class importHabilitationTask extends sfBaseTask
{

  const CSV_ID_EXTRAVITIS = 0;
  const CSV_ID_IDENTITE = 1;
  const CSV_SYNDICAT = 2;
  const CSV_NOM_OPÉRATEUR = 3;
  const CSV_COMMUNE = 4;
  const CSV_CODE_POSTAL = 5;
  const CSV_SIREN = 6;
  const CSV_CVI = 7;
  const CSV_PRODUIT = 8;
  const CSV_STATUT_PRODUCTEURS_DE_RAISINS = 9;
  const CSV_DATE_PRODUCTEURS_DE_RAISINS = 10;
  const CSV_FIN_SUSPENSION_PRODUCTEURS_DE_RAISINS = 11;
  const CSV_HISTORIQUE_PRODUCTEURS_DE_RAISINS = 12;
  const CSV_STATUT_VINIFICATEUR = 13;
  const CSV_DATE_VINIFICATEUR = 14;
  const CSV_FIN_SUSPENSION_VINIFICATEUR = 15;
  const CSV_HISTORIQUE_VINIFICATEUR = 16;
  const CSV_STATUT_CONDITIONNEUR = 17;
  const CSV_DATE_CONDITIONNEUR = 18;
  const CSV_FIN_SUSPENSION_CONDITIONNEUR = 19;
  const CSV_HISTORIQUE_CONDITIONNEUR = 20;
  const CSV_STATUT_ELEVEUR = 21;
  const CSV_DATE_ELEVEUR = 22;
  const CSV_FIN_SUSPENSION_ELEVEUR = 23;
  const CSV_HISTORIQUE_ELEVEUR = 24;
  const CSV_STATUT_ACHAT_ET_VENTE = 25;
  const CSV_DATE_ACHAT_ET_VENTE = 26;
  const CSV_FIN_SUSPENSION_ACHAT_ET_VENTE = 27;
  const CSV_HISTORIQUE_ACHAT_ET_VENTE = 28;
  const CSV_STATUT_ELABORATEUR = 29;
  const CSV_DATE_ELABORATEUR = 30;
  const CSV_FIN_SUSPENSION_ELABORATEUR = 31;
  const CSV_HISTORIQUE_ELABORATEUR = 32;
  const CSV_STATUT_VENTE_TIREUSE = 33;
  const CSV_DATE_VENTE_TIREUSE = 34;
  const CSV_FIN_SUSPENTION_VENTE_TIREUSE = 35;
  const CSV_HISTORIQUE_VENTE_TIREUSE = 36;

    protected $types_ignore = array();

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'Habilitations';
        $this->briefDescription = 'Import des habilitation (via le csv issu de scrapping)';
        $this->detailedDescription = <<<EOF
EOF;

        $this->convert_produits = array();
        $this->convert_produits['côtes du Rhone'] = 'certifications/AOP/genres/TRANQ/appellations/CDR/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT';

        $this->convert_statut = array();
        $this->convert_statut["Demande d'habilitation"] = HabilitationClient::STATUT_DEMANDE_INAO;
        $this->convert_statut['Habilité'] = HabilitationClient::STATUT_HABILITE;
        $this->convert_statut['Refus'] = HabilitationClient::STATUT_REFUS;
        $this->convert_statut['Retrait'] = HabilitationClient::STATUT_RETRAIT;
        $this->convert_statut['Suspendu'] = HabilitationClient::STATUT_SUSPENDU;

    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $datas = array();
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^000000#/", $line)) {
                continue;
            }

            $data = str_getcsv($line, ';');
            if ($data[self::CSV_PRODUIT] == 'produit') {
              continue;
            }
            if (isset($lastid) && $lastid != $data[self::CSV_ID_IDENTITE]) {
              $this->saveRows($rows);
              $rows = [];
            }
            $lastid = $data[self::CSV_ID_IDENTITE];
            $rows[] = $data;
        }
        $this->saveRows($rows);
    }

    private function saveRows($rows) {
      $id = sprintf('%06d', $rows[0][self::CSV_ID_IDENTITE]);
      echo "trying $id \n";
      $soc = SocieteClient::getInstance()->find($id);
      if (!$soc) {
        echo "ERROR: pas de société trouvée pour : ".$id."\n";
        return false;
      }
      $eta = $soc->getEtablissementPrincipal();
      if (!$eta) {
        echo "ERROR: pas d'établissement trouvé pour la société ".$id."\n";
        return false;
      }
      if ($habilitation = HabilitationClient::getInstance()->findMasterByIdentifiant($eta->identifiant)){
        echo "INFO: Habilitation existante pour ".$rows[0][self::CSV_ID_IDENTITE]."\n";
      }else{
        $habilitation = HabilitationClient::getInstance()->createDoc($eta->identifiant, date('Y-m-d'));
      }

      foreach ($rows as $r) {
        if (!isset($this->convert_produits[$r[self::CSV_PRODUIT]])) {
          echo "ERROR: ".$r[self::CSV_PRODUIT]." not found\n";
          continue;
        }
        $hab_produit = $habilitation->declaration->add($this->convert_produits[$r[self::CSV_PRODUIT]]);
        $hab_produit->add('libelle', $r[self::CSV_PRODUIT]);
        $hab_activites = $hab_produit->add('activites');
        if ($r[self::CSV_STATUT_VINIFICATEUR]) {
          $hab_activites->add(HabilitationClient::ACTIVITE_VINIFICATEUR)->add('statut', $this->convert_statut[$r[self::CSV_STATUT_VINIFICATEUR]]);
          $hab_activites->add(HabilitationClient::ACTIVITE_VINIFICATEUR)->add('date', preg_replace("/(\d+)\/(\d+)\/(\d\d\d\d)/", '\3-\2-\1', $r[self::CSV_DATE_VINIFICATEUR]));
        }
        if ($r[self::CSV_STATUT_PRODUCTEURS_DE_RAISINS]) {
          $hab_activites->add(HabilitationClient::ACTIVITE_PRODUCTEUR)->add('statut', $this->convert_statut[$r[self::CSV_STATUT_PRODUCTEURS_DE_RAISINS]]);
          $hab_activites->add(HabilitationClient::ACTIVITE_PRODUCTEUR)->add('date', preg_replace("/(\d+)\/(\d+)\/(\d\d\d\d)/", '\3-\2-\1', $r[self::CSV_DATE_PRODUCTEURS_DE_RAISINS]));
        }
        if ($r[self::CSV_STATUT_ACHAT_ET_VENTE]) {
          $hab_activites->add(HabilitationClient::ACTIVITE_VRAC)->add('statut', $this->convert_statut[$r[self::CSV_STATUT_ACHAT_ET_VENTE]]);
          $hab_activites->add(HabilitationClient::ACTIVITE_VRAC)->add('date', preg_replace("/(\d+)\/(\d+)\/(\d\d\d\d)/", '\3-\2-\1', $r[self::CSV_DATE_ACHAT_ET_VENTE]));
        }
        if ($r[self::CSV_STATUT_CONDITIONNEUR]) {
          $hab_activites->add(HabilitationClient::ACTIVITE_CONDITIONNEUR)->add('statut', $this->convert_statut[$r[self::CSV_STATUT_CONDITIONNEUR]]);
          $hab_activites->add(HabilitationClient::ACTIVITE_CONDITIONNEUR)->add('date', preg_replace("/(\d+)\/(\d+)\/(\d\d\d\d)/", '\3-\2-\1', $r[self::CSV_DATE_CONDITIONNEUR]));
        }
        if ($r[self::CSV_STATUT_VENTE_TIREUSE]) {
          $hab_activites->add(HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE)->add('statut', $this->convert_statut[$r[self::CSV_STATUT_VENTE_TIREUSE]]);
          $hab_activites->add(HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE)->add('date', preg_replace("/(\d+)\/(\d+)\/(\d\d\d\d)/", '\3-\2-\1', $r[self::CSV_DATE_VENTE_TIREUSE]));
        }
      }
      $habilitation->save();

    }
}
