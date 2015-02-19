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

    public function getAcheteursNode($lieu = null) {
        $acheteurs = array();
        if($lieu) {
            $lieu = KeyInflector::slugify(trim($lieu));
        }
        foreach($this->acheteurs as $acheteurs_lieu => $acheteurs_type) {
            foreach($acheteurs_type as $type => $achs) {
                foreach($achs as $acheteur) {
                    if($lieu && $acheteurs_lieu != $lieu) {
                        continue;
                    }
                    $acheteurs[$type][$acheteur->getKey()] = $acheteur;
                }
            }
        }

        return $acheteurs;
    }

    public function addAcheteur($type, $cvi, $lieu = null) {
        $a = $this->getDocument()->addAcheteur($type, $cvi);
        if(!$lieu) {
            $lieu = $this->getCouleur()->getLieu()->getKey();
        } else {
            $lieu = KeyInflector::slugify(trim($lieu));
        }
        $acheteur = $this->acheteurs->add($lieu)->add($type)->add($cvi);
        $acheteur->nom = $a->nom;
        $acheteur->cvi = $a->cvi;
        $acheteur->commune = $a->commune;

        return $acheteur;
    }


    public function addAcheteurFromNode($acheteur, $lieu = null) {
        
        return $this->addAcheteur($acheteur->getParent()->getKey(), $acheteur->getKey(), $lieu);
    }

    public function addDetailNode($key, $commune, $section , $numero_parcelle, $lieu = null,$dpt = null) {
        $detail = $this->getDetailNode($key);
        if($detail) {

            return $detail;
        }

        $detail = $this->detail->add($key);
        $detail->commune = $commune;
        $detail->section = $section;
        $detail->numero_parcelle = $numero_parcelle;
        $detail->lieu = $lieu;
        $detail->departement = $dpt;
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
