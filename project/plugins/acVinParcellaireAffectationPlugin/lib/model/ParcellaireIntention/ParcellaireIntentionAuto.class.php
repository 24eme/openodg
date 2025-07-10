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
        $potentiel = PotentielProduction::cacheCreatePotentielProduction($parcellaire);

        $produitsCepagesAutorises = [];
        foreach($parcelles as $pid => $parcelle) {
            $produits = $potentiel->getProduitsFromParcelleId($pid);
            if (!count($produits)) {
                continue;
            }
            foreach ($produits as $hash) {
                $node = $this->declaration->add($hash);
                $node = $node->detail->add($pid.'-'.str_replace('/', '-', $hash));
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
