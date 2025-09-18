<?php

class ImportParcellaireIrrigableCVPTask extends sfBaseTask
{

    const CSV_CAMPAGNE = 0;
    const CSV_IDOP = 1;
    const CSV_CVI = 2;
    const CSV_SIRET = 3;
    const CSV_OPERATEUR = 4;
    const CSV_CODE_CEPAGE = 5;
    const CSV_CODE_SIQO = 6;
    const CSV_IDU = 7;
    const CSV_CODE_COMMUNE = 8;
    const CSV_COMMUNE = 9;
    const CSV_SECTION = 10;
    const CSV_NO_PARCELLE = 11;
    const CSV_CEPAGE = 12;
    const CSV_ANNEE_DE_PLANTATION = 13;
    const CSV_SUPERFICIE = 14;
    const CSV_MECANISMES_DIRRIGATION = 15;
    const CSV_SOURCE = 16;
    const CSV_CAVE_APPORT = 17;
    const CSV_OBS = 18;
    const CSV_REFERENCE_CADASTRALE = 19;
    const CSV_DATE_MAJ = 20;
    const CSV_DATE_IRRIGATION = 21;
    const CSV_IDOP2 = 22;
    const CSV_TTT_SIRET = 23;


    protected $currentEtablissementKey = null;
    protected $currentEtablissement = null;
    protected $cepages;
    protected $irrigable = null;
    protected $manquant = null;
    protected $meca2materiel = [
        'Aspersion' => 'Canon',
        'Canon' => 'Canon',
        'Goutte à goutte' => 'Goutte à goutte',
        'Goutte à goutte aérien' => 'Goutte à goutte',
        'Goutte à goutte enterré' => 'Goutte à goutte',
        'Goutte à goutte hors sol' => 'Goutte à goutte',
        'Par Gravité' => 'Système enterré',
    ];
    protected $source2ressources = [
        'Canal de Provence' => 'Canal de Provence',
        'Canal de Provence ' => 'Canal de Provence',
        'Canal de Provence et Forage' => 'Canal de Provence',
        'Canal des arrosants' => 'Retenue collinaire',
        'Canal irrigant' => 'Retenue collinaire',
        'Forage' => 'Forage',
        'Prise d\'eau sur l\'Argens' => 'Retenue collinaire',
        'Réseau Communal' => 'Réseau urbain',
    ];

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('dryrun', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', false),
            new sfCommandOption('debug', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', false),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaire-irrigable-cvp';
        $this->briefDescription = 'Import des affectations parcellaire';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->real_save = !$options['dryrun'];

        $this->cpt = 0;
        $this->cpt_warning = 0;
        $this->cpt_error = 0;
        $pkey = null;
        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            if (strpos($data[self::CSV_CAMPAGNE], '20') === false) {
                continue;
            }

            if(!$this->currentEtablissementKey || $this->currentEtablissementKey != $data[self::CSV_CVI]) {
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
            $p->campagne_plantation = $data[self::CSV_ANNEE_DE_PLANTATION];
            $p->section = $data[self::CSV_SECTION];
            $p->numero_parcelle = intval($data[self::CSV_NO_PARCELLE]);
            $p->prefix = null;
            $p->superficie = floatval(str_replace(',', '.', $data[self::CSV_SUPERFICIE]));
            $p->superficie_cadastrale = floatval(str_replace(',', '.', $data[self::CSV_SUPERFICIE]));
            $p->cepage = str_replace(['é', 'è'], 'E', strtoupper($data[self::CSV_CEPAGE]));
            $p->lieu = $data[self::CSV_OBS] ?? null;
            $p->commune = $data[self::CSV_COMMUNE];
            $this->cpt++;
            $pt = ParcellaireClient::getInstance()->findParcelle($parcellaire, $p, 1, true);
            if ($pt) {
                try {
                    $pt = $pt->getParcelleAffectee();
                }catch(sfException $e) {
                    $this->cpt_warning++;
                    if ($options['debug']) {
                        echo "WARNING: Parcelle pas affectée à l'appellation\n";
                        echo " -- ".$etablissement->cvi.' - - '.$p->section.' '.$p->numero_parcelle.' - '.$p->cepage.'/'.$p->campagne_plantation.' = '.$p->superficie."\n";
                        echo " => ".$etablissement->cvi." - - PAS de l'appellation\n";
                    }
                }
            }
            if ( !$pt ) {
                $this->cpt_error++;
                if ($options['debug']) {
                    echo "ERROR: Parcelle pas trouvée :\n";
                    echo " -- ".$etablissement->cvi.' - - '.$p->section.' '.$p->numero_parcelle.' - '.$p->cepage.'/'.$p->campagne_plantation.' = '.$p->superficie."\n";
                    echo " => ".$etablissement->cvi." - - NON TROUVE\n";
                }
            } elseif ( ($p->section != $pt->section) || ($p->numero_parcelle != $pt->numero_parcelle) || ($p->cepage != $pt->cepage) || ($p->campagne_plantation != $pt->campagne_plantation) || ($p->superficie != $pt->superficie) ) {
                $this->cpt_warning++;
                if ($options['debug']) {
                    echo "WARNING: Parcelle pas totalement exacte :\n";
                    echo " -- ".$etablissement->cvi.' - - '.$p->section.' '.$p->numero_parcelle.' - '.$p->cepage.'/'.$p->campagne_plantation.' = '.$p->superficie."\n";
                    echo " => ".$etablissement->cvi.' - - '.$pt->section.' '.$pt->numero_parcelle.' - '.$pt->cepage.'/'.$pt->campagne_plantation.' = '.$pt->superficie."\n";
                }
            }
            if (!$this->irrigable || ($this->irrigable->identifiant != $etablissement->identifiant) || ($this->irrigable->campagne != $data[self::CSV_CAMPAGNE]) ) {
                if ($this->irrigable) {
                    $this->saving();
                    $this->cpt = 0;
                    $this->cpt_warning = 0;
                    $this->cpt_error = 0;
                }
                $this->irrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, substr($data[self::CSV_CAMPAGNE], 0, 4));
                $this->irrigable->observations = str_replace("Import Coteaux Varois", "", $this->irrigable->observations);
                $this->irrigable->observations .= "Import Coteaux Varois";
            }
            if ($pt && $pt->getProduitHash()) {
                $produit_hash = str_replace('/declaration/', '', $pt->getProduitHash());
                if (strpos($produit_hash, 'CVP') === false) {
                    echo "WANING: Parcelle non CVP ($produit_hash - ".$this->irrigable->_id.")\n";
                    continue;
                }
            }
            if ($pt) {
                $pkey = $pt->getKey();
                $pkeyid = 0;
            } else {
                if (!$pkey) {
                    continue;
                }
                $pkey = explode('-', $pkey)[0].'-X'.$pkeyid++;
                $pt = ParcellaireParcelle::freeInstance($parcellaire);
                $pt->idu = ($data[self::CSV_IDU]) ? $data[self::CSV_IDU] : $data[self::CSV_CODE_COMMUNE]."0000".$p->section.sprintf("%04d",$p->numero_parcelle);
                $pt->campagne_plantation = $p->campagne_plantation;
                $pt->section = $p->section;
                $pt->numero_parcelle = $p->numero_parcelle;
                $pt->prefix = $p->prefix;
                $pt->superficie = $p->superficie;
                $pt->superficie_cadastrale = $p->superficie_cadastrale;
                $pt->cepage = $p->cepage;
                $pt->commune = $p->commune;
                $pt->lieu = $p->lieu;
                $pt->parcelle_id = $pkey;
            }

            $item = $this->irrigable->declaration->add($produit_hash);
            $pi = $item->detail->add($pkey);
            ParcellaireClient::CopyParcelle($pi, $pt);
            $pi->materiel = $data[self::CSV_MECANISMES_DIRRIGATION];
            $pi->ressource = $data[self::CSV_SOURCE];
            $pi->superficie = $p->superficie;
            $pi->active = 1;
        }

        $this->saving();
    }

    private function saving() {
        if ($this->irrigable) {
            $this->irrigable->validate($this->irrigable->getPeriode()."-07-15");
            if ($this->real_save) {
                $this->irrigable->save();
                echo "LOG: ".$this->irrigable->_id." saved (".$this->currentEtablissementKey." - $this->cpt dont warning:$this->cpt_warning error:$this->cpt_error)\n";
            }else{
                echo "LOG: ".$this->irrigable->_id." would be saved (".$this->currentEtablissementKey." - $this->cpt dont warning:$this->cpt_warning error:$this->cpt_error)\n";
            }
        }
    }

    public function findEtablissement($data) {
        $etablissement = null;
        if($this->currentEtablissementKey == $data[self::CSV_CVI]) {
            $etablissement = $this->currentEtablissement;
        }

        if(!$etablissement) {
            $etablissement = EtablissementClient::getInstance()->findByCvi(EtablissementClient::repairCVI($data[self::CSV_CVI]));
        }
        if (!$etablissement && $data[self::CSV_OPERATEUR]) {
            $etablissement = EtablissementClient::getInstance()->findByRaisonSociale($data[self::CSV_OPERATEUR]);
        }
        if (!$etablissement && count(explode(" ", $data[self::CSV_OPERATEUR])) == 2) {
            $etablissement = EtablissementClient::getInstance()->findByRaisonSociale(explode(" ", $data[self::CSV_OPERATEUR])[1]." ".explode(" ", $data[self::CSV_OPERATEUR])[0]);
        }
        if(!$etablissement) {

            return null;
        }

        $this->currentEtablissementKey = $data[self::CSV_CVI];
        $this->currentEtablissement = $etablissement;

        return $etablissement;
    }
}
