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
    
    public function getNextAppellationKey() {
        $appellationsKeys = array_keys(ParcellaireClient::getInstance()->getAppellationsKeys());
        $onAppellation = false;
        foreach ($appellationsKeys as $key) {  
            if($onAppellation){
                return $key;
            }
            if($this->getKey() == 'appellation_'.$key){
                $onAppellation = true;
            }
        }
        return false;
    }
    
    public function getPreviousAppellationKey() {
        $appellationsKeys = array_reverse(array_keys(ParcellaireClient::getInstance()->getAppellationsKeys()));
        $onAppellation = false;
        foreach ($appellationsKeys as $key) {  
            if($onAppellation){
                return $key;
            }
            if($this->getKey() == 'appellation_'.$key){
                $onAppellation = true;
            }
        }
        return false;
    }
    

}
