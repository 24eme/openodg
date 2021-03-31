<?php

class importHabilitationIACsvTask extends importOperateurIACsvTask
{

  const CSV_HABILITATION_RS = 0;
  const CSV_HABILITATION_CVI = 1;
  const CSV_HABILITATION_PRODUIT = 2;
  const CSV_HABILITATION_ACTIVITES = 3;
  const CSV_HABILITATION_STATUT = 4;
  const CSV_HABILITATION_ADRESSE = 5;
  const CSV_HABILITATION_COMPLEMENT = 6;
  const CSV_HABILITATION_CP = 7;
  const CSV_HABILITATION_VILLE = 8;

  const CSV_DI_RAISONSOCIALE = 0;
  const CSV_DI_CVI = 1;
  const CSV_DI_IGP = 2;
  const CSV_DI_STATUT = 3;
  const CSV_DI_DATEDEMANDE = 4;
  const CSV_DI_NOTIFIEEOC = 5;
  const CSV_DI_DATEDECISION = 6;
  const CSV_DI_DATESAISIEODG = 7;


  protected $date;
  protected $convert_statut;
  protected $convert_activites;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('fichier_habilitations', sfCommandArgument::REQUIRED, "Fichier csv pour l'import des habilitations"),
            new sfCommandArgument('fichier_di', sfCommandArgument::REQUIRED, "Fichier csv pour l'import de DI"),
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
        $this->convert_products['Ardèche'] = 'certifications/IGP/genres/TRANQ/appellations/ARD';
        $this->convert_products['Collines Rhodaniennes'] = 'certifications/IGP/genres/TRANQ/appellations/CLR';
        $this->convert_products['Comtés Rhodaniens'] = 'certifications/IGP/genres/TRANQ/appellations/CDR';
        $this->convert_products["Ardèche - Coteaux de l'Ardèche"] = 'certifications/IGP/genres/TRANQ/appellations/ARD/mentions/DEFAUT/lieux/CDA';
        $this->convert_products['Mediterranee'] = 'certifications/IGP/genres/TRANQ/appellations/MED';
        $this->convert_products['Pays des Bouches du Rhône'] = 'certifications/IGP/genres/TRANQ/appellations/D13/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['IGP BdR-Terre de Camargue'] = 'certifications/IGP/genres/TRANQ/appellations/D13/mentions/DEFAUT/lieux/TDC';
        $this->convert_products['Var'] = 'certifications/IGP/genres/TRANQ/appellations/VAR';
        $this->convert_products['Mont Caume'] = 'certifications/IGP/genres/TRANQ/appellations/MCA';
        $this->convert_products['Maures'] = 'certifications/IGP/genres/TRANQ/appellations/MAU';
        $this->convert_products['Alpes Maritimes'] = 'certifications/IGP/genres/TRANQ/appellations/AMA';
        $this->convert_products['Vaucluse'] = 'certifications/IGP/genres/TRANQ/appellations/VAU/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Principaute Orange'] = 'certifications/IGP/genres/TRANQ/appellations/VAU/mentions/DEFAUT/lieux/PDO';
        $this->convert_products['Aigues'] = 'certifications/IGP/genres/TRANQ/appellations/VAU/mentions/DEFAUT/lieux/AIG';
        $this->convert_products['Val de Loire'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Loire Atlantique'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/LAT';
        $this->convert_products['Maine et Loire'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/MEL';
        $this->convert_products['Loir et Cher'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/LEC';
        $this->convert_products['Vendée'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/VEN';
        $this->convert_products['Cher'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/CHE';
        $this->convert_products['Allier'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/ALL';
        $this->convert_products['Vienne'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/VIE';
        $this->convert_products['Nievre'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/NIE';
        $this->convert_products['Sarthe'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/SAR';
        $this->convert_products['Indre'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/IND';

        // gascogne
        $this->convert_products['Comté Tolosan'] = 'certifications/IGP/genres/TRANQ/appellations/COT/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Comté Tolosan mousseux'] = 'certifications/IGP/genres/EFF/appellations/COT/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Comté Tolosan surmûri'] = 'certifications/IGP/genres/TRANQ/appellations/COT/mentions/SURMURI/lieux/DEFAUT';
        $this->convert_products['Côtes de Gascogne'] = 'certifications/IGP/genres/TRANQ/appellations/CDG/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Côtes de Gascogne Condomois'] = 'certifications/IGP/genres/TRANQ/appellations/CDG/mentions/DEFAUT/lieux/CON';
        $this->convert_products['Côtes de Gascogne surmûri'] = 'certifications/IGP/genres/TRANQ/appellations/CDG/mentions/SURMURI/lieux/DEFAUT';
        $this->convert_products['Gers'] = 'certifications/IGP/genres/TRANQ/appellations/GER/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Gers surmûri'] = 'certifications/IGP/genres/TRANQ/appellations/GER/mentions/SURMURI/lieux/DEFAUT';

        $this->convert_products['Indre et Loire'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/VAL/mentions/DEFAUT/lieux/IDL';
        $this->convert_products["Calvados"] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/CALV/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products["Coteaux de Tannay"] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/CTXT/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products["Coteaux du Cher et de l'Arnon"] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/CHERAR/mentions/DEFAUT/lieux/DEFAUT';
        $this->convert_products['Cotes de la Charité'] = 'certifications/IGP_VALDELOIRE/genres/TRANQ/appellations/CDLC/mentions/DEFAUT/lieux/DEFAUT';
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $cvi2di = array();
        foreach(file($arguments['fichier_di']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            $cvi = $data[self::CSV_DI_CVI];
            $cvi2di[$cvi] = array();
            foreach(explode(', ', $data[self::CSV_DI_IGP]) as $produit) {
                $cvi2di[$cvi][$produit] = array();
                if ($data[self::CSV_DI_DATEDEMANDE]) {
                    $cvi2di[$cvi][$produit]["DATEDEMANDE"] = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3-$2-$1', $data[self::CSV_DI_DATEDEMANDE]);
                }
                if ($data[self::CSV_DI_NOTIFIEEOC]) {
                    $cvi2di[$cvi][$produit]["NOTIFIEEOC"] = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3-$2-$1', $data[self::CSV_DI_NOTIFIEEOC]);
                }
                if ($data[self::CSV_DI_DATEDECISION]) {
                    $cvi2di[$cvi][$produit]["DATEDECISION"] = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3-$2-$1', $data[self::CSV_DI_DATEDECISION]);
                }
                if ($data[self::CSV_DI_DATESAISIEODG]) {
                    $cvi2di[$cvi][$produit]["DATESAISIEODG"] = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3-$2-$1', $data[self::CSV_DI_DATESAISIEODG]);
                }
            }
        }

        $datas = array();
        foreach(file($arguments['fichier_habilitations']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
             if (!$data) {
               continue;
             }
             $eta = $this->identifyEtablissement($data[self::CSV_HABILITATION_RS], $data[self::CSV_HABILITATION_CVI], $data[self::CSV_HABILITATION_CP]);
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
        //echo "SUCCESS: ".$habilitation->_id."\n";
    }
}
