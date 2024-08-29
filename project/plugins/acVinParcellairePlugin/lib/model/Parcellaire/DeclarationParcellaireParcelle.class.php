<?php

abstract class DeclarationParcellaireParcelle extends ParcellaireParcelle {

    public function getProduit() {

        return $this->getParent()->getParent();
    }

    public function getProduitLibelle() {

        return $this->getProduit()->getLibelle();
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }

    public function getCepageLibelle() {

        return $this->getCepage();
    }

    public function getLieuNode() {

        return $this->getProduit()->getConfig()->getLieu();
    }

    public function getParcelleId() {
        if (!$this->_get('parcelle_id')) {
            if ($this->numero_ordre && $this->idu) {
                $this->_set('parcelle_id', sprintf('%s-%02d', $this->idu, $this->numero_ordre));
            }else {
                $p = null;
                if ($this->getDocument()->getParcellaire()) {
                    $p = ParcellaireClient::getInstance()->findParcelle($this->getDocument()->getParcellaire(), $this, 0);
                }
                if (!$p) {
                    throw new sfException('no parcelle id found for '.$this->getHash());
                }
                $this->_set('parcelle_id', $p->getParcelleId());
            }
        }
        return $this->_get('parcelle_id');
    }

    public function getProduitHash() {

        if ($this->_get('produit_hash')) {
            return $this->_get('produit_hash');
        }
        return $this->getParent()->getParent()->getHash();
    }

    public function getNumeroOrdre() {
        if (preg_match('/^[A-Z].*-([0-9][0-9])$/', $this->getKey(), $m)) {
            $this->_set('numero_ordre', $m[1]);
        }elseif (preg_match('/'.$this->numero_parcelle.'-([0-9][0-9])-/', $this->getKey(), $m)) {
            $this->_set('numero_ordre', $m[1]);
        }
        return $this->_get('numero_ordre');
    }

    public function getParcelleFromParcellaire() {
        return $this->getDocument()->getParcelleFromParcellaire($this->getParcelleId());
    }

    public function getSuperficieParcellaire() {

        $p = $this->getParcelleFromParcellaire();
        if (!$p) {
            if (!$this->_get('superficie_parcellaire')) {
                if ($this->exist('superficie')) {
                    $this->set('superficie_parcellaire', $this->_get('superficie'));
                }elseif ($this->exist('superficie_affectation')) {
                    $this->set('superficie_parcellaire', $this->superficie_affectation);
                }
            }
        } else {
            if ($this->_get('superficie_parcellaire') != $p->getSuperficieParcellaire()) {
                $this->set('superficie_parcellaire', $p->getSuperficieParcellaire());
            }
        }
        return $this->_get('superficie_parcellaire');
    }

    public function isExistingParcelle($docid = null) {
        if (strpos($this->getHash(), '/declaration/') !== false) {
            return false;
        }
        if ($docid && ($docid != $this->getDocument()->_id)) {
            return false;
        }
        return true;
    }

    public function getSuperficie() {
        $s = $this->_get('superficie');
        $p = $this->getSuperficieParcellaire();
        if($s > $p) {
            $this->set('superficie', $p);
        }
        return $this->_get('superficie');
    }

}
