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
        foreach($parcelles as $pid => $parcelle) {
            if ( !in_array($this->getDenominationAire(),  array_keys($parcelle->getIsInAires())) &&
                 !in_array(AireClient::PARCELLAIRE_AIRE_GENERIC_AIRE,  array_keys($parcelle->getIsInAires())) )
            {
                continue;
            }
            $node = $this->declaration->add($this->getDenominationAireHash());
            $node->libelle = $this->getDenominationAire();
            $node = $node->detail->add($pid);
            ParcellaireClient::CopyParcelle($node, $parcelle, true);
            $parcelle->produit_hash = $this->getDenominationAireHash();
            $node->affectation = 1;
        }
    }

    public function getDenominationAire() {
        return ParcellaireConfiguration::getInstance()->affectationDenominationAire();
    }

    public function getDenominationAireHash() {
        return ParcellaireConfiguration::getInstance()->affectationDenominationAireHash();
    }


}
