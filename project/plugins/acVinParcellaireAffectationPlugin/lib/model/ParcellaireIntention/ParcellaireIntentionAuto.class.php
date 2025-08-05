<?php

class ParcellaireIntentionAuto extends ParcellaireIntentionAffectation {
    public function save() {
        throw new sfException('Cannont save ParcellaireIntentionAuto');
    }

    public function getParcellaire2Reference() {
        return $this->getParcellaire();
    }

    public function updateParcelles() {
        $parcellaire = $this->getParcellaire2Reference();
        if (!$parcellaire) {
            return ;
        }
        $parcelles = $parcellaire->getParcelles();
        $this->remove('declaration');
        $this->add('declaration');

        $pid_produits_to_add = [];
        $potentiel = PotentielProduction::cacheCreatePotentielProduction($parcellaire);
        foreach($parcelles as $pid => $parcelle) {
            $produits = $potentiel->getProduitsFromParcelleId($pid);
            if (!count($produits)) {
                continue;
            }
            $pid_produits_to_add[$pid] = $produits;
        }
        if (!count($pid_produits_to_add)) {
            $hashes = $this->getDenominationAireHash();
            $produitsCepagesAutorises = [];
            foreach($parcelles as $pid => $parcelle) {
                foreach ($hashes as $hash) {
                    if (!isset($produitsCepagesAutorises[$hash])) {
                        $produitsCepagesAutorises[$hash] = [];
                        foreach ($this->getConfiguration()->declaration->get($hash)->getProduitsAll() as $confProduit) {
                            $produitsCepagesAutorises[$hash] = array_unique(array_merge($produitsCepagesAutorises[$hash], $confProduit->getCepagesAutorises()->toArray(true,false)));
                        }
                    }
                    if (count($produitsCepagesAutorises[$hash]) > 0 && !in_array($parcelle->cepage, $produitsCepagesAutorises[$hash])) {
                        continue;
                    }
                    if (!isset($pid_produits_to_add[$pid])) {
                        $pid_produits_to_add[$pid] = [];
                    }
                    $pid_produits_to_add[$pid][] = $hash;
                }
            }
        }
        foreach ($pid_produits_to_add as $pid => $produits) {
            $parcelle = $parcelles[$pid];
            foreach ($produits as $hash) {
                $node = $this->declaration->add($hash);
                $node = $node->detail->add($pid);
                ParcellaireClient::CopyParcelle($node, $parcelle, true);
                $parcelle->produit_hash = $hash;
                $node->affectation = 1;
            }
        }
    }

    public function getDenominationAire() {
        return ParcellaireConfiguration::getInstance()->affectationDenominationAire();
    }

    public function getDenominationAireHash() {
        $value = ParcellaireConfiguration::getInstance()->affectationDenominationAireHash();
        return (is_array($value))? $value : [$value];
    }

}
