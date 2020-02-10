<?php
/**
 * Model for ParcellaireAffectationDeclaration
 *
 */

class ParcellaireAffectationDeclaration extends BaseParcellaireAffectationDeclaration {

    public function getParcellesByCommune() {
        $parcelles = array();

        foreach($this->getParcelles() as $parcelle) {
            if(!isset($parcelles[$parcelle->commune])) {
                $parcelles[$parcelle->commune] = array();
            }
            $parcelles[$parcelle->commune][$parcelle->getHash()] = $parcelle;
        }

        ksort($parcelles);
        return $parcelles;
    }

    public function getParcelles() {
        $parcelles = array();
        foreach($this as $produit) {
            foreach ($produit->detail as $parcelle) {
                $parcelles[$parcelle->getHash()] = $parcelle;
            }
        }

        return $parcelles;
    }

    public function setHash($commune_dgc){
        foreach($this as $hash => $produit) {
            
            foreach ($produit->detail as $parcelle) {
                //var_dump($hash);
                if(in_array($parcelle->get("code_commune"), $commune_dgc)){
                    var_dump($parcelle->commune);
                }
                
            }
        }
    }
}
