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
        $produitsCepagesAutorises = [];
        foreach($parcelles as $pid => $parcelle) {
            if ( !in_array($this->getDenominationAire($parcelle->getProduitLibelle()),  array_keys($parcelle->getIsInAires())) &&
                 !in_array(AireClient::PARCELLAIRE_AIRE_GENERIC_AIRE,  array_keys($parcelle->getIsInAires()))) {
                continue;
            }
            $hashes = $this->getDenominationAireHash();
            $nbHashes = count($hashes);
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
                if ($nbHashes > 1) {
                    $tmp = explode('/', $hash);
                    $lastHashData = end($tmp);
                    $newPid = strtoupper($lastHashData).'-'.$pid;
                } else {
                    $newPid = $pid;
                }
                $node = $this->declaration->add($hash);
                $node->libelle = $this->getDenominationAire();
                $node = $node->detail->add($newPid);
                ParcellaireClient::CopyParcelle($node, $parcelle, true);
                $node->produit_hash = $hash;
                $node->parcelle_id = $newPid;
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
