<?php
/**
 * Model for ParcellaireAffectationProduit
 *
 */

class ParcellaireAffectationProduit extends BaseParcellaireAffectationProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    public function getSuperficieTotale() {
        $superficie = 0;
        foreach($this->detail as $parcelle) {
            if(!$parcelle->isAffectee()) {
                continue;
            }
            $superficie += $parcelle->getSuperficie();
        }
        return $superficie;
    }

}
