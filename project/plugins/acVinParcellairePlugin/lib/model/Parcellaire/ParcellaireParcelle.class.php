<?php

require_once(dirname(__FILE__).'/../../vendor/geoPHP/geoPHP.inc');

class ParcellaireParcelle extends BaseParcellaireParcelle {
    private static $_AIRES = [];
    private $geoparcelle = null;

    public function getProduit() {
        if ($this->getParcelleAffectee()) {

            return $this->getDocument()->get(preg_replace('#/detail$#', '', $this->getParcelleAffectee()->getParentHash()));
        }
        return null;
    }

    public function getConfig() {
        try {
            if (strpos($this->produit_hash, 'declaration/') !== false) {
                return $this->getDocument()->getConfiguration()->get(preg_replace('/\/detail\/.*/', '', $this->produit_hash));
            }else {
                return $this->getDocument()->getConfiguration()->declaration->get(preg_replace('/\/detail\/.*/', '', $this->produit_hash));
            }
        }catch(sfException $e) {
            return null;
        }
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
        throw new sfException('updateIUD');
        $this->idu = Parcellaire::computeIDU($this->code_commune, $this->prefix, $this->section, $this->numero_parcelle);
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
        if (!$this->getProduit()->getConfig()) {
            return null;
        }
        return $this->getProduit()->getConfig()->getAppellation();
    }

    public function getProduitLibelle() {
        if (!$this->isRealProduit()) {
            $lib = 'PRODUIT NON GÉRÉ';
            if ($this->source_produit_libelle) {
                $lib .= ' ('.$this->source_produit_libelle.')';
            }
            return $lib;
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

    public function cleanNode() {

        return false;
    }

    public function getSuperficieParcellaire() {
        return $this->superficie;
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
      $ecart_rangs_max = ParcellaireConfiguration::getInstance()->getEcartRangsMax();
      $ecart_pieds_min = ParcellaireConfiguration::getInstance()->getEcartPiedsMin();
      $ecart_pieds_max = ParcellaireConfiguration::getInstance()->getEcartPiedsMax();
      if ($ecart_rangs_max && $this->exist('ecart_rang')) {
          if ($ecart_rangs_max < $this->ecart_rang) {
              return true;
          }
      }
      if ($this->exist('ecart_pieds')) {
          if ($ecart_pieds_max && $ecart_pieds_max < $this->ecart_pieds) {
              return true;
          }
          if ($ecart_pieds_min && $ecart_pieds_min > $this->ecart_pieds) {
              return true;
          }
      }
      return false;
    }

    public function getParcelleParcellaire() {
        $p = $this->getDocument()->getParcellaire()->getDeclarationParcelles();
        if (!isset($p[$this->getParcelleId()])) {
            return null;
        }
        return $p[$this->getParcelleId()];
    }

    public function existsInParcellaire() {
        return ($this->getParcelleParcellaire() != null);
    }

    public function isRealProduit() {
        if (!$this->getDocument()->_exist('parcelles')) {
            return true;
        }
        $p = $this->getParcelleParcellaire();
        if (!$p) {
            return false;
        }
        if (!$p->produit_hash) {
            return false;
        }
        if (!$p->getConfig()) {
            return false;
        }
        return true;
    }

    public function hasProblemParcellaire() {
        if ($this->existsInParcellaire()){
            return false;
        }
        return true;
    }

    public function hasProblemProduitCVI() {
        if (ParcellaireConfiguration::getInstance()->affectationNeedsIntention()) {
            return false;
        }
        $a = $this->getIsInAires();
        if (! count($a)) {
            return true;
        }
        if (isset($a[AireClient::PARCELLAIRE_AIRE_GENERIC_AIRE]) && $a[AireClient::PARCELLAIRE_AIRE_GENERIC_AIRE]) {
            return true;
        }
        return false;
    }

    public function hasProblemCepageAutorise() {
      if (!$this->getConfig()) {
          return false;
      }
      return (count($this->getConfig()->getCepagesAutorises())) && !($this->getConfig()->isCepageAutorise($this->getCepageLibelle()));
    }

    public function hasJeunesVignes() {
        //Troisième ou Quatrieme feuille
        $annee = 3;
        if (ParcellaireConfiguration::getInstance()->isJeunesVignes3emeFeuille()) {
            $annee = 2;
        }
        $year = ConfigurationClient::getInstance()->getCampagneParcellaire()->getCurrentAnneeRecolte() - $annee;
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

    public function getCodeCommune() {
        $c = null;
        if (isset($this->code_commune)) {
            $c = $this->code_commune;
        }
        if (!$c) {
            $c = substr($this->idu, 0, 5);
        }
        return $c;
    }

    public function getPcAire($nom_aire) {
        return AireClient::getInstance()->getPcFromCommuneGeoParcelleAndAire($this->code_commune, $this, $nom_aire);
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

    public function getProduitHash() {
        if ($h = $this->_get('produit_hash')) {
            return $h;
        }
        $h = preg_replace('/\/detail\/.*/', '', $this->getHash());
        $this->_set('produit_hash', $h);
        return $h;
    }
}
