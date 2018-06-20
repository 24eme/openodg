<?php
/**
 * Model for HabilitationHistorique
 *
 */

class HabilitationDemande extends BaseHabilitationDemande {

    public function getConfig()
    {
        return $this->getDocument()->getConfiguration()->get($this->getProduitHash());
    }

    public function setProduitHash($hash) {
        $this->_set('produit_hash', $hash);

        $this->produit_libelle = $this->getConfig()->getLibelleComplet();

        return $this;
    }
}
