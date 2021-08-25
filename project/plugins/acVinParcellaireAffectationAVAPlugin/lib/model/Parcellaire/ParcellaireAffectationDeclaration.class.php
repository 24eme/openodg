<?php

/**
 * Model for ParcellaireDeclaration
 *
 */
class ParcellaireAffectationDeclaration extends BaseParcellaireAffectationDeclaration {

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

    public function getAppellationsOrderParcellaire() {
        $appellations = $this->getAppellations();

        $appellationOrdered = array();

        if(!$appellations) {
            return $appellationOrdered;
        }

        foreach (ParcellaireAffectationClient::getInstance()->getAppellationsKeys($this->getDocument()->getTypeParcellaire()) as $app_key => $app_name) {
           if(array_key_exists('appellation_'.$app_key, $appellations->toArray(1,0))){
               $appellationOrdered['appellation_'.$app_key] = $appellations['appellation_'.$app_key];
           }
        }

        return $appellationOrdered;
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
