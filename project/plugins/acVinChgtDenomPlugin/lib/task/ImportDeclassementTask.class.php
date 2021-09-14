<?php

class ImportDeclassementTask extends importOperateurIACsvTask
{
  const CSV_CVI = 0;
  const CSV_RAISON_SOCIALE = 1;
  const CSV_ADRESSE_1 = 2;
  const CSV_ADRESSE_2 = 3;
  const CSV_CODE_POSTAL = 4;
  const CSV_VILLE = 5;
  const CSV_TELEPHONE = 6;
  const CSV_FAX = 7;
  const CSV_EMAIL = 8;
  const CSV_NUM_DOSSIER = 9;
  const CSV_NUM_LOT_OPERATEUR = 10;
  const CSV_MILLESIME = 11;
  const CSV_APPELLATION = 12;
  const CSV_COULEUR = 13;
  const CSV_TYPE_CHANGEMENT = 14;
  const CSV_CEPAGE = 15;
  const CSV_DATE_DECLARATION = 16;
  const CSV_VOLUME_INITIAL = 17;
  const CSV_VOLUME_DECLASSE = 18;

  protected $produits;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'declassement-ia';
        $this->briefDescription = 'Import des déclassements (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $drev = null;
        $ligne = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if ( !$data || !isset($data[self::CSV_DATE_DECLARATION]) ) {
              continue;
            }


            if ($data[self::CSV_DATE_DECLARATION]) {
                if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_DECLARATION]), $m)) {
                    $dateDeclaration = $m[3].'-'.$m[2].'-'.$m[1];
                }elseif (preg_match('/^[0-9\-]+$/', trim($data[self::CSV_DATE_DECLARATION]))) {
                    $dateDeclaration = trim($data[self::CSV_DATE_DECLARATION]);
                }else{
                    echo "WARNING: wrong date format :".trim($data[self::CSV_DATE_DECLARATION])."\n";
                }
            }else{
                echo "WARNING: pas de date de Déclaration trouvée : ".$data[self::CSV_DATE_DECLARATION]."\n";
                continue;
            }

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], $data[self::CSV_CODE_POSTAL]);
            if (!$etablissement) {
                $etablissement = EtablissementClient::getInstance()->find($data[self::CSV_CVI]);
            }
            if (!$etablissement) {
               echo "ERROR;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $produitIntialKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_APPELLATION])." ".trim($data[self::CSV_COULEUR])));
            if (!isset($this->produits[$produitIntialKey])) {
              echo "ERROR;produit non trouvé ".$data[self::CSV_APPELLATION].' '.$data[self::CSV_COULEUR].";pas d'import;$line\n";
              continue;
            }
            $produitInitial = $this->produits[$produitIntialKey];


            $volumeInitial = str_replace(',','.',trim($data[self::CSV_VOLUME_INITIAL])) * 1;
            $volumeDeclasse = str_replace(',','.',trim($data[self::CSV_VOLUME_DECLASSE])) * 1;


            $numeroDossier = sprintf("%05d", trim($data[self::CSV_NUM_DOSSIER]));
            $numeroLogementOperateur = $data[self::CSV_NUM_LOT_OPERATEUR];
            $millesime = trim($data[self::CSV_MILLESIME]);

            if (preg_match('/[0-9]{4}-[0-9]{4}/', trim($data[self::CSV_NUM_DOSSIER]))) {
                $mouvementsLot = MouvementLotView::getInstance()->getMouvementsByStatutIdentifiantAndUniqueId(Lot::STATUT_MANQUEMENT_EN_ATTENTE, $etablissement->identifiant, trim($data[self::CSV_NUM_DOSSIER]));
            }

            if(!$mouvementsLot) {
                $mouvementsLot = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant,
                     array(
                            'numero_dossier' => $numeroDossier,
                            'numero_logement_operateur' => $numeroLogementOperateur,
                            'volume' => $volumeInitial,
                            'millesime' => $millesime,
                            'produit_hash' => $produitInitial->getHash(),
                            'statut' => Lot::STATUT_CHANGEABLE
                          )
                 );
            }

            if(!$mouvementsLot) {
                $mouvementsLot = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant,
                         array(
                                'numero_dossier' => $numeroDossier,
                                'numero_logement_operateur' => $numeroLogementOperateur,
                                'millesime' => $millesime,
                                'produit_hash' => $produitInitial->getHash(),
                                'statut' => Lot::STATUT_CHANGEABLE
                              )
                );
            }

            if ((count($mouvementsLot) > 1) || !count($mouvementsLot)) {
                echo "WARNING: Pas de mouvements trouvés ".$data[self::CSV_NUM_DOSSIER]."\n";
                echo "\t$line\n";
                continue;
            }
            $mouvementLot = $mouvementsLot[0];
            if($mouvementLot->volume - $volumeDeclasse < 0) {
                echo "Volume final négatif (".$mouvementLot->volume." - $volumeDeclasse)\n";
                echo "\t$line\n";
                continue;
            }

            $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, $mouvementLot, $dateDeclaration." ".sprintf("%02d", rand(0,23)).":".sprintf("%02d", rand(0,59)).":".sprintf("%02d", rand(0,59)), true);
            $chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT);
            $chgtDenom->changement_produit_hash = null;
            $chgtDenom->changement_volume = $volumeDeclasse;
            $chgtDenom->generateLots();
            $chgtDenom->validate($dateDeclaration);
            $chgtDenom->validateOdg($dateDeclaration);
            try {
                $chgtDenom->save();
            } catch (Exception $e) {
                echo "ERROR;".$chgtDenom->_id.";".$e->getMessage()."\n";
            }
        }
    }

    public function initProduitsCepages() {
      $this->produits = array();
      $this->cepages = array();
      $produits = ConfigurationClient::getInstance()->getConfiguration()->declaration->getProduits();
      foreach ($produits as $key => $produit) {
        $this->produits[KeyInflector::slugify($produit->getLibelleFormat())] = $produit;
        foreach($produit->getCepagesAutorises() as $ca) {
          $this->cepages[KeyInflector::slugify($ca)] = $ca;
        }
      }
    }
}
