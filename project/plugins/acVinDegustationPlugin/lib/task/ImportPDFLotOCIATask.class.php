<?php

class ImportPDFLotOCIATask extends importOperateurIACsvTask
{
  const CSV_NUMERO_ECHANTILLON = 0;
  const CSV_RAISON_SOCIALE = 2;
  const CSV_CVI = 3;
  const CSV_PRODUIT = 6;
  const CSV_MILLESIME = 7;
  const CSV_DATE_PRELEVEMENT = 9;
  const CSV_TYPE_DECLARATION = 10;
  const CSV_LOGEMENT = 11;
  const CSV_VOLUME = 12;
  const CSV_NUM_LOT = 13;
  const CSV_TYPE_CONTROLE = 14;

  const CSV_SYNTHESE_PRODUIT = 11;
  const CSV_SYNTHESE_MILLESIME = 12;
  const CSV_SYNTHESE_NUM_LOT = 13;
  const CSV_SYNTHESE_LOGEMENT = 14;
  const CSV_SYNTHESE_VOLUME = 15;
  const CSV_SYNTHESE_DATE_DECLARATION = 21;

  protected $date;
  protected $produits;
  protected $cepages;
  protected $synthese;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('pdf', sfCommandArgument::REQUIRED, "Fichier pdf"),
            new sfCommandArgument('echantillon', sfCommandArgument::REQUIRED, "Numéro échantillon du CSV"),
            new sfCommandArgument('date', sfCommandArgument::REQUIRED, "Date du courrier format iso"),
            new sfCommandArgument('identifiant', sfCommandArgument::OPTIONAL, "Identifiant de l'opérateur"),
            new sfCommandArgument('unique_id', sfCommandArgument::OPTIONAL, "unique id du lot"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'lot-pdf';
        $this->briefDescription = 'Import du PDF d\'un lots oc ia';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $document = null;
        $ligne = 0;
        $nb = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');

            if (!$data) {
              continue;
            }

            if ($data[self::CSV_NUMERO_ECHANTILLON] != $arguments['echantillon']) {
                continue;
            }

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], null);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }

            $produitKey = $this->clearProduitKey(KeyInflector::slugify($this->alias($data[self::CSV_PRODUIT])));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_PRODUIT]." ($produitKey);pas d'import;$line\n";
              continue;
            }

            if(!$data[self::CSV_DATE_PRELEVEMENT]) {
                echo "Warning;pas de date;$line\n";
                continue;
            }

            $numeroLot = null;
            if(isset($data[self::CSV_NUM_LOT])) {
                $numeroLot = trim($data[self::CSV_NUM_LOT]);
            }
            $logement = null;
            if(isset($data[self::CSV_LOGEMENT])) {
                $logement = trim($data[self::CSV_LOGEMENT]);
            }

            if ($arguments['identifiant'] && $arguments['unique_id']) {
                $mvts = MouvementLotView::getInstance()->getMouvementsByStatutIdentifiantAndUniqueId(null, $arguments['identifiant'], $arguments['unique_id']);
            }else{
            $dataAugmented = [];
            $dataAugmented['etablissement'] = $etablissement;
            $dataAugmented['date_prelevement'] = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_PRELEVEMENT]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $dataAugmented['date_declaration'] = $dataAugmented['date_prelevement'];
            $dataAugmented['produit'] = $this->produits[$produitKey];
            $dataAugmented['millesime'] = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : null;
            $dataAugmented['volume'] = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;
            $dataAugmented['numero_logement_operateur'] = trim(preg_replace('#(^/|/$)#', "", trim($logement.' / '.$numeroLot)));
            $dataAugmented['numero_dossier'] = sprintf("%05d", explode("-", $data[self::CSV_NUMERO_ECHANTILLON])[0]);
            $dataAugmented['numero_archive'] = sprintf("%05d", explode("-", $data[self::CSV_NUMERO_ECHANTILLON])[1]);

            $mvts = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $dataAugmented['volume'], 'produit_hash' => $dataAugmented['produit']->getHash(), 'millesime' =>  $dataAugmented['millesime'], 'numero_logement_operateur' => $dataAugmented['numero_logement_operateur']), false);
            }
            $lots = array();
            foreach($mvts as $m) {
                if ($m->date > $arguments['date']) {
                    continue;
                }
                if (isset($lots[$m->declarant_identifiant.'/'.$m->unique_id])) {
                    if ($lots[$m->declarant_identifiant.'/'.$m->unique_id]->document_ordre > $m->document_ordre) {
                        continue;
                    }
                }
                $lots[$m->declarant_identifiant.'/'.$m->unique_id] = $m;
            }
            if (!count($lots)) {
                print("ERROR : lot non trouvé ".implode(':', array_values($arguments))."\n");
                return;
            }
            if (count($lots) > 1) {
                print("ERROR : plusieurs lots trouvés ".implode(':', array_values($arguments))."\n");
                return;
            }
            $lot = current($lots);
            $lot = LotsClient::getInstance()->findByUniqueId($m->declarant_identifiant, $m->unique_id, $m->document_ordre);
            $lot->remove('date_degustation_voulue');
            if (strpos($lot->id_document, 'COURRIER') !== false) {
                print("LOG : A déjà un courrier ".implode(':', array_values($arguments))."\n");
                return;
            }
            $c = CourrierClient::getInstance()->createDoc($lot->declarant_identifiant, CourrierClient::COURRIER_IMPORT, $lot, $arguments['date']);
            echo $c->_id."\n";
            $c->storeAttachment($arguments['pdf'], "application/pdf", basename($arguments['pdf']));
            $c->save();
            print("LOG : courrier ".$c->_id." created\n");

        }
        if (!$c) {
            echo "ERROR: echantillon pas trouvé dans le CSV\n";
        }
    }

    protected function alias($produit) {

        return $produit;
    }

}
