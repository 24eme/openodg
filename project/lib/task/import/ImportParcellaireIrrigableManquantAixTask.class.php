<?php

class ImportParcellaireIrrigableManquantAixTask extends sfBaseTask
{

    const CSV_CVI = 1;
    const CSV_RAISON_SOCIALE = 2;
    const CSV_CAVE_COOP = 3;
    const CSV_PARCELLES_AFFECTEES_RECOLTE_2024 = 4;
    const CSV_PARCELLES_IRRIGABLES = 5;
    const CSV_COMMUNE = 6;
    const CSV_REFERENCE_CADASTRALE = 7;
    const CSV_SURFACE_CADASTRALE = 8;
    const CSV_STATUT = 9;
    const CSV_CEPAGE = 10;
    const CSV_SURFACE_DU_CEPAGE = 11;
    const CSV_CAMPAGNE_PLANTATION = 12;
    const CSV_DENSITE_DE_PLANTATION  = 13;
    const CSV_MODE_DE_CONDUITE = 14;
    const CSV_POURCENTAGE_MANQUANT = 15;



    const DATE_VALIDATION = "04-15";


    protected $currentEtablissementKey = null;
    protected $currentEtablissement = null;
    protected $periode = null;
    protected $materiels;
    protected $ressources;
    protected $cepages;
    protected $irrigable = null;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv"),
            new sfCommandArgument('periode', sfCommandArgument::REQUIRED, "Période")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaire-irrigable-manquant-aix';
        $this->briefDescription = 'Import des affectations parcellaire';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->periode = $arguments['periode'];
        $this->materiels = sfConfig::get('app_parcellaire_irrigable_materiels');
        $this->ressources = sfConfig::get('app_parcellaire_irrigable_ressources');

        $this->cepages = ConfigurationClient::getCurrent()->getCepagesAutorises();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            if($this->currentEtablissementKey != $data[self::CSV_CVI].$data[self::CSV_RAISON_SOCIALE]) {
                $parcellaire = null;
                $etablissement = $this->findEtablissement($data);
                if(!$etablissement) {
                    $this->currentEtablissementKey = '';
                    echo "Error: Etablissement ".$data[self::CSV_CVI]." non trouvé;".implode(";", $data)."\n";
                    continue;
                }
            }
            if (!$parcellaire) {
                $parcellaire = ParcellaireClient::getInstance()->getLast($etablissement->identifiant);
                if (!$parcellaire) {
                    echo "Error: pas de parcellaire pour ".$data[self::CSV_CVI]."/".$etablissement->_id."\n";
                    continue;
                }
            }

            $p = new stdClass();
            $p->campagne_plantation = $data[self::CSV_CAMPAGNE_PLANTATION];
            $ref = explode(' ', $data[self::CSV_REFERENCE_CADASTRALE]);
            $p->section = $ref[0];
            $p->numero_parcelle = intval($ref[1]);
            $p->prefix = null;
            $p->superficie = str_replace(',', '.', $data[self::CSV_SURFACE_DU_CEPAGE]);
            $p->superficie_cadastrale = str_replace(',', '.', $data[self::CSV_SURFACE_CADASTRALE]);
            $p->cepage = str_replace(['é', 'è'], 'E', strtoupper($data[self::CSV_CEPAGE]));
            $lieu_commune = explode(' / ', strtoupper($data[self::CSV_COMMUNE]));
            $p->commune = end($lieu_commune);
            $p->lieu = $lieu_commune[0];
            if ($p->commune == $p->lieu) {
                $p->lieu = null;
            }
            $pt = ParcellaireClient::getInstance()->findParcelle($parcellaire, $p, 1, true);
            if ($pt) {
                try {
                    $pt = $pt->getParcelleAffectee();
                }catch(sfException $e) {
                    echo "WARNING: Parcelle pas affectée à l'appellation\n";
                }
            }
            if ( !$pt ) {
                echo "ERROR: Parcelle pas trouvée :";
                echo " -- ".$etablissement->cvi.' - - '.$p->section.' '.$p->numero_parcelle.' - '.$p->cepage.'/'.$p->campagne_plantation.' = '.$p->superficie."\n";
                echo " => ".$etablissement->cvi." - - NON TROUVE\n";
                continue;
            } elseif ( ($p->section != $pt->section) || ($p->numero_parcelle != $pt->numero_parcelle) || ($p->cepage != $pt->cepage) || ($p->campagne_plantation != $pt->campagne_plantation) || ($p->superficie != $pt->superficie) ) {
                echo "WARNING: Parcelle pas totalement exacte :";
                echo " -- ".$etablissement->cvi.' - - '.$p->section.' '.$p->numero_parcelle.' - '.$p->cepage.'/'.$p->campagne_plantation.' = '.$p->superficie."\n";
                echo " => ".$etablissement->cvi.' - - '.$pt->section.' '.$pt->numero_parcelle.' - '.$pt->cepage.'/'.$pt->campagne_plantation.' = '.$pt->superficie."\n";
            }
            if (!$this->irrigable || $this->irrigable->identifiant != $etablissement->identifiant) {
                if ($this->irrigable) {
                    $this->irrigable->validate();
                    if (!isset($_ENV['DRY_RUN'])) {
                        $this->irrigable->save();
                        echo "LOG: ".$this->irrigable->_id." saved\n";
                    }else{
                        echo "LOG: ".$this->irrigable->_id." would be saved\n";
                    }
                }
                $this->irrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, $this->periode);
                $this->irrigable->observations .= " - Import Aix";
            }
            if ($pt->getProduitHash()) {
                $produit_hash = str_replace('/declaration/', '', $pt->getProduitHash());
            }
            $item = $this->irrigable->declaration->add($produit_hash);
            $pi = $item->detail->add($pt->getKey());
            ParcellaireClient::CopyParcelle($pi, $pt, false);
            $pi->materiel = "Goutte à goutte";
            $pi->ressource = "Canal de Provence";
            $pi->active = 1;
        }
    }

    public function findEtablissement($data) {
        $etablissement = null;
        if($this->currentEtablissementKey == $data[self::CSV_CVI].$data[self::CSV_RAISON_SOCIALE]) {
            $etablissement = $this->currentEtablissement;
        }

        if(!$etablissement) {
            $etablissement = EtablissementClient::getInstance()->findByCvi(EtablissementClient::repairCVI($data[self::CSV_CVI]));
        }
        if (!$etablissement && $data[self::CSV_RAISON_SOCIALE]) {
            $etablissement = EtablissementClient::getInstance()->findByRaisonSociale($data[self::CSV_RAISON_SOCIALE]);
        }
        if (!$etablissement && count(explode(" ", $data[self::CSV_RAISON_SOCIALE])) == 2) {
            $etablissement = EtablissementClient::getInstance()->findByRaisonSociale(explode(" ", $data[self::CSV_RAISON_SOCIALE])[1]." ".explode(" ", $data[self::CSV_RAISON_SOCIALE])[0]);
        }
        if(!$etablissement) {

            return null;
        }

        $this->currentEtablissementKey = $data[self::CSV_CVI].$data[self::CSV_RAISON_SOCIALE];
        $this->currentEtablissement = $etablissement;

        return $etablissement;
    }
}
