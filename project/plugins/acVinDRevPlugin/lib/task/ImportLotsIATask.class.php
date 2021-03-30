<?php

class ImportLotsIATask extends sfBaseTask
{
  const CSV_NUM_DOSSIER = 0;
  const CSV_NUM_LOT_ODG = 1;
  const CSV_CVI = 2;
  const CSV_RAISON_SOCIALE = 3;
  const CSV_NOM = 4;
  const CSV_ADRESSE_1 = 5;
  const CSV_ADRESSE_2 = 6;
  const CSV_CODE_POSTAL = 7;
  const CSV_VILLE = 8;
  const CSV_FAX = 9;
  const CSV_TELEPHONE = 10;
  const CSV_FAMILLE = 11;
  const CSV_NUM_LOT_OPERATEUR = 12;
  const CSV_TYPE = 13;
  const CSV_APPELLATION = 14;
  const CSV_COULEUR = 15;
  const CSV_CEPAGE_1 = 16;
  const CSV_POURCENT_CEPAGE_1 = 17;
  const CSV_CEPAGE_2 = 18;
  const CSV_POURCENT_CEPAGE_2 = 19;
  const CSV_CEPAGE_3 = 20;
  const CSV_POURCENT_CEPAGE_3 = 21;
  const CSV_MILLESIME = 22;
  const CSV_CAMPAGNE = 23;
  const CSV_VOLUME_RESIDUEL = 24;
  const CSV_VOLUME_INITIAL = 25;
  const CSV_DESTINATION = 26;
  const CSV_TRANSACTION_DATE = 27;
  const CSV_CONF = 28;
  const CSV_PRELEVE = 29;
  const CSV_STATUT = 30;
  const CSV_DATE_COMMISSION = 31;
  const CSV_DATE_VALIDATION = 32;

  const CSV_NOM_SITE = 33;
  const CSV_ADRESSE_1_SITE = 34;
  const CSV_ADRESSE_2_SITE = 35;
  const CSV_CODE_POSTAL_SITE = 36;
  const CSV_VILLE_SITE = 37;

  const CSV_EMAIL = 38;

  const TYPE_REVENDIQUE = 'R';
  const TYPE_CONDITIONNEMENT = 'B';
  const TYPE_TRANSACTION_VRAC_FRANCE = 'VF';
  const TYPE_TRANSACTION_VRAC_HORS_FRANCE = 'VHF';
  const TYPE_CHANGEMENT_DE_DENOMINATION_NEGOCIANT = 'BN';

  public static $typeAllowed = array (
      self::TYPE_REVENDIQUE,
      self::TYPE_CONDITIONNEMENT,
      self::TYPE_TRANSACTION_VRAC_FRANCE,
      self::TYPE_TRANSACTION_VRAC_HORS_FRANCE,
      self::TYPE_CHANGEMENT_DE_DENOMINATION_NEGOCIANT,
  );

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $etablissements;
  protected $etablissementsCache = array();
  protected $produits;
  protected $cepages;

  public static $correspondancesCepages = array(
    "Cabernet sauvignon N" => "CAB-SAUV-N",
    "Chardonnay B" => "CHARDONN.B",
    "Cinsault N" => "CINSAUT N",
    "Clairette B" => "CLAIRET.B",
    "Mourvèdre N" => "MOURVED.N",
    "Muscat à petits grains B" => "MUS.PT.G.B",
    "Muscat à petits grains Rs" => "MUS.P.G.RS",
    "Muscat d'Hambourg N" => "MUS.HAMB.N",
    "Muscat PG B" => "MUS.PT.G.B",
    "Nielluccio N" => "NIELLUC.N",
    "Sauvignon B" => "SAUVIGN.B",
    "Savagnin Blanc B" => "SAVAGN.B",
    "Vermentino B" => "VERMENT.B"
  );
    public static $correspondancesStatuts = array(
      "Conforme" => Lot::STATUT_CONFORME,
      "Déclassé" => Lot::STATUT_DECLASSE,
      "Non Conforme" => Lot::STATUT_NONCONFORME,
      "Prélevé A" => Lot::STATUT_PRELEVE, //Prélevé Anonimisé
      "Prélevé NA" => Lot::STATUT_PRELEVE,//Prélevé Non Anonimisé
      "Prévu" => Lot::STATUT_ATTENTE_PRELEVEMENT,
      "Revendiqué C" => Lot::STATUT_REVENDIQUE,
      "Revendiqué NC" => Lot::STATUT_NONCONFORME
    );

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
        $this->name = 'lots-ia';
        $this->briefDescription = 'Import des lots (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $this->etablissements = EtablissementAllView::getInstance()->getAll();
        $document = null;
        $ligne = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }

            $type = trim($data[self::CSV_TYPE]);
            if (!in_array($type, self::$typeAllowed)) {
                echo "SQUEEZE;lot non issu de la revendication, type : ".$type.";pas d'import;$line\n";
                continue;
            }

            $etablissement = $this->identifyEtablissement($data);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $produitKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_APPELLATION])." ".trim($data[self::CSV_COULEUR])));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_APPELLATION].' '.$data[self::CSV_COULEUR].";pas d'import;$line\n";
              continue;
            }
            $produit = $this->produits[$produitKey];
            $cepages = array();
            $volume = str_replace(',','.',trim($data[self::CSV_VOLUME_INITIAL])) * 1;
            if (trim($data[self::CSV_CEPAGE_1])) {
              $cep1 = $this->identifyCepage($data[self::CSV_CEPAGE_1]);
              if (!$cep1) {
                echo "WARNING;cepage_1 non trouvé ".$data[self::CSV_CEPAGE_1].";$line\n";
              } else {
                  $pourcentage = str_replace(',', '.', trim($data[self::CSV_POURCENT_CEPAGE_1])) * 1 / 100;
                  $cepages[$cep1] = ($pourcentage > 0)? round($volume * $pourcentage, 2) : $volume;
              }
            }
            if (trim($data[self::CSV_CEPAGE_2])) {
              $cep2 = $this->identifyCepage($data[self::CSV_CEPAGE_2]);
              if (!$cep2) {
                echo "WARNING;cepage_2 non trouvé ".$data[self::CSV_CEPAGE_2].";$line\n";
              } else {
                  $pourcentage = str_replace(',', '.', trim($data[self::CSV_POURCENT_CEPAGE_2])) * 1 / 100;
                  if ($pourcentage > 0) {
                      $cepages[$cep2] = round($volume * $pourcentage, 2);
                  }

              }
            }
            if (trim($data[self::CSV_CEPAGE_3])) {
              $cep3 = $this->identifyCepage($data[self::CSV_CEPAGE_3]);
              if (!$cep3) {
                echo "WARNING;cepage_3 non trouvé ".$data[self::CSV_CEPAGE_3].";$line\n";
              } else {
                  $pourcentage = str_replace(',', '.', trim($data[self::CSV_POURCENT_CEPAGE_3])) * 1 / 100;
                  if ($pourcentage > 0) {
                      $cepages[$cep3] = round($volume * $pourcentage, 2);
                  }
              }
            }
            $periode = preg_replace('/\/.*/', '', trim($data[self::CSV_CAMPAGNE]));
            if($periode < 2019) {
                continue;
            }
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : $periode;
            $numeroDossier = sprintf("%05d", trim($data[self::CSV_NUM_DOSSIER]));
            $numeroLot = sprintf("%05d", trim($data[self::CSV_NUM_LOT_ODG]));
            $numero = trim($data[self::CSV_NUM_LOT_OPERATEUR]);
            $destinationDate = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_TRANSACTION_DATE]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $date = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_VALIDATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

            $logementAddress = (isset($data[self::CSV_NOM_SITE]) && $data[self::CSV_NOM_SITE])? trim($data[self::CSV_NOM_SITE]) : "";
            $logementAddress .= (isset($data[self::CSV_ADRESSE_1_SITE]) && $data[self::CSV_ADRESSE_1_SITE])? " ".trim($data[self::CSV_ADRESSE_1_SITE]) : "";
            $logementAddress .= (isset($data[self::CSV_ADRESSE_2_SITE]) && $data[self::CSV_ADRESSE_2_SITE])? " ".trim($data[self::CSV_ADRESSE_2_SITE]) : "";
            $logementAddress = ($logementAddress)? $logementAddress : null;

            $logementCP = (isset($data[self::CSV_CODE_POSTAL_SITE]) && $data[self::CSV_CODE_POSTAL_SITE])? trim($data[self::CSV_CODE_POSTAL_SITE]) : null;
            $logementCommune = (isset($data[self::CSV_VILLE_SITE]) && $data[self::CSV_VILLE_SITE])? trim($data[self::CSV_VILLE_SITE]) : null;

            $prelevable = (strtolower(trim($data[self::CSV_PRELEVE])) == 'oui');

           $previousdoc = $document;

           $document = $this->getDocument($type, $previousdoc, $etablissement, $periode, $date, $numeroDossier);

           $needModif = $this->needModificatrice($previousdoc, $etablissement, $periode, $logementAddress, $logementCP, $logementCommune);

            if($needModif){
                try {
                    $previousdoc->save();
                } catch(Exception $e) {
                    echo "ERROR;".$e->getMessage().";".$previousdoc->_id.";".$line."\n";
                }
                $date = $document->validation;
                $document = $previousdoc->generateModificative();
                $document->constructId();
                $document->storeDeclarant();
                $document->validation = $date;
                $document->validation_odg = $date;
                $document->save();
                echo "Nouvelle modificatrice $previousdoc->_id => $document->_id\n";
            }

            if($previousdoc && $document->_id != $previousdoc->_id && !$needModif) {
                try {
                    $previousdoc->save();
                } catch(Exception $e) {
                    echo "ERROR;".$e->getMessage().";".$previousdoc->_id.";".$line."\n";
                }
            }

            $lot = $document->addLot();

            $this->storeAddresseLogt($document, $lot, $logementAddress, $logementCP, $logementCommune);
            $lot->produit_hash = $produit->getHash();
            $lot->produit_libelle = $produit->getLibelleFormat();
            $lot->cepages = $cepages;
            $lot->millesime = $millesime;
            $lot->numero_dossier = $numeroDossier;
            $lot->numero_archive = $numeroLot;
            $lot->numero_logement_operateur = $numero;
            $lot->volume = $volume;
            $lot->destination_type = null;
            $lot->elevage = false;

            if (!$data[self::CSV_DESTINATION]) {
                $data[self::CSV_DESTINATION] = $data[self::CSV_TYPE];
            }

            if(preg_match('/VF/', $data[self::CSV_DESTINATION])) {
                $lot->destination_type .= DRevClient::LOT_DESTINATION_VRAC_FRANCE."_";
            }
            if(preg_match('/VHF/', $data[self::CSV_DESTINATION])) {
                $lot->destination_type .= DRevClient::LOT_DESTINATION_VRAC_EXPORT."_";
            }
            if(preg_match('/B/', $data[self::CSV_DESTINATION])) {
                $lot->destination_type .= DRevClient::LOT_DESTINATION_CONDITIONNEMENT."_";
            }
            if($lot->destination_type) {
                $lot->destination_type = preg_replace('/_$/', "", $lot->destination_type);
            }
            if(preg_match('/E/', $data[self::CSV_DESTINATION])) {
                $lot->elevage = true;
            }
            if(!$destinationDate) {
                $destinationDate = $date;
            }
            $lot->destination_date = $destinationDate;
            $lot->affectable = $prelevable;
            $lot->date = $date;
            $lot->specificite = null;

            if ($data[self::CSV_TYPE] == self::TYPE_CONDITIONNEMENT) {
                $lot->centilisation = "donnée non présente dans l'import";
            }
            if ($data[self::CSV_TYPE] == self::TYPE_TRANSACTION_VRAC_FRANCE) {
                $lot->pays = "France";
            }
            if ($data[self::CSV_TYPE] == self::TYPE_TRANSACTION_VRAC_HORS_FRANCE) {
                $lot->pays = "Export : données du pays non importée";
            }

            $deleted = array();
            foreach($document->lots as $k => $l) {
              if ($lot->getUnicityKey() == $l->getUnicityKey() && $lot->getKey() != $k) {
                $deleted[] = $l;
              }
            }
            foreach($deleted as $d) {
              $d->delete();
            }

            $lots = array_values($document->lots->toArray(true, false));
            $document->remove('lots');
            $document->add('lots', $lots);
        }
        if($document) {
            $document->save();
        }
    }

    protected function clearProduitKey($key) {
      $key = str_replace('PAYS-DES-', '', $key);
      $key = str_replace('VAR-VAR-', 'VAR-', $key);
      $key = str_replace('IGP-BDR-', 'BOUCHES-DU-RHONE-', $key);
      $key = str_replace('NORD-', '', $key);
      $key = preg_replace('/^LOIRE-ATLANTIQUE/', 'VAL-DE-LOIRE-LOIRE-ATLANTIQUE', $key);
      $key = preg_replace('/^INDRE-ET-LOIRE/', 'VAL-DE-LOIRE-INDRE-ET-LOIRE', $key);
      $key = preg_replace('/^MAINE-ET-LOIRE/', 'VAL-DE-LOIRE-MAINE-ET-LOIRE', $key);
      $key = preg_replace('/^LOIR-ET-CHER/', 'VAL-DE-LOIRE-LOIR-ET-CHER', $key);
      $key = preg_replace('/^CHER/', 'VAL-DE-LOIRE-CHER', $key);
      $key = preg_replace('/^SARTHE/', 'VAL-DE-LOIRE-SARTHE', $key);
      $key = preg_replace('/^VENDEE/', 'VAL-DE-LOIRE-VENDEE', $key);
      $key = preg_replace('/^VIENNE/', 'VAL-DE-LOIRE-VIENNE', $key);
      $key = preg_replace('/^ALLIER/', 'VAL-DE-LOIRE-ALLIER', $key);
      return $key;
    }


    protected function needModificatrice($previousdoc, $etablissement, $periode , $addr_logt, $cp_logt, $commune_logt){
        if(!$previousdoc){
            return false;
        }
        if($previousdoc->type != "DRev"){
            return false;
        }

        if($previousdoc->identifiant != $etablissement->identifiant){
            return false;
        }
        if($previousdoc->periode != $periode){
            return false;
        }

        $adresse = $this->constructAddr($addr_logt,$cp_logt,$commune_logt);
        if($previousdoc->exist('chais') && ($c = $previousdoc->chais)){
            $chaiStr = $this->constructAddr($c->adresse,$c->code_postal, $c->commune);
            if($chaiStr != $adresse){
                return true;
            }
        }
        return false;
    }

    protected function storeAddresseLogt($document, $lot, $addr_logt,$cp_logt,$commune_logt){
        $lot->adresse_logement = $this->constructAddr($addr_logt,$cp_logt,$commune_logt);
        $chai = $document->add('chais');
        $chai->adresse = $addr_logt;
        $chai->code_postal = $cp_logt;
        $chai->commune = $commune_logt;
    }

    protected function constructAddr($addr,$cp,$commune){
        $ret = ($addr)? $addr : "";
        $ret .= ($cp)? " ".$cp : "";
        $ret .= ($commune)? " ".$commune : "";
        if(!$ret){
            return null;
        }
        return $ret;
    }

    protected function identifyCepage($key) {
      $key = trim($key);
      if (isset($this->cepages[KeyInflector::slugify($key)])) {
        return $this->cepages[KeyInflector::slugify($key)];
      }
      $correspondances = self::$correspondancesCepages;
      return (isset($correspondances[$key]))? $correspondances[$key] : strtoupper(str_replace(' ', '.', $key));
    }

    protected function identifyEtablissement($data) {

        for ($i = strlen($data[self::CSV_CVI]) ; $i < 10 ; $i++) {
            $data[self::CSV_CVI] = $data[self::CSV_CVI].'0';
        }

        $key = KeyInflector::slugify(str_replace(" ", "", $data[self::CSV_CVI].$data[self::CSV_RAISON_SOCIALE].$data[self::CSV_NOM]));

        if(isset($this->etablissementsCache[$key])) {
            return $this->etablissementsCache[$key];
        }

        foreach ($this->etablissements as $etab) {
            if (isset($data[self::CSV_CVI]) && trim($data[self::CSV_CVI]) && $etab->key[EtablissementAllView::KEY_CVI] == trim($data[self::CSV_CVI]) && isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {

                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
        }
        foreach ($this->etablissements as $etab) {
            if (isset($data[self::CSV_CVI]) && trim($data[self::CSV_CVI]) && $etab->key[EtablissementAllView::KEY_CVI] == trim($data[self::CSV_CVI])) {

                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
        }
        return null;
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

    public function getDocument($type, $previousdoc, $etablissement, $periode, $date, $numeroDossier) {
        if ($type == self::TYPE_REVENDIQUE) {
            return $this->getDocumentDRev($previousdoc, $etablissement, $periode, $date, $numeroDossier);
        }
        if ($type == self::TYPE_CONDITIONNEMENT) {
            return $this->getDocumentConditionnement($previousdoc, $etablissement, $periode, $date, $numeroDossier);
        }
        if ($type == self::TYPE_CHANGEMENT_DE_DENOMINATION_NEGOCIANT) {
            return $this->getDocumentConditionnement($previousdoc, $etablissement, $periode, $date, $numeroDossier);
        }
        if ($type == self::TYPE_TRANSACTION_VRAC_FRANCE) {
            return $this->getDocumentTransaction($previousdoc, $etablissement, $periode, $date, $numeroDossier);
        }
        if ($type == self::TYPE_TRANSACTION_VRAC_HORS_FRANCE) {
            return $this->getDocumentTransaction($previousdoc, $etablissement, $periode, $date, $numeroDossier);
        }
    }

    public function getDocumentDRev($previousdoc, $etablissement, $campagne, $date, $numeroDossier) {
        $drev = $previousdoc;

        $newDrev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne, false, false);
        $newDrev->constructId();
        $newDrev->storeDeclarant();
        $newDrev->validation = $date;
        $newDrev->validation_odg = $date;
        $newDrev->numero_archive = $numeroDossier;
        $newDrev->add('date_degustation_voulue', $date);
        if(!$drev || $newDrev->_id != $drev->_id) {
          $drev = DRevClient::getInstance()->find($newDrev->_id, acCouchdbClient::HYDRATE_DOCUMENT);
          if($drev){
               $drev = $drev->getMaster();
          }
        }

        if(!$drev) {
            $drev = $newDrev;
        }
        return $drev;
    }

    public function getDocumentConditionnement($previousdoc, $etablissement, $campagne, $date, $numeroDossier) {
        $cond = $previousdoc;

        $newCond = ConditionnementClient::getInstance()->findByIdentifiantAndDateOrCreateIt($etablissement->identifiant, $campagne, $date);
        $newCond->constructId();
        $newCond->storeDeclarant();
        $newCond->validation = $date;
        $newCond->validation_odg = $date;
        $newCond->numero_archive = $numeroDossier;
        $newCond->add('date_degustation_voulue', $date);
        if(!$cond || $newCond->_id != $cond->_id) {
          $cond = ConditionnementClient::getInstance()->find($newCond->_id, acCouchdbClient::HYDRATE_DOCUMENT);
        }
        if(!$cond) {
            $cond = $newCond;
        }
        return $cond;
    }

    public function getDocumentTransaction($previousdoc, $etablissement, $campagne, $date, $numeroDossier) {
        $trans = $previousdoc;

        $newTrans = TransactionClient::getInstance()->findByIdentifiantAndDateOrCreateIt($etablissement->identifiant, $campagne, $date);
        $newTrans->constructId();
        $newTrans->storeDeclarant();
        $newTrans->validation = $date;
        $newTrans->validation_odg = $date;
        $newTrans->numero_archive = $numeroDossier;
        $newTrans->add('date_degustation_voulue', $date);
        if(!$trans || $newTrans->_id != $trans->_id) {
          $trans = TransactionClient::getInstance()->find($newTrans->_id, acCouchdbClient::HYDRATE_DOCUMENT);
        }
        if(!$trans) {
            $trans = $newTrans;
        }
        return $trans;
    }


}
