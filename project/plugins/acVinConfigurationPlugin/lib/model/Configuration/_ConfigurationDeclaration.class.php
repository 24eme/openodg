<?php

/**
 * Inheritance tree class _ConfigurationDeclaration
 *
 */
abstract class _ConfigurationDeclaration extends acCouchdbDocumentTree {

    protected $libelles = null;
    protected $codes = null;
    protected $noeud_droits = null;
    protected $droits_type = array();
    protected $produits_all = null;
    protected $produits = array();
    protected $libelle_format = array();
    protected $dates_droits = null;
    protected $format_libelle_calcule = false;
    protected $code_comptable = false;
    protected $code_produit = false;

    const ATTRIBUTE_CVO_FACTURABLE = 'CVO_FACTURABLE';
    const ATTRIBUTE_CVO_ACTIF = 'CVO_ACTIF';
    const ATTRIBUTE_DOUANE_FACTURABLE = 'DOUANE_FACTURABLE';
    const ATTRIBUTE_DOUANE_ACTIF = 'DOUANE_ACTIF';

    protected function loadAllData() {
        parent::loadAllData();

        $this->getDatesDroits();
        $this->getProduitsAll();
        $this->loadProduitsByDates();
        $this->getLibelles();
        $this->getCodes();
        $this->getFormatLibelleCalcule();
        $this->getCodeProduit();
        $this->getCodeComptable();
        $this->getLibelleFormat(array(), "%format_libelle% (%code_produit%)");
        $this->getLibelleFormat(array(), "%format_libelle%");
    }

    abstract public function getChildrenNode();

    public function getParentNode() {
        $parent = $this->getParent()->getParent();
        if (!$parent instanceof _ConfigurationDeclaration) {

            throw new sfException('Noeud racine atteint');
        } else {

            return $this->getParent()->getParent();
        }
    }

    public function getDatesDroits($interpro = "INTERPRO-declaration") {
        if (is_null($this->dates_droits)) {
            $this->dates_droits = $this->getDocument()->declaration->getDatesDroits($interpro);
        }

        return $this->dates_droits;
    }

    public function loadDatesDroits($interpro = "INTERPRO-declaration") {
        $dates_droits = array();

        $noeudDroits = $this->getDroits($interpro);
        if ($noeudDroits) {
            foreach ($noeudDroits as $droits) {
                foreach ($droits as $droit) {
                    $dateObj = new DateTime($droit->date);
                    $dates_droits[$dateObj->format('Y-m-d')] = true;
                }
            }
        }

        krsort($dates_droits);

        if (!$this->getChildrenNode()) {

            return $dates_droits;
        }

        foreach ($this->getChildrenNode() as $child) {
            $dates_droits = array_merge($dates_droits, $child->loadDatesDroits($interpro));
        }

        krsort($dates_droits);
        return $dates_droits;
    }

    public function getProduitsAll($interpro = null, $departement = null) {
        if (is_null($this->produits_all)) {
            $this->produits_all = array();
            foreach ($this->getChildrenNode() as $key => $item) {
                $this->produits_all = array_merge($this->produits_all, $item->getProduitsAll());
            }
        }

        return $this->produits_all;
    }


    public function getArrayAppellations() {
        $appellations = array();
        foreach($this->getChildrenNode() as $item) {

            $appellations = array_merge($appellations, $item->getArrayAppellations());
        }

        return $appellations;
    }

    public function findDroitsDate($date, $interpro) {
        $datesDroits = $this->getDatesDroits($interpro);

        foreach ($datesDroits as $dateDroits => $null) {
            if ($date >= $dateDroits) {

                return $dateDroits;
            }
        }

        return null;
        throw new sfException(sprintf("Aucune date défini pour le droit (interpro: %s, hash: %s)", $interpro, $this->getHash()));
    }

    public function getKeyAttributes($attributes) {
        sort($attributes);

        return implode("", $attributes);
    }

    public function loadProduitsByDates($interpro = "INTERPRO-declaration") {
        $datesDroits = $this->getDatesDroits($interpro);
        $attributesCombinaison = array(
            array(),
            array(self::ATTRIBUTE_CVO_FACTURABLE),
            array(self::ATTRIBUTE_CVO_ACTIF),
            array(self::ATTRIBUTE_CVO_ACTIF, self::ATTRIBUTE_DOUANE_ACTIF)
        );
        foreach ($datesDroits as $dateDroit => $null) {
            foreach ($attributesCombinaison as $attributes) {
                $this->getProduits($dateDroit, $interpro, null, $attributes);
            }
        }
    }

    public function getProduits($date = null, $interpro = "INTERPRO-declaration", $departement = null, $attributes = array()) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $date = $this->findDroitsDate($date, $interpro);
        $attributesKey = $this->getKeyAttributes($attributes);

        if (array_key_exists($date, $this->produits) && array_key_exists($attributesKey, $this->produits[$date])) {

            return $this->produits[$date][$attributesKey];
        }

        $produits = array();

        foreach ($this->getProduitsAll($interpro, $departement) as $hash => $item) {
            if (!$item->hasProduitAttributes($date, $attributes)) {

                continue;
            }

            $produits[$hash] = $item;
        }

        if (!array_key_exists($date, $this->produits)) {

            $this->produits[$date] = array();
        }

        $this->produits[$date][$attributesKey] = $produits;

        return $this->produits[$date][$attributesKey];
    }

    public function getLibelles() {
        if (is_null($this->libelles)) {
            $this->libelles = array_merge($this->getParentNode()->getLibelles(), array($this->libelle));
        }

        return $this->libelles;
    }

    public function getCodes() {
        if (is_null($this->codes)) {
            $this->codes = array_merge($this->getParentNode()->getCodes(), array($this->code));
        }

        return $this->codes;
    }

    public function getProduitsHashByCodeDouane($date, $interpro, $attributes = array()) {
        $produits = array();
        foreach ($this->getProduits($date, $interpro, $attributes) as $hash => $item) {
            $produits[$item->getCodeDouane()] = $hash;
        }

        return $produits;
    }

    public function getCodeDouane() {
        $a = $this->getCodesDouanes();
        if (!$a) {
            return null;
        }
        return array_shift($a);
    }

    public function getCodesDouanes() {
        if (!$this->_get('code_douane')) {
            return $this->getParentNode()->getCodesDouanes();
        }

        return explode(',', $this->_get('code_douane'));
    }

    public function getCodeProduit() {
        if($this->code_produit === false) {
            if (!$this->_get('code_produit')) {
                $this->code_produit = $this->getParentNode()->getCodeProduit();
            } else {
                $this->code_produit = $this->_get('code_produit');
            }
        }

        return $this->code_produit;
    }

    public function getCodeComptable() {
        if($this->code_comptable === false) {
            if (!$this->_get('code_comptable')) {

                return $this->getParentNode()->getCodeComptable();
            } else {

                return $this->_get('code_comptable');
            }
        }

        return $this->code_comptable;
    }

    public function getFormatLibelleCalcule() {
        if($this->format_libelle_calcule === false) {
            if (!$this->getFormatLibelle()) {
                $this->format_libelle_calcule = $this->getParentNode()->getFormatLibelleCalcule();
            } else {
                $this->format_libelle_calcule = $this->getFormatLibelle();
            }
        }

        return $this->format_libelle_calcule;
    }

    public function getFormatLibelleDefinitionNoeud() {
        if ($this->getFormatLibelle()) {

            return $this;
        }

        return $this->getParentNode()->getFormatLibelleDefinitionNoeud();
    }

    public function getDensite() {
        if (!$this->exist('densite') || !$this->_get('densite')) {
            try {

                return $this->getParentNode()->getDensite();
            } catch (Exception $e) {

                return null;
            }
        }

        return $this->_get('densite');
    }

    public function getLibelleComplet() {

        return $this->getLibelleFormat();
    }

    public function getLibelleFormat($labels = array(), $format = "%format_libelle%", $label_separator = ", ") {

        $key = ($this->getDocument()->isEffervescentVindebaseActivate() && $this->isEffervescentNode())? $format.'_vindebase' : $format;

        if (!array_key_exists($key, $this->libelle_format)) {
            $format_libelle = $this->getFormatLibelleCalcule();
            $formatResolu = str_replace("%format_libelle%", $format_libelle, $format);
            $libelle = $this->formatProduitLibelle($formatResolu);
          //  var_dump($this->getDocument()->isEffervescentVindebaseActivate()); exit;
            if($this->getDocument()->isEffervescentVindebaseActivate() && $this->isEffervescentNode()){
                  $libelle= "Vin de base ".$libelle;
            }
            $libelle = $this->getDocument()->formatLabelsLibelle($labels, $libelle, $label_separator);
            $this->libelle_format[$key] = trim($libelle);
        }

        return $this->libelle_format[$key];
    }

    public function formatProduitLibelle($format = "%g% %a% %m% %l% %co% %ce%") {
        $libelle = ConfigurationClient::getInstance()->formatLibelles($this->getLibelles(), $format);

        $libelle = str_replace(array('%code%',
            '%code_produit%',
            '%code_comptable%'), array($this->getCodeFormat(),
            $this->getCodeProduit(),
            $this->getCodeComptable()), $libelle);
        $libelle = str_replace("()", "", $libelle);
        $libelle = preg_replace('/ +/', ' ', $libelle);


        return $libelle;
    }

    public function getCodeFormat($format = "%g%%a%%m%%l%%co%%ce%") {

        return ConfigurationClient::getInstance()->formatCodes($this->getCodes(), $format);
    }

    public function getDroitByType($date, $type, $interpro = "INTERPRO-declaration") {
        $date = $this->findDroitsDate($date, $interpro);
        if (array_key_exists($date, $this->droits_type) && array_key_exists($type, $this->droits_type[$date])) {

            return $this->droits_type[$date][$type];
        }

        if (!array_key_exists($date, $this->droits_type)) {
            $this->droits_type[$date] = array();
        }

        $this->droits_type[$date][$type] = $this->getDroits($interpro)->get($type)->getCurrentDroit($date,false);

        return $this->droits_type[$date][$type];
    }

    public function getDroitCVO($date, $interpro = "INTERPRO-declaration") {

        return $this->getDroitByType($date, ConfigurationDroits::DROIT_CVO, $interpro);
    }

    public function getDroitDouane($date, $interpro = "INTERPRO-declaration") {

        return $this->getDroitByType($date, ConfigurationDroits::DROIT_DOUANE, $interpro);
    }

    public function isCVOActif($date) {

        return $this->getTauxCVO($date) >= 0;
    }

    public function isCVOFacturable($date) {

        return $this->getTauxCVO($date) > 0;
    }

    public function getTauxCVO($date) {
        try {
            $droit_produit = $this->getDroitCVO($date);
            $cvo_produit =null;
            if (is_object($droit_produit)) {
                $cvo_produit = $droit_produit->getTaux();
            } else {
                $cvo_produit = $droit_produit;
            }
        } catch (Exception $ex) {
            $cvo_produit = -1;
        }

        return $cvo_produit;
    }

    public function isDouaneActif($date) {

        return $this->getTauxDouane($date) >= 0;
    }

    public function isDouaneFacturable($date) {

        return $this->getTauxDouane($date) > 0;
    }

    public function getTauxDouane($date) {
        try {
            $droit_produit = $this->getDroitDouane($date);
            $douane_produit = $droit_produit->getTaux();
        } catch (Exception $ex) {
            $douane_produit = -1;
        }

        return $douane_produit;
    }

    public function hasProduitAttribute($date, $attribute) {

        if ($attribute == self::ATTRIBUTE_CVO_ACTIF) {

            return $this->isCVOActif($date);
        }

        if ($attribute == self::ATTRIBUTE_DOUANE_ACTIF) {

            return $this->isDouaneActif($date);
        }

        if ($attribute == self::ATTRIBUTE_CVO_FACTURABLE) {

            return $this->isCVOFacturable($date);
        }

        if ($attribute == self::ATTRIBUTE_DOUANE_FACTURABLE) {

            return $this->isDouaneFacturable($date);
        }

        return false;
    }

    public function hasProduitAttributes($date, $attributes) {
        if (!count($attributes)) {

            return true;
        }

        foreach ($attributes as $attribute) {
            if ($this->hasProduitAttribute($date, $attribute)) {

                return true;
            }
        }

        return false;
    }

    public function getDroits($interpro) {
        if (!is_null($this->noeud_droits)) {

            return $this->noeud_droits;
        }

        $droitsable = $this;
        while (!$droitsable->hasDroits()) {
            $droitsable = $droitsable->getParent()->getParent();
        }

        $this->noeud_droits = $droitsable->interpro->getOrAdd($interpro)->droits;

        return $this->noeud_droits;
    }

    public function compressDroits() {
        foreach ($this->getChildrenNode() as $child) {
            $child->compressDroits();
        }

        $this->compressDroitsSelf();
    }

    protected function compressDroitsSelf() {
        foreach ($this->interpro as $interpro => $object) {
            $droits = $this->getDroits($interpro);
            foreach ($droits as $droit) {
                $droit->compressDroits();
            }
        }
    }

    public function getDateCirulation($campagne, $interpro = "INTERPRO-declaration") {
            $dateCirculationAble = $this;
            while (!$dateCirculationAble->exist('interpro') ||
            !$dateCirculationAble->interpro->getOrAdd($interpro)->exist('dates_circulation') ||
            !count($dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation) ||
            !$dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation->exist($campagne)) {
                if($dateCirculationAble instanceOf ConfigurationDeclaration){
                                   return null;
                }
                $dateCirculationAble = $dateCirculationAble->getParent()->getParent();
            }
            if (!$dateCirculationAble->exist('interpro') ||
                    !$dateCirculationAble->interpro->getOrAdd($interpro)->exist('dates_circulation') ||
                    !count($dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation) ||
                    !$dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation->exist($campagne)) {
                return null;
            }
            return $dateCirculationAble->interpro->getOrAdd($interpro)->dates_circulation->get($campagne);
        }


    public function setLabelCsv($datas) {
        $labels = $this->interpro->getOrAdd('INTERPRO-' . strtolower($datas[LabelCsvFile::CSV_LABEL_INTERPRO]))->labels;
        $canInsert = true;
        foreach ($labels as $label) {
            if ($label == $datas[LabelCsvFile::CSV_LABEL_CODE]) {
                $canInsert = false;
                break;
            }
        }
        if ($canInsert) {
            $labels->add(null, $datas[LabelCsvFile::CSV_LABEL_CODE]);
        }
    }

    protected function setDepartementCsv($datas) {
        if (!array_key_exists(ProduitCsvFile::CSV_PRODUIT_DEPARTEMENTS, $datas) || !$datas[ProduitCsvFile::CSV_PRODUIT_DEPARTEMENTS]) {

            $this->departements = array();

            return;
        }

        $this->departements = explode(',', $datas[ProduitCsvFile::CSV_PRODUIT_DEPARTEMENTS]);
    }

    public function setDroitDouaneCsv($datas, $code_applicatif) {

        if (!array_key_exists(ProduitCsvFile::CSV_PRODUIT_DOUANE_NOEUD, $datas) || $code_applicatif != $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_NOEUD]) {
            return;
        }

        $droits = $this->getDroits('INTERPRO-' . strtolower($datas[ProduitCsvFile::CSV_PRODUIT_INTERPRO]));
        $date = ($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_DATE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_DATE] : '1900-01-01';
        $taux = ($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_TAXE]) ? str_replace(',', '.', $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_TAXE]) : 0;
        $code = (isset($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_CODE]) && $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_CODE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_CODE] : null;
        $libelle = (isset($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_LIBELLE]) && $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_LIBELLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_LIBELLE] : null;

        $currentDroit = null;
        foreach ($droits->douane as $droit) {
            if ($code != $droit->code) {
                continue;
            }

            if ($currentDroit && $droit->date < $currentDroit->date) {
                continue;
            }

            $currentDroit = $droit;
        }

        if ($currentDroit && $currentDroit->taux == $taux) {
            return;
        }

        $droits = $droits->douane->add();
        $droits->date = $date;
        $droits->taux = $taux;
        if(isset($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_CODE])){
          $droits->code = $code;
        }
        if(isset($datas[ProduitCsvFile::CSV_PRODUIT_DOUANE_LIBELLE])){
          $droits->libelle = $libelle;
        }
    }

    public function setDroitCvoCsv($datas, $code_applicatif) {

        if (!isset($datas[ProduitCsvFile::CSV_PRODUIT_CVO_NOEUD]) || $code_applicatif != $datas[ProduitCsvFile::CSV_PRODUIT_CVO_NOEUD]) {

            return;
        }

        $droits = $this->getDroits('INTERPRO-' . strtolower($datas[ProduitCsvFile::CSV_PRODUIT_INTERPRO]));
        $date = ($datas[ProduitCsvFile::CSV_PRODUIT_CVO_DATE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CVO_DATE] : '1900-01-01';
        $taux = ($datas[ProduitCsvFile::CSV_PRODUIT_CVO_TAXE]) ? "" . str_replace(',', '.', $datas[ProduitCsvFile::CSV_PRODUIT_CVO_TAXE]) : "0.0";
        $code = ConfigurationDroits::CODE_CVO;
        $libelle = ConfigurationDroits::LIBELLE_CVO;
        $currentDroit = null;
        foreach ($droits->cvo as $droit) {
            if ($currentDroit && $droit->date < $currentDroit->date) {
                continue;
            }

            $currentDroit = $droit;
        }

        if ($currentDroit && $currentDroit->taux == $taux) {
            return;
        }

        $droits = $droits->cvo->add();
        $droits->date = $date;
        $droits->taux = $taux;
        $droits->code = $code;
        $droits->libelle = $libelle;
    }

    public function formatProduits($date = null, $interpro = null, $departement = null, $format = "%format_libelle% (%code_produit%)", $attributes = array()) {
        if (!$date) {
            $date = date('Y-d-m');
        }

        $produits = $this->getProduits($date, $interpro, $departement, $attributes);
        $produits_formated = array();
        foreach ($produits as $hash => $produit) {
            $produits_formated[$hash] = $produit->getLibelleFormat(array(), $format, ',');
        }
        return $produits_formated;
    }

    public function getLabels($interpro) {

        throw new sfException("The method \"getLabels\" is not defined");
    }

    public function setDonneesCsv($datas) {
        if ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_PRODUIT_NOEUD] == $this->getTypeNoeud()) {
            $this->code_produit = ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_PRODUIT]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CODE_PRODUIT] : null;
        }

        if ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_COMPTABLE_NOEUD] == $this->getTypeNoeud()) {
            $this->code_comptable = ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_COMPTABLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CODE_COMPTABLE] : null;
        }

        if ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_DOUANE_NOEUD] == $this->getTypeNoeud()) {
            $this->code_douane = ($datas[ProduitCsvFile::CSV_PRODUIT_CODE_DOUANE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CODE_DOUANE] : null;
        }

        if (isset($datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE_NOEUD]) && $datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE_NOEUD] == $this->getTypeNoeud()) {
            $this->format_libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_FORMAT_LIBELLE] : null;
        }
    }

    public function formatCodeFromCsv($code) {
        $code = preg_replace("|/.+$|", "", $code);

        if (!$code) {

            return null;
        }

        return $code;
    }

    public abstract function getTypeNoeud();

    public function getDetailConfiguration($key) {
        try {
            $parent_node = $this->getParentNode();
        } catch (Exception $e) {
            return $this->get($key)->getDetail();
            ;
        }

        $details = $this->getParentNode()->getDetailConfiguration($key);
        if ($this->exist('detail')) {
            foreach ($this->detail as $type => $detail) {
                foreach ($detail as $noeud => $droits) {
                    if ($droits->readable !== null)
                        $details->get($type)->get($noeud)->readable = $droits->readable;
                    if ($droits->writable !== null)
                        $details->get($type)->get($noeud)->writable = $droits->writable;
                }
            }
        }
        return $details;
    }

    public function getKeys($noeud) {
        if ($noeud == $this->getTypeNoeud()) {

            return array($this->getKey() => $this);
        }

        $items = array();
        foreach ($this->getChildrenNode() as $key => $item) {
            $items = array_merge($items, $item->getKeys($noeud));
        }

        return $items;
    }

    public function addInterpro($interpro) {
        if ($this->exist('interpro')) {
            $this->interpro->getOrAdd($interpro);
        }
        return $this->getParentNode()->addInterpro($interpro);
    }

    public function hasDepartements() {
        return false;
    }

    public function hasDroits() {
        return true;
    }

    public function hasLabels() {
        return false;
    }

    public function hasRendements() {
    	return true;
    }

    public function hasDetails() {
        return false;
    }

    public function hasDroit($type) {
        if (!$this->hasDroits()) {

            return false;
        }

        if ($type == ConfigurationDroits::DROIT_CVO) {
            return false;
        }

        return false;
    }

    public function hasCodes() {
        return false;
    }

    public function hasCepagesAutorises() {
      return $this->exist('cepages_autorises');
    }

    public function getAttribut($name, $default = null) {
        if (!$this->exist('attributs') || !$this->attributs->exist($name)) {

            return $default;
        }


        return $this->attributs->get($name);
    }

    /**** DR ****/

    public function getRendement() {
        return $this->getRendementByKey('rendement');
    }

    public function getRendementConseille() {
        return $this->getRendementByKey('rendement_conseille');
    }

    public function getRendementDR() {
        return $this->getRendementByKey('rendement_dr');
    }

    public function getRendementNoeud() {

        return -1;
    }

    public function getRendementAppellation() {

        return $this->getRendementByKey('rendement_appellation');
    }

    public function getRendementCouleur() {

        return $this->getRendementByKey('rendement_couleur');
    }

    public function getRendementCepage() {

        return $this->getRendementByKey('rendement');
    }

    public function hasRendementAppellation() {

        return $this->hasRendementByKey('rendement_appellation');
    }

    public function hasRendementCouleur() {

        return $this->hasRendementByKey('rendement_couleur');
    }

    public function hasRendement() {

        return $this->hasRendementByKey('rendement');
    }

    public function existRendementAppellation() {

        return $this->existRendementByKey('rendement_appellation');
    }

    public function existRendementCouleur() {

        return $this->existRendementByKey('rendement_couleur');
    }

    public function existRendementCepage() {

        return $this->existRendementByKey('rendement');
    }

    public function existRendement() {

        return $this->existRendementCepage() || $this->existRendementCouleur() || $this->existRendementAppellation();
    }

    public function existRendementVci() {

        return $this->existRendementByKey('rendement_vci');
    }

    public function getRendementVci() {
        return $this->getRendementByKey('rendement_vci');
    }

    public function getRendementVciTotal() {
        return $this->getRendementByKey('rendement_vci_total');
    }

    public function hasRendementReserveInterpro() {
        return $this->hasRendementByKey('rendement_reserve_interpro');
    }

    public function getRendementReserveInterpro() {
        return $this->getRendementByKey('rendement_reserve_interpro');
    }

    public function hasRendementReserveInterproMin() {
        return $this->hasRendementByKey('rendement_reserve_interpro_min');
    }


    public function getRendementReserveInterproMin() {
        return $this->getRendementByKey('rendement_reserve_interpro_min');
    }

    public function isActif()
    {
    	return ($this->getRendement() <= 0 || $this->getRendementVci() == -1 || $this->getRendementVciTotal() == -1)? false : true;
    }

    public function hasRendementVci() {

        return $this->hasRendementByKey('rendement_vci');
    }

    public function hasRendementVciTotal() {

        return $this->hasRendementByKey('rendement_vci_total');
    }

    public function hasRendementNoeud() {
        $r = $this->getRendementNoeud();

        return ($r && $r > 0);
    }

    public function existRendementByKey($key) {

        return $this->store('exist_rendement_by_key_'.$key, array($this, 'existRendementByKeyStorable'), array($key));
    }

    protected function existRendementByKeyStorable($key) {
      if($this->hasRendementByKey($key)) {

        return true;
      }

      foreach($this->getChildrenNode() as $noeud) {
        if($noeud->existRendementByKey($key)) {

          return true;
        }
      }

      return false;
    }

    protected function getRendementByKey($key) {

        return $this->store('rendement_by_key_'.$key, array($this, 'findRendementByKeyStorable'), array($key));
    }

    protected function findRendementByKeyStorable($key) {
        if ($this->exist('attributs') && $this->attributs->exist($key) && $this->attributs->_get($key) !== null) {

            return $this->attributs->_get($key);
        }

        return $this->getParentNode()->findRendementByKeyStorable($key);
    }

    protected function hasRendementByKey($key) {
        $r = $this->get($key);

        return ($r && $r > 0);
    }

    public function hasMout() {
        if ($this->exist('attributs') && $this->attributs->exist('mout')) {

            return ($this->attributs->mout == 1);
        }

        return $this->getParentNode()->hasMout();
    }

    public function excludeTotal()
    {
        return ($this->exist('attributs') && $this->attributs->exist('exclude_total') && $this->attributs->get('exclude_total'));
    }

    public function hasTotalCepage() {

      return $this->store('has_total_cepage', array($this, 'hasTotalCepageStorable'));
    }

    protected function hasTotalCepageStorable() {

      if ($this->exist('attributs') && $this->attributs->exist('no_total_cepage')) {

          return !($this->attributs->no_total_cepage == 1);
      }

      if ($this->exist('attributs') && $this->attributs->exist('min_quantite') && $this->attributs->get('min_quantite')) {

          return false;
      }

      return $this->getParentNode()->hasTotalCepage();
    }

    public function hasVtsgn() {
        if ($this->exist('attributs') && $this->attributs->exist('no_vtsgn')) {
            return (!$this->attributs->get('no_vtsgn'));
        }


        if ($this->exist('attributs') && $this->exist('min_quantite') && $this->get('min_quantite')) {
            return false;
        }

        return $this->getParentNode()->hasVtsgn();
    }

    public function isForDR() {

        if(!$this->exist('attributs') || !$this->attributs->exist('no_dr') || !$this->attributs->get('no_dr')) {

            return true;
        }

        return false;
    }

    public function isForDS() {
        if(!$this->exist('attributs') || !$this->attributs->exist('no_ds') || !$this->attributs->get('no_ds')) {

            return true;
        }

        return false;
    }

    public function isAutoDs() {
        if($this->exist('attributs') && $this->attributs->exist('auto_ds') && $this->attributs->get('auto_ds')) {

            return true;
        }

        return $this->getParentNode()->isAutoDs();
    }

    public function hasCepageRB() {
        foreach($this->getProduits() as $produit) {
            if($produit->getKey() == "RB") {
                return true;
            }
        }

        return false;
    }

        public function getMentions() {
        $mentions = array();

        foreach($this->getChildrenNode() as $item) {
            $mentionsItems = $item->getMentions();
            foreach($mentionsItems as $mentionItem) {
                $mentions[$mentionItem->getHash()] = $mentionItem;
            }
        }

        return $mentions;
    }

    public function getLieux() {
        $lieux = array();

        foreach($this->getChildrenNode() as $item) {
            $lieuxItems = $item->getLieux();
            foreach($lieuxItems as $lieuxItem) {
                $lieux[$lieuxItem->getHash()] = $lieuxItem;
            }
        }

        return $lieux;
    }

    public function getCouleurs() {
        $couleurs = array();

        foreach($this->getChildrenNode() as $item) {
            $couleursItems = $item->getCouleurs();
            foreach($couleursItems as $couleursItem) {
                $couleurs[$couleursItem->getHash()] = $couleursItem;
            }
        }

        return $couleurs;
    }

    public function hasLieuEditable() {

        return $this->getAppellation()->hasLieuEditable();
    }

    public function getCahierDesCharges() {

        return $this->getAppellation()->getCahierDesCharges();
    }

    public function hasManyLieu() {
        foreach($this->getChildrenNode() as $item) {
            if($item->hasManyLieu()) {

                return true;
            }
        }
        return false;
    }

    public function hasManyCouleur() {

        return count($this->getCouleurs()) > 1;
    }

    public function hasManyNoeuds() {

        return count($this->getChildrenNode()) > 1;
    }

    public function isSuperficieRequired() {

        return true;
    }

    public function canHaveVci() {

        return true;
    }

    public function isEffervescentNode(){
      return (strpos($this->getHash(),ConfigurationAppellation::TYPE_NOEUD) !== false && $this->getGenre()->getKey() == "EFF");
    }

    public function getMout() {
        if(!$this->exist('attributs') ||  !$this->attributs->exist('mout')) {
            return 0;
        }

        return $this->attributs->get('mout');
    }

    /**** DR ****/

    public function isRevendicationParLots() {
        foreach($this->getProduits() as $produit) {
            if($produit->isRevendicationParLots()) {

                return true;
            }
        }

        return false;
    }

    public function isRevendicationAOC() {
        foreach($this->getProduits() as $produit) {
            if($produit->isRevendicationAOC()) {

                return true;
            }
        }

        return false;
    }

    public function getCepagesAutorises() {
        $produits = array();
        foreach($this->getProduits() as $p) {
            $produits = array_merge($produits, $p->getCepage()->getCepagesAutorises()->toArray());
        }
        $produits = array_unique($produits);
        return $produits;
    }

}
