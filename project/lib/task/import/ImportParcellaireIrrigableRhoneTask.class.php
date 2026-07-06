<?php

class ImportParcellaireIrrigueTask extends sfBaseTask
{

    const CSV_IDENTITE = 0;
    const CSV_OPERATEUR = 1;
    const CSV_MILLESIME = 2;
    const CSV_CVI = 3;
    const CSV_AOC = 4;
    const CSV_MECANISMES_DIRRIGATION = 5;
    const CSV_CODE_COMMUNE = 6;
    const CSV_VILLE = 7;
    const CSV_CODE_POSTAL = 8;
    const CSV_LIEU_DIT = 9;
    const CSV_SECTION = 10;
    const CSV_NO_PARCELLE = 11;
    const CSV_CEPAGE = 12;
    const CSV_SUPERFICIE_CADASTRALE = 13;
    const CSV_SUPERFICIE = 14;
    const CSV_ANNEE_DE_PLANTATION = 15;
    const CSV_ECART_RANG = 16;
    const CSV_ECART_PIED = 17;
    const CSV_FAIREVALOIR = 18;
    const CSV_DATE_SAISIE = 19;
    const CSV_CAMPAGNE = "2025";


    protected $currentEtablissementKey = null;
    protected $currentEtablissement = null;
    protected $irrigue = null;


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
        $this->name = 'parcellaire-irrigable-rhone';
        $this->briefDescription = 'Import des parcellaire irrigable rhone';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);$this->configuration->loadMultiDatabases(null, $databaseManager);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->real_save = !$options['dryrun'];

        $this->cpt = 0;
        $this->cpt_warning = 0;
        $this->cpt_error = 0;
        $pkey = null;
        $pkeyid = 0;
        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            if (strpos(self::CSV_CAMPAGNE, '20') === false) {
                continue;
            }
            if(!$this->currentEtablissementKey || $this->currentEtablissementKey != $data[self::CSV_CVI]) {
                $parcellaire = null;
                $pkeyid = 0;
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

            $communes2insee = [];
            foreach ($parcellaire->getParcelles() as $k => $p) {
                $communes2insee[$p->commune] = $p->code_commune;
                $communes2insee[KeyInflector::slugify($p->commune)] = $p->code_commune;
            }

            $p = new stdClass();
            $p->campagne_plantation = ($data[self::CSV_ANNEE_DE_PLANTATION] - 1).'-'.$data[self::CSV_ANNEE_DE_PLANTATION];
            $p->section = trim(strtoupper($data[self::CSV_SECTION]));
            $p->numero_parcelle = intval($data[self::CSV_NO_PARCELLE]);
            $p->prefix = null;
            $p->superficie = floatval(str_replace(',', '.', $data[self::CSV_SUPERFICIE]));
            $p->superficie_cadastrale = floatval(str_replace(',', '.', $data[self::CSV_SUPERFICIE]));
            $p->cepage = trim(str_replace(['é', 'è'], 'E', strtoupper($data[self::CSV_CEPAGE])));
            $p->lieu = trim(strtoupper($data[self::CSV_LIEU_DIT])) ?? null;
            $p->commune = trim(strtoupper($data[self::CSV_VILLE]));
            if (isset($communes2insee[$p->commune])) {
                $p->code_commune = $communes2insee[$p->commune];
            } elseif (isset($communes2insee[KeyInflector::slugify($p->commune)])) {
                $p->code_commune = $communes2insee[KeyInflector::slugify(str_replace(['é', 'è', 'ê', 'ë', 'É', 'È', 'Ê', 'Ë'], 'E', $p->commune))];
            }
            if ($p->code_commune) {
                $p->idu = Parcellaire::computeIDU($p->code_commune, $p->prefix, $p->section, $p->numero_parcelle);
            }
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
            if (!$this->irrigue || ($this->irrigue->identifiant != $etablissement->identifiant) || ($this->irrigue->campagne != self::CSV_CAMPAGNE) ) {
                if ($this->irrigue) {
                    $this->saving();
                    $this->cpt = 0;
                    $this->cpt_warning = 0;
                    $this->cpt_error = 0;
                }
                $this->irrigue = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, self::CSV_CAMPAGNE);
            }
            if ($pt && $pt->getProduitHash()) {
                $produit_hash = str_replace('/declaration/', '', $pt->getProduitHash());
            }
            if ($pt) {
                $pkey = $pt->getParcelleId();
            } else {
                if (!$pkey) {
                    continue;
                }
                $pt = ParcellaireParcelle::freeInstance($parcellaire);
                if (isset($p->idu) && $p->idu) {
                    $pt->idu = $p->idu;
                    $pkey = $p->idu.'-X'.$pkeyid++;
                } else {
                    $pt->idu = $data[self::CSV_CODE_COMMUNE]."0000".$p->section.sprintf("%04d",$p->numero_parcelle);
                    $pkey = explode('-', $pkey)[0].'-X'.$pkeyid++;
                }
                $pt->campagne_plantation = $p->campagne_plantation;
                $pt->section = $p->section;
                $pt->numero_parcelle = $p->numero_parcelle;
                $pt->prefix = $p->prefix;
                $pt->superficie = $p->superficie;
                $pt->cepage = $p->cepage;
                $pt->commune = $p->commune;
                $pt->code_commune = $p->code_commune;
                $pt->lieu = $p->lieu;
                $pt->parcelle_id = $pkey;
            }
            $item = $this->irrigue->declaration->add($produit_hash);
            $pi = $item->detail->add($pkey);
            $pi->superficie_parcellaire = null;
            ParcellaireClient::CopyParcelle($pi, $pt);
            if ($data[self::CSV_MECANISMES_DIRRIGATION] == 'A definir') {
                $pi->materiel = '';
            } else {
                $pi->materiel = $data[self::CSV_MECANISMES_DIRRIGATION];
            }
            $pi->superficie = $p->superficie;
            $pi->active = 1;
        }
        $this->saving();
    }

    private function saving() {
        if ($this->irrigue) {
            $this->irrigue->validate($this->irrigue->getPeriode()."-07-15");
            if ($this->real_save) {
                $this->irrigue->save();
                echo "LOG: ".$this->irrigue->_id." saved (".$this->currentEtablissementKey." - $this->cpt dont warning:$this->cpt_warning error:$this->cpt_error)\n";
            }else{
                echo "LOG: ".$this->irrigue->_id." would be saved (".$this->currentEtablissementKey." - $this->cpt dont warning:$this->cpt_warning error:$this->cpt_error)\n";
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
