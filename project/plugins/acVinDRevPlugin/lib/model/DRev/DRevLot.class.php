<?php
/**
 * Model for DRevLot
 *
 */

class DRevLot extends BaseDRevLot
{
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

    public function getPrefix() {
        preg_match("/^(.+)_/", $this->getKey(), $matches);

        return $matches[1];
    }

    public function getHashProduit() {

        return "/declaration/certification/genre/".preg_replace("/^(.+_)/", "appellation", $this->getKey());
    }

}