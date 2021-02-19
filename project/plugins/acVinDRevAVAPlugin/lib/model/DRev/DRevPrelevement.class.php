<?php

/**
 * Model for DRevPrelevement
 *
 */
class DRevPrelevement extends BaseDRevPrelevement {

    public function getConfig() {

        return $this->getDocument()->getConfiguration()->get($this->getHashProduit());
    }

    public function getConfigProduits() {

        return $this->getConfig()->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function addLotProduit($hash) {
        $this->getDocument()->addLotProduit($hash, $this->getPrefix());
    }

    public function initLots() {
        foreach ($this->getConfigProduits() as $produit) {
            $this->addLotProduit($produit);
        }
    }

    public function getChai() {
        return $this->getDocument()->chais->getOrAdd($this->getKeyType());
    }

    public function hasLots($vtsgn = false, $horsvtsgn = false) {
        foreach ($this->lots as $lot) {
            if ($lot->hasLots($vtsgn, $horsvtsgn)) {
                return true;
            }
        }

        return false;
    }

    public function getDateObject() {
        if (!$this->date) {

            return null;
        }

        return new DateTime($this->date);
    }

    public function getDateFr() {
        $date = $this->getDateObject();

        if (!$date) {

            return null;
        }

        return $date->format('d/m/Y');
    }

    public function getPrefix() {
        preg_match("/^(.+_)/", $this->getKey(), $matches);

        return $matches[1];
    }

    public function reorderByConf() {
        if(!count($this->lots)) {

            return;
        }

        $hashes_by_hash_produit = array();
        $children_by_key = array();

        foreach ($this->lots as $lot) {
            $children_by_key[$lot->getKey()] = $lot->getData();
            $hashes_by_hash_produit[$lot->hash_produit] = $lot->getKey();
        }

        foreach ($hashes_by_hash_produit as $key) {
            $this->lots->remove($key);
        }

        foreach ($this->getConfigProduits() as $hash_produit => $child) {
            if (!array_key_exists($hash_produit, $hashes_by_hash_produit)) {
                continue;
            }
            $key = $hashes_by_hash_produit[$hash_produit];

            $this->lots->add($hashes_by_hash_produit[$hash_produit], $children_by_key[$key]);
        }
    }

    public function getLibelleProduit() {
        if ($this->_get('libelle_produit') === null) {
            try {
                $this->libelle_produit = $this->getConfig()->getLibelle();
            } catch (Exception $e) {
                $this->libelle_produit = "VT / SGN";
            }
        }

        return $this->_get('libelle_produit');
    }

    public function getLibelle() {
        if ($this->_get('libelle') === null) {

            $this->libelle = DRev::$prelevement_libelles[$this->getKeyType()];
        }

        return $this->_get('libelle');
    }

    public function getLibelleProduitType() {
        if ($this->_get('libelle_produit_type') === null) {
            if (isset(DRev::$prelevement_libelles_produit_type[$this->getKey()])) {
                $this->libelle_produit_type = DRev::$prelevement_libelles_produit_type[$this->getKey()];
            } else {
                $this->libelle_produit_type = DRev::$prelevement_libelles_produit_type[$this->getKeyType()];
            }
        }

        return $this->_get('libelle_produit_type');
    }

    public function getKeyType() {
        if (preg_match("/" . DRev::CUVE . "/", $this->getKey())) {

            return DRev::CUVE;
        }

        return DRev::BOUTEILLE;
    }

    public function getHashProduit() {

        return "/declaration/certification/genre/" . preg_replace("/^(.+_)/", "appellation", $this->getKey());
    }

    public function cleanLots() {
        $lots_to_delete = array();

        foreach($this->lots as $lot) {
            if($lot->isCleanable()) {
                $lots_to_delete[] = $lot->getKey();
            }
        }

        foreach($lots_to_delete as $key) {
            $this->lots->remove($key);
        }
    }

    public function updateLotsVolumeRevendique() {
        if(!count($this->lots)) {
            return;
        }

        foreach($this->lots as $lot) {
            $lot->volume_revendique = 0;
        }

        foreach($this->getDocument()->declaration->getProduitsCepage() as $produit) {
            $hash = str_replace('/', '_', $produit->getConfig()->getHashRelation('lots'));

            if(!$this->lots->exist($hash)) {
                continue;
            }

            $lot = $this->lots->get($hash);

            if($lot->nb_hors_vtsgn) {
                $lot->volume_revendique += $produit->volume_revendique;
            }

            if($lot->nb_vtsgn) {
                $lot->volume_revendique += $produit->volume_revendique_vt + $produit->volume_revendique_sgn;
            }
        }

        foreach($this->lots as $lot) {
            $lot->volume_revendique = round($lot->volume_revendique, 2);
        }
    }

    public function updateTotal() {
        if($this->getKey() == DRev::BOUTEILLE_VTSGN) {

            return;
        }

        $this->total_lots = 0;
        foreach ($this->lots as $produit_lot) {
            $nb_hors_vtsgn = ($produit_lot->exist('nb_hors_vtsgn') && $produit_lot->nb_hors_vtsgn) ?
                    $produit_lot->nb_hors_vtsgn : 0;
            $total = $nb_hors_vtsgn;
            $this->total_lots += $total;
        }
    }

    public function updatePrelevement() {
        $this->updateTotal();
        $this->updateLotsVolumeRevendique();
    }

    public function getNbLotsMinimum(){
        $nb = 0;
        foreach($this->getDocument()->getDeclaration()->getProduitsCepage() as $produit) {
            if(!$produit->volume_revendique){
                continue;
            }
            $cepage = $produit->getCepage();
            $hash = $this->getDocument()->getConfiguration()->get($cepage->getHash())->getHashRelation('lots');
            if(DRev::CUVE . $this->getDocument()->getPrelevementsKeyByHash($hash) != $this->getKey()) {
                continue;
            }

            $nb++;
        }
        return $nb;
    }

}
