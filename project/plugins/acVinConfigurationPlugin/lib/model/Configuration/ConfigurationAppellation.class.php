<?php
/**
 * Model for ConfigurationAppellation
 *
 */

class ConfigurationAppellation extends BaseConfigurationAppellation {

	  const TYPE_NOEUD = 'appellation';

    public function getChildrenNode() {

      return $this->mentions;
    }
	
	public function getAppellation() {
		return $this;
	}

    public function getGenre() {

      return $this->getParentNode();
    }

    public function getCertification() {

        return $this->getGenre()->getCertification();
    }
		public function getNodeCahierDesCharges() {
        return $this;
    }

    public function setDonneesCsv($datas) {
      parent::setDonneesCsv($datas);
    	$this->getGenre()->setDonneesCsv($datas);
    	$this->libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_DENOMINATION_LIBELLE])? $datas[ProduitCsvFile::CSV_PRODUIT_DENOMINATION_LIBELLE] : null;
      $this->code = $this->formatCodeFromCsv($datas[ProduitCsvFile::CSV_PRODUIT_DENOMINATION_CODE]);
      $this->densite = ($datas[ProduitCsvFile::CSV_PRODUIT_DENSITE])? $datas[ProduitCsvFile::CSV_PRODUIT_DENSITE] : Configuration::DEFAULT_DENSITE;

    	$this->setDroitDouaneCsv($datas, ProduitCsvFile::CSV_PRODUIT_DENOMINATION_CODE_APPLICATIF_DROIT);
    	$this->setDroitCvoCsv($datas, ProduitCsvFile::CSV_PRODUIT_DENOMINATION_CODE_APPLICATIF_DROIT);

    }

  	public function getTypeNoeud() {
  		return self::TYPE_NOEUD;
  	}

	public function getMentions() {
		return $this->_get('mentions');
	}

	public function hasLieuEditable() {
        if(!$this->exist('attributs') || !$this->attributs->exist('detail_lieu_editable')) {
            return 0;
        }

        return $this->attributs->get('detail_lieu_editable');
    }

		public function getCahierDesCharges(){
			return true;
		}

}
