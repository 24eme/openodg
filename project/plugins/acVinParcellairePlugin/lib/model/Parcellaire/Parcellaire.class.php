<?php

require_once(dirname(__FILE__).'/../../vendor/geoPHP/geoPHP.inc');
/**
 * Model for Parcellaire
 *
 */
class Parcellaire extends BaseParcellaire {

    protected $declarant_document = null;
    protected $piece_document = null;
    private $cache_geophpdelimitation = null;
    private $cache_geojson = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
        $this->piece_document = new PieceDocument($this);
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function initDoc($identifiant, $date, $type = ParcellaireClient::TYPE_COUCHDB) {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $this->set('_id', ParcellaireClient::TYPE_COUCHDB."-".$identifiant."-".str_replace('-', '', $date));
        $this->storeDeclarant();
    }

    public function getConfiguration() {

        return ConfigurationClient::getInstance()->getConfiguration($this->date);
    }

    public function addProduit($hash) {
        $pseudo_produit = false;
        if (!$hash && !ParcellaireConfiguration::getInstance()->getLimitProduitsConfiguration()) {
            $hash = ParcellaireClient::PARCELLAIRE_DEFAUT_PRODUIT_HASH;
            $pseudo_produit = true;
        }
        $hashToAdd = preg_replace("|/declaration/|", '', $hash);
        $exist = $this->exist('declaration/'.$hashToAdd);

        $produit = $this->add('declaration')->add($hashToAdd);
        if(!$exist) {
            $this->declaration->reorderByConf($pseudo_produit);
            if ($pseudo_produit && ParcellaireConfiguration::getInstance()->getLimitProduitsConfiguration())  {
                throw new sfException("produit $hash non trouvé et ajout de parcelle sans produit non disponible pour cette app");
            }
            if (!$pseudo_produit) {
                $this->add('declaration')->get($hashToAdd)->libelle = $produit->getConfig()->getLibelleComplet();
            }else{
                $this->add('declaration')->add($hashToAdd)->libelle = ParcellaireClient::PARCELLAIRE_DEFAUT_PRODUIT_LIBELLE;
            }
          }

        return $this->get($produit->getHash());

  }
    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduits();
    }

    public function getParcelles($onlyVtSgn = false, $active = false) {

        return $this->declaration->getParcelles($onlyVtSgn, $active);
    }

    public function addParcelle($hashProduit, $cepage, $campagne_plantation, $commune, $section, $numero_parcelle, $lieu = null, $numero_ordre = null, $strictNumOrdre = false) {
        $produit = $this->addProduit($hashProduit);
        return $produit->addParcelle($cepage, $campagne_plantation, $commune, $section, $numero_parcelle, $lieu, $numero_ordre, $strictNumOrdre);
    }

    public function countSameParcelle($commune, $section, $numero_parcelle, $lieu, $hashProduit = null, $cepage = null, $campagne_plantation = null){
        $sameParcelle = 0;

        foreach ($this->declaration as $produitKey => $produitParcelles) {
          foreach ($produitParcelles->detail as $pKey => $parcelleExists) {
            if(($parcelleExists->commune == $commune) &&
                ($parcelleExists->section == $section) &&
                ($parcelleExists->numero_parcelle == $numero_parcelle) &&
                ($parcelleExists->lieu == $lieu)){
                  $sameParcelle++;
                }
          }
        }
        return $sameParcelle;

    }

    public function getDateFr() {

        $date = new DateTime($this->date);

        return $date->format('d/m/Y');
    }

    protected function doSave() {
        $this->piece_document->generatePieces();
    }

    /*** PIECE DOCUMENT ***/

    public function getAllPieces() {

        return array(
            array(
            'identifiant' => $this->getIdentifiant(),
            'date_depot' => $this->date,
            'libelle' => 'Parcellaire au '.$this->getDateFr(),
            'mime' => null,
            'visibilite' => 1,
            'source' => $this->source,
            )
        );
    }

    public function generatePieces() {
        return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
        return sfContext::getInstance()->getRouting()->generate('parcellaire_visualisation', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
        return sfContext::getInstance()->getRouting()->generate('parcellaire_visualisation', array('id' => $id));
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
        return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
        return true;
    }

    public static function isPieceEditable($admin = false) {
        return false;
    }

    public function getProduitsByCepageFromHabilitationOrConfiguration($cepage) {
        $hab = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->getDate());
        if (!$hab) {
            return $this->getConfiguration()->getProduitsByCepage($cepage);
        }
        return $hab->getProduitsByCepage($cepage);
    }

    public function getSyntheseCepages() {
        $synthese = array();
        foreach($this->getParcelles() as $p) {
            $cepage = $p->getCepage();
            if (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$p->hasTroisiemeFeuille()) {
                $cepage .= ' - jeunes vignes';
            }
            if (!isset($synthese[$cepage])) {
                $synthese[$cepage] = array();
                $synthese[$cepage]['superficie'] = 0;
            }
            $synthese[$cepage]['superficie'] = round($synthese[$cepage]['superficie'] + $p->superficie, 6);
        }
        return $synthese;
    }

    public function getSyntheseProduitsCepages() {
        $synthese = array();
        foreach($this->getParcelles() as $p) {
            $libelles = array($p->getProduitLibelle());
            $cepage = $p->getCepage();
            if (!ParcellaireConfiguration::getInstance()->getLimitProduitsConfiguration()) {
                $libelles = array();
                foreach($this->getProduitsByCepageFromHabilitationOrConfiguration($cepage) as $prod) {
                    $libelles[] = $prod->getLibelleComplet();
                }
                if (!count($libelles)) {
                    $libelles[] = '';
                }
                if (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$p->hasTroisiemeFeuille()) {
                    $libelles = array('jeunes vignes');
                }
            }
            foreach($libelles as $libelle) {
                if (!isset($synthese[$libelle])) {
                    $synthese[$libelle] = array();
                    $synthese[$libelle]['Total'] = array();
                    $synthese[$libelle]['Total']['superficie_min'] = 0;
                    $synthese[$libelle]['Total']['superficie_max'] = 0;
                }
                if (!isset($synthese[$libelle][$cepage])) {
                    $synthese[$libelle][$cepage] = array();
                    $synthese[$libelle][$cepage]['superficie_min'] = 0;
                    $synthese[$libelle][$cepage]['superficie_max'] = 0;
                }
                if (count($libelles) == 1) {
                    $synthese[$libelle][$cepage]['superficie_min'] = round($synthese[$libelle][$cepage]['superficie_min'] + $p->superficie, 6);
                    $synthese[$libelle]['Total']['superficie_min'] = round($synthese[$libelle]['Total']['superficie_min'] + $p->superficie, 6);
                }
                $synthese[$libelle][$cepage]['superficie_max'] = round($synthese[$libelle][$cepage]['superficie_max'] + $p->superficie, 6);
                $synthese[$libelle]['Total']['superficie_max'] = round($synthese[$libelle]['Total']['superficie_max'] + $p->superficie, 6);
            }
        }
        return $synthese;
    }

    public function getParcellairePDFUri() {
        foreach ($this->_attachments as $key => $attachement) {
            if ($attachement->content_type == 'application/pdf') {
                return $this->getAttachmentUri($key);
            }
        }
        return '';
    }

    public function hasParcellairePDF() {
        return ($this->getParcellairePDFUri());
    }

    public function getParcellairePDF() {
        if ($this->hasParcellairePDF()) {
            return file_get_contents($this->getParcellairePDFUri());
        }
        return null;
    }

    public function getGeoJson(){
        if ($this->cache_geojson !== null) {
            return $this->cache_geojson;
        }

        $file_name = "import-cadastre-".$this->declarant->cvi."-parcelles.json";
        $uri = $this->getAttachmentUri($file_name);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $import = curl_exec($ch);
        curl_close($ch);

        if(strpos($import, "Document is missing attachment")) {
            sfContext::getInstance()->getLogger()->info("getGeoJson() : Document is missing attachment for ".$this->_id);
            $this->cache_geojson = false;
        }else{
            $this->cache_geojson = json_decode($import);
        }
        return $this->cache_geojson;

    }

    public function getAire($jsonFolder = 'communes') {
        return ParcellaireClient::getInstance()->getAire($this->declaration->getCommunes(), $jsonFolder);
    }

    public function getAires() {

        return ParcellaireClient::getInstance()->getAires($this->declaration->getCommunes());
    }

    public function getGeoPHPDelimitations() {
        if ($this->cache_geophpdelimitation) {
            return $this->cache_geophpdelimitation;
        }
        $this->cache_geophpdelimitation = [];
        foreach ($this->getAire() as $d) {
            $this->cache_geophpdelimitation[] = geoPHP::load($d);
        }
        return $this->cache_geophpdelimitation;
    }

}
