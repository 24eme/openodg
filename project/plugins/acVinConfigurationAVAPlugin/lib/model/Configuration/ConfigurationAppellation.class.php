<?php

class ConfigurationAppellation extends BaseConfigurationAppellation {

    public function getAppellation() {
        return $this;
    }

    public function getGenre() {
        return $this->getParentNode();
    }

    public function getCertification() {
        return $this->getGenre()->getParentNode();
    }

    public function hasLieu() {
        return false;
    }
    public function hasMention() {
        return false;
    }
    public function hasCouleur() {
        return false;
    }
    public function hasCepage() {
        return false;
    }
    public function getMentions() {
        return $this->filter('^mention');
    }

    public function getLieux() {

        return $this->getChildrenNodeDeep();
    }

    public function getChildrenNode() {

        return $this->getMentions();
    }

    public function hasManyLieu() {

        return $this->getChildrenNodeDeep()->hasManyNoeuds();
    }

    public function hasLieuEditable() {
        if ($this->exist('detail_lieu_editable') && $this->get('detail_lieu_editable'))
            return true;
        return false;
    }

    public function getNbMention() {
        return count($this->getMentions());
    }

    public function hasManyMention(){
        return ($this->filter('^mention')->count() > 1);
    }
    
    public function hasCepageRB() {
        foreach($this->getLieux() as $lieu) {
            if($lieu->hasCepageRB()) {
                return true;
            }
        }
       
        return false;
    }

    public function getDistinctLieux()
    {
        $arrLieux = array();
        foreach($this->getMentions() as $mention){
            foreach( $mention->getLieux() as $key =>  $lieu){
                if(!array_key_exists($key, $arrLieux)){
                    $arrLieux[$key] = $lieu;
                }
            }
        break;
        }
    return $arrLieux;
    }
    
        public function getLibelleComplet($libelle_long = false) 
    {
        return $this->libelle;
    }

}
