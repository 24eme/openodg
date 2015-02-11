<?php

/**
 * Model for ParcellaireDeclaration
 *
 */
class ParcellaireDeclaration extends BaseParcellaireDeclaration {

    public function getChildrenNode() {
        return $this->getCertifications();
    }

    public function getCertifications() {
        return $this->filter('^certification');
    }

    public function getAppellations() {
        if (!$this->exist('certification')) {
            return array();
        }
        return $this->getChildrenNodeDeep(2)->getAppellations();
    }

    public function getLieux() {
        if (!$this->exist('certification')) {
            return array();
        }
        $lieuArray = array();
        foreach ($this->getAppellations() as $appellationKey => $appellation) {
            foreach ($appellation->getMentions() as $mentionKey => $mention) {
                foreach ($mention->getLieux() as $lieuKey => $lieu) {
                    $lieuArray[$lieu->getHash()] = $lieu;
                }
            }
        }
        return $lieuArray;
    }

}
