<?php
/**
 * Model for DRevPrelevement
 *
 */

class DRevPrelevement extends BaseDRevPrelevement {

    public function getConfig() {

        return $this->getDocument()->getConfiguration()->get($this->getHashProduit());
    }

    public function getConfigProduits() {

        return $this->getConfig()->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function addLotProduit($hash)
    {
        $this->getDocument()->addLotProduit($hash, $this->getPrefix());
    }

    public function initLots() {
        foreach ($this->getConfigProduits() as $produit) {
            $this->addLotProduit($produit);
        }
    }

    public function hasLots($vtsgn = false, $horsvtsgn = false)
    {
        foreach ($this->lots as $lot) {
            if ($lot->hasLots($vtsgn, $horsvtsgn)) {
                return true;
            }
        }
        
        return false;
    }

    public function getDateObject() {
        if(!$this->date) {

            return null;
        }

        return new DateTime($this->date);
    }

    public function getDateFr() {
        $date = $this->getDateObject();

        if(!$date) {

            return null;
        }

        return $date->format('d/m/Y');
    }

    public function getPrefix() {
        preg_match("/^(.+_)/", $this->getKey(), $matches);

        return $matches[1];
    }

    public function getLibelleProduit() {
        if($this->_get('libelle_produit') === null) {
            try {
                $this->libelle_produit = $this->getConfig()->getLibelle();
            } catch (Exception $e) {
                $this->libelle_produit = "VT / SGN";
            }
        }

        return $this->_get('libelle_produit');
    }

    public function getLibelle() {
        if($this->_get('libelle') === null) {

                $this->libelle = DRev::$prelevement_libelles[$this->getKeyType()];
        }

        return $this->_get('libelle');
    }

    public function getLibelleProduitType() {
        if($this->_get('libelle_produit_type') === null) {

                $this->libelle_produit_type = DRev::$prelevement_libelles_produit_type[$this->getKeyType()];
        }

        return $this->_get('libelle_produit_type');
    }

    public function getKeyType() {
        if(preg_match("/".DRev::CUVE."/", $this->getKey())) {

            return DRev::CUVE;
        }

        return DRev::BOUTEILLE;
    }

    public function getHashProduit() {

        return "/declaration/certification/genre/".preg_replace("/^(.+_)/", "appellation", $this->getKey());
    }
    
}