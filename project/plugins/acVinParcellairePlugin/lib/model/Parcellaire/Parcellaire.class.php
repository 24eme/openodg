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

    public function getParcellaire() {

        return $this;
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

    public function affecteParcelleToHashProduit($hash, $parcelle) {
        $p = $this->addProduit($hash);
        if (!$p) {
            return null;
        }
        return $p->affecteParcelle($parcelle);
    }

    public function addProduit($hash) {
        if (!$hash) {
            return;
        }
        $hashToAdd = preg_replace("|/declaration/|", '', $hash);
        $exist = $this->exist('declaration/'.$hashToAdd);

        $produit = $this->add('declaration')->add($hashToAdd);
        if(!$exist) {
            $this->declaration->reorderByConf();
            $this->add('declaration')->add($hashToAdd)->libelle = $produit->getConfig()->getLibelleComplet();
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
        if ($this->exist('parcelles')) {
            $p = $this->_get('parcelles');
            if (count($p)) {
                return $this->_get('parcelles');
            }
        }
        foreach($this->declaration->getParcelles() as $dp) {
            $id = $dp->getParcelleId();
            if (!$this->exist('parcelles') || !$this->_get('parcelles')) {
                $this->add('parcelles', null);
            }
            $p = $this->_get('parcelles')->add($id);
            $dp->produit_hash = preg_replace('/\/detail\/.*/', '', $dp->getHash());
            ParcellaireClient::CopyParcelle($p, $dp);
        }
        return $this->_get('parcelles');
    }

    public function getParcellesByCommune($only_affectee = false) {
        if ($only_affectee) {
            return $this->declaration->getParcellesByCommune();
        }
        return ParcellaireClient::organizeParcellesByCommune($this->getParcelles());
    }

    public function getNextParcelleId($idu, $cepage, $campagne_plantation, $produit = null, $formatNumeroOrdre = "%02d") {
        if (!$idu) {
            throw new sfException('Empty idu not allowed');
        }
        $pid = sprintf('%s-'.$formatNumeroOrdre, $idu, 0);
        if (
            !$this->exist('parcelles') ||
            !count($this->_get('parcelles')->toArray()) ||
            !$this->parcelles->exist($pid)
        ) {
            return $pid;
        }
        for ($i = 1 ; $i < 100 ; $i++) {
            $pid = sprintf('%s-'.$formatNumeroOrdre, $idu, $i);
            if (!$this->parcelles->exist($pid)) {
                return $pid;
            }
        }
        throw new sfException('pid not found for '.$idu);
    }

    public function addParcelle($idu, $source_produit_libelle, $cepage, $campagne_plantation, $commune, $lieu = null, $produit = null, $formatNumeroOrdre = "%02d") {
        $pid = $this->getNextParcelleId($idu, $cepage, $campagne_plantation, $produit, $formatNumeroOrdre);
        $p = $this->parcelles->add($pid);
        $p->idu = $idu;
        $p->add('parcelle_id', $pid);
        $p->cepage = $cepage;
        $p->campagne_plantation = $campagne_plantation;
        $p->commune = $commune;
        $p->source_produit_libelle = $source_produit_libelle;
        if($lieu){
            $lieu = strtoupper($lieu);
            $lieu = trim($lieu);
            $p->lieu = $lieu;
        }
        $p->numero_ordre = explode('-', $pid)[1];
        if (!$produit) {
            $produit = $this->getConfiguration()->identifyProductByLibelle($source_produit_libelle);
        }
        if ($produit) {
            $p->produit_hash = $produit->getHash();
        }
        ParcellaireClient::parcelleSplitIDU($p);

        return $p;
    }

    public function addParcelleWithProduit($hashProduit, $source_produit_libelle, $cepage, $campagne_plantation, $commune, $prefix, $section, $numero_parcelle, $lieu = null) {
        if ($lieu && preg_match('/[0-9]/', $lieu) && !preg_match('/ /', $lieu)) {
            throw new sfException('Strange lieu '.$lieu);
        }
        $produit = $this->addProduit($hashProduit);
        if (preg_match('/^[0-9]+$/', $commune)) {
            $code_commune = $commune;
            $commune = CommunesConfiguration::getInstance()->getCommuneByCode($code_commune);
        }else {
            $code_commune = CommunesConfiguration::getInstance()->findCodeCommune($commune);
        }
        if (!intval($code_commune)) {
            throw new sfException('Wrong code commune : '.$code_commune.'/'.$commune);
        }
        $idu = $this->computeIDU($code_commune, $prefix, $section, $numero_parcelle);
        $parcelle  = $this->addParcelle($idu, $source_produit_libelle, $cepage, $campagne_plantation, $commune, $lieu, $produit->getConfig()->getLibelle());
        return $produit->affecteParcelle($parcelle);
    }

    public function computeIDU($code_commune, $prefix, $section, $numero_parcelle) {
        if (!intval($code_commune)) {
            throw new sfException('Wrong code commune : '.$code_commune);
        }
        if (!intval($numero_parcelle)) {
            throw new sfException('Wrong numero parcelle : '.$numero_parcelle);
        }
        return sprintf('%05s%03s%02s%04s', $code_commune, $prefix, $section, $numero_parcelle);
    }

    public function getParcelleFromParcellaireId($id) {
        if (!$id) {
            throw new sfException('id needed');
        }
        if (!count($this->parcelles)) {
            return null;
        }
        if(!isset($this->parcelles[$id])) {
            return null;
        }
        return $this->parcelles[$id];
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

    public function findParcelle($parcelle, $scoreMin = 1, &$allready_selected = null) {

        return ParcellaireClient::findParcelle($this, $parcelle, $scoreMin, $allready_selected);
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
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
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
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                $libelles[] = 'XXXXjeunes vignes';
                $cepage = 'XXXXjeunes vignes';
            }
            sort($libelles);
            foreach($libelles as $libelle) {
                if (!isset($synthese[$libelle])) {
                    $synthese[$libelle] = array();
                    $synthese[$libelle]['Total'] = array();
                    $synthese[$libelle]['Total']['Total'] = array();
                    $synthese[$libelle]['Total']['Total']['superficie_min'] = 0;
                    $synthese[$libelle]['Total']['Total']['superficie_max'] = 0;
                }
                if (!isset($synthese[$libelle]['Cepage'])) {
                    $synthese[$libelle]['Cepage'] = array();
                }
                if (!isset($synthese[$libelle]['Cepage'][$cepage])) {
                    $synthese[$libelle]['Cepage'][$cepage] = array();
                    $synthese[$libelle]['Cepage'][$cepage]['superficie_min'] = 0;
                    $synthese[$libelle]['Cepage'][$cepage]['superficie_max'] = 0;
                }
                if (count($libelles) == 1) {
                    $synthese[$libelle]['Cepage'][$cepage]['superficie_min'] = round($synthese[$libelle]['Cepage'][$cepage]['superficie_min'] + $p->superficie, 6);
                    $synthese[$libelle]['Total']['Total']['superficie_min'] = round($synthese[$libelle]['Total']['Total']['superficie_min'] + $p->superficie, 6);
                }
                $synthese[$libelle]['Cepage'][$cepage]['superficie_max'] = round($synthese[$libelle]['Cepage'][$cepage]['superficie_max'] + $p->superficie, 6);
                $synthese[$libelle]['Total']['Total']['superficie_max'] = round($synthese[$libelle]['Total']['Total']['superficie_max'] + $p->superficie, 6);
                ksort($synthese);
            }
        }

        foreach ($synthese as $libelle => &$cepagetotal) {
            ksort($cepagetotal);
            foreach($cepagetotal as $l => &$cepages) {
                ksort($cepages);
            }
            if (count($cepagetotal['Cepage']) < 2) {
                unset($cepagetotal['Total']);
            }
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

    public function getParcellaireFileKey($type) {
        foreach ($this->_attachments as $key => $attachement) {
            if (strpos($attachement->content_type, $type) !== false) {
                return $key;
            }
        }
        return null;
    }

    public function getParcellaireFileUri($type) {
        $key = $this->getParcellaireFileKey($type);

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
        return ($this->getParcellaireFileUri('pdf'));
    }

    public function hasParcellaireCSV() {
        return ($this->getParcellaireFileUri('csv'));
    }

    public function getParcellairePDF() {
        if ($this->hasParcellairePDF()) {
            return file_get_contents($this->getParcellaireFileUri('pdf'));
        }
        return null;
    }

    public function getParcellaireCSV() {
        if ($this->hasParcellaireCSV()) {
            return file_get_contents($this->getParcellaireFileUri('csv'));
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
