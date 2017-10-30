<?php
/**
 * Model for HabilitationDeclaration
 *
 */

class HabilitationDeclaration extends BaseHabilitationDeclaration {

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

        foreach($this->getDocument()->getProduitsConfig() as $hash => $child) {
            $hashProduit = str_replace("/declaration/", "", $hash);
            if(!array_key_exists($hashProduit, $children)) {
                continue;
            }
            $this->add($hashProduit, $children[$hashProduit]);
        }
    }

    public function cleanNode() {
        $hash_to_delete = array();
        foreach($this as $child) {
            if($child->isCleanable()) {
                $hash_to_delete[] = $child->getHash();
            }
        }

        foreach($hash_to_delete as $hash) {
            $this->getDocument()->remove($hash);
        }
    }

    public function getProduits($onlyActive = false)
    {
        $produits = array();
        foreach($this as $key => $item) {
			if ($onlyActive && !$item->isActive()) {

	    		continue;
	    	}
            $produits[$item->getHash()] = $item;
        }

        return $produits;
    }


}
