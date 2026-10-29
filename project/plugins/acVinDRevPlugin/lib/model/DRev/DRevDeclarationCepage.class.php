<?php

class DRevDeclarationCepage extends BaseDRevDeclarationCepage
{
	public function getConfig()
	{
		return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
	}

    public function getRegion() {

        return RegionConfiguration::getInstance()->getOdgRegion($this->getHash());
    }

    protected function getProduitsByRegion($region) {
		$produits = array();
		$regionRadixProduits = RegionConfiguration::getInstance()->getOdgProduits($region);
		foreach ($this->getProduits() as $hash => $produit) {
		  	foreach ($regionRadixProduits as $filtre) {
				if(!preg_match("|".$filtre."|", $hash)){
					continue;
				}
				$produits[$hash] = $produit;
				break;
		  	}
		}

		return $produits;
	}

    public function hasDonneesRecolte() {
        foreach($this as $p) {
           if($p->hasDonneesRecolte()) {

               return true;
           }
        }
        return false;
    }

    public function hasProduitsSansDonneesRecolte() {
        foreach($this as $p) {
            if(!$p->hasDonneesRecolte()) {
                return false;
            }
        }

        return true;
    }

	public function getProduits($region = null)
    {
		if($region) {

			return $this->getProduitsByRegion($region);
		}
        $produits = array();
        foreach($this as $item) {
            $produits[$item->getHash()] = $item;
        }

        return $produits;
    }

    public function getSommeProduits($subhash) {
        $somme = 0;
        foreach($this->getProduits() as $p) {
            if(!$p->exist($subhash)) {
                continue;
            }
            if(is_null($p->get($subhash))) {
                continue;
            }
            $somme += $p->get($subhash);
        }
        return $somme;
    }

    public function getLibelleComplet() {
        return $this->getConfig()->getLibelleComplet();
    }

    public function getLibelle() {
        return $this->getLibelleComplet();
    }

    public function getVolumeRevendiqueRendement() {
        $somme = 0;
        foreach($this->getProduits() as $p) {
            $somme += $p->getVolumeRevendiqueRendement();
        }
        return $somme;
    }

    public function getRendementEffectif() {
        $superficie = $this->getSommeProduits('superficie_revendique');
        if (!$superficie) {
            return null;
        }
        return $this->getVolumeRevendiqueRendement() / $superficie;
    }

    public function getRendementVCIConstitue() {
        $superficie = $this->getSommeProduits('superficie_revendique');
        if (!$superficie) {
            return null;
        }
        return $this->getSommeProduits('recolte/vci_constitue') / $superficie;
    }

    public function getRendementVSI() {
        if (!$this->getSommeProduits('volume_revendique_issu_vsi')) {
            return null;
        }
        $superficie = $this->getSommeProduits('superficie_revendique');
        if (!$superficie) {
            return null;
        }
        return $this->getSommeProduits('volume_revendique_issu_vsi') / $superficie;
    }

    public function getRendementEffectifHorsVCI() {
        $superficie = $this->getSommeProduits('superficie_revendique');
        if (!$superficie) {
            return null;
        }

		return $this->getSommeProduits('volume_revendique_issu_recolte') / $superficie;
    }

    public function getRendementDrL15(){
		$superficie = $this->getSommeProduits('recolte/superficie_total');
		if ($superficie) {
			return $this->getSommeProduits('recolte/volume_sur_place_revendique') / $superficie;
		}
		return 0;
	}

}
