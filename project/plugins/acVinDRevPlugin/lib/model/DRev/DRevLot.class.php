<?php
/**
 * Model for DRevLot
 *
 */

class DRevLot extends BaseDRevLot
{
    public function getConfigProduit() {
            return $this->getConfig();
    }

    public function getConfig() {

        return $this->getDocument()->getConfiguration()->get($this->produit_hash);
    }

    public function setProduitHash($hash) {
        if($hash != $this->_get('produit_hash')) {
            $this->produit_libelle = null;
        }
        parent::_set('produit_hash', $hash);
        $this->getProduitLibelle();
    }

    public function getProduitLibelle() {
		if(!$this->_get('produit_libelle') && $this->produit_hash) {
			$this->produit_libelle = $this->getConfig()->getLibelleComplet();
		}

		return $this->_get('produit_libelle');
	}

    public function getCepagesLibelle() {
        $libelle = null;
        foreach($this->cepages as $cepage => $repartition) {
            if($libelle) {
                $libelle .= ", ";
            }
            $libelle .= $cepage . " (".$repartition."%)";
        }
        return $libelle;
    }

    public function isCleanable() {
        foreach($this as $key => $value) {
            if($key == 'millesime' && $value = $this->getDocument()->getCampagne()) {

                continue;
            }
            if($value instanceof acCouchdbJson && !count($value->toArray(true, false))) {
                continue;
            }

            if($value) {

                return false;
            }
        }

        return true;
    }

    public function getDestinationDateFr()
    {

        return Date::francizeDate($this->destination_date);
    }

    public function addCepage($cepage, $repartition) {
        $this->cepages->add($cepage, $repartition);
    }

    public function isProduitValidateOdg(){
      foreach($this->getDocument()->getProduitsLots() as $produit) {
          if(!$produit->isValidateOdg()){
            return false;
          }
      }
      return true;
    }

    public function hasVolumeAndHashProduit(){
      return $this->volume && $this->produit_hash;
    }

    public function getDateVersionfr(){
      if($this->date_version){
        return Date::francizeDate(preg_replace("/_(M?[0-9]*)/",'',$this->date_version));
      }
      return "";
    }

    public function getDrevLastFromDateVersion(){
      if(!$this->date_version){
        return null;
      }
      if(!$this->getDocument()->getMother()){
        return $this->getDocument();
      }
      if(!$this->getDocument()->isModifiedMother($this->getHash(), "date_version")){
        return $this->getDocument()->getMother()->get($this->getHash())->getDrevLastFromDateVersion();
      }
      return $this->getDocument();
    }

}
