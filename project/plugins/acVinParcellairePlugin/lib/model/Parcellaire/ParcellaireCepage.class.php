<?php

/**
 * Model for ParcellaireCepage
 *
 */
class ParcellaireCepage extends BaseParcellaireCepage {

    public function getChildrenNode() {
        return $this->detail;
    }

    public function getCouleur() {

        return $this->getParent();
    }

    public function getProduits($onlyActive = false) {
        if ($onlyActive && !$this->isActive()) {

            return array();
        }

        return array($this->getHash() => $this);
    }

    public function getAppellation() {
        return $this->getCouleur()->getAppellation();
    }

    public function getAcheteursNode($type = null) {
        $acheteurs = array();

        foreach($this->acheteurs as $type => $achs) {
            foreach($achs as $acheteur) {
                $acheteurs[$type][$acheteur->getKey()] = $acheteur;
            }
        }

        return $acheteurs;
    }

    public function addAcheteur($type, $cvi) {
        $a = $this->getDocument()->addAcheteur($type, $cvi);
        $acheteur = $this->acheteurs->add($type)->add($cvi);
        $acheteur->nom = $a->nom;
        $acheteur->cvi = $a->cvi;
        $acheteur->commune = $a->commune;

        return $acheteur;
    }


    public function addAcheteurFromNode($acheteur) {
        
        return $this->addAcheteur($acheteur->getParent()->getKey(), $acheteur->getKey());
    }


    public function addDetailNode($key, $commune, $section , $numero_parcelle, $lieu = null) {
        $detail = $this->getDetailNode($key);
        if($detail) {

            return $detail;
        }

        $detail = $this->detail->add($key);
        $detail->commune = $commune;
        $detail->section = $section;
        $detail->numero_parcelle = $numero_parcelle;
        $detail->lieu = $lieu;
        return $detail;
    }
    
    public function getDetailNode($key) {
       foreach ($this->detail as $parcelleKey => $detail) {
            

            if($parcelleKey ==  $key) {                
             
                return $detail;
            }
        }

        return null;
    }
    
}
