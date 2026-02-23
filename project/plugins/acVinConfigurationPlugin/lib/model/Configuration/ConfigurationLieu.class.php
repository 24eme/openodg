<?php
/**
 * Model for ConfigurationLieu
 *
 */

class ConfigurationLieu extends BaseConfigurationLieu {

	  const TYPE_NOEUD = 'lieu';

    protected function loadAllData() {
        parent::loadAllData();
        $this->hasCepage();
    }

    public function getChildrenNode() {

      return $this->couleurs;
    }

	/**
     *
     * @return ConfigurationAppellation
     */
    public function getMention() {

        return $this->getParentNode();
    }

    public function getAppellation() {

        return $this->getMention()->getAppellation();
    }

    public function getCertification() {

        return $this->getGenre()->getCertification();
    }

    public function getGenre() {

        return $this->getAppellation()->getGenre();
    }

    public function getLieu() {

        return $this;
    }

    public function getLabels($interpro) {

        return $this->getCertification()->getLabels($interpro);
    }

    public function hasCepage() {
        return $this->store('has_cepage', array($this, 'hasCepageStore'));
    }

    public function hasCepageStore() {
        foreach($this->couleurs as $couleur) {
            if ($couleur->hasCepage()) {
                return true;
            }
        }

        return false;
    }

	public function getRendementNoeud() {

		return $this->getRendementAppellation();
	}

    public function setDonneesCsv($datas) {
      parent::setDonneesCsv($datas);

    	$this->getMention()->setDonneesCsv($datas);
    	$this->libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_LIEU_LIBELLE])? $datas[ProduitCsvFile::CSV_PRODUIT_LIEU_LIBELLE] : null;
    	$this->code = $this->formatCodeFromCsv($datas[ProduitCsvFile::CSV_PRODUIT_LIEU_CODE]);
    	$this->setDroitDouaneCsv($datas, ProduitCsvFile::CSV_PRODUIT_LIEU_CODE_APPLICATIF_DROIT);
    	$this->setDroitCvoCsv($datas, ProduitCsvFile::CSV_PRODUIT_LIEU_CODE_APPLICATIF_DROIT);

    	$this->setDepartementCsv($datas);
    }

  	public function getTypeNoeud() {
  		return self::TYPE_NOEUD;
  	}

	public function getCouleurs() {

		return $this->_get('couleurs');
	}

    public function getCepagesAutorises() {
        if(!$this->hasCepagesAutorises()) {
            if ($this->getAppellation()->hasCepagesAutorises()) {
                return $this->getAppellation()->getCepagesAutorises();
            }
            if($this->getCertification()->hasCepagesAutorises()) {
                return $this->getCertification()->getCepagesAutorises();
            }
        }
        return $this->_get('cepages_autorises');
    }

    public function hasCepagesAutorises(){
        return $this->exist('cepages_autorises') && count($this->_get('cepages_autorises')->toArray(true, false));
    }

    public function isCepageAutorise($cepage) {
        if ($this->hasCepagesAutorises()) {
            return true;
        }
        foreach($this->getCouleurs() as $c) {
            if ($c->isCepageAutorise($cepage)) {
                return true;
            }
        }
        return false;
    }

}
