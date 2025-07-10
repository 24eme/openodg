<?php
/**
 * Model for ParcellaireAffectationDeclaration
 *
 */

class ParcellaireAffectationDeclaration extends BaseParcellaireAffectationDeclaration {

    public function getParcellesByCommune($onlyAffectee = true, $hashproduitFilter = null) {
        $parcelles = array();

        foreach($this->getParcelles($hashproduitFilter) as $hash => $parcelle) {
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
            if(!$parcelle->getDgc()) {
                continue;
            }
            $key = $parcelle->getDgcLibelle();
            if ($onlyAffectee && !$parcelle->affectee) {
                continue;
            }

            if(!isset($parcelles[$key])) {
                $parcelles[$key] = array();
            }
            $parcelles[$key][$parcelle->getParcelleId()] = $parcelle;
          }
        }
        ksort($parcelles);
        foreach(array_keys($parcelles) as $k) {
            ksort($parcelles[$k]);
        }
        return $parcelles;
    }

    public function getParcellesByProduit($onlyAffectee = false) {
         $parcelles = array();
         foreach($this as $keyProduit => $produit) {
          foreach ($produit->detail as $parcelle) {
            $key = $produit->libelle.' — '.$parcelle->commune;
            if ($onlyAffectee && !$parcelle->affectee) {
                 continue;
             }
            if(!isset($parcelles[$key])) {
                $parcelles[$key] = array();
            }
            $parcelles[$key][$parcelle->getParcelleId()] = $parcelle;
          }
        }
        ksort($parcelles);
        return $parcelles;
    }

    public function getParcelles($hashproduitFilter = null) {
        $parcelles = array();
        foreach($this as $keyProduit => $produit) {
            if ($hashproduitFilter && $keyProduit != $hashproduitFilter) {
                continue;
            }
            foreach ($produit->detail as $parcelle) {
                $parcelles[$parcelle->getParcelleKeyId()] = $parcelle;
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

    public function getProduits()
    {
        $produits = [];
        foreach($this as $hash => $produit) {
            $produits[$hash] = $produit->libelle;
        }
        ksort($produits);
        return $produits;
    }
}
