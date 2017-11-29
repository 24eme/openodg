<?php

class ConfigurationLieu extends BaseConfigurationLieu {

    public function getMention() {

        return $this->getParentNode();
    }

    public function getAppellation() {

        return $this->getMention()->getParentNode();
    }

    public function getCouleurs() {
        return $this->filter('^couleur');
    }

    public function getChildrenNode() {

        return $this->getCouleurs();
    }

    public function getCouleur() {
        if ($this->getNbCouleurs() > 1) {
            throw new sfException('Pas getCouleur si plusieurs couleurs');
        }
        return $this->_get('couleur');
    }

    public function getNbCouleurs() {

        return count($this->getCouleurs());
    }

    public function getCepagesFilter($type_declaration = null) {
        $cepages = array();
        foreach($this->getChildrenFilter($type_declaration) as $couleur) {
            $cepages = array_merge($cepages, $couleur->getChildrenFilter($type_declaration));
        }

        return $cepages;
    }

    public function hasCepageRB() {

        return $this->getCepageRB() !== null;
    }

    public function getCepageRB() {

        $cepage_rebeche = array();
        foreach ($this->filter('couleur') as $couleur)
            if( $couleur->exist('cepage_RB'))
                $cepage_rebeche[] = $couleur->get('cepage_RB');

        if( count($cepage_rebeche) > 1)
            throw new sfException("getCepagesRB() ne peut retourner plus d'un cepage rebeche par appellation");

        return (count($cepage_rebeche) == 1) ? $cepage_rebeche[0] : null;
    }

    public function getCepages() {
        $cepage = array();
        foreach ($this->getCouleurs() as $couleur) {
            $cepage = array_merge($cepage, $couleur->getCepages()->toArray());
        }
        return $cepage;
    }

    public function hasManyCouleur() {
        return (!$this->exist('couleur') || $this->filter('^couleur.+')->count() > 0);
    }
    
    public function hasLieuEditable(){

        return $this->getAppellation()->hasLieuEditable();
    }

    public function getRendementNoeud() {

        return $this->getRendementAppellation();
    }

}
