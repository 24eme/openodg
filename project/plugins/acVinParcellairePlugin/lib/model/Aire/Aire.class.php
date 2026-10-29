<?php
/**
 * Model for Aire
 *
 */

class Aire extends BaseAire {

    private $need_tobe_saved = null;
    private $geoparcelle = null;

    public function getGeoJson() {
        return trim($this->_get('geojson'));
    }

    public function setPseudoGeojsons(array $geojsons) {
        $this->_set('geojson', implode('|', $geojsons));
        $this->need_tobe_saved = -1;
    }

    public function getPseudoGeojsons() {
        return explode('|', $this->geojson);
    }

    public function setGeojson($geojson) {
        $geojson = trim($geojson);
        $geo = json_decode($geojson);
        $conf = ParcellaireConfiguration::getInstance()->getAireInfoFromDenominationId($geo->features[0]->properties->id_denom);
        if (!$conf) {
            throw new sfException('Denomination '.$geo->features[0]->properties->id_denom.' ('.$geo->features[0]->properties->denom.') not in conf');
        }
        $denom = $conf['name'];
        $commune = $geo->features[0]->properties->nomcom;
        $md5 = md5($geojson.$commune.$denom);
        if ($md5 == $this->md5) {
            return ;
        }
        $this->commune_libelle = $commune;
        $this->denomination_libelle = $denom;
        $this->_set('geojson', $geojson);
        $this->md5 = $md5;
        $this->need_tobe_saved = true;
    }

    public function initDoc($commune_insee, $inao_denomination_id, $type = AireClient::TYPE_COUCHDB) {
        $this->commune_identifiant = $commune_insee;
        $this->denomination_identifiant = $inao_denomination_id;
        $this->updateImportDate();
        $this->set('_id', AireClient::getInstance()->constructionId($commune_insee, $inao_denomination_id));
    }

    public function updateImportDate() {
        $this->date_import = date('Y-m-d');
    }

    public function save() {
        if ($this->need_tobe_saved < 0) {
            throw new sfException('Pseudo Aire cannont be saved');
        }
        if (!$this->need_tobe_saved && $this->_id) {
            return;
        }
        parent::save();
    }

    public function getColor() {
        $conf = ParcellaireConfiguration::getInstance()->getAireInfoFromDenominationId($this->denomination_identifiant);
        if (!isset($conf['color'])) {
            return '#000000';
        }
        return $conf['color'];
    }

    public function getName() {
        return addslashes($this->denomination_libelle.' '.$this->commune_libelle);
    }

    public function getGeoParcelle(): Geometry {
        if (!$this->geoparcelle) {
            if (!geophp::geosInstalled()) {
                throw new sfException("php-geos needed");
            }
            $this->geoparcelle = geoPHP::load($this->geojson);
        }
        return $this->geoparcelle;
    }

    public function getPcInAire(Geometry $geoparcelle) {
        $pc = 0;
        if ($geoparcelle->area()) {
            $pc = $this->getGeoParcelle()->intersection($geoparcelle)->area() / $geoparcelle->area();
        }
        return $pc;
    }

    public function isInAire(Geometry $geoparcelle) {
        $pc = $this->getPcInAire($geoparcelle);
        if ($pc > 0.99) {
            return AireClient::PARCELLAIRE_AIRE_TOTALEMENT;
        }
        if ($pc > 0.01) {
            return AireClient::PARCELLAIRE_AIRE_PARTIELLEMENT;
        }
        return AireClient::PARCELLAIRE_AIRE_HORSDELAIRE;
    }

}
