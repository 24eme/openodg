<?php

class DRevDeclaration extends BaseDRevDeclaration
{

	public function getChildrenNode()
    {
        return $this->getCertifications();
    }

    public function getCertifications()
    {
        return $this->filter('^certification');
    }

    public function getAppellations()
    {
        if(!$this->exist('certification')) {
        	return array();
        }
        return $this->getChildrenNodeDeep(2)->getAppellations();
    }

	public function hasVolumeRevendiqueInCepage() {
		foreach ($this->getProduitsCepage() as $produit) {
			if($produit->volume_revendique_total > 0) {

				return true;
			}
		}
	}

	public function reorderByConf() {
		$children = array();

		foreach($this->getChildrenNode() as $hash => $child) {
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
		foreach($this->getChildrenNode() as $children) {
			if($children->isCleanable()) {
				$hash_to_delete[] = $children->getHash();
			}
		}

		foreach($hash_to_delete as $hash) {
			$this->getDocument()->remove($hash);
		}
	}

}
