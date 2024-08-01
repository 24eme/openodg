<?php

require_once(dirname(__FILE__).'/../../vendor/geoPHP/geoPHP.inc');

/**
 * Model for ParcellaireCepageDetail
 *
 */
class ParcellaireParcelle extends BaseParcellaireParcelle {
    private static $_AIRES = [];
    private $geoparcelle = null;

    public function getProduit() {

        return $this->document->get(preg_replace('/\/detail\/.*/', '', $this->getHash()));
    }

    public function getConfig() {
      return $this->getProduit()->getConfig();
    }

    public function addAcheteur($acheteur) {

        return $this->getCepage()->addAcheteurFromNode($acheteur, $this->lieu);
    }

    public function getAcheteurs() {

        return $this->getCepage()->getAcheteursNode($this->lieu);
    }

    public function getAcheteursByCVI() {
        $acheteursCvi = array();
        foreach($this->getAcheteurs() as $type => $acheteurs) {
            foreach($acheteurs as $cvi => $acheteur) {
                $acheteursCvi[$cvi] = $acheteur;
            }
        }

        return $acheteursCvi;
    }

    public function getProduitsDetails() {

        return array($this->getHash() => $this);
    }

    public function getLibelleComplet() {

        return $this->getAppellation()->getLibelleComplet().' '.$this->getLieuLibelle().' '.$this->getCepageLibelle();
    }

    public function updateIDU() {
        $this->idu = sprintf('%05s%03s%02s%04s', $this->code_commune, $this->prefix, $this->section, $this->numero_parcelle);
    }

    public function setCodeCommune($code_commune) {
        $this->_set('code_commune', $code_commune);

        $this->updateIDU();

        return $this;
    }

    public function setSection($section) {
        $this->_set('section', preg_replace('/^0*/', '', $section));

        $this->updateIDU();

        return $this;
    }

    public function getSection() {
        return preg_replace('/^0*/', '', $this->_get('section'));
    }

    public function setNumeroParcelle($numero_parcelle) {
        $this->_set('numero_parcelle', $numero_parcelle);

        $this->updateIDU();

        return $this;
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }

    public function setModeSavoirfaire($mode)
    {
      if (!$this->exist('mode_savoirfaire')){
        $this->_add('mode_savoirfaire');
      }
      return $this->_set('mode_savoirfaire',array_search($mode, ParcellaireClient::$modes_savoirfaire));
    }

    public function getParcelleIdentifiant() {
        return sprintf('%s %03s %03s', $this->commune, $this->section, $this->numero_parcelle);
    }

    public function getAppellation() {
        return $this->getProduit()->getConfig()->getAppellation();
    }

    public function getProduitLibelle() {
        if (!$this->isRealProduit()) {
            return ' - PRODUIT NON RECONNU - ';
        }
        return $this->getProduit()->getLibelle();
    }

  /*  public function getCepage() {

        return $this->getProduit()->getConfig()->getCepage();
    }
*/
    public function getCepageLibelle() {

        return $this->getCepage(); //->getLibelle();
    }

    public function getLieuNode() {

        return $this->getProduit()->getConfig()->getLieu();
    }

    public function getIdentificationParcelleLibelle() {
    	return $this->section.'-'.$this->numero_parcelle.'<br />'.$this->commune.' '.$this->getLieuLibelle().' '.sprintf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $this->superficie);
    }

    public function getIdentificationCepageLibelle() {
    	return $this->getProduitLibelle().'<br />'.$this->getCepageLibelle().' '.$this->campagne_plantation;
    }

    public function cleanNode() {

        return false;
    }


    public function getVtsgn() {
        $v = $this->_get('vtsgn');
        if ($v === null || !$this->superficie) {
            return false;
        }
        return ($v) ? true : false;
    }
    public function setVtsgn($value) {
        return $this->_set('vtsgn', $value * 1);
    }

    public function setCampagnePlantation($value)
    {
        $campagne = ($value === '9999-9999') ? "" : $value;
        return $this->_set('campagne_plantation', $campagne);
    }

    public function isFromAppellation($appellation){
        return 'appellation_'.$appellation == $this->getAppellation()->getKey();
    }


    public function hasProblemExpirationCepage() {
      $expirations = sfConfig::get('app_parcellaire_expiration_cepage', null);
      if (is_null($expirations)) {
        return false;
      }
      $slug_cepage = strtolower(KeyInflector::slugify(trim($this->getCepageLibelle())));
      if (isset($expirations[$slug_cepage]) && $expirations[$slug_cepage] < $this->campagne_plantation) {
        return true;
      }
      return false;
    }

    public function hasProblemEcartPieds() {
      $ecart_max = sfConfig::get('app_parcellaire_ecart_pieds_max', null);
      if ($ecart_max && $this->exist('ecart_rang') && $this->exist('ecart_pieds') && $this->ecart_rang && $this->ecart_pieds) {
        return (($this->ecart_rang * $this->ecart_pieds) > $ecart_max);
      }
      return false;
    }

    public function isRealProduit() {
        try {
            return $this->getProduit()->isRealProduit();
        }catch(sfException $e) {
            return false;
        }
    }

    public function hasProblemCepageAutorise() {
      if (!$this->isRealProduit()) {
          return false;
      }
      return (count($this->getConfig()->getCepagesAutorises())) && !($this->getConfig()->isCepageAutorise($this->getCepageLibelle()));
    }

    public function hasTroisiemeFeuille() {
        $year = ConfigurationClient::getInstance()->getCampagneParcellaire()->getCurrentAnneeRecolte() - 2;
        $campagne_troisieme_feuille = $year.'-'.($year + 1);
        return ($this->campagne_plantation < $campagne_troisieme_feuille);
    }

    public function getGeoJson() {
        $data = $this->getDocument()->getGeoJson();
        if ($data) {
            foreach($data->features as $f) {
                if ($f->id == $this->idu) {
                    return json_encode($f);
                }
            }
        }
        return '{"type":"Feature","id":"","geometry":{"type":"Polygon","coordinates":[]},"properties":{"error":"parcelle '.$this->idu.' not found in geojeson"}}';
        throw new sfException('parcelle not found in geojeson');
    }

    public function getGeoParcelle(): Geometry {
        if (!$this->geoparcelle) {
            if (!geophp::geosInstalled()) {
                throw new sfException("php-geos needed");
            }
            $this->geoparcelle = geoPHP::load($this->getGeoJson());
        }
        return $this->geoparcelle;
    }

    public function getIsInAires() {
        return AireClient::getInstance()->getIsInAiresFromCommuneAndGeoParcelle($this->code_commune, $this);
    }

    public function isInDenominationLibelle($l) {
        $iia = $this->getIsInAires();
        if (!isset($iia[$l])) {
            return null;
        }
        return $iia[$l];
    }

    public function getParcelleAffectee() {
        if (strpos($this->getHash(), 'declaration') !== false) {
            return $this;
        }
        foreach($this->getDocument()->declaration->getParcelles() as $p) {
            if ($p->getParcelleId() == $this->getParcelleId()) {
                return $p;
            }
        }
        throw new sfException('parcelle not found');
    }

    public function getParcelleId() {
        if (!$this->_get('parcelle_id')) {
            if (strlen($this->getKey()) == 17){
                $this->_set('parcelle_id', $this->getKey());
            }else{
                $parcelle_id = sprintf('%s-%02d', $this->idu, $this->getDocument()->getNbUDIAlreadySeen($this->idu));
                $this->_set('parcelle_id', $parcelle_id);
            }
        }
        return $this->_get('parcelle_id');
    }

    public function getTheoriticalDgs() {
        $communesDenominations = sfConfig::get('app_communes_denominations');
        $dgcs = [];
        foreach ($communesDenominations as $dgc_h => $communes) {
            $dgc_l = $this->getDocument()->getConfiguration()->get($dgc_h)->getLibelle();
            foreach($communes as $commune) {
                if ($this->code_commune == $commune) {
                    $dgcs[$dgc_h] = $dgc_l;
                }
            }
        }
        return $dgcs;
    }

    public function getDensite() {
        if ($this->ecart_pieds && $this->ecart_rang) {
            return round(10000 / (($this->ecart_pieds / 100) * ($this->ecart_rang / 100)), 0);
        }
        return 0;
    }
}
