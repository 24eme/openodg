<?php
/**
 * Model for FacturePaiement
 *
 */

class FacturePaiement extends BaseFacturePaiement {
    public function setVersementComptable($b){
        $this->_set('versement_comptable', $b);
        $this->getDocument()->updateVersementComptablePaiement();
    }
}