<?php

/**
 * Model for ParcellaireDeclaration
 *
 */
class ParcellaireAffectationDeclaration extends BaseParcellaireDeclaration {

    public function getAppellations() {

        return null;
    }

    public function getConfig()
  	{
  		return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
  	}

    public function getAppellationsOrderParcellaire() {
        $appellations = $this->getAppellations();

        $appellationOrdered = array();

        if(!$appellations) {
            return $appellationOrdered;
        }

        foreach (ParcellaireClient::getInstance()->getAppellationsKeys($this->getDocument()->getTypeParcellaire()) as $app_key => $app_name) {
           if(array_key_exists('appellation_'.$app_key, $appellations->toArray(1,0))){
               $appellationOrdered['appellation_'.$app_key] = $appellations['appellation_'.$app_key];
           }
        }

        return $appellationOrdered;
    }

    public function getParcellesByCommune() {
        $parcelles = array();

        foreach($this as $produit) {
            foreach ($produit->detail as $parcelle) {
                if(!isset($parcelles[$parcelle->commune])) {
                    $parcelles[$parcelle->commune] = array();
                }
                $parcelles[$parcelle->commune][$parcelle->getHash()] = $parcelle;
            }
        }

        ksort($parcelles);

        return $parcelles;
    }

    public function getProduits($onlyActive = false) {
        $produits = array();
        foreach ($this as $key => $produit) {
            if ($onlyActive && !$produit->isAffectee()) {

                return array();
            }
            $produits[$produit->getHash()] = $produit;
        }

        return $produits;
    }

    public function getProduitsCepageDetails($onlyVtSgn = false, $active = false) {
        $produits = array();

        return $produits;
    }

    public function getProduitsDetails($onlyVtSgn = false, $active = false) {
        $details = array();
        foreach ($this->getProduits() as $item) {
            $details = array_merge($details, $item->getProduitsDetails($onlyVtSgn, $active));
        }

        return $details;
    }

    public function getProduitsWithLieuEditable()
    {
        return array();
        $produits = array();
        foreach($this->getProduits() as $hash => $produit) {
            if(!count($produit->detail)) {
                continue;
            }

            $lieu_editable = $produit->getLieuxEditable();
            if(!count($lieu_editable)) {

                $produits[$hash] = $produit;
            }

            foreach($produit->getLieuxEditable() as $lieu_key => $lieu) {
                $produits[str_replace("/lieu/", "/lieu".$lieu_key."/", $hash)] = $produit;
            }
        }

        return $produits;
    }

    public function getLieuxEditable() {
        $lieux = array();

        foreach ($this->getProduitsDetails() as $detail) {
            if(!$detail->lieu) {
                continue;
            }

            $lieux[KeyInflector::slugify(trim($detail->lieu))] = $detail->lieu;
        }

        return $lieux;
    }

    public function getLieux() {
        if (!$this->exist('certification')) {
            return array();
        }
        $lieuArray = array();
        foreach ($this->getAppellations() as $appellationKey => $appellation) {
            foreach ($appellation->getMentions() as $mentionKey => $mention) {
                foreach ($mention->getLieux() as $lieuKey => $lieu) {
                    $lieuArray[$lieu->getHash()] = $lieu;
                }
            }
        }
        return $lieuArray;
    }

    public function getProduitsDetailsSortedByParcelle($byfullkey = true) {
        $parcelles = $this->getProduitsDetails();
        if ($byfullkey) {
            usort($parcelles, 'ParcellaireDeclaration::sortParcellesByFullKey');
        }else{
            usort($parcelles, 'ParcellaireDeclaration::sortParcellesByCommune');
        }
        return $parcelles;
    }

    public function getAcheteursNode($lieu = null, $cviFilter = null) {
        $acheteurs = array();
        foreach($this->getProduits() as $produit) {
            $acheteursParcelle = $produit->getAcheteursNode($lieu, $cviFilter);
            if(count($acheteursParcelle) == 0) {
                continue;
            }

            $acheteurs = array_merge_recursive($acheteurs, $acheteursParcelle);
        }

        return $acheteurs;
    }

    public function cleanNode() {
        $hash_to_delete = array();
        foreach ($this->getProduits() as $produit) {
            $produit->cleanNode();
            if ($produit->isCleanable()) {
                $hash_to_delete[] = $produit->getHash();
            }
        }

        foreach ($hash_to_delete as $hash) {
            $this->getDocument()->remove($hash);
        }
    }

    public function hasVtsgn() {
        foreach ($this->getProduitsDetails() as $detail) {
            if ($detail->getVtsgn()) {

                return true;
            }
        }

        return false;
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

    public function getPreviousAppellationKey() {

        return null;
    }

    public function getNextAppellationKey() {

        return null;
    }

    static function sortParcellesByFullKey($detail0, $detail1) {
        return strcmp($detail0->getLibelleComplet().' '.$detail0->getParcelleIdentifiant(),
        $detail1->getLibelleComplet().' '.$detail1->getParcelleIdentifiant());
    }

    static function sortParcellesByCommune($detail0, $detail1) {
        return strcmp($detail0->getParcelleIdentifiant().' '.$detail0->getLibelleComplet(),
        $detail1->getParcelleIdentifiant().' '.$detail1->getLibelleComplet());
    }

}
