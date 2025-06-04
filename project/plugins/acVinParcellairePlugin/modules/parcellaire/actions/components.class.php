<?php

class parcellaireComponents extends sfComponents {

    public function executeSyntheseParCepages(sfWebRequest $request) {
        $this->synthese = array();
        if($this->parcellaire) {
            $this->synthese = $this->parcellaire->getSyntheseCepages(ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration());
        }

    }

}
