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

    public function getHashProduit() {

        return "/declaration/certification/genre/".preg_replace("/^(.+_)/", "appellation", $this->getKey());
    }
    
}