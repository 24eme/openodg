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

    public function isCleanable() {
        foreach($this as $value) {
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


}
