<?php

class ImportParcellaireAffectationVentouxTask extends sfBaseTask
{
    const CSV_CVI_DESTINATION = 1;
    const CSV_CVI = 2;
    const CSV_RAISON_SOCIALE = 3;
    const CSV_NOM_COMMUNE = 4;
    const CSV_LIEUDIT = 5;
    const CSV_SECTION = 7;
    const CSV_NUM_PARCELLE = 8;
    const CSV_SURFACE = 9;
    const CSV_ANNEE_PLANTATION = 10;
    const CSV_CEPAGE = 11;
    const CSV_DENSITE = 12;
    const CSV_POURCENTAGE_MANQUANT = 15;
    const CSV_IRRIGABLE = 16;
    const CSV_MATERIEL = 18;
    const CSV_IRRIGUE = 19;

    const DATE_VALIDATION = "04-15";


    protected $currentEtablissementKey = null;
    protected $currentEtablissement = null;
    protected $periode = null;
    protected $materiels;
    protected $ressources;
    protected $cepages;

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
        $this->name = 'parcellaireaffectation-ventoux';
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

            $etablissement = $this->findEtablissement($data);

            if(!$etablissement) {
                echo "Error: Etablissement ".$data[self::CSV_CVI]." non trouvé;".implode(";", $data)."\n";
                continue;
            }

            if(!$data[self::CSV_SURFACE]) {
                echo "Pas de superficie la parcelle n'est pas importée;".implode(";", $data)."\n";
                continue;
            }

            $parcelle = $this->createParcelle($etablissement->identifiant, $data);

            if(!$parcelle) {
                continue;
            }

            $this->createAffectation($etablissement, $parcelle, $data);
            $this->createManquant($etablissement, $parcelle, $data);
            $this->createIrrigation($etablissement, $parcelle, $data);


        }
    }

    protected function createParcelle($identifiant, $data) {
        $parcellaireTotal = ParcellaireClient::getInstance()->findOrCreate($identifiant, $this->periode.'-'.self::DATE_VALIDATION, 'IMPORT');

        $data[self::CSV_ANNEE_PLANTATION] = str_replace('/', '-', $data[self::CSV_ANNEE_PLANTATION]);
        if(preg_match('/^[0-9]{4}$/', $data[self::CSV_ANNEE_PLANTATION])) {
            $data[self::CSV_ANNEE_PLANTATION] = $data[self::CSV_ANNEE_PLANTATION].'-'.($data[self::CSV_ANNEE_PLANTATION]+1);
        }
        $data[self::CSV_SECTION] = trim($data[self::CSV_SECTION]);
        $data[self::CSV_NUM_PARCELLE] = preg_split("|[\ /]+|", trim($data[self::CSV_NUM_PARCELLE]))[0];
        $cepage = null;
        foreach($this->cepages as $c) {
            if(preg_replace('/ (b|n|blanc|rouge|noir|rge|blanche)$/', '', str_replace(["é", "è", "."], ["e", "e", ""], trim(strtolower(preg_replace("/[ ]+/", " ", $data[self::CSV_CEPAGE]))))) == preg_replace('/ (b|n|blanc|rouge|noir|rge|blanche)$/', '', str_replace(["é", "è"], ["e", "e"], trim(strtolower($c))))) {
                $cepage = $c;
            }
        }
        if(!$cepage) {
            echo "Cepage non trouvé ".$data[self::CSV_CEPAGE].";".implode(";", $data)."\n";
            $cepage = $data[self::CSV_CEPAGE];
        }

        $produitHash = '/declaration/certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT/couleurs';
        if(preg_match('/ B$/', $cepage)) {
            $produitHash .= '/blanc/cepages/DEFAUT';
        } else {
            $produitHash .= '/rouge/cepages/DEFAUT';
        }
        $produit = $parcellaireTotal->addProduit($produitHash);
        $commune = $data[self::CSV_NOM_COMMUNE];
        $code_commune = CommunesConfiguration::getInstance()->findCodeCommune($data[self::CSV_NOM_COMMUNE]);
        $prefix = null;
        $section = $data[self::CSV_SECTION];
        $numero_parcelle = $data[self::CSV_NUM_PARCELLE];
        $lieu = $data[self::CSV_LIEUDIT];
        $campagne_plantation = $data[self::CSV_ANNEE_PLANTATION];
        try {
            $idu = Parcellaire::computeIDU($code_commune, $prefix, $section, $numero_parcelle);
            $parcelle  = $parcellaireTotal->addParcelle($idu, "Ventoux", $cepage, $campagne_plantation, $commune, $lieu, $produit->getConfig()->getLibelle(), "X%02d");
        } catch (Exception $e) {
            echo $e->getMessage().";non importé;".implode(";", $data)."\n";
            return null;
        }
        $parcelle->numero_ordre = explode('-', $parcelle->parcelle_id)[1];
        $parcelle->superficie = (float)($data[self::CSV_SURFACE]);
        $parcelle->superficie_cadastrale = (float)($data[self::CSV_SURFACE]);
        $parcelle = $produit->affecteParcelle($parcelle);
        $parcellaireTotal->save();

        return $parcelle;
    }

    protected function addParcelleFromParcellaireParcelle($doc, $parcelle) {
        $parcellaireTotal = $parcelle->getDocument();
        if($doc->exist('parcellaire_origine')) {
            $doc->parcellaire_origine = $parcellaireTotal->_id;
        }
        $item = $doc->declaration->add('certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT');
        $item->libelle = "Ventoux";
        $subitem = $item->detail->add($parcelle->getKey());
        ParcellaireClient::CopyParcelle($subitem, $parcelle, false);

        return $subitem;
    }

    public function createAffectation($etablissement, $parcelle, $data) {
        $affectation = ParcellaireAffectationClient::getInstance()->findOrCreate($etablissement->identifiant, $this->periode, true);
        if($affectation->isNew()) {
            $affectation->remove('declaration');
            $affectation->add('declaration');
        }

        $affectationParcelle = $affectation->addParcelle($parcelle);

        $etablissementDestination = EtablissementClient::getInstance()->findByCvi($data[self::CSV_CVI_DESTINATION]);
        if(!$etablissementDestination) {
            $etablissementDestination = $etablissement;
        }

        $affectationParcelle->affecter((float)($data[self::CSV_SURFACE]), $etablissementDestination);

        if(!$affectation->isValidee()) {
            $affectation->validate($this->periode.'-'.self::DATE_VALIDATION);
            $affectation->validation_odg = $affectation->validation;
        }

        try {
            $affectation->save();
        } catch(Exception $e) {
            sleep(60);
            $affectation->save();
        }
    }

    public function createManquant($etablissement, $parcelle, $data) {
        $manquant = ParcellaireManquantClient::getInstance()->findOrCreate($etablissement->identifiant, $this->periode);
        if(!$manquant->isValidee()) {
            $manquant->validate($this->periode.'-'.self::DATE_VALIDATION);
            $manquant->validation_odg = $manquant->validation;
        }
        try {
            $manquant->save();
        } catch(Exception $e) {
            sleep(60);
            $manquant->save();
        }

        if(!$data[self::CSV_POURCENTAGE_MANQUANT]) {
            return;
        }

        $manquantParcelle = $this->addParcelleFromParcellaireParcelle($manquant, $parcelle);
        if(!$manquantParcelle) {
            return;
        }

        $data[self::CSV_POURCENTAGE_MANQUANT] = trim(str_replace([" %", "<"], "", $data[self::CSV_POURCENTAGE_MANQUANT]));

        if(!preg_match("/^[0-9\.]+$/", $data[self::CSV_POURCENTAGE_MANQUANT])) {
            return;
        }

        $pourcentageManquant = (float) $data[self::CSV_POURCENTAGE_MANQUANT];
        if($pourcentageManquant < 1) {
            $pourcentageManquant = $pourcentageManquant * 100;
        }
        $pourcentageManquant = round($pourcentageManquant, 2);


        $manquantParcelle->densite = (int)$data[self::CSV_DENSITE];
        $manquantParcelle->superficie = (float)($data[self::CSV_SURFACE]);
        $manquantParcelle->pourcentage = $pourcentageManquant;

        try {
            $manquant->save();
        } catch(Exception $e) {
            sleep(60);
            $manquant->save();
        }
    }

    public function createIrrigation($etablissement, $parcelle, $data) {
        $irrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($etablissement->identifiant, $this->periode);
        if(!$irrigable->isValidee()) {
            $irrigable->validate($this->periode.'-'.self::DATE_VALIDATION);
            $irrigable->validation_odg = $irrigable->validation;
        }
        try {
            $irrigable->save();
        } catch(Exception $e) {
            sleep(60);
            $irrigable->save();
        }
        if ($data[self::CSV_IRRIGABLE] !== 'OUI' && !$data[self::CSV_IRRIGUE]) {
            return;
        }

        $irrigableParcelle = $this->addParcelleFromParcellaireParcelle($irrigable, $parcelle);
        if(!$irrigableParcelle) {
            return;
        }
        $irrigableParcelle->superficie = (float)($data[self::CSV_SURFACE]);
        $irrigableParcelle->materiel = $this->parseRessource($data[self::CSV_MATERIEL]);
        $irrigableParcelle->ressource = $this->parseRessource($data[self::CSV_MATERIEL]);
        try {
            $irrigable->save();
        } catch(Exception $e) {
            sleep(60);
            $irrigable->save();
        }

        if(!$data[self::CSV_IRRIGUE]) {
            return;
        }
        $irrigue = ParcellaireIrrigueClient::getInstance()->createOrGetDocFromIdentifiantAndDate($etablissement->identifiant, $this->periode, true, $this->periode.'-'.self::DATE_VALIDATION);
        $irrigueParcelle = $this->addParcelleFromParcellaireParcelle($irrigue, $parcelle);
        if(!$irrigueParcelle) {
            return;
        }
        $irrigueParcelle->materiel = $this->parseRessource($data[self::CSV_MATERIEL]);
        $irrigueParcelle->ressource = $this->parseRessource($data[self::CSV_MATERIEL]);
        $irrigueParcelle->irrigation = 1;
        $irrigueParcelle->date_irrigation = $this->periode.'-'.self::DATE_VALIDATION;
        if(!$irrigue->isValidee()) {
            $irrigue->validate($this->periode.'-'.self::DATE_VALIDATION);
            $irrigue->validation_odg = $irrigue->validation;
        }
        try {
            $irrigue->save();
        } catch(Exception $e) {
            sleep(60);
            $irrigue->save();
        }
    }

    protected function parseRessource($value)
    {
        if (! $value) {
            return null;
        }

        if ($value === "SCP" || $value === "scp") {
            $value = "Canal de Provence";
        }

        if (strpos($value, " SS ") !== false) {
            $value = str_replace(" SS ", " SOUS ", $value);
        }

        if (strpos(strtoupper($value), "CANAL D") === false && strlen($value) > 5 && strpos(strtoupper($value), "CANAL") !== strlen($value) - 5) {
            $value = str_replace("CANAL", "CANAL DE", strtoupper($value));
        }

        $value = str_replace('GOUTTE A', 'GOUTTE À', strtoupper($value));

        // Si match exact
        foreach ($this->ressources as $ressource) {
            if (mb_strtoupper($ressource) === mb_strtoupper($value)) {
                return $ressource;
            }
        }

        foreach ($this->materiels as $ressource) {
            if (mb_strtoupper($ressource) === mb_strtoupper($value)) {
                return $ressource;
            }
        }

        $value = ucfirst(mb_strtolower($value));

        return str_replace(
            ['ouveze', 'ventoux', 'Asa', 'Reseau', 'prive', 'Neant'],
            ['Ouveze', 'Ventoux', 'ASA', 'Réseau', 'privé', 'Néant'],
            $value
        );
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
