<?php

class DRevClient extends acCouchdbClient implements FacturableClient {

    const TYPE_MODEL = "DRev";
    const TYPE_COUCHDB = "DREV";
    const DENOMINATION_BIO_TOTAL_DEPRECATED = "BIO_TOTAL";
    const DENOMINATION_BIO_PARTIEL_DEPRECATED = "BIO_PARTIEL";
    const DENOMINATION_CONVENTIONNEL = "CONVENTIONNEL";
    const DENOMINATION_BIO = "BIO";
    const DENOMINATION_BIO_LIBELLE_AUTO = "AB hors conversion";
    const DENOMINATION_BIODYNAMIE = "BIODYNAMIE";
    const DENOMINATION_BIODYNAMIE_LIBELLE_AUTO = "Biodynamie hors conversion";
    const DENOMINATION_HVE = "HVE";
    const DENOMINATION_HVE_LIBELLE_AUTO = "HVE";
    const DENOMINATION_CONVERSION_BIO = "CONVERSION_BIO";
    const DENOMINATION_CONVERSION_BIO_LIBELLE_AUTO = "Conversion en Bio";
    const DENOMINATION_JEUNE_VIGNE = "JEUNE_VIGNE";
    const LOT_DESTINATION_VRAC_FRANCE_ET_CONDITIONNEMENT = 'VRAC_FRANCE_CONDITIONNEMENT';
    const LOT_DESTINATION_VRAC = 'VRAC';
    const LOT_DESTINATION_VRAC_FRANCE = 'VRAC_FRANCE';
    const LOT_DESTINATION_VRAC_EXPORT = 'VRAC_EXPORT';
    const LOT_DESTINATION_CONDITIONNEMENT_ENCOURS = 'CONDITIONNEMENT_ENCOURS';
    const LOT_DESTINATION_CONDITIONNEMENT = 'CONDITIONNEMENT';
    const LOT_DESTINATION_CONDITIONNEMENT_CONSERVATOIRE = 'CONDITIONNEMENT_CONSERVATOIRE';
    const LOT_DESTINATION_TRANSACTION = 'VRAC_EXPORT';
    const LOT_DESTINATION_VRAC_FRANCE_ET_VRAC_EXPORT = "VRAC_FRANCE_VRAC_EXPORT";
    const LOT_DESTINATION_VRAC_EXPORT_ET_CONDITIONNEMENT = "VRAC_EXPORT_CONDITIONNEMENT";
    const LOT_DESTINATION_VRAC_FRANCE_VRAC_EXPORT_CONDITIONNEMENT = "VRAC_FRANCE_VRAC_EXPORT_CONDITIONNEMENT";

    const STATUT_EN_ATTENTE = "En attente";
    const STATUT_SIGNE = "À approuver";
    const STATUT_VALIDATION_ODG = "Approuvé";
    const STATUT_BROUILLON = null;

    public static function getDenominationsAuto() {
        $denom = array(
            self::DENOMINATION_CONVENTIONNEL => "Conventionnel",
            self::DENOMINATION_CONVERSION_BIO => self::DENOMINATION_CONVERSION_BIO_LIBELLE_AUTO,
            self::DENOMINATION_HVE => self::DENOMINATION_HVE_LIBELLE_AUTO,
            self::DENOMINATION_BIO => self::DENOMINATION_BIO_LIBELLE_AUTO,
        );
        if (DRevConfiguration::getInstance()->hasDenominationBiodynamie()) {
            $denom[self::DENOMINATION_BIODYNAMIE] = self::DENOMINATION_BIODYNAMIE_LIBELLE_AUTO;
        }
        return $denom;
    }

    public static $lotDestinationsType = array(
        DRevClient::LOT_DESTINATION_CONDITIONNEMENT => "Conditionnement",
        DRevClient::LOT_DESTINATION_CONDITIONNEMENT_CONSERVATOIRE => "Conditionnement sur conservatoire",
        DRevClient::LOT_DESTINATION_TRANSACTION => "Vrac Export",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE => "Vrac France",
        DRevClient::LOT_DESTINATION_VRAC_EXPORT => "Vrac Export",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_CONDITIONNEMENT => "Vrac France et Conditionnement",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_VRAC_EXPORT => "Vrac France et Vrac Export",
        DRevClient::LOT_DESTINATION_VRAC_EXPORT_ET_CONDITIONNEMENT => "Vrac Export et Conditionnement",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE_VRAC_EXPORT_CONDITIONNEMENT => "Vrac Export, Vrac France et Conditionnement"
    );

    public $cache_find_drev = null;

    public static function getInstance()
    {

        return acCouchdbManager::getClient("DRev");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findViaCache($id) {
        if (!$this->cache_find_drev) {
            $this->cache_find_drev = array();
        }
        if (!isset($this->cache_find_drev[$id])) {
            $this->cache_find_drev[$id] = $this->find($id);
        }
        return $this->cache_find_drev[$id];
    }

    public function findMasterByIdentifiantAndPeriode($identifiant, $periode, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->findMasterByIdentifiantAndCampagne($identifiant, $periode.'-'.($periode + 1), $hydrate );
    }

    public function findMasterByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $drevs = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, $campagne, self::TYPE_MODEL);
        foreach ($drevs as $id => $drev) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function findFacturable($identifiant, $periode) {
    	$drevs = $this->getHistory($identifiant,$periode,$periode);

        if(!$drevs){

            return array();
        }
        $facturables = array();
        foreach ($drevs as $drev) {
            if($drev->validation_odg){
                $facturables[$drev->_id] = $drev;
            }
        }

        return $facturables;
    }

    public function createDoc($identifiant, $periode, $papier = false, $reprisePrecedente = true)
    {
        $drev = new DRev();
        $drev->initDoc($identifiant, $periode);
        $drev->storeDeclarant();

        $etablissement = $drev->getEtablissementObject();

        if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR)) {
            $drev->add('non_recoltant', 1);
        }

        if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_CONDITIONNEUR)) {
            $drev->add('non_conditionneur', 1);
        }

        if($papier) {
            $drev->add('papier', 1);
        }

        if($reprisePrecedente) {
            $previous_drev = self::findMasterByIdentifiantAndPeriode($identifiant, $periode - 1 );
            if ($previous_drev) {
                $drev->set('chais', $previous_drev->chais->toArray(true, false));
            }
        }

        return $drev;
    }

    public function getIds($periode) {
        $ids = $this->startkey_docid(sprintf("DREV-%s-%s", "0000000000", "0000"))
                    ->endkey_docid(sprintf("DREV-%s-%s", "9999999999", "9999"))
                    ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $ids_periode = array();

        foreach($ids as $id) {
            if(strpos($id, "-".$periode) !== false) {
                $ids_periode[] = $id;
            }
        }

        sort($ids_periode);

        return $ids_periode;
    }

    public function getDateOuvertureDebut() {
        $dates = sfConfig::get('app_dates_ouverture_drev');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_drev');

        return $dates['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin();
    }

    public function getHistory($identifiant, $periode_from = "0000", $periode_to = "9999", $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {

        return $this->startkey(sprintf("DREV-%s-%s", $identifiant, $periode_from))
                    ->endkey(sprintf("DREV-%s-%s_ZZZZZZZZZZZZZZ", $identifiant, $periode_to))
                    ->execute($hydrate);
    }

    public function getNonHabilitationINAO($drev) {
        $non_habilite = array();
        $identifiant = $drev->declarant->cvi;
        if (!$identifiant) {
            $identifiant = preg_replace('/ /', '', $drev->declarant->siret);
        }
        if (!$identifiant) {
            return array();
        }
        $regions = RegionConfiguration::getInstance()->getOdgRegions();
        foreach($regions as $region) {
            $produits = $drev->getProduits($region);
            if (!count($produits)) {
                continue;
            }
            $inao_fichier = DrevConfiguration::getInstance()->getOdgINAOHabilitationFile($region);
            if (!$inao_fichier) {
                continue;
            }
            $inao_csv = new INAOHabilitationCsvFile(sfConfig::get('sf_root_dir').'/'.$inao_fichier);
            foreach ($produits as $produit) {
                if (! $inao_csv->isHabilite($identifiant, $produit->getConfig()->getAppellation()->getLibelle())) {
                    $non_habilite[] = $produit;
                }
            }
        }
        return $non_habilite;
    }

    public function getLastDrevFromEtablissement($etablissement){
      $lastDrevs = $this->getHistory($etablissement->getIdentifiant());
      foreach ($lastDrevs as $drev) {
        return $drev;
      }
      return null;
    }

    public function matchFilterDrev($drev, TemplateFactureCotisationCallbackParameters $filterparameters)
    {
        $match = true;

        if ($filterparameters === null) {
            $filters = [];
        }else{
            $filters = $filterparameters->getParameters();
        }
        foreach ($filters as $type => $filter) {
            if ($type === 'appellations') {
                throw new sfException('not implemented');
            } elseif ($type === 'millesime') {
                throw new sfException('not implemented');
            } elseif ($type === 'deja') {
                throw new sfException('not implemented');
            } elseif ($type === 'region') {
                if ($drev->exist('region')) {
                    $region = str_replace('/region/', '', $filter);
                    $match = $match && strpos($drev->region, $region) !== false;
                }
            } elseif($type === 'famille') {
                $matchfamille = strpos($filter, $drev->declarant->famille) !== false;
                if (strpos($filter, 'NOT ') === 0) {
                    $match = $match && !$matchfamille;
                }else{
                    $match = $match && $matchfamille;
                }
            }
        }

        return $match;
    }

    public function matchFilterLot($lot, TemplateFactureCotisationCallbackParameters $produitFilter = null)
    {
        $etablissements = [];
        $match = true;

        if ($produitFilter === null) {
            $produitFilter = [];
        }else{
            $produitFilter = $produitFilter->getParameters();
        }

        foreach ($produitFilter as $type => $filter) {
            if ($type === 'appellations') {
                $match = $match && $this->matchFilterLotOnProduit($lot, $filter);
            } elseif ($type === 'millesime') {
                $match = $match && $this->matchFilterMillesime($lot, $filter);
            } elseif ($type === 'deja') {
                // On gère que l'option (NOT)? /deja/CONFORME pour le moment
                // Pas NONCONFORME
                $match = $match && $this->matchFilterConformite($lot, $filter);
            } elseif ($type === 'region') {
                $region = str_replace('/region/', '', $filter);
                $match = $match && RegionConfiguration::getInstance()->isHashProduitInRegion($region, $lot->getProduitHash());
            } elseif($type === 'famille') {
                if (array_key_exists($lot->declarant_identifiant, $etablissements) === false) {
                    $etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->find($lot->declarant_identifiant);
                }

                $match = $match && $this->matchFilterFamille($etablissements[$lot->declarant_identifiant]->famille, $filter);
            }
        }

        return $match;
    }

    public function matchFilterFamille($famille, $familleFilter)
    {
        if(! $famille){
            return false;
        }

        $familleFilterMatch = preg_replace("/^NOT /", "", $familleFilter, -1, $exclude);
        $exclude = (bool) $exclude;
        $regexpFilter = "#^(".implode("|", explode(",", $familleFilterMatch)).")$#";

        if(!$exclude && preg_match($regexpFilter, $famille)) {
            return true;
        }

        if($exclude && !preg_match($regexpFilter, $famille)) {
            return true;
        }

        return false;
    }

    private function matchFilterLotOnProduit($lot, $produitFilter)
    {
        $produitFilterMatch = preg_replace("/^NOT /", "", $produitFilter, -1, $produitExclude);
        $isExcludeMode = (bool) $produitExclude;
        $regexpFilter = "#(".implode("|", explode(",", $produitFilterMatch)).")#";

        if($produitFilter && !$isExcludeMode && !preg_match($regexpFilter, $lot->getProduitHash())) {
            return false;
        }

        if($produitFilter && $isExcludeMode && preg_match($regexpFilter, $lot->getProduitHash())) {
            return false;
        }

        return true;
    }

    private function matchFilterMillesime($lot, $filter)
    {
        if(strpos($filter, '/millesime/courant') !== false && $lot->millesime != $lot->getDocument()->getPeriode()) {

            return false;
        }

        if(strpos($filter, '/millesime/precedent') !== false && $lot->millesime >= intval($lot->getDocument()->getPeriode())) {

            return false;
        }

        return true;
    }

    private function matchFilterConformite($lot, $filter)
    {
        $not = strpos($filter, 'NOT') === 0;
        $conformite = str_replace(['NOT', ' ', '/deja/'], '', $filter);
        return ($not) ? $lot->isRedegustationDejaConforme() === false : $lot->isRedegustationDejaConforme() === true;
    }

    public function retrieveRelatedDrev($identifiant, $periode, $drev_produit_filter = null) {

        $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($identifiant, preg_replace('/-.*/', '', $periode));
        if ($drev && $drev_produit_filter) {
            if ($drev->hasLotsProduitFilter($drev_produit_filter)) {
                return $drev;
            }
            return null;
        }
        return $drev;
    }

}
