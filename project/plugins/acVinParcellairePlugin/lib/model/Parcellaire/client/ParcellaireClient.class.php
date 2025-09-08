<?php

class ParcellaireClient extends acCouchdbClient {

    const TYPE_MODEL = "Parcellaire";
    const TYPE_COUCHDB = "PARCELLAIRE";

    const MODE_SAVOIRFAIRE_FERMIER = 'FERMIER';
    const MODE_SAVOIRFAIRE_PROPRIETAIRE = 'PROPRIETAIRE';
    const MODE_SAVOIRFAIRE_METAYER = 'METAYER';

    const PARCELLAIRE_DEFAUT_PRODUIT_HASH = '/declaration/certifications/DEFAUT/genres/DEFAUT/appellations/DEFAUT/mentions/DEFAUT/lieux/DEFAUT/couleurs/DEFAUT/cepages/DEFAUT';
    const PARCELLAIRE_DEFAUT_PRODUIT_LIBELLE = 'Appellation non reconnue';

    const PARCELLAIRE_SUPERFICIE_UNIT_ARE = 'are';
    const PARCELLAIRE_SUPERFICIE_UNIT_HECTARE = 'hectare';

    public static $modes_savoirfaire = array(
        self::MODE_SAVOIRFAIRE_FERMIER => "Fermier",
        self::MODE_SAVOIRFAIRE_PROPRIETAIRE => "Propriétaire",
        self::MODE_SAVOIRFAIRE_METAYER => "Métayer",
    );

    public static function getInstance() {
        return acCouchdbManager::getClient("Parcellaire");
    }

    /**
     * Recherche une entrée dans les documents existants
     *
     * @param string $identifiant L'identifiant etablissement du parcellaire
     * @param string $date La date de création du parcellaire
     *
     * @return Un document existant
     */
    public function findByArgs($identifiant, $date)
    {
        $id = self::TYPE_COUCHDB . '-' . $identifiant . '-' . $date;
        return $this->find($id);
    }

    /**
     * Scrape le site des douanes via le scrapy
     *
     * @param string $cvi Le numéro du CVI à scraper
     *
     * @throws Exception Si aucun CVI trouvé
     * @return string Le fichier le plus récent
     */
    public function scrapeParcellaireCSV($cvi, $scrappe = true, $contextInstance = null)
    {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();

        $status = 0;
        if ($scrappe && is_file(ProdouaneScrappyClient::getScrapyBin().'/download_parcellaire.sh')) {
            $status = ProdouaneScrappyClient::exec("download_parcellaire.sh", "$cvi", $output);
        }

        $scrapydocs = ProdouaneScrappyClient::getDocumentPath($contextInstance);
        $file = $scrapydocs.'/parcellaire-'.$cvi.'.csv';

        if (empty($file)) {
            $contextInstance->getLogger()->info("scrapeParcellaireCSV() : pas de fichiers trouvés");
        }
        if ($status != 0) {
            $contextInstance->getLogger()->info("scrapeParcellaireCSV() : retour du scrap problématique : $status");
        }

        return $file;
    }
    /**
     * Scrape le site des douanes via le scrapy
     *
     * @param string $cvi Le numéro du CVI à scraper
     *
     * @throws Exception Si aucun CVI trouvé
     * @return string Le fichier le plus récent
     */
    public function scrapeParcellaireJSON($cvi, $contextInstance = null)
    {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        $scrapydocs = ProdouaneScrappyClient::getDocumentPath();
        $status = ProdouaneScrappyClient::exec("download_parcellaire_geojson.sh", "$cvi", $output);
        $file = $scrapydocs.'/cadastre-'.$cvi.'-parcelles.json';
        $message = "";

        if (empty($file)) {
            $message = "Les parcelles n'existent pas dans les fichier du Cadastre. ";

            if($status != 0){
                $message .= "La récupération des geojson n'a pas fonctionné.";
            }
        }

        if(!empty($message)){
            $contextInstance->getLogger()->info("scrapeParcellaireJSON: error: ".$message);
            throw new Exception($message);
        }

        return $file;
    }

    /**
     * Prend un chemin de fichier en paramètre et le transforme en Parcellaire
     * Vérifie que le nouveau parcellaire est différent du courant avant de le
     * sauver
     *
     * @param Etablissement $etablissement L'établissement à mettre à jour
     * @param Array &$error Le potentiel message d'erreur de retour
     *
     * @return bool
     */
    public function saveParcellaire(Etablissement $etablissement, Array &$errors, $contextInstance = null, $scrapping = true)
    {
        try {
            return $this->saveParcellaireScrapyApi($etablissement, $errors, $contextInstance, $scrapping);
        }catch(sfException $e) {
        }
        return $this->saveParcellaireScrapyLocal($etablissement, $errors, $contextInstance, $scrapping);
    }

    public function saveParcellaireScrapyApi(Etablissement $etablissement, Array &$errors, $contextInstance = null, $scrapping = true)
    {
        if (ProdouaneScrappyClient::scrape('parcellaire', date('Y'), $etablissement->cvi) != ProdouaneScrappyClient::SCRAPING_SUCCESS) {
            return false;
        }
        $nb = 0;
        $files = ProdouaneScrappyClient::listAndSaveInTmp('parcellaire', date('Y'), $etablissement->cvi);
        foreach ($files as $f) {
            $i = pathinfo($f);
            switch ($i['extension']) {
                case 'pdf':
                    $parcellaire->storeAttachment($f, 'application/pdf', "import-cadastre-$cvi-parcelles.pdf");
                    $parcellaire->save();
                    $nb++;
                    break;
                case 'csv':
                    $parcellaire->storeAttachment($f, 'text/csv', "import-cadastre-$cvi-parcelles.csv");
                    $parcellaire->save();
                    $nb++;
                    break;
                case 'json':
                    $parcellaire->storeAttachment($f, 'text/json', "import-cadastre-$cvi-parcelles.json");
                    $parcellaire->save();
                    $nb++;
                    break;
            }
            unlink($f);
        }
        return ($nb > 1);
     }

    public function saveParcellaireScrapyLocal(Etablissement $etablissement, Array &$errors, $contextInstance = null, $scrapping = true)
    {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        $cvi = $etablissement->cvi;

        $fileCsv = ProdouaneScrappyClient::getDocumentPath($contextInstance).'/parcellaire-'.$cvi.'.csv';

        $fileCsv = $this->scrapeParcellaireCSV($cvi, $scrapping, $contextInstance);
        $filePdf = str_replace('.csv', '-parcellaire.pdf', $fileCsv);

        $lastParcellaire = $this->getLast($etablissement->identifiant);
        if($filePdf && is_file($filePdf) && $lastParcellaire && $lastParcellaire->hasParcellairePDF() && md5_file($filePdf) == $lastParcellaire->getParcellairePDFMd5()) {

            throw new Exception("Aucune nouvelle version du PDF trouvée (il se peut que le parcellaire de cet opérateur ne soit pas accessible sur prodouane)");
        }

        $parcellaire = ParcellaireClient::getInstance()->findOrCreate(
            $etablissement->identifiant,
            date('Y-m-d'),
            'PRODOUANE'
        );

        $return = false;
        if (is_file($filePdf)) {
            $parcellaire->storeAttachment($filePdf, 'application/pdf', "import-cadastre-$cvi-parcelles.pdf");
            $parcellaire->save();
            $return = true;
        }else{
            $errors['pdf'] = 'Pas de PDF issu du scrapping trouvé';
        }

        $returncsv = false;
        if (is_file($fileCsv)) {
            $parcellaire->storeAttachment($fileCsv, 'text/csv', "import-cadastre-$cvi-parcelles.csv");
            $parcellaire->save();
            $returncsv = true;
        }else{
            $errors['csv'] = 'Pas de CSV issu du scrapping trouvé ('.$fileCsv.')';
        }

        $this->loadParcellaireCSV($parcellaire);
        $parcellaire->save();

        if ($returncsv) {
            $fileJson = ProdouaneScrappyClient::getDocumentPath($contextInstance).'/cadastre-'.$cvi.'-parcelles.json';
            if($scrapping) {
                $fileJson = $this->scrapeParcellaireJSON($cvi, $contextInstance);
            }
            if (is_file($fileJson)) {
                $parcellaire->storeAttachment($fileJson, 'text/json', "import-cadastre-$cvi-parcelles.json");
                $parcellaire->save();
            }
        }
        return $return || $returncsv;
    }

    public function loadParcellaireCSV(Parcellaire $parcellaire, $contextInstance = null) {
        $contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        try {
            $parcellairecsv = ParcellaireCsvFile::getInstance($parcellaire);
            $parcellairecsv->convert();
        } catch (Exception $e) {
            $contextInstance->getLogger()->info("loadParcellaireCSV() : exception ".$e->getMessage());
            if (sfConfig::get('sf_environment') == 'dev') {
                throw $e;
            }
            $error = $e->getMessage();
            return false;
        }

        $parcellairecsv->save();
        return true;
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {
            sfContext::getInstance()->getLogger()->info("ParcellaireClient::find()".sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findOrCreate($identifiant, $date = null, $source = null, $type = self::TYPE_COUCHDB) {
        if (! $date) {
            $date = date('Ymd');
        }
        $parcellaire = $this->findPreviousByIdentifiantAndDate($identifiant, $date);
        if ($parcellaire && $parcellaire->date == $date) {
            return $parcellaire;
        }
        $parcellaire = new Parcellaire();
        $parcellaire->initDoc($identifiant, $date);
        $parcellaire->source = $source;

        return $parcellaire;
    }

    public function findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $h = $this->getHistory($identifiant, $date, $hydrate);
        if (!count($h)) {
        return NULL;
        }
        $h = $h->getDocs();
        end($h);
        $doc = $h[key($h)];
        return $doc;
    }

    public function getLastByCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $date = ConfigurationClient::getInstance()->getCampagneVinicole()->getDateFinByCampagne($campagne);

        return $this->findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate);
    }

    public function getLast($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
        $history = $this->getHistory($identifiant, $hydrate);

        return $this->findPreviousByIdentifiantAndDate($identifiant, '9999-99-99');
    }

    public function getHistory($identifiant, $date = '9999-99-99', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $dateDebut = "0000-00-00") {

        return $this->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $dateDebut)))
                    ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $date)))->execute($hydrate);
    }

    public function findAll($limit = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$view = $this->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", "AAA0000000", "00000000"))
    	->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", "ZZZ9999999", "99999999"));
    	if ($limit) {
    		$view->limit($limit);
    	}
    	return $view->execute($hydrate)->getDatas();
    }

    public static function sortParcellesForCommune($a, $b) {
        $aK = $a->section.sprintf("%04d",$a->numero_parcelle);
        $bK = $b->section.sprintf("%04d",$b->numero_parcelle);
        return strcmp($aK,$bK);

    }

    public static function findParcelle($parcellaire, $parcelle, $scoreMin = 1, $with_cepage_match = false, &$allready_selected = null) {
        $parcelles = $parcellaire->getParcellesByIdu();

        $parcellesMatch = [];

        $selected_parcellaires = [];
        $is_parcelle_from_parcellaire = method_exists($parcelle, 'exist');

        if ($is_parcelle_from_parcellaire && $parcelle->exist('idu') && $parcelle->idu && isset($parcelles[$parcelle->idu])) {
            $selected_parcellaires = $parcelles[$parcelle->idu];
        }else{
            foreach($parcelles as $idu => $multip) {
                foreach($multip as $p) {
                    $selected_parcellaires[] = $p;
                }
            }
        }
        foreach($selected_parcellaires as $p) {
            $score = 0;
            $debug_score = [];
            if ($is_parcelle_from_parcellaire) {
                $cepage =  $parcelle->getCepageLibelle();
            }else {
                $cepage = $parcelle->cepage;
            }
            if(preg_replace('/ (b|n|blanc|rouge)$/', '', strtolower($cepage)) == preg_replace('/ (b|n|blanc|rouge)$/', '', strtolower($p->getCepageLibelle()))) {
                $debug_score[] = 'libelle:+0.25';
                $score += 0.25;
            }
            if(strpos($p->campagne_plantation, $parcelle->campagne_plantation) !== false) {
                $debug_score[] = 'campagne_plantation:+0.25';
                $score += 0.25;
            }
            if(KeyInflector::slugify($parcelle->lieu) == KeyInflector::slugify($p->lieu)) {
                $debug_score[] = 'lieu:+0.25';
                $score += 0.25;
            }
            if(KeyInflector::slugify($parcelle->commune) == KeyInflector::slugify($p->commune)) {
                $debug_score[] = 'commune:+0.25';
                $score += 0.25;
            }
            $has_one_exact_superficie = false;
            if($is_parcelle_from_parcellaire && $parcelle->exist('parcelle_id') && $parcelle->_get('parcelle_id') && $parcelle->exist('superficie_parcellaire') && $p->exist('superficie_parcellaire') && abs($parcelle->_get('superficie_parcellaire') - $p->_get('superficie_parcellaire')) < 0.0001) {
                $debug_score[] = 'superficie_parcellaire:+0.10';
                $has_one_exact_superficie = true;
                $score += 0.10;
            }
            $s = 0;
            if ($is_parcelle_from_parcellaire) {
                $s = $parcelle->_get('superficie');
            }else{
                $s = $parcelle->superficie;
            }
            if(abs($s - $p->_get('superficie')) < 0.0001) {
                $debug_score[] = 'superficie:+0.25';
                $has_one_exact_superficie = true;
                $score += 0.25;
            }

            if($is_parcelle_from_parcellaire && $parcelle->exist('parcelle_id') && $parcelle->_get('parcelle_id') && $parcelle->exist('superficie_parcellaire') && abs($parcelle->_get('superficie_parcellaire') - $p->_get('superficie')) < 0.0001) {
                $debug_score[] = 'superficie:+0.05';
                $has_one_exact_superficie = true;
                $score += 0.05;
            }
            if (!$has_one_exact_superficie) {
                $debug_score[] = 'no_superficie:-0.30';
                $score -= 0.30;
            }
            if ($is_parcelle_from_parcellaire && ($parcelle->idu == $p->idu)) {
                $debug_score[] = 'idu:+0.25';
                $score += 0.25;
            }elseif ( (!$is_parcelle_from_parcellaire || !$parcelle->getIDU(false)) && ( ($parcelle->section == $p->section) && ($parcelle->numero_parcelle == $p->numero_parcelle) && (intval($parcelle->prefix == intval($p->prefix))) )) {
                $debug_score[] = 'parcelledetail:+0.25';
                $score += 0.25;
            }
            if ($allready_selected && isset($allready_selected[$p->getParcelleId()])) {
                continue;
            }
            if($score < $scoreMin) {
                continue;
            }

            $parcellesMatch[sprintf("%03d", $score*100)."_".$p->getKey()] = ['parcelle' => $p, 'debug' => $debug_score];
            if ($allready_selected !== null) {
                $allready_selected[$p->getParcelleId()] = $p->getParcelleId();
            }
        }

        krsort($parcellesMatch);
        foreach($parcellesMatch as $key => $pMatch) {
            if ($with_cepage_match) {
                if ($pMatch['parcelle']->cepage != $parcelle->cepage) {
                    continue;
                }
            }
            return $pMatch['parcelle'];
        }
        return null;
    }

    public static function parcelleSplitIDU($parcelle) {
        if (!$parcelle->idu) {
            return;
        }
        $parcelle->setCodeCommune(substr($parcelle->idu, 0, 5));
        $parcelle->setPrefix(substr($parcelle->idu, 5, 3));
        $parcelle->setSection(preg_replace('/^0+/', '', substr($parcelle->idu, 8, 2)));
        $parcelle->setNumeroParcelle(preg_replace('/^0+/', '', substr($parcelle->idu, 10, 4)));
    }

    public static function CopyParcelle($p1, $p2, $withSuperficie = true) {
        if (!$p2) {
            throw new sfException('2d parcelle should not be empty');
        }
        $p1->idu = $p2->idu;
        self::parcelleSplitIDU($p1);
        $p1->campagne_plantation = $p2->campagne_plantation;
        $p1->commune = $p2->commune;
        $p1->code_commune = $p2->code_commune;
        if($p1->exist('cepage') && $p2->exist('cepage')) {
            $p1->cepage = $p2->cepage;
        }
        if ($withSuperficie) {
            $p1->superficie = $p2->superficie;
        }
        if ($p1->exist('produit_hash')) {
            $p1->produit_hash = $p2->produit_hash;
        }
        if($p2->exist('lieu')){
            $p1->lieu = $p2->lieu;
        }
        if ($p1->exist('superficie_parcellaire')) {
            $p1->superficie_parcellaire = $p2->getSuperficieParcellaire();
        }
        if ($p1->exist('superficie_cadastrale') && $p2->exist('superficie_cadastrale')) {
            $p1->superficie_cadastrale = $p2->superficie_cadastrale;
        }
        if ($p1->exist('ecart_rang')) {
            $p1->ecart_rang = $p2->ecart_rang;
        }
        if ($p1->exist('ecart_pieds')) {
            $p1->ecart_pieds = $p2->ecart_pieds;
        }
        if ($p1->exist('mode_savoirfaire')) {
            $p1->mode_savoirfaire = $p2->mode_savoirfaire;
        }
        if ($p1->exist('porte_greffe')) {
            $p1->porte_greffe = $p2->porte_greffe;
        }
        $p1->parcelle_id = $p2->getParcelleId();
        if ($p1->exist('numero_ordre')) {
            $p1->numero_ordre = explode('-', $p1->parcelle_id)[1];
        }
        if (strpos($p2->getHash(), 'declaration') !== false) {
            if (!$p1->produit_hash) {
                $p1->produit_hash = $p2->getParent()->getParent()->getHash();
            }
        }
        if ($p2->exist('source_produit_libelle') && $p2->source_produit_libelle) {
            $p1->add('source_produit_libelle', $p2->source_produit_libelle);
        }
        return $p1;

    }

    public static function organizeParcellesByCommune($parcelles) {
        $res = array();

        foreach($parcelles as $pid => $parcelle) {
            if(!isset($res[$parcelle->commune])) {
                $res[$parcelle->commune] = array();
            }
            $res[$parcelle->commune][$parcelle->getParcelleId()] = $parcelle;
        }
        foreach ($res as $key => $parcelleByCommune) {
            uasort($parcelleByCommune, "ParcellaireClient::sortParcellesForCommune");
            $res[$key] = $parcelleByCommune;
        }
        ksort($res);
        return $res;

    }

    public function getSyntheseCepages($parcellairedoc, $filter_produit_hash = null, $filter_insee = null) {
        $synthese = array();
        foreach($parcellairedoc->getParcelles() as $p) {
            if ($filter_produit_hash) {
                if (is_string($filter_produit_hash)) {
                    if (strpos($p->produit_hash, $filter_produit_hash) === false) {
                        continue;
                    }
                } else {
                    if (!$p->produit_hash) {
                        continue;
                    }
                }
            }
            if ($filter_insee && !in_array($p->code_commune, $filter_insee)) {
                continue;
            }
            $cepage = $p->getCepage();
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                $cepage .= ' - jeunes vignes';
            }
            if (!isset($synthese[$cepage])) {
                $synthese[$cepage] = array();
                $synthese[$cepage]['superficie'] = 0;
                $synthese[$cepage]['idus'] = [];
            }
            $synthese[$cepage]['superficie'] = $synthese[$cepage]['superficie'] + $p->superficie;
            $synthese[$cepage]['idus'][$p->getParcelleId()] = $p->superficie;
        }
        ksort($synthese);
        return $synthese;
    }

}
