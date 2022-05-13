<?php

class DRevDeclaration extends BaseDRevDeclaration
{
	public function getConfig()
	{
		return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
	}

	public function reorderByConf() {
		$children = array();

		foreach($this as $hash => $child) {
			$children[$hash] = $child->getData();
		}

		foreach($children as $hash => $child) {
			$this->remove($hash);
		}

		foreach($this->getConfig()->getProduits() as $hash => $child) {
			$hashProduit = str_replace("/declaration/", "", $hash);
			if(!array_key_exists($hashProduit, $children)) {
				continue;
			}
			$this->add($hashProduit, $children[$hashProduit]);
		}
	}

	public function cleanNode() {
		$hash_to_delete = array();
		foreach($this->getProduits() as $produit) {
			if($produit->isCleanable()) {
				$hash_to_delete[] = $produit->getHash();
			}
		}

		foreach($hash_to_delete as $hash) {
			$this->getDocument()->remove($hash);
		}

		$hash_to_delete = array();
		foreach($this as $children) {
			if(count($children) > 0) {
				continue;
			}

			$hash_to_delete[] = $children->getHash();
		}

		foreach($hash_to_delete as $hash) {
			$this->getDocument()->remove($hash);
		}
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

	public function getSyndicats() {
		$syndicats = array();
		foreach (RegionConfiguration::getInstance()->getOdgRegions() as $region) {
			if(!count($this->getProduitsByRegion($region))) {
					continue;
			}
			$syndicats[] = $region;
		}
		return $syndicats;
	}

	public function getProduits($region = null)
    {
		if($region) {

			return $this->getProduitsByRegion($region);
		}

        $produits = array();
        foreach($this as $items) {
			foreach($items as $item) {
	            $produits[$item->getHash()] = $item;
			}
        }

        return $produits;
    }

	public function getProduitsWithoutLots($region = null){
		if($region){

			return $this->getProduitsWithoutLotsByRegion($region);
		}

		$produits = array();

		foreach ($this->getProduits() as $produit) {
			if($produit->getConfig()->isRevendicationParLots()){
				continue;
			}
			$produits[$produit->getHash()] = $produit;
		}

		foreach (RegionConfiguration::getInstance()->getOdgRegions() as $region) {
			$produitsByRegion = $this->getProduitsWithoutLotsByRegion($region);
			foreach($produitsByRegion as $hash => $produit) {
				unset($produits[$hash]);
			}
			uasort($produitsByRegion, "DrevDeclaration::sortByLibelle");
			$produits = array_merge($produits,$produitsByRegion);
		}

		return $produits;
	}

	protected function getProduitsWithoutLotsByRegion($region) {
		$produits = array();
		foreach ($this->getProduits($region) as $produit) {
			if($produit->getConfig()->isRevendicationParLots()){

				continue;
			}
			$produits[$produit->getHash()] = $produit;
		}
		return $produits;
	}


    public function getProduitsVci($region = null)
    {
        $produitsVci = array();
        $produits = $this->getProduits($region);
        foreach($produits as $produit) {
            if(!$produit->hasVci()) {

				continue;
            }
            $produitsVci[$produit->getHash()] = $produit;
        }
				uasort($produits, "DrevDeclaration::sortByLibelle");
        return $produitsVci;
    }

	public function getProduitsLots($region = null)
    {
        $produits = array();
        foreach($this->getProduits($region) as $produit) {
            if(!$produit->getConfig()->isRevendicationParLots()) {
                continue;
            }
            $produits[$produit->getHash()] = $produit;
        }
				uasort($produits, "DrevDeclaration::sortByLibelle");
        return $produits;
    }

    public function hasVciDetruit()
    {
    	$has = false;
    	$produits = $this->getProduits();
    	foreach($produits as $produit) {
    		if($produit->hasVciDetruit()) {
    			$has = true;
    			break;
    		}
    	}
    	return $has;
    }

    public function removeVolumeRevendique() {

        foreach($this->getProduits() as $produit) {
            $produit->detail->volume_sur_place = 0;
            $produit->detail->volume_sur_place_revendique = 0;
            $produit->detail->superficie_vinifiee = 0;
            $produit->detail->usages_industriels_sur_place = 0;
            if($produit->exist('detail_vtsgn')) {
                $produit->detail_vtsgn->volume_sur_place = 0;
                $produit->detail_vtsgn->volume_sur_place_revendique = 0;
                $produit->detail_vtsgn->usages_industriels_sur_place = 0;
                if($produit->detail_vtsgn->exist('superficie_vinifiee')) {
                    $produit->detail_vtsgn->superficie_vinifiee = 0;
                }
            }
            $produit->updateRevendiqueFromDetail();
        }

        foreach($this->getProduitsCepage() as $detail) {
            $detail->resetRevendique();
        }

    }

    public function hasVtsgn() {
        foreach($this->getProduits() as $produit) {
            if($produit->canHaveVtsgn() && $produit->volume_revendique_vtsgn) {

                return true;
            }
        }
        foreach($this->getProduitsCepage() as $produit) {
            if($produit->hasVtsgn()) {

                return true;
            }
        }

        return false;
    }

	public function getTotalTotalSuperficie()
    {
    	$total = 0;
        foreach($this->getProduits() as $key => $item) {
            $total += $item->getTotalTotalSuperficie();
        }
        return $total;
    }

	public function getTotalVolumeRevendique()
    {
    	$total = 0;
        foreach($this->getProduits() as $key => $item) {
            $total += $item->getTotalVolumeRevendique();
        }
        return $total;
    }

	public function getTotalSuperficieVinifiee()
    {
    	$total = 0;
        foreach($this->getProduits() as $key => $item) {
            $total += $item->getTotalSuperficieVinifiee();
        }
        return $total;
    }

	public function getTotalVolumeRevendiqueVCI()
    {
		$total = 0;
        foreach($this->getProduits() as $key => $item) {
            $total += $item->volume_revendique_issu_vci;
        }
        return $total;
	}

	public function getTotalVolumeRevendiqueMutage() {
		$total = 0;
        foreach($this->getProduits() as $key => $item) {
            $total += $item->volume_revendique_issu_mutage;
        }
        return $total;
	}

	public static function sortByLibelle($p1,$p2){
		return strcmp($p1->getLibelle(), $p2->getLibelle());
	}


}
