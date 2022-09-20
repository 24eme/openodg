<?php

class DRevDeclarationCepage extends BaseDRevDeclarationCepage
{
	public function getConfig()
	{
		return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
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

    public function getRendementEffectif() {
        $superficie = $this->getSommeProduits('superficie_revendique');
        if (!$superficie) {
            return null;
        }
        return $this->getSommeProduits('volume_revendique_rendement') / $superficie;
    }

    public function getRendementVCIConstitue() {
        $superficie = $this->getSommeProduits('superficie_revendique');
        if (!$superficie) {
            return null;
        }
        return $this->getSommeProduits('recolte/vci_constitue') / $superficie;
    }

    public function getRendementVSI() {
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

}
