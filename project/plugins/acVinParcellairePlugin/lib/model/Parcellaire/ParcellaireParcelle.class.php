<?php

/**
 * Model for ParcellaireCepageDetail
 *
 */
class ParcellaireParcelle extends BaseParcellaireParcelle {

    public function getProduit() {

        return $this->getParent()->getParent();
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

    public function getProduitsDetails($onlyVtSgn = false, $active = false) {
		if ($active && !$this->getActive()) {
			return array();
		}
        return array($this->getHash() => $this);
    }

    public function getLibelleComplet() {

        return $this->getAppellation()->getLibelleComplet().' '.$this->getLieuLibelle().' '.$this->getCepageLibelle();
    }

    public function updateIDU() {
        $this->idu = sprintf('%05s%03s%02s%04s', $this->code_commune, "", $this->section, $this->numero_parcelle);
    }

    public function setCodeCommune($code_commune) {
        $this->_set('code_commune', $code_commune);

        $this->updateIDU();

        return $this;
    }

    public function setSection($section) {
        $this->_set('section', $section);

        $this->updateIDU();

        return $this;
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

    public function isCleanable() {
    	if (!$this->getActive()) {
    		return true;
    	}
        return ($this->isFromAppellation('ALSACEBLANC') && !$this->getVtsgn());
    }

    public function isAffectee($lieu = null) {
        if ($lieu && $this->lieu && KeyInflector::slugify(trim($lieu)) != KeyInflector::slugify(trim($this->lieu))) {

    		return false;
    	}

        return !$this->isCleanable();
    }

    public function getProduitLibelle() {

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

    public function getActive() {
        $v = $this->_get('active');
        if (!$this->superficie) {
            return false;
        }
        if ($v === null) {
            return true;
        }
        return ($v) ? true : false;
    }
    public function setActive($value) {
        $this->add('active');
        return $this->_set('active', $value * 1);
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
      if ($this->exist('ecart_rang') && $this->exist('ecart_pieds') && $this->ecart_rang && $this->ecart_pieds) {
        return (($this->ecart_rang * $this->ecart_pieds) > 25000);
      }
      return false;
    }

    public function hasProblemCepageAutorise() {
      return !($this->getConfig()->isCepageAutorise($this->getCepageLibelle()));
    }

    public function hasTroisiemeFeuille() {
        $year = date('Y', strtotime('1st november')) - 2;
        $campagne_troisieme_feuille = ($year - 1).'-'.$year;
        return ($this->campagne_plantation < $campagne_troisieme_feuille);
    }
}
