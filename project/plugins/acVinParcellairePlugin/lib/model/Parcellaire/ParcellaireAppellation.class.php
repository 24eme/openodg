<?php

/**
 * Model for ParcellaireAppellation
 *
 */
class ParcellaireAppellation extends BaseParcellaireAppellation {

    public function getGenre() {
        return $this->getParent();
    }

    public function getChildrenNode() {
        return $this->getMentions();
    }

    public function getMentions() {
        return $this->filter('^mention');
    }

    public function getConfigProduits() {

        return $this->getConfig()->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
    }

}
