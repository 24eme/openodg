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
  const CSV_HABILITATION_TYPE = 9;
  const CSV_HABILITATION_STATUT = 10;
  const CSV_HABILITATION_DATE = 11;
  const CSV_HABILITATION_FIN = 12;
  const CSV_HABILITATION_HISTORIQUE = 13;
#  const CSV_STATUT_PRODUCTEURS_DE_RAISINS = 9;
#  const CSV_DATE_PRODUCTEURS_DE_RAISINS = 10;
#  const CSV_FIN_SUSPENSION_PRODUCTEURS_DE_RAISINS = 11;
#  const CSV_HISTORIQUE_PRODUCTEURS_DE_RAISINS = 12;
#  const CSV_STATUT_VINIFICATEUR = 13;
#  const CSV_DATE_VINIFICATEUR = 14;
#  const CSV_FIN_SUSPENSION_VINIFICATEUR = 15;
#  const CSV_HISTORIQUE_VINIFICATEUR = 16;
#  const CSV_STATUT_CONDITIONNEUR = 17;
#  const CSV_DATE_CONDITIONNEUR = 18;
#  const CSV_FIN_SUSPENSION_CONDITIONNEUR = 19;
#  const CSV_HISTORIQUE_CONDITIONNEUR = 20;
#  const CSV_STATUT_ELEVEUR = 21;
#  const CSV_DATE_ELEVEUR = 22;
#  const CSV_FIN_SUSPENSION_ELEVEUR = 23;
#  const CSV_HISTORIQUE_ELEVEUR = 24;
#  const CSV_STATUT_ACHAT_ET_VENTE = 25;
#  const CSV_DATE_ACHAT_ET_VENTE = 26;
#  const CSV_FIN_SUSPENSION_ACHAT_ET_VENTE = 27;
#  const CSV_HISTORIQUE_ACHAT_ET_VENTE = 28;
#  const CSV_STATUT_ELABORATEUR = 29;
#  const CSV_DATE_ELABORATEUR = 30;
#  const CSV_FIN_SUSPENSION_ELABORATEUR = 31;
#  const CSV_HISTORIQUE_ELABORATEUR = 32;
#  const CSV_STATUT_VENTE_TIREUSE = 33;
#  const CSV_DATE_VENTE_TIREUSE = 34;
#  const CSV_FIN_SUSPENTION_VENTE_TIREUSE = 35;
#  const CSV_HISTORIQUE_VENTE_TIREUSE = 36;

  const CSV_DOSSIER_ID = 0;
  const CSV_DOSSIER_LIBELLE = 1;
  const CSV_DOSSIER_DATE = 2;
  const CSV_DOSSIER_UTILISATEUR = 3;


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
        $this->name = 'Habilitations';
        $this->briefDescription = 'Import des habilitation (via le csv issu de scrapping)';
        $this->detailedDescription = <<<EOF
EOF;

        $this->convert_produits = array();
        //awk -F ';' '{print $9}' data/habilitation.csv | sort | uniq -c | wc -l   =====> 17
        $this->convert_produits['Beaumes de Venise'] = 'certifications/AOP/genres/TRANQ/appellations/BEA';
        $this->convert_produits['CAIRANNE'] = 'certifications/AOP/genres/TRANQ/appellations/CAR';
        $this->convert_produits['chateau grillet'] = 'certifications/AOP/genres/TRANQ/appellations/CGR';
        $this->convert_produits['condrieu'] = 'certifications/AOP/genres/TRANQ/appellations/COD';
        $this->convert_produits['cornas'] = 'certifications/AOP/genres/TRANQ/appellations/COR';
        $this->convert_produits['cote rotie'] = 'certifications/AOP/genres/TRANQ/appellations/CRO';
        $this->convert_produits['côtes du Rhone'] = 'certifications/AOP/genres/TRANQ/appellations/CDR';
        $this->convert_produits['côtes du Rhone village'] = 'certifications/AOP/genres/TRANQ/appellations/CVG';
        $this->convert_produits['cotes du vivarais'] = 'certifications/AOP/genres/TRANQ/appellations/VIV';
        $this->convert_produits['crozes hermitage'] = 'certifications/AOP/genres/TRANQ/appellations/CRH';
        $this->convert_produits['gigondas'] = 'certifications/AOP/genres/TRANQ/appellations/GIG';
        $this->convert_produits['grignan les adhémar'] = 'certifications/AOP/genres/TRANQ/appellations/GLA';
        $this->convert_produits['hermitage'] = 'certifications/AOP/genres/TRANQ/appellations/HER';
        $this->convert_produits['lirac'] = 'certifications/AOP/genres/TRANQ/appellations/LIR';
        $this->convert_produits['muscat de Beaumes'] = 'certifications/AOP/genres/VDN/appellations/VDB';
        $this->convert_produits['st joseph'] = 'certifications/AOP/genres/TRANQ/appellations/SJO';
        $this->convert_produits['st péray'] = 'certifications/AOP/genres/TRANQ/appellations/SPT';
        $this->convert_produits['vdn rasteau'] = 'certifications/AOP/genres/VDN/appellations/VDR';
        $this->convert_produits['vinsobres'] = 'certifications/AOP/genres/TRANQ/appellations/VBR';

        $this->convert_statut = array();
        $this->convert_statut["Demande d'habilitation"] = HabilitationClient::STATUT_DEMANDE_HABILITATION;
        $this->convert_statut['Habilité'] = HabilitationClient::STATUT_HABILITE;
        $this->convert_statut['Refus'] = HabilitationClient::STATUT_REFUS;
        $this->convert_statut['Retrait'] = HabilitationClient::STATUT_RETRAIT;
        $this->convert_statut['Suspendu'] = HabilitationClient::STATUT_SUSPENDU;

        $this->convert_statuts = array();
        $this->convert_statuts['producteur de raisin'] = HabilitationClient::ACTIVITE_PRODUCTEUR;
        $this->convert_statuts['vinificateur'] = HabilitationClient::ACTIVITE_VINIFICATEUR;
        $this->convert_statuts['achat et vente'] = HabilitationClient::ACTIVITE_VRAC;
        $this->convert_statuts['conditionneur'] = HabilitationClient::ACTIVITE_CONDITIONNEUR;
        $this->convert_statuts['elaborateur'] = HabilitationClient::ACTIVITE_ELABORATEUR;
        $this->convert_statuts['vente tireuse'] = HabilitationClient::ACTIVITE_VENTE_A_LA_TIREUSE;
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
            if(preg_match("/^000000#/", $line)) {
                continue;
            }
            $data = str_getcsv($line, ';');
            if ($data[self::CSV_PRODUIT] == 'produit') {
              continue;
            }
            if (!isset($this->convert_produits[$data[self::CSV_PRODUIT]])) {
              echo "ERROR: ".$data[self::CSV_PRODUIT]." not found\n";
              continue;
            }
            $id = sprintf('%06d', $data[self::CSV_ID_IDENTITE]);
            echo "trying $id \n";
            $soc = SocieteClient::getInstance()->find($id);
            if (!$soc) {
              echo "ERROR: pas de société trouvée pour : ".$id."\n";
              continue;
            }
            $eta = $soc->getEtablissementPrincipal();
            if (!$eta) {
              $eta = $soc->createEtablissement(EtablissementFamilles::FAMILLE_PRODUCTEUR);
              $eta->nom = $soc->raison_sociale;
              $eta->save();
              echo "WARNING: établissement créé pour la société ".$id."\n";
            }
            if (!$data[self::CSV_HABILITATION_DATE]) {
              $data[self::CSV_HABILITATION_DATE] = '2000-01-01';
            }
            $habilitation = HabilitationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($eta->identifiant, $data[self::CSV_HABILITATION_DATE]);
            $hab_activites = $habilitation->addProduit($this->convert_produits[$data[self::CSV_PRODUIT]])->add('activites');
            if ($statut = $this->convert_statuts[$data[self::CSV_HABILITATION_TYPE]]) {
              $hab_activites->add($statut)->updateHabilitation($this->convert_statut[$data[self::CSV_HABILITATION_STATUT]], '', $data[self::CSV_HABILITATION_DATE]);
            }
            $habilitation->save(true);
            echo $habilitation->_id."\n";
        }
    }
}
