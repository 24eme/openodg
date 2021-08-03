<?php

/**
 * Model for ParcellaireAppellation
 *
 */
class ParcellaireAffectationAppellation extends BaseParcellaireAffectationAppellation {

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
        $appellationsKeys = array_keys(ParcellaireAffectationClient::getInstance()->getAppellationsAndVtSgnKeys($this->getDocument()->getTypeParcellaire()));
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

    public function getDetailsSortedByParcelle($byfullkey = true) {
        $parcelles = $this->getProduitsCepageDetails();
        if ($byfullkey) {
            usort($parcelles, 'ParcellaireAffectationAppellation::sortParcellesByFullKey');
        }else{
            usort($parcelles, 'ParcellaireAffectationAppellation::sortParcellesByCommune');
        }
        return $parcelles;
    }

    static function sortParcellesByFullKey($detail0, $detail1) {
        return strcmp($detail0->getLibelleComplet().' '.$detail0->getParcelleIdentifiant(),
        $detail1->getLibelleComplet().' '.$detail1->getParcelleIdentifiant());
    }

    static function sortParcellesByCommune($detail0, $detail1) {
        return strcmp($detail0->getParcelleIdentifiant().' '.$detail0->getLibelleComplet(),
        $detail1->getParcelleIdentifiant().' '.$detail1->getLibelleComplet());
    }

    public function getPreviousAppellationKey() {
        $appellationsKeys = array_reverse(array_keys(ParcellaireAffectationClient::getInstance()->getAppellationsKeys($this->getDocument()->getTypeParcellaire())));
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
