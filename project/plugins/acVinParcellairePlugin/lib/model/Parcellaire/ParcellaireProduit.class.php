<?php

/**
 * Model for ParcellaireCepage
 *
 */
class ParcellaireProduit extends BaseParcellaireProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    public function getLibelle() {
		if(!$this->_get('libelle')) {
			$this->libelle = $this->getConfig()->getLibelleComplet();
		}

		return $this->_get('libelle');
	}

    public function getProduitsDetails($onlyVtSgn = false, $active = false) {

    	if ($onlyVtSgn && !$this->getConfig()->hasVtsgn()) {
    		return array();
    	}

        $details = array();

        foreach ($this->detail as $item) {
            $details = array_merge($details, $item->getProduitsDetails($onlyVtSgn, $active));
        }

    	return $details;
    }

    public function getAcheteursNode($lieu = null, $cviFilter = null) {
        $acheteurs = array();
        if($lieu) {
            $lieu = KeyInflector::slugify(trim($lieu));
        }
        if($cviFilter) {
            $cviFounded = false;
            foreach($this->acheteurs as $acheteurs_lieu => $acheteurs_type) {
                foreach($acheteurs_type as $type => $achs) {
                    foreach($achs as $acheteur) {
                        if($lieu && $acheteurs_lieu != $lieu) {
                            continue;
                        }
                        if($acheteur->cvi == $cviFilter) {
                            $cviFounded = true;
                            break;
                        }
                    }
                    if($cviFounded) {
                        break;
                    }
                }
            }
            if(!$cviFounded) {
                return array();
            }
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

    public function addParcelle($cepage, $commune, $section, $numero_parcelle, $lieu = null, $dpt = null) {
        $key = KeyInflector::slugify($cepage.'-'.$commune . '-' . $section . '-' . $numero_parcelle);

        if ($lieu) {
            $key.='-' . KeyInflector::slugify($lieu);
        }

        if($this->detail->exist($key)) {
           return $this->detail->get($key);
        }

        $detail = $this->detail->add($key);
        $detail->cepage = $cepage;
        $detail->commune = $commune;
        $detail->section = $section;
        $detail->numero_parcelle = $numero_parcelle;
        if($lieu){
            $lieu = strtoupper($lieu);
        }
        $detail->lieu = $lieu;
        $detail->departement = $dpt;

        return $detail;
    }

    public function isAffectee($lieu = null) {
        foreach($this->detail as $detail) {
            if($detail->isAffectee($lieu)) {
                return true;
            }
        }

        return false;
    }


    public function isCleanable() {
        if (count($this->detail) == 0) {

            return true;
        }

        return false;
    }

    public function cleanNode() {

    }

    public function getSuperficieTotale(){
      $total = 0.0;
      foreach ($this->getDetail() as $detail) {
        $total += $detail->superficie;
      }
      return $total;
    }

}
