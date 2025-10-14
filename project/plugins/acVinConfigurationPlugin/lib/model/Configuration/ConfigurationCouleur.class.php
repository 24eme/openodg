<?php
/**
 * Model for ConfigurationCouleur
 *
 */

class ConfigurationCouleur extends BaseConfigurationCouleur {

	const TYPE_NOEUD = 'couleur';

    public function getChildrenNode() {

      return $this->cepages;
    }

    public function getCouleur() {
        return $this;
    }

    public function getLieu() {

        return $this->getParentNode();
    }

	public function getMention() {

        return $this->getLieu()->getMention();
    }

    public function getAppellation() {

        return $this->getMention()->getAppellation();
    }

	public function getGenre() {

		return $this->getAppellation()->getGenre();
	}

    public function getCertification() {

            return $this->getGenre()->getCertification();
    }

    public function hasCepage() {
    	return (count($this->cepages) > 1 || (count($this->cepages) == 1 && $this->cepages->getFirst()->getKey() != Configuration::DEFAULT_KEY));
    }

    public function hasCouleur() {
        return true;
    }

    public function setDonneesCsv($datas) {
      parent::setDonneesCsv($datas);

    	$this->getLieu()->setDonneesCsv($datas);
    	$this->libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_COULEUR_LIBELLE])? $datas[ProduitCsvFile::CSV_PRODUIT_COULEUR_LIBELLE] : null;
      $this->code = $this->formatCodeFromCsv($datas[ProduitCsvFile::CSV_PRODUIT_COULEUR_CODE]);

      $this->setDroitDouaneCsv($datas, ProduitCsvFile::CSV_PRODUIT_COULEUR_CODE_APPLICATIF_DROIT);
      $this->setDroitCvoCsv($datas, ProduitCsvFile::CSV_PRODUIT_COULEUR_CODE_APPLICATIF_DROIT);

      $this->setDepartementCsv($datas);
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
  	public function hasDetails() {
  		return false;
  	}
  	public function getTypeNoeud() {
  		return self::TYPE_NOEUD;
  	}

	public function getRendementNoeud() {

		return $this->getRendementCouleur();
	}

    public function getLibelleDR() {
        if (strpos($this->libelle, 'Blanc') !== false){
            return "Blanc";
        }elseif (strpos($this->libelle, 'Rosé') !== false){
            return "Rosé";
        }elseif (strpos($this->libelle, 'Rouge') !== false){
            return "Rouge";
        }
        return $this->libelle;
    }

    public function getLibelleCompletDR() {
        return str_replace('Vin de base ', '', trim($this->getLieu()->getLibelleComplet(). " ".$this->getLibelleDR()));
    }

    public function getCepagesAutorises() {
        if(!$this->hasCepagesAutorises() && $this->getAppellation()->hasCepagesAutorises()) {
            return $this->getAppellation()->getCepagesAutorises();
        }
        if(!$this->hasCepagesAutorises() && $this->getCertification()->hasCepagesAutorises()) {
            return $this->getCertification()->getCepagesAutorises();
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
        foreach($this->getCepages() as $c) {
            if ($c->isCepageAutorise($cepage)) {
                return true;
            }
        }
        return false;
    }

}
