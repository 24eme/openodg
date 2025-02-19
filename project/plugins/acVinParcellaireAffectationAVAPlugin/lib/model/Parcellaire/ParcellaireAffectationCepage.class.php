<?php

/**
 * Model for ParcellaireCepage
 *
 */
class ParcellaireAffectationCepage extends BaseParcellaireAffectationCepage {

    public function getChildrenNode() {

        return $this->detail;
    }

    public function getLieu() {

        return $this->getCouleur()->getLieu();
    }

    public function getProduitHash() {

        return $this->getHash();
    }

    public function getCouleur() {

        return $this->getParent();
    }

    public function getProduits($onlyActive = false) {
        if ($onlyActive && !$this->isAffectee()) {

            return array();
        }

        return array($this->getHash() => $this);
    }

    public function getProduitsCepageDetails($onlyVtSgn = false, $active = false) {

    	if ($onlyVtSgn && !$this->getConfig()->hasVtsgn()) {
    		return array();
    	}

    	return parent::getProduitsCepageDetails($onlyVtSgn, $active);
    }

    public function getAppellation() {
        return $this->getCouleur()->getAppellation();
    }

    public function getAcheteursNode($lieu = null, $cviFilter = null) {
        $acheteurs = array();
        if($lieu) {
            $lieu = KeyInflector::slugify(trim($lieu));
        }
        foreach($this->acheteurs as $acheteurs_lieu => $acheteurs_type) {

            foreach($acheteurs_type as $type => $achs) {
                foreach($achs as $acheteur) {
                    if($cviFilter && $acheteur->cvi != $cviFilter) {
                        continue;
                    }
                    if($lieu && $acheteurs_lieu != $lieu) {
                        continue;
                    }
                    if (!isset($acheteurs[$type])) {
                        $acheteurs[$type] = array();
                    }
                    $acheteurs[$type][$acheteur->getKey()] = $acheteur;
                }
            }
        }

        return $acheteurs;
    }

    public function getAcheteursByHash($lieu = null) {
        $acheteurs = array();
        foreach($this->getAcheteursNode($lieu) as $type => $acheteursByType) {
            foreach($acheteursByType as $cvi => $acheteur) {
                $acheteurs["/acheteurs/".$type."/".$cvi] = $acheteur;
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

    public function getLieuKeyFromHash($hash) {
        $lieu_key = null;
        if($this->getConfig()->hasLieuEditable()) {
            $lieu_key = preg_replace("|^.*/lieu([^/]*)/.+$|", '\1', $hash);
        }

        return $lieu_key;
    }

    public function addAcheteurFromNode($acheteur, $lieu = null) {

        return $this->addAcheteur($acheteur->getParent()->getKey(), $acheteur->getKey(), $lieu);
    }

    public function hasMultipleAcheteur($lieu = null) {
        foreach($this->getProduitsCepageDetails() as $parcelle) {
            if($lieu && $parcelle->lieu != $lieu) {

                continue;
            }

            if($parcelle->hasMultipleAcheteur()) {

                return true;
            }
        }

        return false;
    }

    public function addDetailNode($key, $parcelle) {
        $detail = $this->getDetailNode($key);
        if($detail) {

            return $detail;
        }

        $detail = $this->detail->add($parcelle->getParcelleId());
        ParcellaireClient::CopyParcelle($detail, $parcelle, $parcelle->getDocument()->getType() !== 'Parcellaire');
        $detail->origine_doc = $parcelle->getDocument()->_id;
        $detail->superficie = null;
        if($detail->lieu){
           $detail->lieu = strtoupper($detail->getLieu());
        }
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
