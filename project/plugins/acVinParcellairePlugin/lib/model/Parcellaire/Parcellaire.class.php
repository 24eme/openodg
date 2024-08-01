<?php

require_once(dirname(__FILE__).'/../../vendor/geoPHP/geoPHP.inc');
require_once(dirname(__FILE__).'/../../vendor/Simplex-Calculator/Simplex/simplex.php');

/**
 * Model for Parcellaire
 *
 */
class Parcellaire extends BaseParcellaire {

    protected $declarant_document = null;
    protected $piece_document = null;
    protected $cache_produitsbycepagefromhabilitationorconfiguration = null;
    protected $habilitation = false;
    protected $parcelles_idu = null;
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
        $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
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
                $this->add('declaration')->add($hashToAdd)->libelle = $produit->getConfig()->getLibelleComplet();
            }else{
                $this->add('declaration')->add($hashToAdd)->libelle = ParcellaireClient::PARCELLAIRE_DEFAUT_PRODUIT_LIBELLE;
            }
          }

        return $this->get($produit->getHash());

  }
    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduits();
    }

    public function getDeclarationParcelles() {
        $parcelles = [];
        foreach($this->declaration->getParcelles() as $k => $p) {
            $parcelles[$p->getParcelleId()] = $p;
        }
        return $parcelles;
    }

    private $idunumbers = null;
    public function getNbUDIAlreadySeen($idu) {
        if (!$this->idunumbers) {
            $this->idunumbers = [];
        }
        if (!isset($this->idunumbers[$idu])) {
            $this->idunumbers[$idu] = 0;
        }
        return $this->idunumbers[$idu]++;
    }

    public function getParcelles() {

        return $this->declaration->getParcelles();
    }

    public function addParcelle($hashProduit, $cepage, $campagne_plantation, $commune, $prefix, $section, $numero_parcelle, $lieu = null, $numero_ordre = null, $strictNumOrdre = false) {
        $produit = $this->addProduit($hashProduit);
        return $produit->addParcelle($cepage, $campagne_plantation, $commune, $prefix, $section, $numero_parcelle, $lieu, $numero_ordre, $strictNumOrdre);
    }

    public function countSameParcelle($commune, $prefix, $section, $numero_parcelle, $lieu, $hashProduit = null, $cepage = null, $campagne_plantation = null){
        $sameParcelle = 0;

        foreach ($this->getParcelles() as $parcelleExistante) {
            if ($parcelleExistante->section !== preg_replace('/^0*/', '', $section)) {
                continue;
            }

            if ($parcelleExistante->numero_parcelle !== $numero_parcelle) {
                continue;
            }

            if (KeyInflector::slugify($parcelleExistante->lieu) !== KeyInflector::slugify($lieu)) {
                continue;
            }

            if (KeyInflector::slugify($parcelleExistante->commune) !== KeyInflector::slugify($commune)) {
                continue;
            }

            $sameParcelle++;
        }

        return $sameParcelle;

    }

    public function getParcellesByIdu() {
        if(is_array($this->parcelles_idu)) {

            return $this->parcelles_idu;
        }

        $this->parcelles_idu = [];

        foreach($this->getParcelles() as $parcelle) {
            $this->parcelles_idu[$parcelle->idu][] = $parcelle;
        }

        return $this->parcelles_idu;
    }

    public function findParcelle($parcelle) {

        return ParcellaireClient::findParcelle($this, $parcelle);
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

    public function getCachedProduitsByCepageFromHabilitationOrConfiguration($cepage) {
        return $this->getProduitsByCepageFromHabilitationOrConfiguration($cepage);
            if (!$this->cache_produitsbycepagefromhabilitationorconfiguration) {
                $this->cache_produitsbycepagefromhabilitationorconfiguration = array();
            }
            if(!isset($this->cache_produitsbycepagefromhabilitationorconfiguration[$cepage])) {
                $this->cache_produitsbycepagefromhabilitationorconfiguration[$cepage] = $this->getProduitsByCepageFromHabilitationOrConfiguration($cepage);
            }
            return $this->cache_produitsbycepagefromhabilitationorconfiguration[$cepage];
    }

    public function getProduitsByCepageFromHabilitationOrConfiguration($cepage) {
        if ($this->habilitation === false) {
            $this->habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->getDate());
        }
        if (!$this->habilitation) {
            return $this->getConfiguration()->getProduitsByCepage($cepage);
        }
        return $this->habilitation->getProduitsByCepage($cepage);
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
        ksort($synthese);
        return $synthese;
    }

    public function getSyntheseProduitsCepages() {
        $synthese = array();
        foreach($this->getParcelles() as $p) {
            $cepage = $p->getCepage();
            $libelles = array();
            foreach($this->getCachedProduitsByCepageFromHabilitationOrConfiguration($cepage) as $prod) {
                $libelles[] = $prod->getLibelleComplet();
            }
            if (!count($libelles)) {
                $libelles[] = '';
            }
            if (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$p->hasTroisiemeFeuille()) {
                $libelles = array('jeunes vignes');
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

        foreach ($synthese as $libelle => &$cepages) {
            uksort($cepages, function ($cepage1, $cepage2) {
                if ($cepage1 === "Total") {
                    return -1;
                }
                if ($cepage2 === "Total") {
                    return 1;
                }
                return strcmp($cepage1, $cepage2);
            });
        }
        return $synthese;
    }

    public function getSuperficieTotale($avec_jv = true) {
        $superficie = 0;
        foreach ($this->getSyntheseCepages() as $nom => $cepage) {
            // Ajoute les jeunes vignes seulement si $avec_jv
            if ($avec_jv || ! strpos($nom, ' - jeunes vignes')) {
                $superficie += $cepage['superficie'];
            }
        }
        return $superficie;
    }

    public function getSuperficieCadastraleTotale() {
        $superficie = 0;
        foreach ($this->declaration as $declaration) {
            foreach ($declaration->detail as $detail) {
                $superficie += $detail->superficie_cadastrale;
            }
        }
        return $superficie;
    }

    public function getParcellairePDFKey() {
        foreach ($this->_attachments as $key => $attachement) {
            if ($attachement->content_type == 'application/pdf') {
                return $key;
            }
        }
        return null;
    }

    public function getParcellairePDFUri() {
        $key = $this->getParcellairePDFKey();

        if(!$key) {

            return null;
        }

        return $this->getAttachmentUri($key);
    }

    public function getParcellairePDFMd5() {
        if (!$this->hasParcellairePDF()) {

            return null;
        }

        return md5($this->getParcellairePDF());
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
            $this->cache_geojson = array();
        }else{
            $this->cache_geojson = json_decode($import);
        }
        return $this->cache_geojson;

    }

    public function getGeoJsonWithAires(){
        $geojson = $this->getGeoJson();

        // Ajoute des couleurs et l'identification
        foreach ($geojson->features as $feat) {
            $feat->properties->stroke = '#FF0000';
            $feat->properties->{'stroke-width'} = 4;
            $feat->properties->{'stroke-opacity'} = 1;
            $feat->properties->fill = '#fff';
            $feat->properties->{'fill-opacity'} = 0;
            $feat->properties->name = $feat->properties->section. ' ' . $feat->properties->numero;
            foreach ($feat->properties->parcellaires as $key => $parcellaire_detail) {
                $feat->properties->{'parcellaire'.$key} = '';
                foreach (["Commune","Lieu dit","Produit","Cepage","Superficie","Superficie cadastrale","Campagne","Ecart pied","Ecart rang","Mode savoir faire"] as $prop) {
                    if ($prop == "Lieu dit" && ! $parcellaire_detail->{$prop}) {
                        continue;
                    }
                    $feat->properties->{'parcellaire'.$key} .= $prop . ' : ' . $parcellaire_detail->{$prop} . " / \n";
                }
            }
        }

        // On ajoute les aires des appelations des communes associées avec la bonne couleur
        foreach ($this->getMergedAires() as $aire) {
            $aireobj = json_decode($aire->getGeojson());
            foreach ($aireobj->features as $feat) {
                // Ajoute les couleurs et infos qui vont bien
                $feat->properties->name = $aire->getName();
                $feat->properties->fill = $aire->getColor();
                $feat->properties->{'fill-opacity'} = 0.5;
                $feat->properties->stroke = '#000';
                $feat->properties->{'stroke-width'} = 2;
                $feat->properties->{'stroke-opacity'} = 0.1;
                // Ajoute l'aire au début du tableau, les parcelles doivent être au dessus pour être plus facilement clickables.
                array_unshift($geojson->features, $feat);
            }
        }
        return $geojson;
    }

    /**
     * Reprend le geojson et le transforme en KML
     */
    public function getKML($with_aire = true, $with_parcelles = true) {
        // La transfo de geojson -> kml de geophp ne se faisant que partiellement on reparcours le geojson et on reconstruit le xml
        $kml = '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document>';

        // Le style pour les parcelles (les couleurs)
        // Info : L'hexa de la couleur est inversé par rapport à la notation habituelle
        // aabbggrr, où aa=alpha (00 à ff) ; bb=blue (00 à ff) ; gg=green(00 à ff) ; rr=red (00 à ff).
        $kml .= '<Style id="parcelle-style">
        <LineStyle>
          <width>2</width>
        </LineStyle>
        <PolyStyle>
          <color>7d0000ff</color>
        </PolyStyle>
      </Style>';

        // Définit un style par couleur à utiliser dans les aires plus bas
        $styles = [];
        if ($with_aire) {
            foreach ($this->getMergedAires() as $aire) {
                $aireobj = json_decode($aire->getGeojson());
                if (isset($aireobj->features)) foreach ($aireobj->features as $feat) {
                    $color = '7d' . str_replace('#', '', $aire->getColor());
                    $styles[$color] = '<Style id="aire-style-'.$color.'">
            <LineStyle>
            <width>1</width>
            </LineStyle>
            <PolyStyle>
            <color>'.$color.'</color>
            </PolyStyle>
        </Style>';
                }
            }

            // Met les styles en haut du KML
            $kml .= implode("\n", $styles);


            // On met en premier les aires des appelations des communes associées avec la bonne couleur
            foreach ($this->getMergedAires() as $aire) {
                foreach ($aire->getPseudoGeojsons() as $geojson) {
                    $aireobj = json_decode($geojson);
                    foreach ($aireobj->features as $feat) {
                        $feat_str = json_encode($feat);
                        $feat_obj = GeoPHP::load($feat_str, 'geojson');

                        $kml .= '<Placemark>';
                        $kml .= '<name>'. $aire->getName() .'</name>';
                        $kml .= '<styleUrl>#aire-style-7d' . str_replace('#', '', $aire->getColor()) . '</styleUrl>';
                        $kml .= $feat_obj->out('kml');
                        $kml .= '</Placemark>'."\n";
                    }
                }
            }
        }

        if ($with_parcelles) {

            $geojson = $this->getGeoJson();

            // Met ensuite les parcelles par dessus les éventuelles aires.
            foreach ($geojson->features as $feat) {
                $feat_str = json_encode($feat);
                $feat_obj = GeoPHP::load($feat_str, 'geojson');

                $kml .= '<Placemark>';
                $kml .= '<name>'.$feat->properties->commune. ' - ' .$feat->properties->section. ' ' . $feat->properties->numero.'</name>';
                $kml .= '<description><![CDATA[';
                foreach ($feat->properties->parcellaires as $key => $parcellaire_detail) {
                    foreach (["Commune","Lieu dit","Produit","Cepage","Superficie","Superficie cadastrale","Campagne","Ecart pied","Ecart rang","Mode savoir faire"] as $prop) {
                        if ($prop == "Lieu dit" && ! $parcellaire_detail->{$prop}) {
                            continue;
                        }
                        $kml .= '<p>' . $prop . ' : ' . $parcellaire_detail->{$prop} . '</p>';
                    }

                    if ($key !== array_key_last($feat->properties->parcellaires)) {
                        $kml .= "<p>-----------------</p>";
                    }
                }
                $kml .= ']]></description>';
                $kml .= '<styleUrl>#parcelle-style</styleUrl>';
                $kml .= $feat_obj->out('kml');
                $kml .= '</Placemark>';
            }
        }

        $kml .= '</Document></kml>';
        return $kml;
    }

    public function getMergedAires() {

        return AireClient::getInstance()->getMergedAiresForInseeCommunes($this->declaration->getCommunes());
    }

}
