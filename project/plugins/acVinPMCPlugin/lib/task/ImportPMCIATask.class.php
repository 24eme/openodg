<?php

class ImportPMCIATask extends importOperateurIACsvTask
{
  const CSV_STATUT_LOT = 0;
  const CSV_RAISON_SOCIALE = 1;
  const CSV_CVI = 2;
  const CSV_AOC = 3;
  const CSV_PRODUIT = 4;
  const CSV_VOLUME = 5;
  const CSV_MILLESIME = 6;
  const CSV_MOIS_PRESENTATION = 7;
  const CSV_DATE_DECLARATION = 8;
  const CSV_DATE_COMMISSION = 9;
  const CSV_LOGEMENT = 10;
  const CSV_NUM_LOT = 11;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $produits;
  protected $cepages;

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
        $this->name = 'pmc-ia';
        $this->briefDescription = 'Import des déclaration de première mise en circulation (via un csv)';
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
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
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
            $produit = $this->produits[$produitKey];
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : null;
            $numeroLot = null;
            if(isset($data[self::CSV_NUM_LOT])) {
                $numeroLot = trim($data[self::CSV_NUM_LOT]);
            }
            $logement = null;
            if(isset($data[self::CSV_LOGEMENT])) {
                $logement = trim($data[self::CSV_LOGEMENT]);
            }
            $volume = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;

            if(!$volume) {
                echo "WARNING;pas de volume;pas d'import;$line\n";
                continue;
            }

            $moisPresentation = $data[self::CSV_MOIS_PRESENTATION];
            $moisListe = ["janvier" => "01", "février" => "02", "mars" => "03", "avril" => "04", "mai" => "05", "juin" => "06", "juillet" => "07", "août" => "08", "septembre" => "09", "octobre" => "10", "novembre" => "11", "décembre" => "12"];
            $datePresentation = "01/".str_replace(array_keys($moisListe), array_values($moisListe), str_replace(" ", "/", $moisPresentation));
            $datePresentation = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($datePresentation), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

            $dateDeclaration = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_DECLARATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $dateCommission = (isset($data[self::CSV_DATE_COMMISSION]) && preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_COMMISSION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $campagne = ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate($dateDeclaration);
            $region = RegionConfiguration::getInstance()->getOdgRegion($produit->getHash());
            $pmc = PMCClient::getInstance()->findByIdentifiantAndDateOrCreateIt($etablissement->identifiant, $campagne, $dateDeclaration." 00:00:".sprintf("%02d", array_search($region, RegionConfiguration::getInstance()->getOdgRegions())));

            if(isset($etablissement->chais)) {
                foreach($etablissement->chais as $chai) {
                    if($chai->adresse == $pmc->declarant->adresse && $chai->commune == $pmc->declarant->commune && $chai->code_postal == $pmc->declarant->code_postal) {
                        $pmc->chais->nom = $chai->nom;
                        $pmc->chais->adresse = $chai->adresse;
                        $pmc->chais->commune = $chai->commune;
                        $pmc->chais->code_postal = $chai->code_postal;
                        $pmc->chais->secteur = $chai->secteur;
                        break;
                    }
                }
            }

            $lot = $pmc->addLot();
            $lot->produit_hash = $produit->getHash();
            $lot->produit_libelle = $produit->getLibelleFormat();
            $lot->millesime = $millesime;
            $lot->volume = $volume;
            $lot->numero_logement_operateur = trim(preg_replace('#(^/|/$)#', "", trim($logement.' / '.$numeroLot)));
            $lot->date_degustation_voulue = $datePresentation;
            $lot->date_commission = $dateCommission;
            $lot->affectable = true;

            try {
                $pmc->validate($dateDeclaration);
                $pmc->validateOdg($dateDeclaration);
            } catch(Exception $e) {
                sleep(60);
                $pmc->validate($dateDeclaration);
                $pmc->validateOdg($dateDeclaration);
            }
            $pmc->save();
        }
    }

    protected function alias($produit) {

        return $produit;
    }

}
