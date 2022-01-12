<?php
/**
 * Model for ParcellaireAffectationDeclaration
 *
 */

class ParcellaireAffectationDeclaration extends BaseParcellaireAffectationDeclaration {

    public function getParcellesByCommune($onlyAffectee = false) {
        $parcelles = array();

        foreach($this->getParcelles() as $hash => $parcelle) {
            if ($onlyAffectee && !$parcelle->affectee) {
                continue;
            }
            if(!isset($parcelles[$parcelle->commune])) {
                $parcelles[$parcelle->commune] = array();
            }
            $parcelles[$parcelle->commune][$parcelle->getHash()] = $parcelle;
        }

        ksort($parcelles);
        return $parcelles;
    }

    public function getParcellesByDgc($onlyAffectee = false) {
        $parcelles = array();

        foreach($this as $keyProduit => $produit) {
          foreach ($produit->detail as $parcelle) {
            $key = str_replace(" ", "-", $parcelle->getDgcLibelle());

            if ($onlyAffectee && !$parcelle->affectee) {
                continue;
            }

            if(!isset($parcelles[$key])) {
                $parcelles[$key] = array();
            }
            $parcelles[$key][$parcelle->commune.$parcelle->section.sprintf('%06d', $parcelle->numero_parcelle).$parcelle->getHash()] = $parcelle;
          }
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
                if(in_array($parcelle->get("code_commune"), $commune_dgc)){
                    var_dump($parcelle->commune);
                }

            }
        }
    }
}
