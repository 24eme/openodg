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
    
    public function getDetailsSortedByParcelle() {
        $parcelles = $this->getProduitsCepageDetails();
        usort($parcelles, 'ParcellaireAppellation::sortParcellesByDetail');
        return $parcelles;
    }
    
    static function sortParcellesByDetail($detail0, $detail1) {
        return strcmp($detail0->getLibelleComplet().' '.$detail0->getParcelleIdentifiant(),
        $detail1->getLibelleComplet().' '.$detail1->getParcelleIdentifiant());
        
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
    
    public function addParcelle($parcelleKey,$commune,$section,$numero_parcelle) {
       $parcelle = $this->parcelles->add($parcelleKey);
       $parcelle->commune = $commune;
       $parcelle->section = $section;
       $parcelle->numero_parcelle = $numero_parcelle;
       return $parcelle;
    }
    
}
