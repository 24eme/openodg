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

    public function getHashProduit() {

        return "/declaration/certification/genre/".preg_replace("/^(.+_)/", "appellation", $this->getKey());
    }

}